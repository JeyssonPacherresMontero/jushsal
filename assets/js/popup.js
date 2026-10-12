/**
 * JUSHSAL - Popup de Convocatorias a Asamblea
 * Consulta api/convocatoria_activa.php y muestra el modal con vigencia, retraso, duración y frecuencia.
 */

(function () {
  'use strict';

  function initPopupConvocatoria() {
    var baseUrl = window.JUSHSAL_BASE_URL || '';
    if (!baseUrl.endsWith('/')) {
      baseUrl += '/';
    }

    var apiUrl = baseUrl + 'api/convocatoria_activa.php';

    fetch(apiUrl, { cache: 'no-store' })
      .then(function (response) {
        if (!response.ok) {
          throw new Error('Error en respuesta de API convocatoria: ' + response.status);
        }
        return response.json();
      })
      .then(function (data) {
        if (!data || !data.activa) {
          return;
        }
        procesarConvocatoria(data);
      })
      .catch(function (err) {
        console.warn('JUSHSAL Popup:', err);
      });
  }

  function procesarConvocatoria(data) {
    var storageKey = 'jushsal_conv_' + data.id + '_' + (data.version || 'v1');
    var hoy = new Date().toISOString().slice(0, 10);

    // Evaluación de frecuencia configurada
    if (data.frecuencia === 'una_vez_por_sesion') {
      try {
        if (sessionStorage.getItem(storageKey)) {
          return;
        }
      } catch (e) {}
    } else if (data.frecuencia === 'una_vez_por_dia') {
      try {
        if (localStorage.getItem(storageKey) === hoy) {
          return;
        }
      } catch (e) {}
    }

    var retrasoMs = Math.max(0, parseInt(data.retraso_seg, 10) || 0) * 1000;

    setTimeout(function () {
      mostrarModalConvocatoria(data, storageKey, hoy);
    }, retrasoMs);
  }

  function mostrarModalConvocatoria(data, storageKey, hoy) {
    // Registrar en storage para respetar la frecuencia
    try {
      if (data.frecuencia === 'una_vez_por_sesion') {
        sessionStorage.setItem(storageKey, '1');
      } else if (data.frecuencia === 'una_vez_por_dia') {
        localStorage.setItem(storageKey, hoy);
      }
    } catch (e) {}

    // Eliminar modal anterior si existiera
    var existente = document.getElementById('popupConvocatoria');
    if (existente) {
      existente.remove();
    }

    var wrapper = document.createElement('div');
    wrapper.id = 'popupConvocatoria';
    wrapper.className = 'modal popup-convocatoria';
    wrapper.setAttribute('role', 'dialog');
    wrapper.setAttribute('aria-modal', 'true');
    wrapper.setAttribute('aria-labelledby', 'popupConvocatoriaTitle');

    wrapper.innerHTML =
      '<div class="modal-backdrop" id="popupConvocatoriaBackdrop"></div>' +
      '<div class="modal-card">' +
        '<button type="button" class="modal-close" id="popupConvocatoriaClose" aria-label="Cerrar convocatoria">&times;</button>' +
        '<div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">' +
          '<span style="background:var(--primary); color:#fff; font-size:11px; font-weight:700; padding:3px 8px; border-radius:4px; text-transform:uppercase; letter-spacing:.5px;">Convocatoria Oficial</span>' +
        '</div>' +
        '<h3 id="popupConvocatoriaTitle" style="margin-top:2px; margin-bottom:10px; font-size:20px; line-height:1.3; color:var(--primary-darker);"></h3>' +
        '<div class="popup-convocatoria-img-wrapper">' +
          '<img id="popupConvocatoriaImg" class="popup-convocatoria-img" alt="Convocatoria a Asamblea JUSHSAL">' +
        '</div>' +
        '<div class="popup-convocatoria-footer">' +
          '<a id="popupConvocatoriaVerDoc" href="#" target="_blank" rel="noopener noreferrer" class="btn btn-primary" style="font-size:13px; padding:8px 16px;">' +
            'Ver documento completo ↗' +
          '</a>' +
          '<button type="button" id="popupConvocatoriaBtnCerrar" class="btn btn-outline" style="font-size:13px; padding:8px 16px;">' +
            'Entendido / Cerrar' +
          '</button>' +
        '</div>' +
      '</div>';

    document.body.appendChild(wrapper);

    // Asignar datos seguros evitando XSS
    var titleEl = document.getElementById('popupConvocatoriaTitle');
    if (titleEl) {
      titleEl.textContent = data.titulo || 'Convocatoria a Asamblea General';
    }

    var imgEl = document.getElementById('popupConvocatoriaImg');
    if (imgEl && data.imagen_url) {
      imgEl.src = data.imagen_url;
      imgEl.alt = data.titulo || 'Convocatoria';
    }

    var verDocEl = document.getElementById('popupConvocatoriaVerDoc');
    if (verDocEl && data.imagen_url) {
      verDocEl.href = data.imagen_url;
    }

    // Abrir modal con animación de la maqueta
    requestAnimationFrame(function () {
      wrapper.classList.add('open');
      document.body.style.overflow = 'hidden';
    });

    function cerrar() {
      wrapper.classList.remove('open');
      document.body.style.overflow = '';
      setTimeout(function () {
        if (wrapper && wrapper.parentNode) {
          wrapper.parentNode.removeChild(wrapper);
        }
      }, 300);
      document.removeEventListener('keydown', onKeyDown);
    }

    function onKeyDown(e) {
      if (e.key === 'Escape' || e.key === 'Esc') {
        cerrar();
      }
    }

    var closeBtn = document.getElementById('popupConvocatoriaClose');
    if (closeBtn) closeBtn.addEventListener('click', cerrar);

    var btnCerrar = document.getElementById('popupConvocatoriaBtnCerrar');
    if (btnCerrar) btnCerrar.addEventListener('click', cerrar);

    var backdrop = document.getElementById('popupConvocatoriaBackdrop');
    if (backdrop) backdrop.addEventListener('click', cerrar);

    document.addEventListener('keydown', onKeyDown);

    // Cierre automático si duracion_seg > 0
    var duracionSeg = parseInt(data.duracion_seg, 10) || 0;
    if (duracionSeg > 0) {
      setTimeout(function () {
        cerrar();
      }, duracionSeg * 1000);
    }
  }

  // Exportar helper para pruebas desde el panel admin
  window.JUSHSAL_PROBAR_POPUP = function (data) {
    mostrarModalConvocatoria(data, 'jushsal_test', 'test');
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPopupConvocatoria);
  } else {
    initPopupConvocatoria();
  }
})();
