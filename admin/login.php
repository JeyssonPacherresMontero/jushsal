<?php
/**
 * JUSHSAL - Inicio de Sesión Administrativo (admin/login.php)
 */

define('JUSHSAL_INIT', true);

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

iniciar_sesion();

// Si ya está autenticado, redirigir al panel
if (esta_autenticado()) {
    redirigir(BASE_URL . 'admin/');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_requerir();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Por favor ingrese su correo electrónico y contraseña.';
    } else {
        $resultado = autenticar_usuario($email, $password);
        if ($resultado['exito']) {
            flash('exito', '¡Bienvenido(a) al Panel Administrativo!');
            redirigir(BASE_URL . 'admin/');
        } else {
            $error = $resultado['mensaje'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Acceso al Panel Administrativo · JUSHSAL</title>
  <link rel="icon" href="<?= BASE_URL ?>assets/images/logo.png"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous"/>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css"/>
  <style>
    body {
      background: linear-gradient(135deg, #0b3d76 0%, #08284d 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      font-family: 'Open Sans', sans-serif;
    }
    .login-card {
      background: #ffffff;
      border-radius: 14px;
      box-shadow: 0 15px 35px rgba(0,0,0,0.25);
      width: 100%;
      max-width: 440px;
      padding: 40px 36px;
    }
    .login-brand {
      text-align: center;
      margin-bottom: 28px;
    }
    .login-brand img {
      width: 64px;
      height: 64px;
      margin-bottom: 12px;
    }
    .login-brand h1 {
      font-size: 20px;
      font-family: 'Montserrat', sans-serif;
      color: #0b3d76;
      margin: 0 0 6px 0;
    }
    .login-brand p {
      font-size: 13px;
      color: #64748b;
      margin: 0;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: #334155;
      margin-bottom: 6px;
    }
    .form-group input {
      width: 100%;
      padding: 12px 14px;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      font-size: 14px;
      transition: border-color 0.2s;
      box-sizing: border-box;
    }
    .form-group input:focus {
      outline: none;
      border-color: #0b3d76;
      box-shadow: 0 0 0 3px rgba(11, 61, 118, 0.1);
    }
    .btn-submit {
      width: 100%;
      padding: 13px;
      background: #0b3d76;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      font-family: 'Montserrat', sans-serif;
      transition: background 0.2s;
    }
    .btn-submit:hover {
      background: #082e5b;
    }
    .login-alert {
      background: #fef2f2;
      border-left: 4px solid #ef4444;
      color: #991b1b;
      padding: 12px 16px;
      border-radius: 6px;
      font-size: 13px;
      margin-bottom: 20px;
    }
    .login-footer {
      text-align: center;
      margin-top: 24px;
      font-size: 13px;
    }
    .login-footer a {
      color: #0b3d76;
      text-decoration: none;
      font-weight: 600;
    }
  </style>
</head>
<body>

<div class="login-card">
  <div class="login-brand">
    <img src="<?= BASE_URL ?>assets/images/logo.png" alt="Logo JUSHSAL"/>
    <h1>Panel Institucional</h1>
    <p>Junta de Usuarios San Lorenzo · Gestión de Contenido</p>
  </div>

  <?php if ($error): ?>
    <div class="login-alert">
      <?= e($error) ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="">
    <?= csrf_campo() ?>

    <div class="form-group">
      <label for="email">Correo electrónico institucional</label>
      <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required autofocus placeholder="ejemplo@jusanlorenzo.org.pe"/>
    </div>

    <div class="form-group">
      <label for="password">Contraseña</label>
      <input type="password" id="password" name="password" required placeholder="••••••••"/>
    </div>

    <button type="submit" class="btn-submit">Ingresar al Panel</button>
  </form>

  <div class="login-footer">
    <a href="<?= BASE_URL ?>">← Volver a la web pública</a>
  </div>
</div>

</body>
</html>
