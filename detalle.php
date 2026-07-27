<?php
require_once __DIR__ . '/data/investigaciones.php';
require_once __DIR__ . '/includes/funciones.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$investigacion = obtenerInvestigacionPorId($id);

$tituloPagina = $investigacion ? $investigacion['titulo'] . ' | Qullqa' : 'Investigación no encontrada | Qullqa';
require __DIR__ . '/includes/header.php';

$feedbackEnviado = false;
$errorFeedback   = '';

// Solo un Docente logueado puede dejar feedback, y solo con POST
if ($investigacion && ($_SESSION['rol'] ?? '') === 'Docente' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $comentario = trim($_POST['comentario'] ?? '');

    if ($comentario === '') {
        $errorFeedback = 'Escribe un comentario antes de enviarlo.';
    } elseif (strlen($comentario) < 10) {
        $errorFeedback = 'El comentario debe tener al menos 10 caracteres.';
    } else {
        registrarFeedback($investigacion['id'], $_SESSION['usuario_id'], $comentario);
        $feedbackEnviado = true;
    }
}

$feedback = $investigacion ? obtenerFeedbackDe($investigacion['id']) : [];
?>

  <?php if (!$investigacion): ?>

    <section class="page-hero">
      <div class="container">
        <h1>Investigación no encontrada</h1>
        <p>Puede que el enlace esté roto o la investigación ya no exista.</p>
      </div>
    </section>
    <section class="content-section">
      <div class="container">
        <a class="filter-btn" href="investigaciones.php">← Volver al listado</a>
      </div>
    </section>

  <?php else: ?>

    <section class="page-hero">
      <div class="container">
        <h1><?php echo htmlspecialchars($investigacion['titulo']); ?></h1>
        <p><?php echo htmlspecialchars(etiquetaCategoria($investigacion['categoria'])); ?> · Publicado el <?php echo htmlspecialchars($investigacion['publicado_el']); ?></p>
      </div>
    </section>

    <section class="content-section">
      <div class="container two-col">

        <div>
          <h2>Resumen</h2>
          <p><?php echo nl2br(htmlspecialchars($investigacion['desc'])); ?></p>

          <?php if (!empty($investigacion['archivo_pdf'])): ?>
            <div class="pdf-viewer-section">
              <div class="pdf-viewer-header">
                <h2>Documento de Investigación (PDF)</h2>
                <a href="uploads/<?php echo htmlspecialchars($investigacion['archivo_pdf']); ?>" target="_blank" class="pdf-expand-link">Ver a pantalla completa ↗</a>
              </div>
              <div class="pdf-frame-container">
                <iframe src="uploads/<?php echo htmlspecialchars($investigacion['archivo_pdf']); ?>#toolbar=1" class="pdf-iframe" title="Visor PDF"></iframe>
              </div>
            </div>
          <?php endif; ?>

          <h2>Retroalimentación docente</h2>
          <?php if (empty($feedback)): ?>
            <p style="color:var(--color-text-light);">Todavía no hay comentarios de un docente sobre esta investigación.</p>
          <?php else: ?>
            <?php foreach ($feedback as $comentario): ?>
              <div class="info-card" style="margin-bottom:14px;">
                <strong style="color:var(--color-purple);"><?php echo htmlspecialchars($comentario['docente']); ?></strong>
                <span style="color:var(--color-text-light); font-size:.82rem;"> · <?php echo htmlspecialchars($comentario['fecha']); ?></span>
                <p style="margin:8px 0 0;"><?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?></p>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

          <?php if (($_SESSION['rol'] ?? '') === 'Docente'): ?>
            <h3 style="margin-top:28px;">Dejar un comentario</h3>

            <?php if ($feedbackEnviado): ?>
              <p class="form-success is-visible">✓ Tu comentario fue publicado.</p>
            <?php endif; ?>
            <?php if ($errorFeedback !== ''): ?>
              <p class="form-success" style="display:block; background:#FBEAEA; color:#8A2C2C;">⚠ <?php echo htmlspecialchars($errorFeedback); ?></p>
            <?php endif; ?>

            <form method="post" action="detalle.php?id=<?php echo $investigacion['id']; ?>">
              <div class="form-group">
                <textarea name="comentario" placeholder="Escribe tu retroalimentación para el autor..." required></textarea>
              </div>
              <button type="submit" class="form-submit" style="width:auto; padding:12px 28px;">Enviar comentario</button>
            </form>
          <?php endif; ?>
        </div>

        <aside class="info-card">
          <h3>Ficha</h3>
          <p><strong>Autor<?php echo count($investigacion['autores']) > 1 ? 'es' : ''; ?>:</strong><br>
             <?php echo htmlspecialchars(listarAutores($investigacion['autores'])); ?></p>
          <p><strong>Categoría:</strong><br><?php echo htmlspecialchars(etiquetaCategoria($investigacion['categoria'])); ?></p>
          <p><strong>Citas:</strong><br><?php echo $investigacion['citas']; ?></p>

          <?php if (!empty($investigacion['archivo_pdf'])): ?>
            <div class="pdf-sidebar-actions">
              <a href="uploads/<?php echo htmlspecialchars($investigacion['archivo_pdf']); ?>" target="_blank" class="form-submit btn-pdf-view">
                📄 Abrir PDF
              </a>
              <a href="uploads/<?php echo htmlspecialchars($investigacion['archivo_pdf']); ?>" download class="filter-btn btn-pdf-download" style="display:block; text-align:center; margin-top:8px;">
                📥 Descargar PDF
              </a>
            </div>
          <?php else: ?>
            <div class="no-pdf-badge" style="margin-top:14px; padding:10px; background:#F5F4F8; border-radius:6px; font-size:.85rem; color:var(--color-text-light); text-align:center;">
              Documento sin archivo PDF adjunto.
            </div>
          <?php endif; ?>
        </aside>

      </div>
    </section>

  <?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
