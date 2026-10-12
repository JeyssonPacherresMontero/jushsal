# PRP-003 · Padrón de usuarios de agua (RADA) y notificaciones de convocatoria

**Proyecto:** JUSHSAL · **Stack:** PHP 8 + MySQL (XAMPP) · **Fase:** 3 · **Estado:** BORRADOR — bloqueado por T0 · **Confianza:** 6/10 (sube a 8/10 al cerrar T0)

## 1. Goal
Registrar el **padrón de usuarios de agua** (con sus predios, comisión y estado de habilidad), importarlo desde el Excel actual y permitir a Comunicaciones **notificar convocatorias a los usuarios por comisión** de forma controlada (con consentimiento), sin exponer datos personales.

## 2. Why
La convocatoria real establece que la Asamblea General la integran los **usuarios hábiles del padrón elaborado en base al RADA**, de **16 sub sectores**. Hoy la convocatoria llega solo por Facebook/web/popup; con el padrón se puede avisar a cada comisión y llevar un registro de a quién se avisó.

## 3. What
1. **T0 – Levantamiento** (obligatorio): cerrar `docs/FASE3_CUESTIONARIO.md`, ajustar `database/003_fase3_BORRADOR.sql` → `003_fase3.sql`.
2. Rol `padron` + permisos (matriz abajo).
3. CRUD de usuarios de agua, predios y representantes (`/admin/padron/`).
4. Importación CSV con vista previa y reporte de errores.
5. Habilidad (hábil/no hábil) manual con motivo y fecha.
6. Notificaciones: armar envío por comisión (o todas) para una convocatoria; lista de destinatarios con consentimiento; envío **asistido por WhatsApp (enlaces `wa.me`)** y/o correo; estado por destinatario.
7. Reportes: padrón por comisión, hábiles/no hábiles, destinatarios de cada envío (CSV con control de rol).
8. (Opcional, si T0 lo confirma) acreditación de representantes y asistencia a asamblea.

### Matriz de permisos
| Función | admin | padron | comunicaciones |
|---|---|---|---|
| Ver/editar padrón completo (DNI, dirección) | ✔ | ✔ | ✘ |
| Importar / exportar padrón | ✔ | ✔ | ✘ |
| Ver destinatarios (nombre, comisión, consentimiento) | ✔ | ✔ | ✔ |
| Crear/enviar notificaciones | ✔ | ✘ | ✔ |
| Bitácora | ✔ | ✘ | ✘ |

### Criterios de éxito
- [ ] T0 cerrado: cada campo de `usuarios_agua`/`predios` está justificado por el cuestionario.
- [ ] Importar un CSV de 500 filas [DEMO] en < 30 s; las filas con error (DNI inválido, duplicado, comisión inexistente) se listan con n.º de fila y **no** bloquean las válidas tras confirmar.
- [ ] `comunicaciones` no puede abrir `/admin/padron/*` (403) ni ve DNI en ninguna pantalla ni respuesta HTTP.
- [ ] Ningún dato del padrón en HTML público ni en `api/` pública (`view-source`, pestaña Network).
- [ ] Un usuario sin `acepta_notificaciones` o sin celular queda **omitido** con el motivo registrado.
- [ ] Un envío a la comisión "Hualtaco III" solo genera destinatarios de esa comisión.
- [ ] Ver DNI, importar y exportar quedan en `bitacora`.
- [ ] El enlace `wa.me/51XXXXXXXXX?text=…` se genera con número normalizado (+51, 9 dígitos) y mensaje URL-encoded.
- [ ] `php -l` sin errores; responsive 375 px / 1280 px.

## 4. Contexto
### Leer primero
`AGENTS.md`, `.agents/rules/*` (incluida `datos-personales-padron.md`), `docs/ESTRUCTURA.md`, `database/schema.sql`, `database/003_fase3.sql`, `docs/FASE3_CUESTIONARIO.md`, PRP-001 (módulo convocatorias).
### Decisiones ya tomadas (no reabrir sin avisar)
- **Excel → CSV UTF-8**: no se usan librerías de Excel (sin Composer). El usuario exporta el CSV.
- **WhatsApp:** Fase 3 usa envío **asistido** (el operador hace clic en cada enlace `wa.me`). Una API de WhatsApp Business es costosa y requiere verificación/plantillas: queda como mejora futura, no se implementa sin aprobación.
- **Correo:** `mail()` de XAMPP no es confiable. Solo implementar correo si el hosting definitivo tiene SMTP confirmado (pregunta 12); si no, omitir y dejarlo documentado.
- **Habilidad:** manual en esta fase (motivo + fecha). El cálculo automático por pagos pertenece a la Fase 4.
- **No interpretar ni resumir la ley** (Ley 31801, D.S. 007-2024-MIDAGRI): el sistema registra datos; las reglas de quién es hábil las define la Junta (T0).
### Gotchas
- Excel guarda DNI con ceros a la izquierda perdidos / como número científico → validar y rellenar a 8 dígitos solo si el cuestionario lo permite; en caso contrario, rechazar fila.
- CSV de Excel en Perú suele usar `;` y Windows-1252: detectar delimitador y convertir a UTF-8 (`mb_convert_encoding`).
- Normalizar celular: quitar espacios/guiones, prefijo `51` si falta, 9 dígitos que empiezan en 9.
- Importaciones grandes: procesar en transacción por lotes de 200; límite de 5 MB y 5000 filas por archivo.
- `wa.me` abre una pestaña por usuario: mostrar lista con botón "Enviar" por fila y marcar `enviado` al hacer clic (confirmación del operador), no abrir todo de golpe.
- `uploads/poderes/` debe ser **privado**: servir los archivos solo a través de un script con `requerir_rol()`, nunca por URL directa.

## 5. Blueprint (EN ORDEN; validar cada tarea)

**T0 · Levantamiento y diseño** *(sin código de aplicación)*
- Responder `docs/FASE3_CUESTIONARIO.md` conmigo. Con las respuestas: ajustar el SQL borrador, confirmar campos y relaciones (¿predio ↔ varios usuarios?), definir "hábil" y reportes.
- Entregables: `database/003_fase3.sql` final, sección "Decisiones de T0" añadida a este PRP, diagrama simple de tablas.
- ✅ Validar: yo apruebo el SQL y la lista de campos por escrito. **No pasar a T1 sin esa aprobación.**

**T1 · Base de datos y rol**
- Respaldar BD; importar `003_fase3.sql`. Crear usuario `padron` desde `admin/usuarios`. Extender `requerir_rol()` y el menú del admin según la matriz.
- ✅ Validar: tablas creadas; login con `padron` y `comunicaciones` muestra menús distintos; acceso directo por URL a `/admin/padron/` con `comunicaciones` → 403.

**T2 · CRUD de usuarios de agua**
- Listado paginado (50/pág) con búsqueda por nombre/comisión/documento (DNI enmascarado), formulario con validación (DNI 8 dígitos, RUC 11, celular normalizado), baja lógica con motivo, ficha con predios y bitácora de "ver DNI".
- ✅ Validar: alta, edición, baja; duplicado de documento rechazado; DNI enmascarado en listado.

**T3 · Predios y representantes**
- CRUD de predios dentro de la ficha; representantes con vigencia y archivo de poder (subida privada, `finfo`, ≤5 MB, servido por script con rol).
- ✅ Validar: un usuario con 2 predios; archivo de poder inaccesible por URL directa.

**T4 · Importación CSV**
- Subir → detectar delimitador/codificación → **vista previa** (primeras 20 filas + conteo de errores) → confirmar → insertar por lotes → registrar en `importaciones_padron` y `bitacora`. Plantilla CSV descargable.
- Duplicados: omitir y reportar (no sobrescribir salvo opción explícita "actualizar").
- ✅ Validar: CSV [DEMO] de 500 filas con 10 errores sembrados → 490 ok, 10 listados con n.º de fila; reimportar el mismo CSV → 0 nuevos.

**T5 · Habilidad y consentimiento**
- Marcar hábil/no hábil con motivo y fecha (historial en bitácora); gestionar consentimiento (sí/no, fecha, origen) y baja de notificaciones. Filtros en el listado.
- ✅ Validar: filtro "hábiles de Somate Alto"; sin consentimiento no aparece como destinatario.

**T6 · Notificaciones**
- Admin/comunicaciones: nuevo envío → elegir convocatoria (precarga título/fecha/lugar, mensaje editable con `{nombre}`, `{comision}`) → elegir comisión o todas → se genera `notificacion_destinatarios` (omitidos con motivo) → pantalla de envío asistido con botón `wa.me` por fila (y correo si SMTP confirmado) → estado `pendiente/enviado/fallido/omitido`.
- Comunicaciones ve solo nombre, comisión y estado; **nunca** DNI ni dirección (consultas SQL distintas por rol, no solo ocultar columnas en la vista).
- ✅ Validar: criterios de éxito sobre comisión, omitidos y enlace `wa.me`; inspeccionar la respuesta HTTP como `comunicaciones` sin DNI.

**T7 · Reportes**
- Padrón por comisión, hábiles/no hábiles, historial de envíos por convocatoria. Exportar CSV solo `admin/padron`, con bitácora.
- ✅ Validar: totales por comisión suman el total del padrón; el CSV exportado por `comunicaciones` está bloqueado.

**T8 · (Opcional) Acreditación y asistencia a asamblea** — solo si T0 lo confirma
- Registro de acreditaciones (personal o por representante, con fecha/hora de recepción) y marcado de asistencia; reporte de quórum por comisión. No interpretar plazos legales: solo registrar la fecha de recepción.
- ✅ Validar: una acreditación por usuario y convocatoria; reporte coincide con los datos cargados.

**T9 · Cierre**
- Revisión de privacidad (checklist abajo), actualizar `README.md`, `docs/ESTRUCTURA.md` y `docs/ROADMAP.md`; respaldo de BD.

## 6. Bucle de validación (PowerShell, raíz del proyecto)
```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { & "C:\xampp\php\php.exe" -l $_.FullName }
& "C:\xampp\mysql\bin\mysql.exe" -u root -e "SOURCE database/003_fase3.sql"
& "C:\xampp\mysql\bin\mysql.exe" -u root junta_bd -e "SELECT comision_id, COUNT(*) FROM usuarios_agua GROUP BY comision_id;"
```
Manual: probar con los 3 roles; `view-source` y pestaña Network en páginas públicas; usar `/validar-tarea`.

## 7. Checklist final de privacidad
- [ ] Ningún dato del padrón en rutas públicas ni en repositorio (CSV reales fuera del proyecto, `.gitignore`).
- [ ] DNI solo para `admin`/`padron`, enmascarado por defecto; lecturas y exportaciones en bitácora.
- [ ] Consentimiento respetado y baja de notificaciones disponible.
- [ ] Datos de prueba `[DEMO]`; padrón real cargado solo por la Junta en su entorno.
- [ ] Respaldo de BD hecho antes de importar el padrón real.

## 8. Anti-patrones
- Inventar columnas del padrón o criterios de habilidad. Cargar datos reales en pruebas del agente.
- Mostrar todo el padrón sin paginar. Dar a `comunicaciones` acceso al DNI "para facilitar".
- Enviar a quien no consintió. Abrir masivamente pestañas de WhatsApp.
- Implementar API de WhatsApp, pagos o cobranza (Fase 4) sin aprobación.
