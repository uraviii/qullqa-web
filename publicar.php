<?php
require_once __DIR__ . '/data/investigaciones.php';
require_once __DIR__ . '/includes/funciones.php';

$tituloPagina = 'Publicar Investigación | Qullqa';
require __DIR__ . '/includes/header.php';

// Solo un Estudiante logueado puede entrar a esta página
if (($_SESSION['rol'] ?? '') !== 'Estudiante') {
    echo '<section class="content-section"><div class="container">';
    echo '<p class="empty-state is-visible">Esta sección es solo para cuentas de Estudiante. <a href="login.php">Inicia sesión</a> con una cuenta de ese tipo.</p>';
    echo '</div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$errores  = [];
$publicado = false;
$nuevoId   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errores = validarFormularioPublicacion($_POST);
    $nombreArchivoPdf = null;

    // Procesar archivo PDF si fue enviado
    if (isset($_FILES['archivo_pdf']) && $_FILES['archivo_pdf']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['archivo_pdf']['error'] !== UPLOAD_ERR_OK) {
            $errores[] = 'Ocurrió un error al subir el archivo PDF.';
        } else {
            $ext = strtolower(pathinfo($_FILES['archivo_pdf']['name'], PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                $errores[] = 'El archivo adjunto debe ser de formato PDF (.pdf).';
            } else {
                $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($_FILES['archivo_pdf']['name'], PATHINFO_FILENAME));
                $nombreArchivoPdf = $nombreLimpio . '_' . uniqid() . '.pdf';
                $destino = __DIR__ . '/uploads/' . $nombreArchivoPdf;

                if (!move_uploaded_file($_FILES['archivo_pdf']['tmp_name'], $destino)) {
                    $errores[] = 'No se pudo guardar el archivo en la carpeta uploads.';
                    $nombreArchivoPdf = null;
                }
            }
        }
    }

    if (empty($errores)) {
        $nuevoId = publicarInvestigacion($_POST, $_SESSION['usuario_id'], $nombreArchivoPdf);
        $publicado = true;
    }
}

$categorias = ['ia' => 'Inteligencia Artificial', 'bigdata' => 'Big Data', 'calidad' => 'Calidad de Software', 'ciberseguridad' => 'Ciberseguridad'];
?>

  <section class="page-hero">
    <div class="container">
      <h1>Publicar Investigación</h1>
      <p>Comparte tu proyecto con la comunidad Qullqa.</p>
    </div>
  </section>

  <section class="content-section">
    <div class="container">
      <div class="form-card">

        <?php if ($publicado): ?>
          <p class="form-success is-visible">
            ✓ Tu investigación fue publicada exitosamente.
            <a href="detalle.php?id=<?php echo $nuevoId; ?>">Verla ahora</a>.
          </p>
        <?php endif; ?>

        <?php if (!empty($errores)): ?>
          <div class="form-success" style="display:block; background:#FBEAEA; color:#8A2C2C;">
            <?php foreach ($errores as $error): ?>
              <div>⚠ <?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="post" action="publicar.php" enctype="multipart/form-data" novalidate>
          <div class="form-group">
            <label for="titulo">Título</label>
            <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($_POST['titulo'] ?? ''); ?>" required>
          </div>

          <div class="form-group">
            <label for="categoria">Categoría</label>
            <select id="categoria" name="categoria" required>
              <option value="">Selecciona una categoría</option>
              <?php $categoriaSeleccionada = $_POST['categoria'] ?? ''; ?>
              <?php foreach ($categorias as $slug => $nombre): ?>
                <option value="<?php echo $slug; ?>" <?php echo $categoriaSeleccionada === $slug ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($nombre); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="autores">Autores</label>
            <input type="text" id="autores" name="autores" placeholder="Separa varios autores con comas (Ej: Leo Pari, Juan Pari)"
                   value="<?php echo htmlspecialchars($_POST['autores'] ?? ''); ?>" required>
          </div>

          <div class="form-group">
            <label for="resumen">Resumen</label>
            <textarea id="resumen" name="resumen" placeholder="Describe brevemente tu investigación" required><?php echo htmlspecialchars($_POST['resumen'] ?? ''); ?></textarea>
          </div>

          <div class="form-group">
            <label for="archivo_pdf">Documento PDF (Opcional)</label>
            <input type="file" id="archivo_pdf" name="archivo_pdf" accept=".pdf,application/pdf">
            <span style="font-size:0.8rem; color:var(--color-text-light);">Formatos permitidos: .pdf (Máx. 10MB)</span>
          </div>

          <button type="submit" class="form-submit">Publicar Investigación</button>
        </form>
      </div>
    </div>
  </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
