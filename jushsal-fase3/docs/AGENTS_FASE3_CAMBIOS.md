# Cambios a `AGENTS.md` al iniciar la Fase 3

**§1 Objetivo → reemplazar por:**
> **Objetivo Fase 3** — Fases 1 y 2 ya implementadas (no modificar salvo para integrar). Ahora: padrón de usuarios de agua (RADA) con predios, comisión, habilidad y consentimiento; importación CSV; notificaciones de convocatorias por comisión (envío asistido). Plan en `PRPs/003--padron-usuarios-notificaciones.md`. **No se escribe código del padrón hasta cerrar la Tarea T0.**

**§3 Fuera de alcance → reemplazar por:**
> Cobranza, tarifas, cálculo automático de habilidad por pagos, API de WhatsApp/SMS de pago, pasarelas, app móvil, migrar WordPress, chatbots (Fase 4 o posterior).

**§4 Roles → añadir fila:**
> `padron` | Gestiona padrón, predios, representantes; importa/exporta; ve DNI. No publica noticias ni envía notificaciones.

**§7 Anti-desvío → añadir:**
> - ¿Toca datos personales? Aplicar `.agents/rules/datos-personales-padron.md`. Si falta información del padrón → preguntar, no inventar campos.
