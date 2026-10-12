<?php
/**
 * JUSHSAL - Pie de Página Modular (footer.php)
 * Incluye pie en 4 columnas, botón de WhatsApp, enlaces legales y scripts.
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

$baseUrl = defined('BASE_URL') ? BASE_URL : './';
?>
</main>

<!-- ===== Pie de página ===== -->
<footer class="footer">
  <div class="container footer-grid">
    <div class="footer-col footer-about">
      <img class="brand-logo brand-logo-light" src="<?= $baseUrl ?>assets/images/logo.png" alt="Logo Junta de Usuarios San Lorenzo"/>
      <p>Somos una institución de prestigio, reconocida por su vocación integradora y gestión eficiente del recurso hídrico; brindamos servicios de calidad a nuestros usuarios para generar bienestar.</p>
    </div>
    <div class="footer-col">
      <h4>Institucional</h4>
      <ul class="footer-links">
        <li><a href="<?= $baseUrl ?>index.php#quienes-somos">Quiénes Somos</a></li>
        <li><a href="<?= $baseUrl ?>index.php#historia">Nuestra Historia</a></li>
        <li><a href="<?= $baseUrl ?>presidentes.php">Presidentes y Gestiones</a></li>
        <li><a href="<?= $baseUrl ?>documentos.php">Documentos Institucionales</a></li>
        <li><a href="<?= $baseUrl ?>comisiones.php">Comisiones de Usuarios</a></li>
        <li><a href="<?= $baseUrl ?>index.php#servicios">Servicio de Maquinaria</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Canales de atención</h4>
      <ul class="footer-links">
        <li><a href="mailto:juntasanlorenzo@jusanlorenzo.org.pe">juntasanlorenzo@jusanlorenzo.org.pe</a></li>
        <li><a href="tel:+51942028075">+51 942 028 075</a></li>
        <li><span>Lunes a viernes: 8:00 a.m. – 4:00 p.m.</span></li>
        <li><span>Sábado: 8:00 a.m. – 1:00 p.m.</span></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Enlaces rápidos</h4>
      <ul class="footer-links">
        <li><a href="<?= $baseUrl ?>index.php#inicio">Inicio</a></li>
        <li><a href="<?= $baseUrl ?>index.php#noticias">Noticias</a></li>
        <li><a href="<?= $baseUrl ?>index.php#galeria">Galería</a></li>
        <li><a href="<?= $baseUrl ?>index.php#contacto">Contáctanos</a></li>
        <li><a href="<?= $baseUrl ?>admin/login.php" style="opacity: 0.8;"><small>Acceso al Panel Administrativo</small></a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="container footer-bottom-inner">
      <span>© <span id="year"><?= date('Y') ?></span> Junta de Usuarios San Lorenzo · Sector Hidráulico Menor San Lorenzo</span>
      <span>Tambogrande – Piura – Perú</span>
    </div>
  </div>
</footer>

<div class="wa-float">
  <a href="https://w.app/FkwQes" target="_blank" rel="noopener" aria-label="Chatear por WhatsApp">
    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.66 15L2 22l5.15-1.35A10 10 0 1 0 12 2zm5.2 14.15c-.22.62-1.28 1.2-1.8 1.24-.48.03-1.1.17-3.74-.78-3.16-1.19-5.16-4.24-5.32-4.44-.15-.2-1.27-1.69-1.27-3.22 0-1.53.8-2.28 1.08-2.6.28-.3.62-.38.83-.38h.6c.2 0 .45-.07.7.54.24.6.84 2.06.92 2.2.07.15.12.32.02.52-.08.2-.15.32-.28.5l-.42.5c-.14.14-.29.3-.13.57.17.27.75 1.23 1.6 2 1.09.98 2.01 1.29 2.3 1.43.28.14.45.12.61-.08.17-.2.7-.82.9-1.1.18-.28.37-.23.62-.14.26.1 1.63.77 1.91.91.28.14.47.2.54.32.07.11.07.65-.15 1.28z"/></svg>
  </a>
</div>

<script>
  window.JUSHSAL_BASE_URL = <?= json_encode($baseUrl) ?>;
</script>
<script src="<?= $baseUrl ?>js/script.js"></script>
<script src="<?= $baseUrl ?>js/popup.js" defer></script>
</body>
</html>
