# JUSHSAL — Portal Institucional y Panel Administrativo (Fases 1 y 2 Completadas)

Sitio web institucional y panel administrativo (**backoffice**) de la **Junta de Usuarios del Sector Hidráulico Menor San Lorenzo – Clase A** (Tambogrande – Piura – Perú).

---

## 1. Alcance Implementado

### Fase 1: Comunicación Institucional, Gestión y Convocatorias
1. **Noticias Institucionales:** CRUD completo con categorías, carga de imágenes (`uploads/noticias/`), estados (Borrador / Publicado / Archivado), slugs amigables y paginación en la web pública.
2. **Historial de Presidentes y Logros por Año:** Tarjeta de la gestión vigente arriba y línea de tiempo histórica de gestiones anteriores, con rendición de cuentas y registro de obras/logros agrupados por año.
3. **Convocatorias a Asamblea en Ventana Emergente (Popup):** Despliegue de afiches oficiales en modal accesible, con vigencia calculada en servidor (`America/Lima`), control de retraso, duración, frecuencia (`una_vez_por_sesion`, `una_vez_por_dia`, `cada_visita`) y previsualización.
4. **Comisiones de Usuarios (16 Sub Sectores):** Representación territorial de las 16 comisiones reales de San Lorenzo. Los números celulares de contacto de los presidentes se gestionan de forma confidencial para uso interno administrativo y **nunca se publican en el frontend público**.
5. **Control de Acceso y Auditoría:** Autenticación protegida contra ataques de fuerza bruta (bloqueo tras 5 intentos fallidos), sesiones `HttpOnly`/`SameSite=Lax`, matriz de permisos por roles (`admin` y `comunicaciones`) y bitácora detallada de eventos con IP y fecha.

### Fase 2: Documentos, Galería y Consultas Ciudadanas
6. **Documentos Institucionales y Estados Financieros:** Repositorio público (`documentos.php`) y en portada (`#documentos`) con filtros por 5 categorías (Leyes, ANA, Tarifas POMDIH, Auditoría Financiera, Guías), año y búsqueda por palabras clave. Subida de archivos con validación binaria de cabecera `%PDF-`, límite de 15 MB, sanitización de nombres, contador de descargas y borrado físico de archivos.
7. **Galería Fotográfica Administrable:** Mosaico fotográfico dinámico en portada (`#galeria`) con soporte de formato destacado de ancho doble (clase `.gallery-wide`), ordenación personalizada, gestión de imágenes desde `/admin/galeria/` y borrado sincronizado de archivos en `uploads/galeria/`.
8. **Bandeja de Consultas Ciudadanas y Formulario de Contacto:** Endpoint API (`api/contacto_enviar.php`) con validación de datos, protección anti-flood por IP (rate limiting de 5 envíos / 10 min), persistencia en BD con IP de origen, integración sin fisuras con el chat de WhatsApp y bandeja administrativa (`/admin/contactos/`) con gestión de estados (`pendiente`, `atendido`, `archivado`) y notas de seguimiento interno.

---

## 2. Stack Tecnológico

- **Lenguaje:** PHP 8.x puro (sin frameworks ni librerías externas).
- **Base de Datos:** MySQL / MariaDB (XAMPP), base de datos `junta_bd`, juego de caracteres `utf8mb4`, motor `InnoDB`.
- **Acceso a Datos:** PDO con sentencias preparadas y emulación desactivada (`ATTR_EMULATE_PREPARES => false`).
- **Frontend:** HTML5 semántico, CSS3 responsivo original (`css/style.css`), JavaScript vanilla.
- **Zona Horaria Oficial:** `America/Lima` (UTC-5) sincronizada tanto en PHP como en MySQL.

---

## 3. Requisitos del Sistema

- **XAMPP 8.x** (Apache 2.4 + MariaDB 10.4+ + PHP 8.1 o superior).
- Extensiones PHP requeridas:
  - `pdo_mysql`
  - `fileinfo` (para validación MIME binaria de imágenes)
  - `mbstring`
  - `gd` o `exif` (para verificación de imágenes)

---

## 4. Instalación Paso a Paso en Windows / XAMPP

### Paso 1: Ubicación del Proyecto
Ubique el proyecto en el directorio web de Apache:
```text
C:\xampp\htdocs\jushsal\
```
*(Si utiliza una carpeta de trabajo independiente, puede crear un enlace simbólico o junction con `mklink /J C:\xampp\htdocs\jushsal "C:\Ruta\De\Su\Proyecto"`).*

### Paso 2: Configuración de Base de Datos en phpMyAdmin / MySQL
1. Inicie los servicios **Apache** y **MySQL** desde el Panel de Control de XAMPP.
2. Abra la consola de MySQL o phpMyAdmin (`http://localhost/phpmyadmin/` o `http://localhost:8080/phpmyadmin/`).
3. Importe en orden los archivos SQL ubicados en `database/`:
   ```powershell
   & "C:\xampp\mysql\bin\mysql.exe" -u root -e "SOURCE database/schema.sql"
   & "C:\xampp\mysql\bin\mysql.exe" -u root -e "SOURCE database/seed.sql"
   & "C:\xampp\mysql\bin\mysql.exe" -u root -e "SOURCE database/002_fase2.sql"
   ```
   *Esto creará la base de datos `junta_bd`, las tablas de Fase 1 (noticias, presidentes, convocatorias, 16 comisiones) y las tablas de Fase 2 (documentos, galería, mensajes de contacto).*

### Paso 3: Configuración de Parámetros (`includes/config.php`)
Copie `includes/config.example.php` como `includes/config.php`:
```powershell
Copy-Item "includes/config.example.php" "includes/config.php"
```
Verifique las credenciales de conexión en `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'junta_bd');
define('DB_USER', 'root');
define('DB_PASS', '');
```
*`BASE_URL` se detecta dinámicamente según el host y puerto de Apache (`localhost` o `localhost:8080`).*

### Paso 4: Ajustes Recomendados en `php.ini`
En `C:\xampp\php\php.ini`, confirme los siguientes valores:
```ini
date.timezone = America/Lima
upload_max_filesize = 10M
post_max_size = 12M
extension=fileinfo
extension=pdo_mysql
```
Reinicie el servicio Apache desde el panel de XAMPP si modificó `php.ini`.

### Paso 5: Creación del Primer Usuario Administrador
Ejecute el script interactivo CLI desde la terminal PowerShell:
```powershell
& "C:\xampp\php\php.exe" database/crear_admin.php
```
Ingrese el correo electrónico, nombre completo y una contraseña segura (mínimo 8 caracteres).
*(Credenciales configuradas por defecto durante el desarrollo: `admin@jusanlorenzo.org.pe` / `AdminPassword2026!`)*.

---

## 5. Estructura de Navegación y URLs

### Portal Web Institucional (Público)
| URL | Descripción |
|---|---|
| `http://localhost:8080/jushsal/index.php` | Portada institucional, 6 últimas noticias, documentos y 16 comisiones |
| `http://localhost:8080/jushsal/noticias.php` | Catálogo de noticias con paginación (9 por página) y filtros |
| `http://localhost:8080/jushsal/noticia.php?slug=...` | Vista detallada de noticia institucional |
| `http://localhost:8080/jushsal/presidentes.php` | Historial de presidentes por gestión y obras anuales |
| `http://localhost:8080/jushsal/comisiones.php` | Directorio de las 16 comisiones de usuarios y sub sectores |
| `http://localhost:8080/jushsal/api/convocatoria_activa.php` | Endpoint JSON anti-caché evaluador del popup de convocatorias |

### Panel Administrativo (`/admin/`)
| URL | Rol Requerido | Descripción |
|---|---|---|
| `http://localhost:8080/jushsal/admin/login.php` | Público | Acceso seguro al panel |
| `http://localhost:8080/jushsal/admin/index.php` | `admin` / `comunicaciones` | Tablero de métricas institucionales |
| `http://localhost:8080/jushsal/admin/noticias/listar.php` | `comunicaciones` / `admin` | CRUD de noticias y carga de imágenes |
| `http://localhost:8080/jushsal/admin/convocatorias/listar.php` | `comunicaciones` / `admin` | CRUD de convocatorias y botón de prueba popup |
| `http://localhost:8080/jushsal/admin/presidentes/listar.php` | `comunicaciones` / `admin` | CRUD de gestiones presidenciales y logros anuales |
| `http://localhost:8080/jushsal/admin/comisiones/listar.php` | `comunicaciones` / `admin` | Asignación de presidentes de comisiones y teléfonos internos |
| `http://localhost:8080/jushsal/admin/usuarios/listar.php` | **Exclusivo `admin`** | Creación de cuentas, cambio de estado y reseteo de claves |
| `http://localhost:8080/jushsal/admin/bitacora.php` | **Exclusivo `admin`** | Registro cronológico de auditoría y accesos con IP |

---

## 6. Matriz de Permisos y Seguridad

| Funcionalidad / Módulo | Rol `admin` | Rol `comunicaciones` | Público |
|---|:---:|:---:|:---:|
| Ver noticias, presidentes y comisiones publicadas | ✅ | ✅ | ✅ |
| Gestión de Noticias (Crear/Editar/Eliminar) | ✅ | ✅ | ❌ |
| Gestión de Convocatorias y Popup | ✅ | ✅ | ❌ |
| Gestión de Presidentes, Gestiones y Logros | ✅ | ✅ | ❌ |
| Edición de Comisiones y Contacto Privado | ✅ | ✅ | ❌ |
| Gestión de Usuarios del Panel | ✅ | ❌ (HTTP 403) | ❌ |
| Visor de Bitácora de Auditoría | ✅ | ❌ (HTTP 403) | ❌ |

### Políticas de Seguridad Activas:
1. **Protección CSRF:** Todas las acciones `POST` validan un token criptográfico generado por sesión. Peticiones sin token o alteradas devuelven HTTP 419.
2. **Defensa contra Fuerza Bruta:** Bloqueo automático por 15 minutos tras 5 intentos fallidos consecutivos de inicio de sesión.
3. **Subida Segura de Archivos:** Las imágenes subidas a `uploads/` se inspeccionan con `finfo_file` para admitir únicamente `image/jpeg`, `image/png` y `image/webp` (≤5 MB), se almacenan con nombres generados con `random_bytes`, y la carpeta `uploads/` cuenta con `.htaccess` que desactiva la ejecución de cualquier script PHP.
4. **Privacidad de Datos:** La base de datos no almacena DNI de directivos. Los números telefónicos y celulares de los presidentes de comisiones están restringidos al panel interno administrativo y jamás se imprimen en el HTML de la web pública.

---

## 7. Baterías de Pruebas y Validación Automatizada

Para ejecutar las suites de pruebas automatizadas en consola PowerShell:
```powershell
# Validación sintáctica completa de todo el proyecto
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { & "C:\xampp\php\php.exe" -l $_.FullName }

# Pruebas de Núcleo de Seguridad (T2)
& "C:\xampp\php\php.exe" scratch/test_t2_seguridad.php

# Pruebas de Módulo de Noticias (T4)
& "C:\xampp\php\php.exe" scratch/test_t4_noticias.php

# Pruebas de Gestiones Presidenciales y Logros Anuales (T5)
& "C:\xampp\php\php.exe" scratch/test_t5_gestiones.php

# Pruebas de Convocatorias y Popup en Servidor (T6)
& "C:\xampp\php\php.exe" scratch/test_t6_convocatorias.php

# Pruebas de 16 Comisiones y Privacidad (T7)
& "C:\xampp\php\php.exe" scratch/test_t7_comisiones.php

# Pruebas de Usuarios, Permisos y Bitácora (T8)
& "C:\xampp\php\php.exe" scratch/test_t8_admin.php

# --- FASE 2: Documentos, Galería y Contacto ---
# Pruebas de Módulo de Documentos y PDF seguro (Fase 2 - T1 a T3)
& "C:\xampp\php\php.exe" scratch/test_t1_fase2.php
& "C:\xampp\php\php.exe" scratch/test_t2_fase2.php
& "C:\xampp\php\php.exe" scratch/test_t3_fase2.php

# Pruebas de Galería Fotográfica (Fase 2 - T4)
& "C:\xampp\php\php.exe" scratch/test_t4_fase2.php

# Pruebas de Mensajes de Contacto y API (Fase 2 - T5)
& "C:\xampp\php\php.exe" scratch/test_t5_fase2.php

# Suite Integral Automatizada de Fase 2 (100% Cobertura)
& "C:\xampp\php\php.exe" scratch/test_fase2.php
```

---

## 8. Datos Institucionales Reales de JUSHSAL

- **Razón Social:** Junta de Usuarios del Sector Hidráulico Menor San Lorenzo – Clase A (JUSHSAL).
- **Sede Central:** Av. Reforma Agraria S/N – Centro Poblado La Cruceta, San Lorenzo, Tambogrande, Piura, Perú.
- **Canales Oficiales:** Correo: `juntasanlorenzo@jusanlorenzo.org.pe` · Teléfono: `+51 942 028 075` · WhatsApp: `https://w.app/FkwQes`.
- **Horario de Atención:** Lunes a viernes 8:00 a.m. – 4:00 p.m. · Sábados 8:00 a.m. – 1:00 p.m.
- **Presidente Institucional Vigente (2026–2029):** Miguel Castillo Vera.
- **Número de Comisiones:** 16 sub sectores de riego regulados por la Autoridad Nacional del Agua (ANA).
