---
description: Ejecuta UNA tarea de un PRP de JUSHSAL con plan, implementación y validación
---

# /ejecutar-prp  (uso: `/ejecutar-prp 003 T2`)

1. Lee `AGENTS.md`, `.agents/rules/*`, `docs/ESTRUCTURA.md`, `database/schema.sql` y el PRP indicado en `PRPs/<número>--*.md`. Si no se indicó PRP o tarea, pregunta cuál.
2. Verifica que las tareas anteriores del PRP estén completas y que no haya preguntas pendientes en su Tarea T0. Si no, avisa y detente.
3. Crea un **plan de implementación** solo para esa tarea: archivos a crear/editar, tablas implicadas, riesgos. Espera mi aprobación antes de escribir código.
4. Implementa únicamente lo de esa tarea. No toques módulos de otras tareas ni de fases anteriores salvo para integrarlos, y no "arregles de paso".
5. Ejecuta `/validar-tarea` con la validación de esa tarea.
6. Si falla algo, corrige y repite el paso 5. No avances si hay fallos.
7. Resumen final: `PRP · Tarea · Archivos tocados · Validaciones · Pendientes · Siguiente tarea`.
