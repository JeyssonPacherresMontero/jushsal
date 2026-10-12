<?php
/**
 * JUSHSAL - Cabecera Pública Modular (header.php)
 * Incluye barra superior institucional, navegación responsiva y submenú La Institución.
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

$tituloPagina = $tituloPagina ?? 'Junta de Usuarios San Lorenzo – Sector Hidráulico Menor San Lorenzo';
$descripcionPagina = $descripcionPagina ?? 'Junta de Usuarios del Sector Hidráulico Menor San Lorenzo – Tambogrande, Piura';
$baseUrl = defined('BASE_URL') ? BASE_URL : './';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <meta name="description" content="<?= e($descripcionPagina) ?>"/>
  <title><?= e($tituloPagina) ?></title>
  <link rel="icon" href="<?= $baseUrl ?>assets/images/logo.png"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous"/>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="<?= $baseUrl ?>css/style.css"/>
</head>
<body>

<!-- ===== Barra superior ===== -->
<div class="topbar">
  <div class="container topbar-inner">
    <div class="topbar-contactos">
      <a class="topbar-link" href="mailto:juntasanlorenzo@jusanlorenzo.org.pe">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6.5l8 5 8-5V8z"/></svg>
        juntasanlorenzo@jusanlorenzo.org.pe
      </a>
      <a class="topbar-link" href="tel:+51942028075">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24 11.36 11.36 0 0 0 3.57.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1 11.36 11.36 0 0 0 .57 3.57 1 1 0 0 1-.25 1.02z"/></svg>
        +51 942 028 075
      </a>
    </div>
    <div class="topbar-info">
      <span class="topbar-link">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg>
        Av. Reforma Agraria S/N – La Cruceta – Tambogrande – Piura
      </span>
      <span class="topbar-link topbar-horario">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 10h5v2h-7V6h2v6z"/></svg>
        Lun–Vie 8:00 a.m. – 4:00 p.m. · Sáb 8:00 a.m. – 1:00 p.m.
      </span>
    </div>
  </div>
</div>

<!-- ===== Cabecera / Menú ===== -->
<header class="header" id="header">
  <div class="container header-inner">
    <a class="brand" href="<?= $baseUrl ?>index.php#inicio">
      <img class="brand-logo" src="<?= $baseUrl ?>assets/images/logo.png" alt="Logo Junta de Usuarios San Lorenzo"/>
      <div class="brand-text">
        <strong>Junta de Usuarios</strong>
        <span>Sector Hidráulico Menor San Lorenzo</span>
      </div>
    </a>

    <button class="nav-toggle" id="navToggle" aria-label="Abrir menú" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>

    <nav class="nav" id="nav" aria-label="Menú principal">
      <ul class="nav-list">
        <li class="nav-item"><a class="nav-link" href="<?= $baseUrl ?>index.php#inicio">Inicio</a></li>
        <li class="nav-item has-sub">
          <a class="nav-link" href="<?= $baseUrl ?>index.php#institucion">La Institución
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 10l5 5 5-5H7z"/></svg>
          </a>
          <ul class="submenu">
            <li><a href="<?= $baseUrl ?>index.php#quienes-somos">Quiénes Somos</a></li>
            <li><a href="<?= $baseUrl ?>index.php#historia">Nuestra Historia</a></li>
            <li><a href="<?= $baseUrl ?>presidentes.php">Presidentes y Logros</a></li>
            <li><a href="<?= $baseUrl ?>documentos.php">Documentos Institucionales</a></li>
            <li><a href="<?= $baseUrl ?>comisiones.php">Comisiones de Usuarios</a></li>
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link" href="<?= $baseUrl ?>index.php#noticias">Noticias</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $baseUrl ?>index.php#galeria">Galería</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $baseUrl ?>index.php#servicios">Servicios</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $baseUrl ?>index.php#contacto">Contáctanos</a></li>
      </ul>
      <div class="nav-cta">
        <a class="btn btn-primary" href="<?= $baseUrl ?>index.php#contacto">Solicitar información</a>
      </div>
    </nav>
  </div>
</header>

<main>
