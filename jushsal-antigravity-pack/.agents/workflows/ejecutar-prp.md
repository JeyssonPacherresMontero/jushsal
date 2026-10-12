---
description: Ejecuta UNA tarea (T1–T9) del PRP-001 de JUSHSAL con plan, implementación y validación
---

# /ejecutar-prp  (uso: `/ejecutar-prp T3`)

1. Lee `AGENTS.md`, `.agents/rules/*`, `docs/ESTRUCTURA.md`, `database/schema.sql` y `PRPs/001--comunicaciones-noticias-presidentes-popup.md`.
2. Identifica la tarea pedida (si no se indicó, pregunta cuál: T1–T9). Verifica que las tareas anteriores estén completas; si no, avisa y detente.
3. Crea un **plan de implementación** solo para esa tarea: archivos a crear/editar, tablas implicadas, riesgos. Espera mi aprobación antes de escribir código.
4. Implementa únicamente lo de esa tarea. No toques módulos de otras tareas ni "arregles de paso".
5. Ejecuta el workflow `/validar-tarea` con la validación de esa tarea.
6. Si falla algo, corrige y repite el paso 5. No avances si hay fallos.
7. Entrega el resumen: `Tarea · Archivos creados/modificados · Resultado de validaciones · Pendientes · Siguiente tarea`.
