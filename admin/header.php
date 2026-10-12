<?php
/**
 * JUSHSAL - Cabecera del Panel Administrativo (admin/header.php)
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/helpers.php';
require_once dirname(__DIR__) . '/includes/modelos/contacto.php';

// Exigir autenticación en todas las vistas administrativas
requerir_login();

$usuario = usuario_actual();
$tituloPanel = $tituloPanel ?? 'Panel Administrativo';
$baseUrl = BASE_URL;
$adminUrl = $baseUrl . 'admin/';
$pendientesContacto = contacto_contar_pendientes();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?= e($tituloPanel) ?> · JUSHSAL Admin</title>
  <link rel="icon" href="<?= $baseUrl ?>assets/images/logo.png"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous"/>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="<?= $baseUrl ?>css/style.css"/>
  <style>
    :root {
      --admin-bg: #f4f7fa;
      --admin-card: #ffffff;
      --admin-border: #e2e8f0;
      --admin-text: #1e293b;
      --admin-sidebar: #0b3d76;
    }
    body { background: var(--admin-bg); color: var(--admin-text); display: flex; flex-direction: column; min-height: 100vh; }
    .admin-wrapper { display: flex; flex: 1; }
    .admin-sidebar {
      width: 260px;
      background: var(--admin-sidebar);
      color: #fff;
      display: flex;
      flex-direction: column;
      flex-shrink: 0;
      box-shadow: 2px 0 10px rgba(0,0,0,0.05);
    }
    .admin-sidebar-header {
      padding: 24px 20px;
      display: flex;
      align-items: center;
      gap: 12px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .admin-sidebar-header img { width: 38px; height: 38px; border-radius: 6px; background: #fff; padding: 2px; }
    .admin-sidebar-header strong { font-size: 15px; font-family: 'Montserrat', sans-serif; display: block; line-height: 1.2; }
    .admin-sidebar-header span { font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px; }
    .admin-nav { padding: 16px 0; flex: 1; }
    .admin-nav-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 20px;
      color: rgba(255,255,255,0.85);
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.2s;
    }
    .admin-nav-item:hover, .admin-nav-item.active {
      background: rgba(255,255,255,0.12);
      color: #ffab00;
      border-left: 4px solid #ffab00;
    }
    .admin-main { flex: 1; display: flex; flex-direction: column; overflow-x: hidden; }
    .admin-topbar {
      background: #fff;
      border-bottom: 1px solid var(--admin-border);
      padding: 14px 28px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .admin-user-pill { display: flex; align-items: center; gap: 12px; }
    .admin-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: #14919b;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 14px;
    }
    .admin-role-badge {
      background: #e0f2fe;
      color: #0369a1;
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
    }
    .admin-content { padding: 28px; flex: 1; max-width: 1300px; width: 100%; margin: 0 auto; }
    .admin-card {
      background: #fff;
      border-radius: 10px;
      border: 1px solid var(--admin-border);
      box-shadow: 0 2px 6px rgba(0,0,0,0.02);
      padding: 24px;
      margin-bottom: 24px;
    }
    .admin-title-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
      flex-wrap: wrap;
      gap: 12px;
    }
    .admin-title-row h1 { font-size: 22px; color: #0b3d76; margin: 0; font-family: 'Montserrat', sans-serif; }
    .badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; }
    .badge-success { background: #dcfce7; color: #15803d; }
    .badge-warning { background: #fef3c7; color: #b45309; }
    .badge-secondary { background: #f1f5f9; color: #475569; }
    .table-responsive { width: 100%; overflow-x: auto; }
    .admin-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
    .admin-table th { background: #f8fafc; padding: 12px 14px; border-bottom: 2px solid var(--admin-border); color: #475569; font-weight: 600; }
    .admin-table td { padding: 12px 14px; border-bottom: 1px solid var(--admin-border); vertical-align: middle; }
    .admin-table tr:hover td { background: #f8fafc; }
    .btn-sm { padding: 6px 12px; font-size: 13px; }
    .alert-flash {
      padding: 14px 18px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .alert-flash-success { background: #ecfdf5; border-left: 4px solid #10b981; color: #065f46; }
    .alert-flash-error { background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; }
    @media (max-width: 860px) {
      .admin-wrapper { flex-direction: column; }
      .admin-sidebar { width: 100%; }
      .admin-content { padding: 16px; }
    }
  </style>
</head>
<body>

<div class="admin-wrapper">
  <!-- Barra lateral -->
  <aside class="admin-sidebar">
    <div class="admin-sidebar-header">
      <img src="<?= $baseUrl ?>assets/images/logo.png" alt="Logo"/>
      <div>
        <strong>JUSHSAL</strong>
        <span>Panel Institucional</span>
      </div>
    </div>
    <nav class="admin-nav">
      <a class="admin-nav-item" href="<?= $adminUrl ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
        Inicio / Tablero
      </a>
      <a class="admin-nav-item" href="<?= $adminUrl ?>noticias/listar.php">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
        Noticias
      </a>
      <a class="admin-nav-item" href="<?= $adminUrl ?>convocatorias/listar.php">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-7 12h-2v-2h2v2zm0-4h-2V6h2v4z"/></svg>
        Convocatorias (Popup)
      </a>
      <a class="admin-nav-item" href="<?= $adminUrl ?>presidentes/listar.php">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        Presidentes y Logros
      </a>
      <a class="admin-nav-item" href="<?= $adminUrl ?>comisiones/listar.php">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
        Comisiones (16)
      </a>
      <a class="admin-nav-item" href="<?= $adminUrl ?>documentos/listar.php">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
        Documentos y Balances
      </a>
      <a class="admin-nav-item" href="<?= $adminUrl ?>galeria/listar.php">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
        Galería de Fotos
      </a>
      <a class="admin-nav-item" href="<?= $adminUrl ?>contactos/listar.php">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
        <span>Mensajes de Contacto</span>
        <?php if (!empty($pendientesContacto) && $pendientesContacto > 0): ?>
          <span style="margin-left: auto; background: #ef4444; color: #fff; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 10px; line-height: 1;">
            <?= (int)$pendientesContacto ?>
          </span>
        <?php endif; ?>
      </a>

      <?php if (($usuario['rol_nombre'] ?? '') === 'admin'): ?>
      <div style="padding: 12px 20px 4px; font-size: 11px; text-transform: uppercase; opacity: 0.6; font-weight: 700; letter-spacing: 0.5px;">Administración</div>
      <a class="admin-nav-item" href="<?= $adminUrl ?>usuarios/listar.php">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
        Usuarios del Panel
      </a>
      <a class="admin-nav-item" href="<?= $adminUrl ?>bitacora.php">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
        Bitácora de Auditoría
      </a>
      <?php endif; ?>

      <div style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 12px;">
        <a class="admin-nav-item" href="<?= $baseUrl ?>" target="_blank">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 19H5V5h7V3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
          Ver Web Pública
        </a>
        <a class="admin-nav-item" href="<?= $adminUrl ?>logout.php" style="color: #fca5a5;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
          Cerrar Sesión
        </a>
      </div>
    </nav>
  </aside>

  <!-- Contenedor principal -->
  <div class="admin-main">
    <header class="admin-topbar">
      <div>
        <h2 style="font-size: 16px; margin: 0; color: #475569; font-weight: 600;"><?= e($tituloPanel) ?></h2>
      </div>
      <div class="admin-user-pill">
        <div class="admin-avatar">
          <?= mb_strtoupper(mb_substr($usuario['nombre'] ?? 'A', 0, 1, 'UTF-8'), 'UTF-8') ?>
        </div>
        <div>
          <strong style="font-size: 14px; display: block; color: #1e293b;"><?= e($usuario['nombre'] ?? 'Usuario') ?></strong>
          <span class="admin-role-badge"><?= e($usuario['rol_nombre'] ?? 'comunicaciones') ?></span>
        </div>
      </div>
    </header>

    <main class="admin-content">
      <?php if ($msgExito = flash('exito')): ?>
        <div class="alert-flash alert-flash-success">
          <span><?= e($msgExito) ?></span>
        </div>
      <?php endif; ?>
      <?php if ($msgError = flash('error')): ?>
        <div class="alert-flash alert-flash-error">
          <span><?= e($msgError) ?></span>
        </div>
      <?php endif; ?>
