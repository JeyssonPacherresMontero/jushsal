# Prompt para Google Antigravity — JUSHSAL

## Configuración recomendada (una sola vez)
- Abrir como workspace la carpeta `C:\xampp\htdocs\jushsal\` (con este paquete copiado dentro).
- Modo del agente: **Planning** (genera plan e implementación antes de codificar) y política de revisión **Request Review** para aprobar planes.
- En *Customizations → Rules*, comprobar que `seguridad-php` y `datos-reales-jushsal` estén en **Always On**. `AGENTS.md` se lee automáticamente (si tu versión es anterior a la 1.20.5, copia `AGENTS.md` como `GEMINI.md`).
- Terminal: política **Request review** (no "Always proceed") para evitar borrados o SQL destructivo sin confirmar.
- Si tu versión usa la carpeta antigua `.agent/` (sin "s"), renombra `.agents` → `.agent`.
- **Una tarea (T1…T9) por conversación.** Así el agente no acumula contexto ni se desvía.

## Prompt inicial (pegar en la conversación de la tarea T1)
```
Actúa como desarrollador PHP senior. Proyecto: web institucional + panel administrativo de la
Junta de Usuarios del Sector Hidráulico Menor San Lorenzo (JUSHSAL), Tambogrande – Piura.

Lee primero: AGENTS.md, .agents/rules/*, docs/ESTRUCTURA.md, database/schema.sql y
PRPs/001--comunicaciones-noticias-presidentes-popup.md, además de la maqueta existente
(index.html, css/style.css, js/script.js, js/noticias.js, conexion.php).

Stack obligatorio: PHP 8 puro + PDO, MySQL en XAMPP (BD junta_bd, utf8mb4), HTML/CSS/JS vanilla.
Prohibido agregar frameworks o dependencias sin preguntarme.

Objetivo (solo Fase 1):
- Rol "comunicaciones" (y "admin") con login en /admin.
- Comunicaciones sube NOTICIAS (imagen, categoría, borrador/publicado).
- Comunicaciones administra el HISTORIAL DE PRESIDENTES por gestión (periodo año–año) y los LOGROS
  realizados en cada año; la web pública los muestra como línea de tiempo (presidentes.php).
- Comunicaciones sube la CONVOCATORIA A ASAMBLEA como IMAGEN; en la web pública aparece en un POPUP
  tras un retraso configurable, dentro de una vigencia (desde/hasta), con duración y frecuencia configurables.
- Reutiliza el diseño de la maqueta. No rediseñes.

Reglas: sin inventar datos (presidentes anteriores, años, logros); sin DNI ni celulares en la web pública;
todo en español; si algo es ambiguo o está fuera de la Fase 1, pregunta.

Ejecuta: /ejecutar-prp T1
Primero entrégame el plan de implementación para aprobarlo.
```

## Flujo por tarea
| Paso | Comando / acción |
|---|---|
| Iniciar tarea | `/ejecutar-prp T2` (nueva conversación por tarea) |
| Verificar | `/validar-tarea` |
| El agente se desvía o inventa datos | `/reencuadrar` |
| Fases futuras | Crear `PRPs/002--….md` copiando la estructura del 001 y un workflow nuevo si hace falta |

## Orden de tareas (resumen del PRP)
T1 base+BD · T2 seguridad · T3 layout · T4 noticias · T5 presidentes+logros · T6 convocatoria+popup · T7 comisiones · T8 dashboard/usuarios/bitácora · T9 cierre y README.

## Prompts cortos de control
- "Detente. Ejecuta /reencuadrar."
- "No inventes datos: estado vacío editable desde el panel."
- "Antes de continuar muéstrame la evidencia de /validar-tarea (salida del terminal y captura a 375 px)."
