<?php
/**
 * JUSHSAL - Cierre de Sesión Administrativo (admin/logout.php)
 */

define('JUSHSAL_INIT', true);

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

cerrar_sesion();
redirigir(BASE_URL . 'admin/login.php');
