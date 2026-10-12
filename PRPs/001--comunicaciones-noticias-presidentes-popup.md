# PRP-001 · Panel de Comunicaciones: noticias, historial de presidentes y popup de convocatoria

**Proyecto:** JUSHSAL · **Stack:** PHP 8 + MySQL (XAMPP) · **Fase:** 1 · **Confianza:** 8/10

## 1. Goal
Migrar la maqueta estática a PHP/MySQL y entregar un **panel `/admin`** donde el rol *Comunicaciones* publique noticias, mantenga el historial de presidentes por gestión (con lo realizado año a año) y suba convocatorias a asamblea como imagen que se muestran en **popup** en la web pública durante un tiempo configurable.

## 2. Why
- Hoy las noticias se guardan en `localStorage` del navegador de quien publica: nadie más las ve y cualquiera podría usar el botón. No sirve para una institución.
- La presidencia cambia cada periodo (actual 2026–2029); la institución necesita memoria histórica y transparencia.
- Las convocatorias (p. ej. Asamblea General Ordinaria 29/09/2026, 9:30/10:00 a.m., Sede Administrativa, La Cruceta) deben llegar a los usuarios sin depender solo de Facebook.

## 3. What (entregables)
1. BD `junta_bd` importable (`database/schema.sql` + `seed.sql`).
2. Login con roles `admin` / `comunicaciones`.
3. CRUD Noticias + páginas públicas `noticias.php`, `noticia.php`, bloque en `index.php`.
4. CRUD Gestiones presidenciales + Logros por año + página pública `presidentes.php` (línea de tiempo).
5. CRUD Convocatorias (imagen + vigencia + retraso + duración + frecuencia) + popup público.
6. Comisiones (16) desde BD en `comisiones.php`/`index.php`.
7. Bitácora básica y seguridad según `AGENTS.md` §5.

### Criterios de éxito (medibles)
- [ ] `schema.sql` y `seed.sql` se importan en phpMyAdmin sin errores; 16 comisiones y 1 gestión vigente (Miguel Castillo Vera, 2026–2029) presentes.
- [ ] Usuario `comunicaciones` crea una noticia con imagen y aparece en `index.php` y `noticias.php` solo si está **publicada**.
- [ ] `comunicaciones` **no** puede abrir `admin/usuarios/` ni `admin/bitacora.php` (redirige/403).
- [ ] Una convocatoria con vigencia activa abre el popup tras `retraso_seg`; fuera de vigencia **no** abre; con `duracion_seg>0` se cierra sola; con `una_vez_por_sesion` no reaparece al recargar.
- [ ] Subir `shell.php` renombrado a `.jpg` es **rechazado**; archivo >5 MB rechazado.
- [ ] POST sin token CSRF es rechazado.
- [ ] `presidentes.php` lista gestiones de más reciente a más antigua y, dentro de cada una, los logros agrupados por año.
- [ ] Sin `localStorage` para datos de negocio (solo para frecuencia del popup).
- [ ] Todas las páginas pasan `php -l` y se ven bien a 375 px y 1280 px.

## 4. Contexto necesario
### Archivos del repo (leer primero)
- `index.html`, `css/style.css`, `js/script.js`, `js/noticias.js`, `conexion.php` (maqueta base)
- `docs/ESTRUCTURA.md`, `database/schema.sql`, `AGENTS.md`
### Referencias externas
- Sitio actual: https://www.jusanlorenzo.org.pe/web/ (secciones: Inicio, Quiénes somos, Nuestra historia, Documentos, Galería, Noticias, Contáctanos, Comisiones, Servicio de maquinaria)
- PHP: PDO, `password_hash`, `finfo_file`, sesiones — https://www.php.net/manual/es/
- Marco legal citado en la convocatoria: Ley N.º 31801 y D.S. N.º 007-2024-MIDAGRI (arts. 42 y 47). **No** interpretar ni resumir su contenido legal en el sitio; solo enlazar los PDF de `documentos/`.
### Gotchas conocidos
- XAMPP/Windows: `upload_max_filesize` por defecto 2 MB → subir a 6M en `php.ini` y reiniciar Apache (documentarlo en README).
- La imagen de convocatoria suele ser **vertical (documento A4 escaneado/captura)**: el popup debe permitir scroll interno y mostrar enlace a imagen completa.
- `CHECK` en MySQL < 8.0.16 se ignora (MariaDB ≥10.2 sí): validar rangos también en PHP.
- Solo UNA gestión `es_vigente=1`: cambiar con transacción (`UPDATE ... SET es_vigente=0` y luego el nuevo en 1).
- Zona horaria: `date_default_timezone_set('America/Lima')` y `SET time_zone='-05:00'` en la conexión, o `NOW()` desfasará la vigencia.
- Nombres de archivo con tildes/espacios en `documentos/`: usar `rawurlencode()` al enlazar.
- No hay `.htaccess` por defecto en `uploads/`: crearlo (ver §5, tarea 2).

## 5. Blueprint de implementación (EN ORDEN; validar cada tarea)

**T1 · Base y BD**
- Crear carpetas de `docs/ESTRUCTURA.md`. Mover `conexion.php` → `includes/db.php`; credenciales a `includes/config.php` (+ `config.example.php`). Añadir zona horaria a la conexión.
- Importar `schema.sql` y `seed.sql`. Crear `database/crear_admin.php` (CLI: pide email/nombre/contraseña con `readline`, inserta con `password_hash`).
- ✅ Validar: `php -l includes/*.php`; tablas visibles en phpMyAdmin; `SELECT COUNT(*) FROM comisiones` = 16.

**T2 · Núcleo de seguridad**
- `helpers.php` (`e()`, `slugify()`, `formato_fecha()`, `flash()`), `csrf.php`, `auth.php` (login con bloqueo tras 5 intentos / 15 min, `requerir_rol()`), `upload.php`, `bitacora.php`.
- `uploads/.htaccess`:
  ```
  php_flag engine off
  <FilesMatch "\.(php|phtml|phar|php[0-9])$">
    Require all denied
  </FilesMatch>
  Options -Indexes
  ```
- `subir_imagen()`: `finfo_file` ∈ {image/jpeg, image/png, image/webp}, ≤5 MB, nombre `bin2hex(random_bytes(12)).ext`, verificar `getimagesize()`, `move_uploaded_file`.
- ✅ Validar: login correcto/incorrecto; archivo `.php` camuflado rechazado; POST sin token → 419.

**T3 · Layout y migración visual**
- Partir `index.html` en `includes/vistas/header.php` / `footer.php`; `index.php` los incluye. Mantener CSS/clases. Quitar botón y modal "Publicar noticia". Corregir textos: "+50 años"/"1980" → "Más de 63 años de servicio" (según web oficial) o dato confirmado por el usuario.
- Menú: Inicio · La Institución (Quiénes somos, Nuestra historia, **Presidentes**, Documentos, Comisiones) · Noticias · Galería · Servicios · Contáctanos.
- ✅ Validar: visual idéntico a la maqueta; sin errores 404 de assets (revisar rutas `assets/…`).

**T4 · Noticias**
- Modelo `noticias.php`: `listar_publicadas($limite,$offset)`, `obtener_por_slug()`, CRUD admin, slug único.
- Admin: listar (filtro por estado), formulario (título, categoría, resumen ≤400, contenido, imagen, fecha, estado, destacada). Público: bloque en `index.php` (6 últimas), `noticias.php` paginado (9/pág), `noticia.php`.
- Sin imagen → monograma (reutilizar `.news-monogram`). Contenido: texto plano con `nl2br(e())` (NO HTML libre en Fase 1).
- ✅ Validar: crear borrador (no visible) → publicar (visible) → archivar (desaparece); XSS `<script>` en título se muestra escapado.

**T5 · Presidentes y logros por año**
- Admin: CRUD gestiones (nombre, periodo inicio/fin, foto, semblanza, vigente) y, dentro de cada gestión, CRUD de **logros** (año dentro del periodo, título, descripción, imagen opcional, orden).
- Validar en PHP: `periodo_fin ≥ periodo_inicio`; `anio` del logro dentro de `[inicio, fin ?? año actual]`; al marcar vigente, desmarcar las demás (transacción). No permitir eliminar la gestión vigente sin confirmar.
- Público `presidentes.php`: tarjeta de la gestión vigente arriba (foto, nombre, periodo) + **línea de tiempo** de gestiones anteriores; al expandir una gestión: logros agrupados por año (`2026`, `2027`…). Enlace desde "Nuestra historia".
- Estado vacío amable: "Aún no hay logros registrados para esta gestión." (NO inventar contenido).
- ✅ Validar: crear gestión 2022–2025 [DEMO] con 2 logros en años distintos → se agrupan por año; marcar vigente cambia la jerarquía.

**T6 · Convocatorias + popup**
- Admin: formulario (título, imagen obligatoria, fecha asamblea opcional, `mostrar_desde/hasta` con `datetime-local`, `retraso_seg` 0–60, `duracion_seg` 0–300 con ayuda "0 = hasta que lo cierren", frecuencia, activo) + vista previa de la imagen + botón "Probar popup".
- Valores por defecto sugeridos: desde = ahora, hasta = fecha de asamblea (si existe), retraso 3 s, duración 0, frecuencia sesión.
- `api/convocatoria_activa.php`: `SELECT ... WHERE activo=1 AND NOW() BETWEEN mostrar_desde AND mostrar_hasta ORDER BY created_at DESC LIMIT 1`; responde JSON `{activa, id, titulo, imagen_url, retraso_seg, duracion_seg, frecuencia, version}`; cabecera `Cache-Control: no-store`.
- `assets/js/popup.js`: según `docs/ESTRUCTURA.md` §Flujo (modal accesible, Esc, clic fuera, cierre automático, frecuencia por `sessionStorage`/`localStorage` con `version`=`updated_at`). Reutilizar estilos `.modal` existentes + `.popup-convocatoria`.
- Si hay más de una vigente: mostrar la más reciente (documentar).
- ✅ Validar: ver criterios de éxito §3 (vigente/no vigente, retraso, cierre automático, frecuencia, imagen vertical con scroll en móvil).

**T7 · Comisiones**
- Modelo + `comisiones.php` y bloque en `index.php` desde BD (16, con nombre y presidente del periodo vigente **sin celular ni DNI**). Admin: editar presidente por comisión (periodo 2026–2029).
- ✅ Validar: 16 tarjetas; ningún número de teléfono en el HTML público (`view-source`).

**T8 · Admin: dashboard, usuarios, bitácora**
- Dashboard con contadores (noticias publicadas, convocatoria vigente sí/no, gestión vigente). `admin/usuarios` (solo admin): crear usuarios `comunicaciones`, activar/desactivar, resetear contraseña. `admin/bitacora.php` (solo admin).
- ✅ Validar: matriz de permisos de `AGENTS.md` §4.

**T9 · Cierre**
- `README.md` de instalación en XAMPP (pasos, `php.ini`, crear admin). Revisión final con el checklist §7. Reportar pendientes.

## 6. Bucle de validación (Windows/XAMPP · PowerShell desde la raíz del proyecto)
```powershell
# Sintaxis PHP
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { & "C:\xampp\php\php.exe" -l $_.FullName }
# Esquema y datos
& "C:\xampp\mysql\bin\mysql.exe" -u root -e "SOURCE database/schema.sql"
& "C:\xampp\mysql\bin\mysql.exe" -u root -e "SOURCE database/seed.sql"
& "C:\xampp\mysql\bin\mysql.exe" -u root junta_bd -e "SELECT COUNT(*) FROM comisiones; SELECT presidente_nombre FROM gestiones_presidenciales WHERE es_vigente=1;"
# API del popup
curl.exe -s http://localhost/jushsal/api/convocatoria_activa.php
```
Manual (navegador de Antigravity o Chrome): 375 px y 1280 px, consola sin errores. Usa `/validar-tarea`.

## 7. Checklist final
- [ ] Todo SQL con PDO preparado; toda salida escapada; CSRF en POST; roles verificados en servidor.
- [ ] Sin DNI en BD ni celulares en HTML público; sin datos inventados (seeds de prueba marcados `[DEMO]`).
- [ ] `config.php` no versionado; `uploads/.htaccess` presente.
- [ ] Nada fuera del alcance de Fase 1 (ver `AGENTS.md` §3).
- [ ] Textos corregidos (63 años; 16 comisiones; sin "Publicar noticia" público).

## 8. Anti-patrones (NO hacer)
- No usar `localStorage` para noticias o convocatorias. No guardar imágenes como base64 en BD.
- No poner la lógica de vigencia solo en JS (la decide el servidor con `NOW()`).
- No hardcodear presidentes, años o logros en el HTML.
- No añadir frameworks, editores WYSIWYG ni librerías sin aprobación.
- No resumir/interpretar leyes en el sitio.
