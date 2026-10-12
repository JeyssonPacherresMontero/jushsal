/* ============================================================
   Módulo de noticias
   - Noticias por defecto (editables en NOTICIAS_POR_DEFECTO)
   - Publicación local: se guarda en el navegador (localStorage)
   ============================================================ */

var NOTICIAS_POR_DEFECTO = [
  {
    titulo: "Inscripción de nuevos usuarios para el presente año agrícola",
    categoria: "Comunicado",
    fecha: "2026-09-10",
    resumen: "La Junta de Usuarios San Lorenzo comunica el inicio del proceso de inscripción y actualización de datos de los usuarios del sector hidráulico para el nuevo año agrícola.",
    imagen: "assets/images/repartidor.jpg"
  },
  {
    titulo: "Mantenimiento preventivo de la red de canales de riego",
    categoria: "Gestión",
    fecha: "2026-08-28",
    resumen: "Se realizarán trabajos de limpieza y mantenimiento de los canales principales, programados por comisión de usuarios. Coordina con tu comité el cronograma correspondiente.",
    imagen: "assets/images/valvula.jpg"
  },
  {
    titulo: "Asamblea general ordinaria de usuarios convocada",
    categoria: "Actividad",
    fecha: "2026-08-05",
    resumen: "Convocamos a todos los usuarios a la asamblea general ordinaria donde se informarán los acuerdos de gestión y el plan de inversiones del sector.",
    imagen: "assets/images/asamblea.jpg"
  },
  {
    titulo: "Campaña de concientización sobre uso eficiente del agua",
    categoria: "Campaña",
    fecha: "2026-07-19",
    resumen: "Iniciamos una campaña de sensibilización para promover el ahorro y la distribución equitativa del recurso hídrico entre los agricultores de la zona.",
    imagen: "assets/images/reservorio2.jpg"
  }
];

var KEY_NOTICIAS = "jusanlorenzo_noticias";

function obtenerNoticias() {
  var pub = [];
  try {
    var raw = localStorage.getItem(KEY_NOTICIAS);
    if (raw) {
      pub = JSON.parse(raw);
      if (!Array.isArray(pub)) pub = [];
    }
  } catch (e) {
    pub = [];
  }
  var todas = pub.concat(NOTICIAS_POR_DEFECTO);
  return todas.sort(function (a, b) {
    return new Date(b.fecha || 0) - new Date(a.fecha || 0);
  });
}

function guardarNoticia(nueva) {
  var pub = [];
  try {
    var raw = localStorage.getItem(KEY_NOTICIAS);
    if (raw) pub = JSON.parse(raw) || [];
  } catch (e) {
    pub = [];
  }
  pub.unshift(nueva);
  localStorage.setItem(KEY_NOTICIAS, JSON.stringify(pub));
}

function anioActual() {
  return new Date().getFullYear().toString();
}

function formatoFecha(fecha) {
  if (!fecha) return "";
  var partes = String(fecha).split("-");
  if (partes.length !== 3) return fecha;
  var meses = ["ene", "feb", "mar", "abr", "may", "jun", "jul", "ago", "sep", "oct", "nov", "dic"];
  var m = parseInt(partes[1], 10) - 1;
  return partes[2] + " " + meses[m] + " " + partes[0];
}

function monograma(texto) {
  var limpio = (texto || "").trim().replace(/[^a-zA-ZñÑáéíóúÁÉÍÓÚ0-9 ]/g, "");
  var palabras = limpio.split(/\s+/).filter(Boolean);
  if (palabras.length === 0) return "N";
  if (palabras.length === 1) return palabras[0].charAt(0).toUpperCase();
  return (palabras[0].charAt(0) + palabras[palabras.length - 1].charAt(0)).toUpperCase();
}

function renderNoticias() {
  var grid = document.getElementById("newsGrid");
  if (!grid) return;
  var noticias = obtenerNoticias();
  if (noticias.length === 0) {
    grid.innerHTML = '<div class="empty-news">Aún no hay noticias publicadas.</div>';
    return;
  }
  var html = noticias.slice(0, 6).map(function (n, i) {
    var tint = "tint-" + ((i % 4) + 1);
    var media;
    if (n.imagen) {
      media =
        '<img class="news-img" src="' + n.imagen + '" alt="' + n.titulo + '" loading="lazy"/>';
    } else {
      media = '<span class="news-monogram">' + monograma(n.titulo) + "</span>";
    }
    return (
      '<article class="news-card">' +
        '<div class="news-media ' + tint + '">' +
          media +
          '<span class="news-badge">' + (n.categoria || "Noticia") + "</span>" +
        "</div>" +
        '<div class="news-body">' +
          '<span class="news-date">' +
            '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 16H5V9h14v11z"/></svg>' +
            (n.fecha ? "Publicado: " + formatoFecha(n.fecha) : "Reciente") +
          "</span>" +
          "<h3>" + n.titulo + "</h3>" +
          "<p>" + n.resumen + "</p>" +
          '<a class="link-more" href="#noticias">Leer más <span>→</span></a>' +
        "</div>" +
      "</article>"
    );
  }).join("");
  grid.innerHTML = html;
}

/* ================= Publicación ================= */

function leerImagenInput(input, callback) {
  var archivo = input.files && input.files[0];
  if (!archivo) {
    callback(null);
    return;
  }
  if (!window.FileReader) {
    callback(null);
    return;
  }
  var reader = new FileReader();
  reader.onload = function () {
    var img = new Image();
    img.onload = function () {
      var MAX = 1200;
      var escala = Math.min(1, MAX / img.width);
      var ancho = Math.round(img.width * escala);
      var alto = Math.round(img.height * escala);
      var canvas = document.createElement("canvas");
      canvas.width = ancho;
      canvas.height = alto;
      var ctx = canvas.getContext("2d");
      ctx.drawImage(img, 0, 0, ancho, alto);
      callback(Math.max(0.7, escala) >= 1 ? canvas.toDataURL("image/jpeg", 0.82) : canvas.toDataURL("image/jpeg", 0.82));
    };
    img.onerror = function () { callback(null); };
    img.src = reader.result;
  };
  reader.onerror = function () { callback(null); };
  reader.readAsDataURL(archivo);
}

function abrirModalNoticia() {
  var modal = document.getElementById("modalNoticia");
  if (!modal) return;
  var hoy = new Date().toISOString().split("T")[0];
  var campoFecha = document.getElementById("n-fecha");
  if (campoFecha) campoFecha.value = hoy;
  var form = document.getElementById("formNoticia");
  if (form) form.reset();
  if (campoFecha) campoFecha.value = hoy;
  modal.classList.add("open");
  modal.setAttribute("aria-hidden", "false");
  var primerCampo = document.getElementById("n-titulo");
  if (primerCampo) primerCampo.focus();
}

function cerrarModalNoticia() {
  var modal = document.getElementById("modalNoticia");
  if (!modal) return;
  modal.classList.remove("open");
  modal.setAttribute("aria-hidden", "true");
}

function initNoticias() {
  var btn = document.getElementById("btnAbrirPublicacion");
  if (btn) btn.addEventListener("click", abrirModalNoticia);

  var form = document.getElementById("formNoticia");
  if (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var titulo = document.getElementById("n-titulo").value.trim();
      var categoria = document.getElementById("n-categoria").value.trim();
      var fecha = document.getElementById("n-fecha").value;
      var resumen = document.getElementById("n-resumen").value.trim();
      if (!titulo || !fecha || !resumen) return;

      leerImagenInput(document.getElementById("n-imagen"), function (imagen) {
        guardarNoticia({
          titulo: titulo,
          categoria: categoria || "Comunicado",
          fecha: fecha,
          resumen: resumen,
          imagen: imagen
        });
        cerrarModalNoticia();
        renderNoticias();
        var seccion = document.getElementById("noticias");
        if (seccion) seccion.scrollIntoView({ behavior: "smooth" });
      });
    });
  }

  var cerrarBtns = document.querySelectorAll("[data-cerrar-modal]");
  cerrarBtns.forEach(function (el) {
    el.addEventListener("click", cerrarModalNoticia);
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") cerrarModalNoticia();
  });

  renderNoticias();
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initNoticias);
} else {
  initNoticias();
}