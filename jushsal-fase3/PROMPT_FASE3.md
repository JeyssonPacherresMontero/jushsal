# Prompt — Fase 3 (padrón + notificaciones) en Antigravity

## Antes de empezar
1. Fases 1 (y 2 si la hiciste) validadas con `/validar-tarea`; **respaldo de BD** (phpMyAdmin → Exportar) y copia/commit del proyecto.
2. Copiar este paquete sobre el proyecto (reemplaza `.agents/workflows/ejecutar-prp.md`, que ahora acepta número de PRP).
3. Aplicar los cambios de `docs/AGENTS_FASE3_CAMBIOS.md` a `AGENTS.md`.
4. Conseguir las respuestas de `docs/FASE3_CUESTIONARIO.md` (al menos A y B) y una muestra **anonimizada** del Excel del padrón.
5. Verificar en *Customizations → Rules* que `datos-personales-padron` esté en **Always On**.

## Prompt para la Tarea T0 (nueva conversación)
```
Lee AGENTS.md, .agents/rules/*, docs/ESTRUCTURA.md, database/schema.sql,
PRPs/003--padron-usuarios-notificaciones.md, database/003_fase3_BORRADOR.sql y docs/FASE3_CUESTIONARIO.md.

Estamos en la Tarea T0 (levantamiento). NO escribas código de la aplicación.
1) Hazme las preguntas del cuestionario que aún no tengan respuesta, de a 4–5 por mensaje.
2) Con mis respuestas y la muestra anonimizada del Excel (te la adjunto), propón:
   - lista final de campos de usuarios_agua / predios / representantes, justificando cada campo;
   - ajustes al SQL borrador y el archivo database/003_fase3.sql;
   - definición de "usuario hábil" tal como la Junta la entiende (sin interpretar la ley);
   - plantilla CSV de importación.
3) Actualiza el PRP-003 con una sección "Decisiones de T0" y espera mi aprobación escrita.
No inventes campos ni reglas. Datos de prueba siempre ficticios y marcados [DEMO].
```

## Luego, tarea por tarea (una conversación por tarea)
`/ejecutar-prp 003 T1` → `/validar-tarea` → `/ejecutar-prp 003 T2` … hasta T9 (T8 solo si se confirmó).
Si el agente se desvía o inventa datos: `/reencuadrar`.

## Para futuras fases
`/crear-prp 004 tarifas-cobranza` (el workflow ya existe en este paquete).
