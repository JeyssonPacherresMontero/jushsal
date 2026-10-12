<?php
/**
 * JUSHSAL - Página Principal de Inicio (index.php)
 * Migrado desde la maqueta estática preservando diseño y estilos visuales CSS.
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/modelos/comisiones.php';
require_once __DIR__ . '/includes/modelos/documentos.php';
require_once __DIR__ . '/includes/modelos/galeria.php';

$comisionesHome = comisiones_listar_publicas();
$documentosHome = documentos_listar_destacados(6);
$fotosGaleria   = galeria_listar_publica();

$tituloPagina = 'Junta de Usuarios San Lorenzo – Sector Hidráulico Menor San Lorenzo';
$descripcionPagina = 'Institución de prestigio dedicada a la administración, operación y distribución eficiente del agua en Tambogrande, Piura.';
$baseUrl = BASE_URL;

include __DIR__ . '/includes/vistas/header.php';
?>

<!-- ===== Portada ===== -->
<section class="hero" id="inicio">
  <div class="hero-overlay"></div>
  <div class="container hero-inner">
    <p class="hero-eyebrow">Sector Hidráulico Menor San Lorenzo</p>
    <h1 class="hero-title">Junta de Usuarios San Lorenzo</h1>
    <p class="hero-subtitle">Institución de prestigio, reconocida por su vocación integradora y la gestión eficiente del recurso hídrico; brindamos servicios de calidad a nuestros usuarios para generar bienestar.</p>
    <div class="hero-actions">
      <a class="btn btn-primary btn-lg" href="#contacto">Contáctanos</a>
      <a class="btn btn-ghost btn-lg" href="#quienes-somos">Conócenos</a>
    </div>
  </div>
  <div class="hero-card">
    <div class="hero-card-item">
      <span class="hero-card-num">+63</span>
      <span class="hero-card-label">Años de gestión hídrica</span>
    </div>
    <div class="hero-card-item">
      <span class="hero-card-num">16</span>
      <span class="hero-card-label">Comisiones de usuarios</span>
    </div>
    <div class="hero-card-item">
      <span class="hero-card-num">1,000+</span>
      <span class="hero-card-label">Usuarios atendidos</span>
    </div>
  </div>
</section>

<!-- ===== Quiénes Somos ===== -->
<section class="section" id="institucion">
  <div class="container grid-2 align-center" id="quienes-somos">
    <div class="about-visual">
      <div class="about-visual-bg"></div>
      <div class="about-visual-content">
        <span class="about-visual-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4z" stroke-linejoin="round"/><path d="M12 2v20" stroke-linejoin="round"/></svg>
        </span>
        <h3>Gestión eficiente del recurso hídrico</h3>
        <p>Vocación integradora al servicio de los agricultores y usuarios de agua de San Lorenzo.</p>
      </div>
    </div>
    <div class="content">
      <p class="eyebrow">¿Quiénes somos?</p>
      <h2 class="section-title">Una institución con vocación integradora</h2>
      <p>Somos la <strong>Junta de Usuarios del Sector Hidráulico Menor San Lorenzo – Clase A</strong>, una institución de prestigio que administra y distribuye el recurso hídrico con eficiencia, promoviendo el desarrollo sostenible de la agricultura en Tambogrande, Piura.</p>
      <p>Nuestra labor se centra en la operación y mantenimiento de la infraestructura de riego, la atención oportuna a nuestros usuarios y la gestión transparente de los recursos.</p>
      <div class="check-list">
        <div class="check-item"><span class="check-ico"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"/></svg></span><span>Operación y mantenimiento de canales de riego</span></div>
        <div class="check-item"><span class="check-ico"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"/></svg></span><span>Servicios de calidad para los usuarios</span></div>
        <div class="check-item"><span class="check-ico"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"/></svg></span><span>Gestión transparente y participativa del agua</span></div>
      </div>
      <a class="btn btn-primary" href="#contacto">Trabajemos juntos</a>
    </div>
  </div>
</section>

<!-- ===== Noticias ===== -->
<?php
require_once __DIR__ . '/includes/modelos/noticias.php';
$noticiasPortada = noticias_listar_publicadas(6);
?>
<section class="section section-alt" id="noticias">
  <div class="container">
    <div class="section-head" style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:16px;">
      <div>
        <p class="eyebrow">Información institucional</p>
        <h2 class="section-title">Últimas noticias</h2>
        <p class="section-desc">Comunicados, actividades y novedades de la Junta de Usuarios San Lorenzo.</p>
      </div>
      <div>
        <a href="noticias.php" class="btn btn-outline">Ver todas las noticias <span>→</span></a>
      </div>
    </div>
    
    <div class="news-grid" id="newsGrid">
      <?php if (empty($noticiasPortada)): ?>
        <div style="grid-column: 1 / -1; text-align:center; padding:50px 20px; background:#fff; border-radius:10px; border:1px solid #e2e8f0; color:#64748b;">
          <p style="margin:0; font-size:15px;">Aún no hay noticias publicadas en el portal institucional.</p>
        </div>
      <?php else: ?>
        <?php foreach ($noticiasPortada as $i => $n): ?>
          <?php $tintClass = 'tint-' . (($i % 4) + 1); ?>
          <article class="news-card">
            <div class="news-media <?= $tintClass ?>">
              <?php if (!empty($n['imagen_portada'])): ?>
                <img class="news-img" src="<?= $baseUrl . e($n['imagen_portada']) ?>" alt="<?= e($n['titulo']) ?>" loading="lazy"/>
              <?php else: ?>
                <span class="news-monogram"><?= e(monograma_noticia($n['titulo'])) ?></span>
              <?php endif; ?>
              <span class="news-badge"><?= e($n['categoria_nombre'] ?? 'Comunicado') ?></span>
            </div>
            <div class="news-body">
              <span class="news-date">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 16H5V9h14v11z"/></svg>
                <?= e(formato_fecha($n['fecha_publicacion'])) ?>
              </span>
              <h3><?= e($n['titulo']) ?></h3>
              <p><?= e($n['resumen']) ?></p>
              <a class="link-more" href="noticia.php?slug=<?= urlencode($n['slug']) ?>">Leer más <span>→</span></a>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ===== Historia ===== -->
<section class="section" id="historia">
  <div class="container grid-2 align-center">
    <div class="content">
      <p class="eyebrow">Trayectoria</p>
      <h2 class="section-title">Nuestra historia</h2>
      <p>La <strong>Junta de Usuarios del Sector Hidráulico Menor San Lorenzo – Clase A</strong> cuenta con más de 63 años de servicio institucional al cuidado y distribución del recurso hídrico en los valles de San Lorenzo.</p>
      <p>A lo largo de las distintas gestiones se ha consolidado la infraestructura hidráulica mayor y menor, velando permanentemente por la justicia en el reparto del agua y el bienestar de los agricultores.</p>
      <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:20px;">
        <a class="btn btn-primary" href="presidentes.php">Ver Presidentes y Logros</a>
        <a class="btn btn-outline" href="#documentos">Documentos oficiales</a>
      </div>
    </div>
    <div class="stats">
      <div class="stat"><span class="stat-num">+63</span><span class="stat-label">Años de servicio institucional</span></div>
      <div class="stat"><span class="stat-num">16</span><span class="stat-label">Comisiones integradas</span></div>
      <div class="stat"><span class="stat-num">Clase A</span><span class="stat-label">Calificación hidráulica</span></div>
      <div class="stat"><span class="stat-num">Piura</span><span class="stat-label">Tambogrande, Perú</span></div>
    </div>
  </div>
</section>

<!-- ===== Documentos ===== -->
<section class="section section-alt" id="documentos">
  <div class="container">
    <div class="section-head center">
      <p class="eyebrow">Transparencia</p>
      <h2 class="section-title">Documentos institucionales</h2>
      <p class="section-desc">Accede a la normativa, tarifas y documentos oficiales de la Junta de Usuarios San Lorenzo.</p>
    </div>
    <div class="docs-grid">
      <?php foreach ($documentosHome as $d): ?>
        <?php
          $urlDoc = $baseUrl . str_replace('%2F', '/', rawurlencode(ltrim($d['archivo_ruta'], '/')));
        ?>
        <div class="doc-card">
          <span class="doc-icon"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 2c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6H6zm7 7V3.5L18.5 9H13z"/></svg></span>
          <h3 title="<?= e($d['titulo']) ?>"><?= e($d['titulo']) ?></h3>
          <p><?= !empty($d['descripcion']) ? e(mb_substr($d['descripcion'], 0, 75)) . '...' : e($d['categoria_nombre']) ?></p>
          <a class="link-more" href="<?= e($urlDoc) ?>" target="_blank" rel="noopener noreferrer">Consultar <span>→</span></a>
        </div>
      <?php endforeach; ?>
    </div>

    <div style="text-align: center; margin-top: 36px;">
      <a href="<?= $baseUrl ?>documentos.php" class="btn btn-outline" style="font-size: 14.5px; padding: 12px 28px;">
        Ver todos los documentos y resoluciones <span>→</span>
      </a>
    </div>
  </div>
</section>

<!-- ===== Comisiones ===== -->
<section class="section" id="comisiones">
  <div class="container">
    <div class="section-head center">
      <p class="eyebrow">Organización</p>
      <h2 class="section-title">Comisiones de usuarios</h2>
      <p class="section-desc">Conoce los 16 sub sectores y comisiones que conforman nuestro sector hidráulico.</p>
    </div>
    <div class="comm-grid" id="commGrid">
      <?php foreach ($comisionesHome as $c): ?>
        <div class="comm-card">
          <div class="comm-monogram"><?= sprintf('%02d', (int)$c['numero']) ?></div>
          <h3><?= e($c['nombre']) ?></h3>
          <p>
            <?php if (!empty($c['presidente_nombre'])): ?>
              Presidente: <strong><?= e($c['presidente_nombre']) ?></strong>
              <?php if (!empty($c['periodo_inicio'])): ?>
                <br><small style="color:var(--muted); font-size:12px;">Periodo <?= e($c['periodo_inicio']) ?>–<?= e($c['periodo_fin'] ?? '2029') ?></small>
              <?php endif; ?>
            <?php else: ?>
              Sub sector N.° <?= (int)$c['numero'] ?> de JUSHSAL
            <?php endif; ?>
          </p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== Servicios ===== -->
<section class="section section-dark" id="servicios">
  <div class="container">
    <div class="section-head center light">
      <p class="eyebrow">Servicios</p>
      <h2 class="section-title">Servicio de maquinaria</h2>
      <p class="section-desc">Ponemos a disposición de nuestros usuarios maquinaria para labores de mantenimiento y rehabilitación de la infraestructura de riego.</p>
    </div>
    <div class="services-grid">
      <div class="service-card">
        <span class="service-ico"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2a3 3 0 0 0 6 0h6a3 3 0 0 0 6 0h1v-5l-2-4zM6 18.5A1.5 1.5 0 1 1 7.5 17 1.5 1.5 0 0 1 6 18.5zm12 0A1.5 1.5 0 1 1 19.5 17 1.5 1.5 0 0 1 18 18.5z"/></svg></span>
        <h3>Limpieza de canales</h3>
        <p>Descolmatación y limpieza de la red de riego.</p>
      </div>
      <div class="service-card">
        <span class="service-ico"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19.1 8.5l.6-1.1c.3-.5 1-1 1.5-.9l.2.1-1.5 1.1a1 1 0 0 0-.8 2.8l1.4 5.8a2 2 0 1 1-3.9.9l1.2-4.9-1.5 1a2.3 2.3 0 0 1-1.2.8H8.9A2 2 0 0 1 7 15.9l-4.8-.6a1.2 1.2 0 0 1-.2-2.4 2 2 0 0 1 2.4 1.4l3.2.4V8H4.8a2.2 2.2 0 0 1-.6-4.3l6.2-2.2c.8-.3 1.6.1 1.9.9l.4 1.1a2 2 0 0 0 1.3 1.2l2.5.7a2 2 0 0 0 2.6 1.1z"/></svg></span>
        <h3>Obras de rehabilitación</h3>
        <p>Mejoramiento de la infraestructura de riego.</p>
      </div>
      <div class="service-card">
        <span class="service-ico"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21 18v1a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3v-1h18zM5 16v-7a7 7 0 0 1 14 0v7h-3v-6.5a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1V16H5zm3-7a4 4 0 0 0 8 0h-8z"/></svg></span>
        <h3>Alquiler de maquinaria</h3>
        <p>Servicio de maquinaria a disposición de los usuarios.</p>
      </div>
    </div>
  </div>
</section>

<!-- ===== Galería ===== -->
<section class="section" id="galeria">
  <div class="container">
    <div class="section-head center">
      <p class="eyebrow">Galería</p>
      <h2 class="section-title">Nuestra comunidad</h2>
      <p class="section-desc">Momentos de nuestras actividades institucionales.</p>
    </div>
    <div class="gallery-grid">
      <?php if (!empty($fotosGaleria)): ?>
        <?php foreach ($fotosGaleria as $foto): ?>
          <?php
            $claseWide = !empty($foto['es_destacada']) ? ' gallery-wide' : '';
            $urlImg = $baseUrl . rawurlencode(ltrim($foto['imagen'], '/'));
            $urlImg = str_replace('%2F', '/', $urlImg);
            $altTexto = !empty($foto['descripcion']) ? $foto['descripcion'] : $foto['titulo'];
          ?>
          <figure class="gallery-item<?= $claseWide ?>">
            <div class="gallery-tile">
              <img src="<?= e($urlImg) ?>" alt="<?= e($altTexto) ?>" loading="lazy"/>
              <span class="gallery-caption"><?= e($foto['titulo']) ?></span>
            </div>
          </figure>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="grid-column: 1 / -1; text-align: center; color: var(--muted); padding: 40px 0;">No hay fotografías disponibles en este momento.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ===== Ubicación ===== -->
<section class="section section-dark" id="ubicacion">
  <div class="container grid-2 align-center">
    <div class="content light">
      <p class="eyebrow">Ubicación</p>
      <h2 class="section-title">Visítanos</h2>
      <p class="section-desc">Atendemos a nuestros usuarios en nuestra sede institucional de lunes a sábado.</p>
      <div class="contact-cards">
        <div class="contact-card">
          <span class="contact-ico"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg></span>
          <div><strong>Dirección</strong><span>Av. Reforma Agraria S/N – La Cruceta – Tambogrande – Piura</span></div>
        </div>
        <div class="contact-card">
          <span class="contact-ico"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 10h5v2h-7V6h2v6z"/></svg></span>
          <div><strong>Horario de trabajo</strong><span>Lun–Vie 8:00 a.m. – 4:00 p.m. · Sáb 8:00 a.m. – 1:00 p.m.</span></div>
        </div>
      </div>
    </div>
    <div class="map">
      <iframe title="Mapa de ubicación de la Junta de Usuarios San Lorenzo" src="https://maps.google.com/maps?q=Junta%20de%20usuarios%20del%20sector%20hidr%C3%A1ulico%20menor%20San%20Lorenzo&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near" loading="lazy"></iframe>
    </div>
  </div>
</section>

<!-- ===== Contacto ===== -->
<section class="section" id="contacto">
  <div class="container grid-2">
    <div class="content">
      <p class="eyebrow">Contáctanos</p>
      <h2 class="section-title">Estamos listos para atenderte</h2>
      <p class="section-desc">Utiliza el canal que desees para comunicarte con nosotros. Respondemos en un plazo de 24 horas.</p>
      <div class="contact-cards">
        <div class="contact-card">
          <span class="contact-ico"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6.5l8 5 8-5V8z"/></svg></span>
          <div><strong>Correo electrónico</strong><a href="mailto:juntasanlorenzo@jusanlorenzo.org.pe">juntasanlorenzo@jusanlorenzo.org.pe</a></div>
        </div>
        <div class="contact-card">
          <span class="contact-ico"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24 11.36 11.36 0 0 0 3.57.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1 11.36 11.36 0 0 0 .57 3.57 1 1 0 0 1-.25 1.02z"/></svg></span>
          <div><strong>Teléfono / WhatsApp</strong><a href="tel:+51942028075">+51 942 028 075</a></div>
        </div>
      </div>
      <div class="social-row">
        <strong>Síguenos:</strong>
        <a class="social-btn" href="https://www.facebook.com/profile.php?id=100076060864594" target="_blank" rel="noopener" aria-label="Facebook">
          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.78-3.89 1.09 0 2.24.2 2.24.2v2.46H15.2c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0 0 22 12z"/></svg>
        </a>
        <a class="social-btn" href="https://w.app/FkwQes" target="_blank" rel="noopener" aria-label="WhatsApp">
          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.66 15L2 22l5.15-1.35A10 10 0 1 0 12 2zm5.2 14.15c-.22.62-1.28 1.2-1.8 1.24-.48.03-1.1.17-3.74-.78-3.16-1.19-5.16-4.24-5.32-4.44-.15-.2-1.27-1.69-1.27-3.22 0-1.53.8-2.28 1.08-2.6.28-.3.62-.38.83-.38h.6c.2 0 .45-.07.7.54.24.6.84 2.06.92 2.2.07.15.12.32.02.52-.08.2-.15.32-.28.5l-.42.5c-.14.14-.29.3-.13.57.17.27.75 1.23 1.6 2 1.09.98 2.01 1.29 2.3 1.43.28.14.45.12.61-.08.17-.2.7-.82.9-1.1.18-.28.37-.23.62-.14.26.1 1.63.77 1.91.91.28.14.47.2.54.32.07.11.07.65-.15 1.28z"/></svg>
        </a>
      </div>
    </div>

    <form class="form" id="contactForm">
      <h3 class="form-title">Formulario de contacto</h3>
      <div class="form-grid">
        <div class="field">
          <label for="f-nombre">Nombres</label>
          <input id="f-nombre" name="nombre" type="text" required/>
        </div>
        <div class="field">
          <label for="f-apellidos">Apellidos</label>
          <input id="f-apellidos" name="apellidos" type="text" required/>
        </div>
        <div class="field">
          <label for="f-email">Correo electrónico</label>
          <input id="f-email" name="email" type="email" required/>
        </div>
        <div class="field">
          <label for="f-celular">Celular</label>
          <input id="f-celular" name="celular" type="tel"/>
        </div>
        <div class="field field-full">
          <label for="f-mensaje">Escribe aquí tu consulta</label>
          <textarea id="f-mensaje" name="mensaje" rows="5" required></textarea>
        </div>
      </div>
      <button class="btn btn-primary btn-lg btn-block" type="submit">Enviar mensaje</button>
      <p class="form-note" id="formNote"></p>
    </form>
  </div>
</section>

<?php
include __DIR__ . '/includes/vistas/footer.php';
?>
