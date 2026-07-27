<?php
require_once __DIR__ . '/data/investigaciones.php';
require_once __DIR__ . '/includes/funciones.php';

$tituloPagina = 'Panel Docente | Qullqa';
require __DIR__ . '/includes/header.php';

if (($_SESSION['rol'] ?? '') !== 'Docente') {
    echo '<section class="content-section"><div class="container">';
    echo '<p class="empty-state is-visible">Esta sección es solo para cuentas de Docente. <a href="login.php">Inicia sesión</a> con una cuenta de ese tipo.</p>';
    echo '</div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$investigacionesEstudiantes = obtenerInvestigacionesDeEstudiantes();
?>

  <section class="page-hero">
    <div class="container">
      <h1>Panel Docente</h1>
      <p>Investigaciones publicadas por estudiantes. Entra a cada una para dejar tu retroalimentación.</p>
    </div>
  </section>

  <section class="content-section">
    <div class="container">

      <?php if (empty($investigacionesEstudiantes)): ?>

        <p class="empty-state is-visible">Todavía no hay investigaciones publicadas por estudiantes.</p>

      <?php else: ?>

        <div class="research-grid" style="margin-top:0;">
          <?php foreach ($investigacionesEstudiantes as $item): ?>
            <div class="research-card">
              <h3 class="research-card__title">
                <a href="detalle.php?id=<?php echo $item['id']; ?>" style="color:var(--color-gold);"><?php echo htmlspecialchars($item['titulo']); ?></a>
              </h3>
              <p class="research-card__desc"><?php echo htmlspecialchars($item['desc']); ?></p>
              <p class="research-card__desc" style="opacity:.9; font-size:.84rem;">
                Publicado por: 
                <a href="perfil.php?id=<?php echo $item['usuario_id']; ?>" style="color:var(--color-gold); text-decoration:underline; font-weight:600;">
                  <?php echo htmlspecialchars($item['autor_cuenta']); ?>
                </a><br>
                <?php echo count($item['feedback']); ?> comentario<?php echo count($item['feedback']) === 1 ? '' : 's'; ?> de retroalimentación
              </p>
              <div style="display:flex; justify-content:space-between; align-items:center; margin-top:auto; padding-top:8px;">
                <span class="research-card__tag" style="margin:0;"><?php echo htmlspecialchars(etiquetaCategoria($item['categoria'])); ?></span>
                <a href="perfil.php?id=<?php echo $item['usuario_id']; ?>" style="font-size:0.75rem; color:#FFF; opacity:0.85; text-decoration:underline;">
                  Ver perfil
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

      <?php endif; ?>

    </div>
  </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
