<?php
require_once __DIR__ . '/includes/funciones.php';

$tituloPagina = 'Soporte | Qullqa';
require __DIR__ . '/includes/header.php';

$errores = [];
$enviado = false;
$mensajeConfirmacion = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errores = validarFormularioSoporte($_POST);

    if (empty($errores)) {
        $mensajeConfirmacion = construirMensajeConfirmacion($_POST['asunto']);
        registrarMensajeSoporte($_POST);
        $enviado = true;
    }
}
?>

  <section class="page-hero">
    <div class="container">
      <h1>Soporte</h1>
      <p>¿Tienes dudas o problemas con la plataforma? Escríbenos.</p>
    </div>
  </section>

  <section class="content-section">
    <div class="container">
      <div class="form-card">

        <?php if ($enviado): ?>
          <p class="form-success is-visible">✓ <?php echo htmlspecialchars($mensajeConfirmacion); ?></p>
        <?php endif; ?>

        <?php if (!empty($errores)): ?>
          <div class="form-success" style="display:block; background:#FBEAEA; color:#8A2C2C;">
            <?php foreach ($errores as $error): ?>
              <div>⚠ <?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form id="supportForm" method="post" action="soporte.php" novalidate>
          <div class="form-group">
            <label for="nombre">Nombre completo</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>" required>
          </div>

          <div class="form-group">
            <label for="correo">Correo electrónico</label>
            <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($_POST['correo'] ?? ''); ?>" required>
          </div>

          <div class="form-group">
            <label for="asunto">Asunto</label>
            <select id="asunto" name="asunto" required>
              <option value="">Selecciona una opción</option>
              <?php
                $opciones = [
                    'cuenta'      => 'Problema con mi cuenta',
                    'publicacion' => 'Publicar una investigación',
                    'tecnico'     => 'Problema técnico',
                    'otro'        => 'Otro',
                ];
                $asuntoSeleccionado = $_POST['asunto'] ?? '';
                foreach ($opciones as $valor => $texto):
              ?>
                <option value="<?php echo $valor; ?>" <?php echo $asuntoSeleccionado === $valor ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($texto); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="mensaje">Mensaje</label>
            <textarea id="mensaje" name="mensaje" required><?php echo htmlspecialchars($_POST['mensaje'] ?? ''); ?></textarea>
          </div>

          <button type="submit" class="form-submit">Enviar mensaje</button>
          <p class="form-note">El formulario envía los datos por POST a este mismo archivo (soporte.php), y la validación vive en la función <code>validarFormularioSoporte()</code> de includes/funciones.php.</p>
        </form>
      </div>
    </div>
  </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
