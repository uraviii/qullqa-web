<?php
require_once __DIR__ . '/includes/funciones.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Logout vía GET (?logout=1) ---
if (isset($_GET['logout'])) {
    unset($_SESSION['usuario'], $_SESSION['rol'], $_SESSION['usuario_id']);
    header('Location: login.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    $resultado = validarCredenciales($usuario, $password);

    if ($resultado['ok']) {
        $_SESSION['usuario']    = $resultado['nombre'];
        $_SESSION['nombre']     = $resultado['nombre'];
        $_SESSION['email']      = $usuario;
        $_SESSION['rol']        = $resultado['rol'];
        $_SESSION['usuario_id'] = $resultado['id'];
        header('Location: index.php');
        exit;
    }

    $error = $resultado['mensaje'];
}

$tituloPagina = 'Iniciar Sesión | Qullqa';
require __DIR__ . '/includes/header.php';
?>

  <section class="page-hero">
    <div class="container">
      <h1>Iniciar Sesión</h1>
      <p>Accede a tu cuenta para publicar y gestionar tus investigaciones.</p>
    </div>
  </section>

  <section class="content-section">
    <div class="container">
      <div class="form-card">

        <?php if (isset($_SESSION['usuario'])): ?>

          <p class="form-success is-visible">✓ Sesión iniciada como <strong><?php echo htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['usuario']); ?></strong> (<?php echo htmlspecialchars($_SESSION['rol'] ?? ''); ?>).</p>
          <p class="form-note"><a href="login.php?logout=1">Cerrar sesión</a></p>

        <?php else: ?>

          <?php if ($error !== ''): ?>
            <p class="form-success" style="display:block; background:#FBEAEA; color:#8A2C2C;">⚠ <?php echo htmlspecialchars($error); ?></p>
          <?php endif; ?>

          <form id="loginForm" method="post" action="login.php" novalidate>
            <div class="form-group">
              <label for="usuario">Correo institucional</label>
              <input type="email" id="usuario" name="usuario" placeholder="usuario@unam.edu.pe"
                     value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
              <label for="password">Contraseña</label>
              <input type="password" id="password" name="password" required minlength="6">
            </div>

            <button type="submit" class="form-submit">Ingresar</button>
            
            <p class="form-note" style="margin-top:14px;">
              ¿No tienes una cuenta? <a href="registro.php" style="color:var(--color-purple); font-weight:600;">Regístrate aquí</a>
            </p>

            <p class="form-note" style="margin-top:14px; border-top:1px solid #EEE; padding-top:12px;">
              <strong>Cuentas de prueba:</strong><br>
              Estudiante: <code>estudiante@unam.edu.pe</code> / <code>clave123</code><br>
              Docente: <code>docente@unam.edu.pe</code> / <code>clave456</code><br>
              Administrador: <code>admin@unam.edu.pe</code> / <code>admin123</code>
            </p>
          </form>

        <?php endif; ?>

      </div>
    </div>
  </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
