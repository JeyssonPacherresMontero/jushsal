---
name: jushsal-php-xampp
description: Reglas y patrones para desarrollar en el proyecto JUSHSAL (PHP 8 + PDO + MySQL en XAMPP): login por roles, CRUD seguro, subida de imágenes, popup de convocatoria y historial de presidentes. Usar al crear o modificar cualquier archivo PHP, SQL o JS del sitio de la Junta de Usuarios San Lorenzo.
---

# JUSHSAL · PHP + MySQL (XAMPP)

Lee `AGENTS.md` y `docs/ESTRUCTURA.md` primero. Esta skill solo fija patrones de código.

## Patrones obligatorios
**Conexión (`includes/db.php`)**
```php
$pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS, [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
]);
$pdo->exec("SET time_zone = '-05:00'");
```
**Salida:** `function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }` — todo `echo` de dato dinámico pasa por `e()`.
**POST del panel:** `csrf_verificar();` al inicio; `requerir_rol(['admin','comunicaciones']);` antes de cualquier lógica.
**Subida:** siempre `subir_imagen($_FILES['imagen'], 'noticias')` (finfo + 5 MB + nombre aleatorio). Nunca `move_uploaded_file` directo.
**Flujo guardar:** validar → transacción si toca varias tablas → `registrar_bitacora()` → `flash()` → redirigir (PRG).

## Popup de convocatoria (resumen)
Servidor decide vigencia (`NOW() BETWEEN mostrar_desde AND mostrar_hasta AND activo=1`); JS solo aplica `retraso_seg`, `duracion_seg` y `frecuencia`. Modal accesible, imagen con scroll interno (`max-height:90vh`), cierre por ×, Esc y clic fuera.

## Historial de presidentes
Una sola gestión `es_vigente=1` (transacción). Logros agrupados por `anio` dentro del periodo de la gestión. Sin datos inventados: estado vacío editable.

## Prohibido
Concatenar SQL · `localStorage` para datos de negocio · frameworks/Composer sin aprobación · DNI/celulares en HTML público · `eval`, `extract($_POST)`, `include` con ruta de usuario.
