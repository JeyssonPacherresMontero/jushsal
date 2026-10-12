/* ============================================================
   Junta de Usuarios San Lorenzo – Funciones principales
   ============================================================ */

(function () {
  "use strict";

  /* ===== Fecha dinámica ===== */
  function setAnio() {
    var el = document.getElementById("year");
    if (el) el.textContent = new Date().getFullYear().toString();
  }

  /* ===== Cabecera con sombra al hacer scroll ===== */
  function initHeader() {
    var header = document.getElementById("header");
    if (!header) return;
    var onScroll = function () {
      if (window.scrollY > 20) header.classList.add("scrolled");
      else header.classList.remove("scrolled");
    };
    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();
  }

  /* ===== Menú móvil + submenús optimizados ===== */
  function initNav() {
    var toggle = document.getElementById("navToggle");
    var nav = document.getElementById("nav");
    if (!toggle || !nav) return;

    toggle.addEventListener("click", function () {
      var abierto = nav.classList.toggle("open");
      toggle.classList.toggle("active", abierto);
      toggle.setAttribute("aria-expanded", abierto ? "true" : "false");
      toggle.setAttribute("aria-label", abierto ? "Cerrar menú" : "Abrir menú");
    });

    /* Submenús en móvil: clic abre/cierra */
    nav.querySelectorAll(".nav-item.has-sub > .nav-link").forEach(function (link) {
      link.addEventListener("click", function (e) {
        if (window.innerWidth <= 860) {
          e.preventDefault();
          link.parentElement.classList.toggle("open");
        }
      });
    });

    /* Cerrar al hacer clic en un enlace normal (móvil) */
    nav.querySelectorAll(".nav-link, .submenu a").forEach(function (a) {
      a.addEventListener("click", function () {
        var esSubmenuPadre = a.parentElement.classList.contains("has-sub");
        if (window.innerWidth > 860) return;
        if (esSubmenuPadre) return;
        nav.classList.remove("open");
        toggle.classList.remove("active");
        toggle.setAttribute("aria-expanded", "false");
      });
    });

    /* Cerrar al hacer clic fuera (móvil) */
    document.addEventListener("click", function (e) {
      if (window.innerWidth <= 860 && nav.classList.contains("open")) {
        if (!nav.contains(e.target) && !toggle.contains(e.target)) {
          nav.classList.remove("open");
          toggle.classList.remove("active");
          toggle.setAttribute("aria-expanded", "false");
        }
      }
    });
  }

  /* ===== Formulario de contacto ===== */
  function initForm() {
    var form = document.getElementById("contactForm");
    var nota = document.getElementById("formNote");
    if (!form) return;

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var nombre = (document.getElementById("f-nombre") || {}).value || "";
      var apellidos = (document.getElementById("f-apellidos") || {}).value || "";
      var email = (document.getElementById("f-email") || {}).value || "";
      var celular = (document.getElementById("f-celular") || {}).value || "";
      var mensaje = (document.getElementById("f-mensaje") || {}).value || "";
      var btnSubmit = form.querySelector('button[type="submit"]');

      if (!nombre || !apellidos || !email || !mensaje) {
        nota.className = "form-note err";
        nota.textContent = "Por favor completa todos los campos obligatorios.";
        return;
      }

      if (btnSubmit) {
        btnSubmit.disabled = true;
        btnSubmit.textContent = "Enviando mensaje...";
      }

      nota.className = "form-note";
      nota.textContent = "Registrando tu consulta institucional...";

      var formData = new FormData(form);

      fetch("api/contacto_enviar.php", {
        method: "POST",
        body: formData
      })
      .then(function (res) {
        return res.json().then(function (data) {
          return { ok: res.ok, data: data };
        });
      })
      .then(function (resultado) {
        if (resultado.ok && resultado.data.exito) {
          nota.className = "form-note ok";
          nota.textContent = "¡Gracias " + nombre + "! Tu mensaje ha sido registrado exitosamente. Si prefieres atención inmediata, abrimos WhatsApp.";

          // Abrir WhatsApp con el mensaje preformateado
          var url = "https://w.app/FkwQes";
          var texto = encodeURIComponent(
            "Hola, soy " + nombre + " " + apellidos +
            " (" + email + (celular ? " - Cel: " + celular : "") + "). Consulta: " + mensaje
          );
          window.open(url + "?text=" + texto, "_blank", "noopener");

          form.reset();
        } else {
          nota.className = "form-note err";
          nota.textContent = (resultado.data && resultado.data.error) ? resultado.data.error : "No se pudo registrar el mensaje.";
        }
      })
      .catch(function () {
        nota.className = "form-note err";
        nota.textContent = "Error de conexión al enviar el formulario. Inténtalo nuevamente.";
      })
      .finally(function () {
        if (btnSubmit) {
          btnSubmit.disabled = false;
          btnSubmit.textContent = "Enviar mensaje";
        }
      });
    });
  }

  /* ===== Inicio ===== */
  function init() {
    setAnio();
    initHeader();
    initNav();
    initForm();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();