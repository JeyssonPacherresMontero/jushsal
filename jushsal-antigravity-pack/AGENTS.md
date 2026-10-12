# AGENTS.md — JUSHSAL (Junta de Usuarios del Sector Hidráulico Menor San Lorenzo)

> Lee este archivo completo antes de tocar código. Si una petición lo contradice, DETENTE y pregunta.
> Reglas complementarias (siempre activas): `.agents/rules/seguridad-php.md` y `.agents/rules/datos-reales-jushsal.md`.

## 1. Qué es
Sitio web institucional + **panel administrativo (backoffice)** de JUSHSAL (Clase A, Tambogrande – Piura).
Referencia actual (WordPress): https://www.jusanlorenzo.org.pe/web/ · Facebook oficial: https://www.facebook.com/profile.php?id=100076060864594
La **maqueta estática** (`index.html`, `css/style.css`, `js/script.js`, `js/noticias.js`, `conexion.php`) es la base visual: se **migra a PHP, no se rediseña**.

**Objetivo Fase 1** — el rol **Comunicaciones** gestiona desde `/admin`:
1. **Noticias** (CRUD con imagen, categoría, borrador/publicado).
2. **Historial de presidentes por gestión** (periodo año–año) y **lo realizado en cada año** (logros).
3. **Convocatorias a asamblea** subidas como **imagen** y mostradas en **popup** en la web pública, con vigencia, retraso y duración configurables.

## 2. Stack (no negociable)
- PHP 8.x puro, **PDO** con sentencias preparadas. Sin Laravel/Composer/Node/React/Vue.
- MySQL/MariaDB en **XAMPP**. BD `junta_bd`, `utf8mb4`, InnoDB. Esquema en `database/schema.sql`.
- Frontend: HTML5 + `css/style.css` existente + JS vanilla.
- Zona horaria `America/Lima`. UI, comentarios, tablas y columnas en **español**.
- Ruta local: `C:\xampp\htdocs\jushsal\` → http://localhost/jushsal/
- Windows: `C:\xampp\php\php.exe` y `C:\xampp\mysql\bin\mysql.exe` (usar rutas completas en el terminal).

## 3. Fuera de alcance (responder "fuera de Fase 1")
Padrón/RADA, cobranza, tarifas, riego, pagos, app móvil, migrar WordPress, correos masivos, chatbots (ver `docs/ROADMAP.md`).
"CRM" = panel de gestión de contenido y comunicación institucional.

## 4. Roles
| Rol | Puede |
|---|---|
| `admin` | Todo + usuarios del panel + bitácora |
| `comunicaciones` | CRUD noticias, convocatorias, presidentes/gestiones/logros, comisiones. No usuarios ni bitácora |
| Público | Solo lectura de lo publicado. **Se elimina el botón/modal "Publicar noticia" de la web pública**; publicar vive en `/admin` |

## 5. Convenciones
- Carpetas según `docs/ESTRUCTURA.md`; no crear otras sin justificarlo.
- Una responsabilidad por archivo; SQL en `includes/modelos/*.php`, nunca en vistas.
- Tablas en plural snake_case, PK `id`, FK `<tabla_singular>_id`, `created_at/updated_at`.
- Borrado lógico en noticias/convocatorias; borrado físico solo de imágenes huérfanas.
- Diseño: variables CSS existentes (`--primary:#0b3d76; --accent:#ffab00; --water:#14919b`), Montserrat/Open Sans, clases `.btn`, `.news-card`, `.modal`, `.section`. Responsive (breakpoint 860px).

## 6. Flujo de trabajo (Antigravity)
1. Plan primero: leer `PRPs/001--comunicaciones-noticias-presidentes-popup.md` y generar el plan de la tarea actual (T1…T9). **Una tarea por conversación/agente.**
2. Implementar solo esa tarea. Validar con su checklist (`/validar-tarea`). Si falla, corregir antes de seguir.
3. Entregar resumen: `Tarea · Archivos tocados · Validación · Pendientes`.
4. Cambios en terminal: pedir confirmación antes de borrar archivos, tocar `php.ini`, o ejecutar SQL destructivo (`DROP`, `TRUNCATE`).

## 7. Anti-desvío (antes de cada cambio)
- ¿Está en Fase 1 (noticias / presidentes / popup / base del panel)? Si no → no hacerlo.
- ¿Solo PHP+MySQL+JS vanilla? Si añade dependencia → preguntar.
- ¿Respeta roles, CSRF, escape de salida y validación de subidas? (ver rule de seguridad)
- ¿Mantiene el diseño de la maqueta? ¿Inventó datos? → corregir.
- Comandos útiles: `/ejecutar-prp`, `/validar-tarea`, `/reencuadrar`.
