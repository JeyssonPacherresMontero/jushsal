# Regla: Datos personales del padrón (activación: Always On desde la Fase 3)

Aplica a todo código que toque usuarios de agua, predios, contactos o notificaciones (Ley N.º 29733 y su reglamento).

- **Mínimo necesario:** guardar solo campos aprobados en el cuestionario (`docs/FASE3_CUESTIONARIO.md`). Prohibido añadir campos "por si acaso".
- **Rol nuevo `padron`** (secretaría/registro): único (junto con `admin`) que ve DNI/RUC, dirección y celular completos. `comunicaciones` solo ve **nombre, comisión y estado de consentimiento** del destinatario al armar un envío; nunca el DNI.
- **Nunca público:** ningún dato del padrón aparece en páginas públicas, JSON públicos ni en `view-source`. Los endpoints del padrón exigen sesión + rol.
- **Consentimiento:** campo `acepta_notificaciones` (sí/no) + `fecha_consentimiento` + `origen_consentimiento`. Sin consentimiento = no se incluye en envíos. Debe existir el modo "dar de baja".
- **Trazabilidad:** registrar en `bitacora` quién consultó, exportó o modificó datos sensibles (ver DNI, exportar CSV, importar, editar titular).
- **Exportaciones:** solo `admin`/`padron`, con registro en bitácora; el CSV no incluye columnas que el rol no deba ver.
- **Importación:** solo CSV UTF-8 subido por `padron`; vista previa con errores por fila **antes** de confirmar; no sobrescribe sin confirmación; deduplica por documento.
- **Búsqueda:** por nombre, comisión o N.º de documento; nunca listar todo el padrón sin paginar (máx. 50 por página).
- **Borrado:** baja lógica (`estado='baja'`, motivo, fecha). No se elimina físicamente salvo petición formal; en ese caso anonimizar.
- **Datos de prueba:** siempre ficticios y marcados `[DEMO]`. Nunca cargar el padrón real en pruebas del agente ni subirlo a repositorios.
- **DNI:** validar formato (8 dígitos) y RUC (11); mostrar enmascarado (`****1234`) salvo en la ficha del usuario con permiso.
