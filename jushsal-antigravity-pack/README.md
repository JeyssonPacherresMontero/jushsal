# Paquete JUSHSAL para Google Antigravity (PHP + MySQL · XAMPP)

Copiar **todo el contenido** de esta carpeta a `C:\xampp\htdocs\jushsal\` junto a tu maqueta (index.html, css/, js/, assets/, documentos/, conexion.php) y abrir esa carpeta como workspace.

| Archivo | Para qué |
|---|---|
| `PROMPT_ANTIGRAVITY.md` | Configuración del agente + prompt inicial + flujo por tarea |
| `AGENTS.md` | Reglas permanentes: alcance, stack, roles, convenciones, anti-desvío |
| `.agents/rules/seguridad-php.md` | Regla Always On: seguridad PHP/MySQL, subidas, privacidad |
| `.agents/rules/datos-reales-jushsal.md` | Regla Always On: datos reales (16 comisiones, presidente vigente, contacto) |
| `.agents/workflows/` | Slash commands: `/ejecutar-prp`, `/validar-tarea`, `/reencuadrar` |
| `.agents/skills/jushsal-php-xampp/` | Skill con patrones de código |
| `PRPs/001--….md` | Plan de 9 tareas con validaciones y criterios de éxito |
| `docs/ESTRUCTURA.md` · `docs/ROADMAP.md` | Árbol de carpetas, migración de la maqueta, flujo del popup, fases futuras |
| `database/schema.sql` · `seed.sql` | BD `junta_bd` |

**XAMPP:** iniciar Apache + MySQL → phpMyAdmin → importar `schema.sql` y luego `seed.sql`. En `php.ini`: `upload_max_filesize=6M`, `post_max_size=8M`; reiniciar Apache.
