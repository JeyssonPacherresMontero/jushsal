# Estructura del proyecto (XAMPP · `C:\xampp\htdocs\jushsal\`)

```
jushsal/
├── index.php                  # Inicio (migrado de index.html; mismo diseño)
├── noticias.php               # Listado público paginado (solo estado=publicado)
├── noticia.php                # Detalle por ?slug=
├── presidentes.php            # Historial público: gestiones → logros por año
├── comisiones.php             # 16 comisiones (desde BD)
├── documentos.php             # (reusa la carpeta documentos/ existente)
│
├── includes/
│   ├── config.php             # credenciales BD, BASE_URL, zona horaria  (NO versionar)
│   ├── config.example.php
│   ├── db.php                 # PDO (parte de conexion.php actual)
│   ├── auth.php               # login, sesión, requerir_login(), requerir_rol()
│   ├── csrf.php               # csrf_token(), csrf_verificar()
│   ├── helpers.php            # e(), slugify(), formato_fecha(), redirigir(), flash()
│   ├── upload.php             # subir_imagen($file,$carpeta) con finfo + límites
│   ├── bitacora.php           # registrar_bitacora()
│   ├── modelos/
│   │   ├── noticias.php
│   │   ├── gestiones.php      # gestiones + logros
│   │   ├── convocatorias.php
│   │   └── comisiones.php
│   └── vistas/                # header.php, footer.php, popup_convocatoria.php
│
├── admin/
│   ├── login.php · logout.php · index.php (dashboard)
│   ├── noticias/      listar.php · form.php · guardar.php · eliminar.php
│   ├── convocatorias/ listar.php · form.php · guardar.php · eliminar.php
│   ├── presidentes/   listar.php · form.php (gestión) · logros.php (hitos por año) · guardar.php
│   ├── comisiones/    listar.php · form.php
│   ├── usuarios/      (solo admin)
│   └── bitacora.php   (solo admin)
│
├── api/
│   └── convocatoria_activa.php   # JSON: convocatoria vigente (o {"activa":false})
│
├── assets/
│   ├── css/style.css          # el existente + admin.css
│   ├── js/script.js           # existente (menú, header)
│   ├── js/popup.js            # lógica del popup (retraso, duración, frecuencia)
│   └── images/                # existentes
├── uploads/                   # imágenes subidas (fuera de assets/)
│   ├── .htaccess              # php_flag engine off + bloquear .php/.phtml
│   ├── noticias/ · convocatorias/ · presidentes/ · logros/
├── documentos/                # PDFs institucionales existentes
├── database/  schema.sql · seed.sql · crear_admin.php (CLI)
├── PRPs/      001--comunicaciones-noticias-presidentes-popup.md
├── AGENTS.md                  # reglas permanentes (Antigravity lo lee solo)
└── .agents/
    ├── rules/      seguridad-php.md · datos-reales-jushsal.md
    ├── workflows/  ejecutar-prp.md · validar-tarea.md · reencuadrar.md
    └── skills/jushsal-php-xampp/SKILL.md
```

## Mapa de lo que se migra de la maqueta
| Maqueta | Destino |
|---|---|
| `index.html` (secciones) | `index.php` + `includes/vistas/header|footer.php` |
| `js/noticias.js` (localStorage + `NOTICIAS_POR_DEFECTO`) | **Se elimina.** Noticias desde MySQL |
| Botón/modal "Publicar noticia" público | **Se elimina** del público; formulario vive en `admin/noticias/form.php` |
| `COMISIONES` (4 inventadas en `script.js`) | Tabla `comisiones` (16 reales) |
| `conexion.php` | `includes/db.php` + `includes/config.php` |
| Sección "Nuestra historia" (+50 años / 1980) | Texto corregido + enlace a `presidentes.php` |
| Formulario de contacto (abre WhatsApp) | Se mantiene igual en Fase 1 |

## Flujo del popup de convocatoria
1. Comunicaciones sube la **imagen** (jpg/png/webp ≤ 5 MB) y define: vigencia (`mostrar_desde/hasta`), `retraso_seg`, `duracion_seg`, `frecuencia`.
2. `index.php` (y solo páginas públicas) incluye `popup.js`, que llama a `api/convocatoria_activa.php`.
3. El API devuelve la convocatoria con `activo=1` y `NOW()` dentro de la vigencia (la más reciente si hay varias).
4. `popup.js` espera `retraso_seg` → abre modal con la imagen (scroll interno, `max-height:90vh`, botón "Ver imagen completa" en pestaña nueva) → si `duracion_seg>0` cierra solo.
5. Frecuencia controlada en el navegador: `sessionStorage` / `localStorage` con clave `conv_<id>_<updated_at>`; si se edita la convocatoria, vuelve a mostrarse.
6. Cierre con ×, clic fuera y tecla Esc. Accesible: `role="dialog"`, `aria-modal`, foco atrapado, `alt` = título.
