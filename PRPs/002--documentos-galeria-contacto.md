# PRP-002 · Fase 2: Documentos institucionales y estados financieros, galería administrable y formulario de contacto

**Proyecto:** JUSHSAL · **Stack:** PHP 8 + MySQL (XAMPP) · **Fase:** 2 · **Confianza:** 9/10

---

## 1. Goal
Entregar la **Fase 2 del ROADMAP** institucional permitiendo que el rol *Comunicaciones* gestione desde `/admin`:
1. El repositorio de **documentos institucionales, normativos y estados financieros** (subida de PDFs, categorización, año fiscal, destacados en portada y descargas).
2. La **galería fotográfica institucional** (alta/baja de fotografías con títulos y pie de foto, control de elementos anchos `.gallery-wide` y ordenación visual).
3. La **bandeja de mensajes de contacto** recibidos desde el formulario web (almacenamiento en BD con IP y fecha, estados *pendiente* / *atendido*, notas internas y opción de continuar la atención vía WhatsApp).

---

## 2. Why
- **Transparencia y Exigencia Legal:** La Ley N.º 31801 y las Resoluciones Jefaturales de la ANA (como la RJ-239-2026-ANA) obligan a las juntas de usuarios a publicar sus estados financieros auditados, balances, reglamentos y tarifas de agua aprobadas. En la Fase 1, estos archivos estaban fijos en el HTML; ahora deben ser administrables dinámicamente desde la base de datos con categorización y buscador.
- **Identidad Institucional:** Las fotografías de obras, reservorios, canales y asambleas no deben estar cableadas en el código estático, sino ser renovables por el personal de comunicaciones conforme avanzan las actividades de la Junta.
- **Trazabilidad de Consultas:** El formulario web actual solo abría un enlace a WhatsApp sin dejar constancia en la institución. Guardar los mensajes en MySQL permite al equipo dar seguimiento formal a las solicitudes de los regantes y usuarios de riego.

---

## 3. What (entregables)
1. Script de base de datos `database/002_fase2.sql` (tablas `categorias_documento`, `documentos`, `galeria_imagenes` y `mensajes_contacto` con semillas reales sin tocar las tablas de Fase 1).
2. Módulo de carga segura de PDFs `includes/upload_documento.php` (validación estricta de tipo MIME `application/pdf`, límite de 15 MB y nombres aleatorios higienizados).
3. Modelos de datos: `includes/modelos/documentos.php`, `includes/modelos/galeria.php` y `includes/modelos/contacto.php`.
4. Portal público:
   - Catálogo completo `documentos.php` con filtros por categoría, año y buscador de documentos.
   - Actualización de la sección `#documentos` en `index.php` (renderizado de los 4 documentos destacados desde BD).
   - Actualización de la sección `#galeria` en `index.php` (renderizado de fotografías desde BD con soporte de `.gallery-wide`).
   - Envío asíncrono seguro del formulario `#contacto` que guarda en BD e inicia opcionalmente el chat de WhatsApp.
5. Panel administrativo (`/admin`):
   - `/admin/documentos/`: Listar, subir nuevo PDF, editar metadatos/categoría, marcar destacado y eliminar.
   - `/admin/galeria/`: Listar imágenes, subir nuevas fotografías con título/descripción, activar/desactivar y marcar formato ancho.
   - `/admin/contactos/`: Bandeja de mensajes con badges de estado (*pendiente* / *atendido* / *archivado*), modal de lectura completa y notas internas de respuesta.
   - Tablero `admin/index.php`: Nuevas tarjetas de métricas (Documentos publicados, Mensajes de contacto pendientes).
6. Suite de validación automatizada `scratch/test_fase2.php`.

### Criterios de éxito (medibles)
- [ ] `database/002_fase2.sql` se ejecuta limpiamente sin alterar ni borrar ninguna tabla de la Fase 1 (`comisiones`, `noticias`, `gestiones_presidenciales`, `convocatorias`, `usuarios`, `bitacora`).
- [ ] Subir un PDF de prueba lo almacena en `uploads/documentos/` con nombre aleatorio criptográfico; intentar subir un archivo `.php` o `.exe` renombrado a `.pdf` es **estrictamente rechazado** con error descriptivo.
- [ ] `documentos.php` lista todos los documentos activos agrupados/filtrables por categoría; al hacer clic se abre o descarga el PDF legítimo.
- [ ] `index.php` en `#documentos` muestra únicamente los documentos marcados como `es_destacado = 1`.
- [ ] `index.php` en `#galeria` renderiza las imágenes activas de la BD; las marcadas como `es_destacada = 1` aplican la clase CSS `.gallery-wide`.
- [ ] Enviar el formulario `#contacto` almacena el mensaje en `mensajes_contacto` con IP, fecha exacta y estado `pendiente`.
- [ ] El panel `/admin/contactos/` permite al usuario `comunicaciones` leer el mensaje, registrar notas y marcarlo como `atendido`.
- [ ] Ningún número celular de directivos ni DNI se expone en el HTML público de las nuevas secciones.
- [ ] 100% de los archivos PHP superan `php -l` sin errores de sintaxis.

---

## 4. Contexto necesario
### Archivos del repositorio
- Maqueta y estilos: `css/style.css`, `index.php`, `includes/vistas/header.php`, `includes/vistas/footer.php`.
- Documentos existentes: carpeta `documentos/` (18 archivos reales entre leyes, tarifas y resoluciones ANA).
- Galería existente: carpeta `assets/images/` (`sede.jpg`, `repartidor.jpg`, `valvula.jpg`, `campos.jpg`, `reservorio.jpg`).
- Seguridad existente: `includes/auth.php`, `includes/csrf.php`, `includes/helpers.php`, `includes/bitacora.php`, `uploads/.htaccess`.
### Referencias externas
- Resolución Jefatural N.º 239-2026-ANA: Lineamientos para auditoría de estados financieros en juntas de usuarios.
- Ley N.º 31801 y Ley N.º 29338 (Recursos Hídricos).
### Gotchas conocidos
- **Tamaño de PDFs:** Algunos documentos escaneados superan 8 MB (ej. `RHE10726562453E0015.pdf` mide 9 MB). `upload_max_filesize` en `php.ini` debe ser al menos `12M` a `15M`, y la validación en PHP debe tener un tope seguro de 15 MB.
- **Nombres de archivo existentes con caracteres especiales:** Muchos PDFs existentes tienen espacios, comas y tildes (`D.S N° 007-2024-MIDAGRI, REGLAMENTO.pdf`). Al enlazar o redirigir en PHP siempre usar `rawurlencode()` para evitar errores 404 en navegadores y servidores web.
- **Seguridad en subida de PDFs:** No basta con validar `$_FILES['archivo']['type']`. Se debe usar `finfo_file($finfo, $tmp)` exigiendo `application/pdf`, verificar que la cabecera del archivo empiece con `%PDF-` y almacenarlo con nombre hexadecimal en `uploads/documentos/` bajo la protección de `uploads/.htaccess` que impide la ejecución de PHP.
- **Layout de galería responsivo:** El contenedor `.gallery-grid` tiene `grid-auto-rows: 210px` y `.gallery-wide` usa `grid-column: span 2`. La alternancia de fotos normales y destacadas debe mantener la armonía visual de la maqueta.

---

## 5. Blueprint de implementación (EN ORDEN; validar cada tarea)

**T1 · Base de Datos Fase 2 y Módulo de Carga de Documentos**
- Ejecutar `database/002_fase2.sql` en `junta_bd` (tablas `categorias_documento`, `documentos`, `galeria_imagenes` y `mensajes_contacto`).
- Crear `includes/upload_documento.php` con función `subir_documento_pdf($archivo, $carpeta = 'documentos')`:
  - Verificación `UPLOAD_ERR_OK`.
  - Validación de tamaño (máx. 15 MB).
  - Verificación MIME real con `finfo_file` (`application/pdf`).
  - Verificación de cabecera binaria `%PDF-`.
  - Nombre aleatorio seguro: `bin2hex(random_bytes(16)) . '.pdf'`.
- Modelo `includes/modelos/documentos.php` (`documentos_listar_publicos()`, `documentos_listar_destacados()`, `documentos_obtener_por_id()`, `documentos_crear()`, `documentos_actualizar()`, `documentos_eliminar()`, `categorias_documento_listar()`).
- ✅ **Validar:** Comprobar que `database/002_fase2.sql` importó las 5 categorías y 9 documentos iniciales; prueba unitaria de `subir_documento_pdf()` rechazando archivo simulado `.php`.

**T2 · Panel Administrativo de Documentos**
- Crear vistas en `admin/documentos/`:
  - `listar.php`: Tabla con título, categoría, año fiscal, formato, tamaño, estado destacado, botón editar y eliminar.
  - `form.php`: Formulario para subir nuevo PDF o actualizar metadatos (título, descripción, categoría, año fiscal, checkbox destacado, checkbox activo).
  - `guardar.php`: Procesador POST con token CSRF, validación, subida segura de archivo y registro en `bitacora`.
  - `eliminar.php`: Eliminación lógica o física (borrando archivo de `uploads/documentos/` si fue subido allí) con CSRF y bitácora.
- ✅ **Validar:** Crear un documento con PDF de prueba desde `/admin/documentos/`, verificar registro en BD y bitácora; editar categoría; eliminar documento y certificar remoción del archivo huérfano.

**T3 · Vistas Públicas de Documentos (`documentos.php` y Portada)**
- Crear página pública `documentos.php`:
  - Encabezado institucional idéntico al diseño de `presidentes.php` y `comisiones.php`.
  - Filtro por categoría y selector de año fiscal.
  - Buscador en tiempo real o por formulario de títulos.
  - Enlaces de visualización y descarga en pestaña nueva con `rawurlencode()`.
- Actualizar sección `#documentos` en `index.php`:
  - Reemplazar las tarjetas fijas por la consulta `documentos_listar_destacados(4)` desde BD.
  - Enlace al final de la sección: "Ver todos los documentos y resoluciones →" hacia `documentos.php`.
- Actualizar enlaces del menú en `includes/vistas/header.php` y `includes/vistas/footer.php` hacia `documentos.php`.
- ✅ **Validar:** Abrir `documentos.php` en navegador local; verificar que los PDFs abren correctamente; verificar que la portada `index.php` muestra los 4 destacados desde BD.

**T4 · Galería Fotográfica Administrable**
- Crear modelo `includes/modelos/galeria.php`:
  - `galeria_listar_publica()` (ordenadas por `orden ASC`).
  - `galeria_listar_admin()`, `galeria_obtener_por_id()`, `galeria_crear()`, `galeria_actualizar()`, `galeria_eliminar()`.
- Crear vistas en `admin/galeria/`:
  - `listar.php`: Grid/tabla administrativa con miniatura, título, descripción, switch activo, switch formato ancho y acciones.
  - `form.php`: Subida de fotografía (jpg/png/webp ≤ 5 MB con `subir_imagen()`), título, pie de foto, orden y checkbox formato ancho.
  - `guardar.php` y `eliminar.php`: Controladores POST con token CSRF, manejo de archivos huérfanos y bitácora.
- Actualizar sección `#galeria` en `index.php`:
  - Renderizar dinámicamente las fotografías desde `galeria_listar_publica()`.
  - Aplicar clase `.gallery-wide` si `es_destacada == 1`.
- ✅ **Validar:** Subir foto nueva desde `/admin/galeria/`; refrescar `index.php` y confirmar aparición con diseño responsivo a 375 px y 1280 px.

**T5 · Formulario y Bandeja de Mensajes de Contacto**
- Crear modelo `includes/modelos/contacto.php`:
  - `contacto_guardar_mensaje($datos, $ip)`.
  - `contacto_listar_mensajes($filtroEstado, $limite, $offset)`.
  - `contacto_obtener_por_id($id)`, `contacto_cambiar_estado($id, $estado, $notas, $usuarioId)`, `contacto_contar_pendientes()`.
- Crear endpoint `api/contacto_enviar.php`:
  - Validación de campos: nombre, apellidos, email válido, mensaje (mínimo 10 caracteres).
  - Sanitización estricta.
  - Rate-limiting básico (evitar envíos masivos consecutivos desde la misma IP).
  - Respuesta JSON: `{"exito": true, "mensaje": "..."}`.
- Actualizar `assets/js/script.js` / `js/script.js`:
  - Al enviar `#contactForm`: enviar primero vía `fetch()` a `api/contacto_enviar.php`.
  - Mostrar mensaje de confirmación en `#formNote` y abrir opcionalmente el enlace a WhatsApp con el texto codificado.
- Crear módulo en `admin/contactos/`:
  - `listar.php`: Bandeja de entrada con filtros (*todos*, *pendientes*, *atendidos*), fecha, remitente, correo y mensaje.
  - `detalle.php` o modal: Ver mensaje completo, registrar notas de respuesta interna y cambiar estado a *atendido* o *archivado*.
- Actualizar `admin/index.php`:
  - Tarjeta de métrica: "Mensajes de Contacto" con contador de pendientes.
- ✅ **Validar:** Enviar mensaje desde la portada pública; verificar que se guarda en BD con IP; ingresar a `/admin/contactos/`, marcar como atendido y confirmar cambio de estado.

**T6 · Cierre, Pruebas Automatizadas y Checklist Fase 2**
- Crear suite automatizada `scratch/test_fase2.php` verificando:
  - Integridad de tablas y claves foráneas de Fase 2.
  - Creación, listado y eliminación de documentos con validación MIME.
  - Creación y listado de galería con formato destacado.
  - Envío y cambio de estado de mensajes de contacto.
  - Cero exposición de DNI o celulares en HTML público.
- Actualizar [README.md](file:///c:/Users/JEYSSON/Downloads/jushsal/README.md) y [docs/ROADMAP.md](file:///c:/Users/JEYSSON/Downloads/jushsal/docs/ROADMAP.md) marcando la Fase 2 como completada.
- ✅ **Validar:** Ejecutar el bucle de validación completo en PowerShell (0 errores de sintaxis, 100% de tests aprobados).

---

## 6. Bucle de validación (Windows/XAMPP · PowerShell desde la raíz del proyecto)
```powershell
# 1. Sintaxis PHP de todo el proyecto
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { & "C:\xampp\php\php.exe" -l $_.FullName } | Where-Object { $_ -notmatch "No syntax errors" }

# 2. Esquema Fase 2 y conteos
& "C:\xampp\mysql\bin\mysql.exe" -u root -e "SOURCE database/002_fase2.sql"
& "C:\xampp\mysql\bin\mysql.exe" -u root junta_bd -e "SELECT COUNT(*) AS total_docs FROM documentos; SELECT COUNT(*) AS total_fotos FROM galeria_imagenes; SELECT COUNT(*) AS total_contactos FROM mensajes_contacto;"

# 3. Suites de pruebas unitarias y de integración
& "C:\xampp\php\php.exe" scratch/test_fase2.php

# 4. Códigos de respuesta HTTP
curl.exe -s -o NUL -w "%{http_code}" http://localhost:8080/jushsal/documentos.php
curl.exe -s -o NUL -w " %{http_code}" http://localhost:8080/jushsal/admin/documentos/listar.php
curl.exe -s -o NUL -w " %{http_code}" http://localhost:8080/jushsal/admin/contactos/listar.php
```

---

## 7. Checklist final
- [x] Tablas creadas con `CREATE TABLE IF NOT EXISTS` en `002_fase2.sql` sin modificar las tablas de la Fase 1.
- [x] Subida de PDFs restringida a `application/pdf` con verificación binaria de cabecera `%PDF-`.
- [x] Sin DNI ni celulares en el HTML público de documentos, galería o contacto.
- [x] Todos los formularios administrativos y de acción POST protegidos con token CSRF y `requerir_rol()`.
- [x] Registro de auditoría en `bitacora` para alta, edición y eliminación de documentos y fotografías.
- [x] Enlaces con nombres de archivo especiales codificados mediante `rawurlencode()`.
- [x] Diseño visual de la maqueta original (`#documentos`, `#galeria`, `#contacto`) preservado fielmente.

---

## 8. Anti-patrones (NO hacer)
- No permitir la subida de archivos `.exe`, `.php`, `.js` o `.html` en el gestor de documentos.
- No guardar el contenido binario de los PDFs ni imágenes en columnas `BLOB` o `base64` en MySQL; guardar solo la ruta en disco.
- No romper la funcionalidad de apertura de WhatsApp en el formulario de contacto: guardar en BD y permitir la apertura del chat.
- No alterar las tablas existentes de la Fase 1 (`comisiones`, `noticias`, `convocatorias`, etc.).
- No agregar dependencias de Composer ni librerías externas (mantener PHP puro y JS vanilla).
