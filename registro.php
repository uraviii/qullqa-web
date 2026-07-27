<?php
require_once __DIR__ . '/includes/funciones.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si ya tiene sesión, redirigir al inicio
if (isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

$error   = '';
$exito   = false;
$nombre  = '';
$correo  = '';
$rolId   = 1; // 1 = Estudiante por defecto

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $correo   = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $rolId    = (int) ($_POST['rol_id'] ?? 1);

    if ($password !== $confirm) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $resultado = registrarUsuario($nombre, $correo, $password, $rolId);

        if ($resultado['ok']) {
            // Iniciar sesión automáticamente tras el registro exitoso
            $rolesDict = [1 => 'Estudiante', 2 => 'Docente', 3 => 'Administrador'];
            $_SESSION['usuario']    = $nombre;
            $_SESSION['nombre']     = $nombre;
            $_SESSION['email']      = $correo;
            $_SESSION['rol']        = $rolesDict[$rolId] ?? 'Estudiante';
            $_SESSION['usuario_id'] = $resultado['id'];

            header('Location: index.php?registrado=1');
            exit;
        } else {
            $error = $resultado['mensaje'];
        }
    }
}

$tituloPagina = 'Crear Cuenta | Qullqa';
require __DIR__ . '/includes/header.php';
?>

  <section class="page-hero">
    <div class="container">
      <h1>Crear una Cuenta</h1>
      <p>Únete a la plataforma Qullqa para publicar y revisar trabajos de investigación.</p>
    </div>
  </section>

  <section class="content-section">
    <div class="container">
      <div class="form-card">

        <?php if ($error !== ''): ?>
          <div class="form-success" style="display:block; background:#FBEAEA; color:#8A2C2C; margin-bottom:18px;">
            ⚠ <?php echo htmlspecialchars($error); ?>
          </div>
        <?php endif; ?>

        <form id="registroForm" method="post" action="registro.php" novalidate>
          <div class="form-group">
            <label for="nombre">Nombre completo</label>
            <input type="text" id="nombre" name="nombre" placeholder="Ej: Leo Pari Puma"
                   value="<?php echo htmlspecialchars($nombre); ?>" required>
          </div>

          <div class="form-group">
            <label for="correo">Correo institucional</label>
            <input type="email" id="correo" name="correo" placeholder="usuario@unam.edu.pe"
                   value="<?php echo htmlspecialchars($correo); ?>" required>
          </div>

          <div class="form-group">
            <label for="rol_id">Tipo de cuenta</label>
            <select id="rol_id" name="rol_id" required>
              <option value="1" <?php echo $rolId === 1 ? 'selected' : ''; ?>>Estudiante</option>
              <option value="2" <?php echo $rolId === 2 ? 'selected' : ''; ?>>Docente</option>
            </select>
          </div>

          <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required minlength="6" placeholder="Mínimo 6 caracteres">
          </div>

          <div class="form-group">
            <label for="confirm_password">Confirmar Contraseña</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="6" placeholder="Repite tu contraseña">
          </div>

          <button type="submit" class="form-submit">Registrarme</button>
          
          <p class="form-note" style="margin-top:16px;">
            ¿Ya tienes una cuenta? <a href="login.php" style="color:var(--color-purple); font-weight:600;">Inicia sesión aquí</a>
          </p>
        </form>

      </div>
    </div>
  </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
