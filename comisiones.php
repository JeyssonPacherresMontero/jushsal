<?php
/**
 * JUSHSAL - Comisiones de Usuarios (comisiones.php)
 * Información institucional de los 16 sub sectores hidráulicos que conforman JUSHSAL.
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/modelos/comisiones.php';

$comisiones = comisiones_listar_publicas();

$tituloPagina = 'Comisiones de Usuarios | Junta de Usuarios San Lorenzo';
$descripcionPagina = 'Conoce las 16 Comisiones de Usuarios y sub sectores que integran el Sector Hidráulico Menor San Lorenzo – Clase A.';
$baseUrl = BASE_URL;

include __DIR__ . '/includes/vistas/header.php';
?>

<section class="section section-alt" style="padding-top:40px;">
  <div class="container">
    
    <div class="section-head center">
      <p class="eyebrow">Organización y Representación Territorial</p>
      <h1 class="section-title">Comisiones de Usuarios</h1>
      <p class="section-desc">
        El Sector Hidráulico Menor San Lorenzo – Clase A está organizado territorial y operativamente en 16 comisiones de usuarios para una distribución eficiente y equitativa del agua de riego.
      </p>
    </div>

    <!-- Grid de las 16 comisiones -->
    <div class="comm-grid" style="margin-top:20px;">
      <?php foreach ($comisiones as $c): ?>
        <div class="comm-card" style="display:flex; flex-direction:column; justify-content:space-between;">
          <div>
            <div class="comm-monogram"><?= sprintf('%02d', (int)$c['numero']) ?></div>
            <h3 style="font-size:17px; margin-bottom:8px; color:var(--primary-darker);"><?= e($c['nombre']) ?></h3>
            
            <div style="font-size:13px; color:var(--muted); margin-top:8px; line-height:1.4;">
              <?php if (!empty($c['presidente_nombre'])): ?>
                <div style="margin-bottom:4px;">
                  <span style="display:block; font-size:11px; text-transform:uppercase; color:#64748b; font-weight:600;">Presidente:</span>
                  <strong style="color:#0b3d76; font-size:14px;"><?= e($c['presidente_nombre']) ?></strong>
                </div>
                <?php if (!empty($c['periodo_inicio'])): ?>
                  <span style="display:inline-block; font-size:12px; background:#e0f2fe; color:#0369a1; padding:2px 8px; border-radius:10px; font-weight:600; margin-top:4px;">
                    Periodo <?= e($c['periodo_inicio']) ?>–<?= e($c['periodo_fin'] ?? '2029') ?>
                  </span>
                <?php endif; ?>
              <?php else: ?>
                <span style="font-style:italic; color:#94a3b8;">Representación en proceso de actualización</span>
              <?php endif; ?>
            </div>
          </div>

          <div style="margin-top:16px; padding-top:12px; border-top:1px dashed #e2e8f0; font-size:12px; color:#64748b;">
            Sub Sector N.° <?= (int)$c['numero'] ?> · JUSHSAL
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Caja de atención e información de comisiones -->
    <div style="margin-top:48px; background:#fff; border-radius:12px; border:1px solid #e2e8f0; padding:28px 32px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:20px;">
      <div>
        <h3 style="font-size:18px; color:#0b3d76; margin:0 0 6px;">¿Perteneces a alguna de nuestras comisiones?</h3>
        <p style="margin:0; color:#475569; font-size:14px;">
          Para trámites de rol de turnos de riego, reclamos o consultas sobre tu sub sector, acércate a la sede central o comunícate con la Junta.
        </p>
      </div>
      <div>
        <a href="index.php#contacto" class="btn btn-primary" style="font-size:14px; padding:10px 20px;">
          Contáctanos
        </a>
      </div>
    </div>

  </div>
</section>

<?php include __DIR__ . '/includes/vistas/footer.php'; ?>
