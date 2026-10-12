---
description: Crea un nuevo PRP (fase o módulo) de JUSHSAL con el formato del PRP-001, sin escribir código
---

# /crear-prp  (uso: `/crear-prp 003 padron-usuarios-notificaciones`)

1. Lee `AGENTS.md`, `.agents/rules/*`, `docs/ESTRUCTURA.md`, `docs/ROADMAP.md`, `database/schema.sql` y `PRPs/001--*.md` (plantilla de formato).
2. Si el PRP depende de información externa (reglas, campos reales), **pregúntame primero** lo que falte; no asumas ni inventes.
3. Redacta `PRPs/<número>--<nombre>.md` con: Goal, Why, What + criterios de éxito medibles, contexto y gotchas, tareas T0…Tn en orden con "✅ Validar", bucle de validación en PowerShell (XAMPP), checklist final y anti-patrones.
4. Si hay tablas nuevas, crea `database/<número>_<nombre>.sql` con SOLO `CREATE TABLE IF NOT EXISTS` y sin tocar tablas existentes (cambios a existentes → archivo de migración aparte).
5. NO escribas código de la aplicación. Muéstrame el PRP y el SQL para aprobación.
