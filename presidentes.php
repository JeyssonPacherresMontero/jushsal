<?php
/**
 * JUSHSAL - Historial de Presidentes y Logros Institucionales (presidentes.php)
 * Línea de tiempo histórica y rendición de cuentas por periodo y por año.
 */

define('JUSHSAL_INIT', true);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/modelos/gestiones.php';

$gestionVigente = gestiones_obtener_vigente();
$gestionesAnteriores = gestiones_listar_anteriores();

$tituloPagina = 'Historial de Presidentes y Logros | Junta de Usuarios San Lorenzo';
$descripcionPagina = 'Memoria institucional y registro de obras realizadas año a año por las gestiones de la Junta de Usuarios del Sector Hidráulico Menor San Lorenzo.';
$baseUrl = BASE_URL;

include __DIR__ . '/includes/vistas/header.php';
?>

<section class="section section-alt" style="padding-top:40px;">
  <div class="container" style="max-width:960px;">
    
    <div class="section-head center">
      <p class="eyebrow">Memoria Histórica y Transparencia</p>
      <h1 class="section-title">Historial de Presidentes y Logros</h1>
      <p class="section-desc">Conoce a los líderes que han guiado nuestra institución y las obras y avances realizados año a año en cada periodo de gestión.</p>
    </div>

    <!-- ===== Gestión Vigente ===== -->
    <?php if ($gestionVigente): ?>
      <?php 
        $logrosVigente = logros_listar_por_gestion($gestionVigente['id']); 
        $logrosVigenteAnio = logros_agrupar_por_anio($logrosVigente);
      ?>
      <div style="background:#ffffff; border-radius:14px; box-shadow:0 8px 30px rgba(0,0,0,0.06); padding:36px; margin-bottom:48px; border-left:6px solid #0b3d76; border-top:1px solid #e2e8f0; border-right:1px solid #e2e8f0; border-bottom:1px solid #e2e8f0;">
        <div style="display:flex; align-items:center; gap:24px; flex-wrap:wrap; margin-bottom:24px;">
          <?php if (!empty($gestionVigente['foto'])): ?>
            <img src="<?= $baseUrl . e($gestionVigente['foto']) ?>" alt="<?= e($gestionVigente['presidente_nombre']) ?>" 
                 style="width:110px; height:110px; border-radius:50%; object-fit:cover; border:3px solid #ffab00; box-shadow:0 4px 12px rgba(0,0,0,0.1);"/>
          <?php else: ?>
            <div style="width:110px; height:110px; border-radius:50%; background:#e8f1f8; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:36px; color:#0b3d76; border:3px solid #ffab00;">
              <?= mb_strtoupper(mb_substr($gestionVigente['presidente_nombre'], 0, 2, 'UTF-8'), 'UTF-8') ?>
            </div>
          <?php endif; ?>

          <div style="flex:1; min-width:260px;">
            <span style="display:inline-block; background:#ffab00; color:#0b3d76; font-size:12px; font-weight:700; padding:4px 12px; border-radius:20px; text-transform:uppercase; margin-bottom:6px;">
              ★ Gestión Vigente
            </span>
            <h2 style="font-size:26px; color:#0b3d76; margin:0 0 6px 0; font-family:'Montserrat',sans-serif;">
              <?= e($gestionVigente['presidente_nombre']) ?>
            </h2>
            <p style="margin:0; color:#475569; font-size:15px; font-weight:600;">
              Periodo: <strong><?= e($gestionVigente['periodo_inicio']) ?> – <?= !empty($gestionVigente['periodo_fin']) ? e($gestionVigente['periodo_fin']) : 'En curso' ?></strong>
            </p>
          </div>
        </div>

        <?php if (!empty($gestionVigente['semblanza'])): ?>
          <div style="font-size:15px; line-height:1.7; color:#334155; margin-bottom:28px; background:#f8fafc; padding:18px 20px; border-radius:8px; border-left:3px solid #14919b;">
            <?= nl2br(e($gestionVigente['semblanza'])) ?>
          </div>
        <?php endif; ?>

        <!-- Logros de la gestión vigente -->
        <div style="border-top:1px solid #e2e8f0; padding-top:24px;">
          <h3 style="font-size:18px; color:#0b3d76; margin:0 0 16px 0; font-family:'Montserrat',sans-serif; display:flex; align-items:center; gap:8px;">
            <svg style="width:20px; height:20px; color:#14919b;" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            Lo realizado en esta gestión por año
          </h3>

          <?php if (empty($logrosVigente)): ?>
            <p style="color:#64748b; font-size:14px; margin:0; font-style:italic;">Aún no hay logros registrados para esta gestión.</p>
          <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:20px;">
              <?php foreach ($logrosVigenteAnio as $anio => $itemsAnio): ?>
                <div style="background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0; padding:16px 20px;">
                  <span style="display:inline-block; background:#0b3d76; color:#fff; font-size:12px; font-weight:700; padding:3px 10px; border-radius:12px; margin-bottom:12px;">
                    Año <?= e($anio) ?>
                  </span>
                  <div style="display:grid; gap:14px;">
                    <?php foreach ($itemsAnio as $logro): ?>
                      <div style="display:flex; gap:16px; align-items:flex-start;">
                        <?php if (!empty($logro['imagen'])): ?>
                          <img src="<?= $baseUrl . e($logro['imagen']) ?>" alt="<?= e($logro['titulo']) ?>" 
                               style="width:90px; height:65px; object-fit:cover; border-radius:6px; flex-shrink:0; border:1px solid #cbd5e1;"/>
                        <?php endif; ?>
                        <div>
                          <strong style="color:#0b3d76; font-size:15px; display:block;"><?= e($logro['titulo']) ?></strong>
                          <?php if (!empty($logro['descripcion'])): ?>
                            <p style="margin:4px 0 0; color:#475569; font-size:14px; line-height:1.5;"><?= nl2br(e($logro['descripcion'])) ?></p>
                          <?php endif; ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- ===== Línea de Tiempo de Gestiones Anteriores ===== -->
    <div style="margin-top:20px;">
      <h2 style="font-size:22px; color:#0b3d76; text-align:center; margin-bottom:32px; font-family:'Montserrat',sans-serif;">
        Gestiones Históricas Anteriores
      </h2>

      <?php if (empty($gestionesAnteriores)): ?>
        <div style="text-align:center; padding:40px 20px; background:#fff; border-radius:12px; border:1px solid #e2e8f0; color:#64748b;">
          <p style="margin:0; font-size:15px;">El historial de gestiones anteriores se encuentra en proceso de recopilación y digitalización.</p>
        </div>
      <?php else: ?>
        <div class="timeline" style="position:relative; padding-left:36px; border-left:3px solid #cbd5e1; margin-left:12px;">
          <?php foreach ($gestionesAnteriores as $g): ?>
            <?php 
              $logrosHist = logros_listar_por_gestion($g['id']); 
              $logrosHistAnio = logros_agrupar_por_anio($logrosHist);
            ?>
            <div style="position:relative; margin-bottom:40px;">
              <!-- Punto en la línea de tiempo -->
              <div style="position:absolute; left:-46px; top:12px; width:18px; height:18px; border-radius:50%; background:#0b3d76; border:3px solid #fff; box-shadow:0 0 0 2px #0b3d76;"></div>

              <div style="background:#fff; border-radius:12px; padding:24px 28px; border:1px solid #e2e8f0; box-shadow:0 2px 10px rgba(0,0,0,0.03);">
                <div style="display:flex; align-items:center; gap:18px; flex-wrap:wrap; margin-bottom:14px;">
                  <?php if (!empty($g['foto'])): ?>
                    <img src="<?= $baseUrl . e($g['foto']) ?>" alt="<?= e($g['presidente_nombre']) ?>" 
                         style="width:64px; height:64px; border-radius:50%; object-fit:cover; border:2px solid #cbd5e1;"/>
                  <?php else: ?>
                    <div style="width:64px; height:64px; border-radius:50%; background:#f1f5f9; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:20px; color:#475569;">
                      <?= mb_strtoupper(mb_substr($g['presidente_nombre'], 0, 2, 'UTF-8'), 'UTF-8') ?>
                    </div>
                  <?php endif; ?>

                  <div>
                    <span style="font-size:13px; font-weight:700; color:#14919b; text-transform:uppercase; letter-spacing:0.5px;">
                      Periodo <?= e($g['periodo_inicio']) ?> – <?= !empty($g['periodo_fin']) ? e($g['periodo_fin']) : 'En curso' ?>
                    </span>
                    <h3 style="font-size:20px; color:#0b3d76; margin:2px 0 0 0; font-family:'Montserrat',sans-serif;">
                      <?= e($g['presidente_nombre']) ?>
                    </h3>
                  </div>
                </div>

                <?php if (!empty($g['semblanza'])): ?>
                  <p style="font-size:14px; color:#475569; line-height:1.6; margin:0 0 18px 0;">
                    <?= nl2br(e($g['semblanza'])) ?>
                  </p>
                <?php endif; ?>

                <!-- Logros por año de la gestión histórica -->
                <div style="border-top:1px solid #f1f5f9; padding-top:16px;">
                  <strong style="font-size:13px; color:#0b3d76; text-transform:uppercase; letter-spacing:0.5px; display:block; margin-bottom:10px;">
                    Logros y Obras por Año:
                  </strong>

                  <?php if (empty($logrosHist)): ?>
                    <p style="color:#94a3b8; font-size:13px; margin:0; font-style:italic;">Aún no hay logros registrados para esta gestión.</p>
                  <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:12px;">
                      <?php foreach ($logrosHistAnio as $anioH => $itemsH): ?>
                        <div style="background:#f8fafc; border-radius:6px; padding:12px 16px; border:1px solid #e2e8f0;">
                          <span style="display:inline-block; background:#14919b; color:#fff; font-size:11px; font-weight:700; padding:2px 8px; border-radius:10px; margin-bottom:8px;">
                            Año <?= e($anioH) ?>
                          </span>
                          <div style="display:grid; gap:10px;">
                            <?php foreach ($itemsH as $lh): ?>
                              <div style="display:flex; gap:12px; align-items:flex-start;">
                                <?php if (!empty($lh['imagen'])): ?>
                                  <img src="<?= $baseUrl . e($lh['imagen']) ?>" alt="<?= e($lh['titulo']) ?>" 
                                       style="width:70px; height:50px; object-fit:cover; border-radius:4px; flex-shrink:0;"/>
                                <?php endif; ?>
                                <div>
                                  <strong style="font-size:13px; color:#0b3d76; display:block;"><?= e($lh['titulo']) ?></strong>
                                  <?php if (!empty($lh['descripcion'])): ?>
                                    <p style="margin:2px 0 0; color:#475569; font-size:12px; line-height:1.4;"><?= nl2br(e($lh['descripcion'])) ?></p>
                                  <?php endif; ?>
                                </div>
                              </div>
                            <?php endforeach; ?>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>

              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>
</section>

<?php
include __DIR__ . '/includes/vistas/footer.php';
?>
