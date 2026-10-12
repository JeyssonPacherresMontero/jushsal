# Regla: Seguridad PHP/MySQL (activación: Always On)

- Contraseñas: `password_hash(PASSWORD_DEFAULT)` / `password_verify`. Nunca en texto plano ni en el repo.
- Credenciales BD solo en `includes/config.php` (no versionar); subir `config.example.php`.
- 100 % PDO preparado. Prohibido concatenar variables en SQL.
- Toda salida dinámica escapada con `htmlspecialchars($x, ENT_QUOTES, 'UTF-8')` vía helper `e()`. La maqueta usa `innerHTML` con datos crudos: se reemplaza por render PHP.
- CSRF token en todo POST del panel. `session_regenerate_id(true)` al login. Cookies `HttpOnly` + `SameSite=Lax`. Bloqueo tras 5 intentos fallidos / 15 min.
- Verificar rol en CADA página y acción de `/admin` con `requerir_rol([...])`; no basta con ocultar el menú.
- Subida de imágenes: `finfo_file` ∈ {jpeg, png, webp} + `getimagesize()`, máx. 5 MB, nombre aleatorio (`bin2hex(random_bytes(12))`), carpeta `uploads/<modulo>/`, y `uploads/.htaccess` que deshabilite PHP (`php_flag engine off`, bloquear `.php/.phtml/.phar`, `Options -Indexes`). Nunca confiar en extensión ni en `$_FILES['type']`.
- Privacidad (Ley 29733): no publicar ni guardar DNI; celulares de presidentes de comisión solo en admin, nunca en HTML público (verificar con "ver código fuente").
- Registrar en `bitacora` login, crear, editar y eliminar.
- La vigencia del popup la decide el servidor con `NOW()` (con `SET time_zone='-05:00'`), no el JavaScript.
- Prohibido: `eval`, `extract($_POST)`, `include` con ruta del usuario, `localStorage` para datos de negocio, imágenes en base64 dentro de la BD.
