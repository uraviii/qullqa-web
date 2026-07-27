<?php
require_once __DIR__ . '/data/investigaciones.php';

$tituloPagina = 'Qullqa | El mayor almacén de conocimiento sobre tecnología';
require __DIR__ . '/includes/header.php';

$investigaciones = obtenerInvestigaciones();
$destacadas = array_slice($investigaciones, 0, 4); // solo las 4 primeras, como en el diseño original
?>

  <section class="hero">
    <div class="container hero-inner">
      <h1 class="hero-title">El mayor <strong>almacén</strong> de conocimiento<br>sobre <strong>tecnología</strong></h1>

      <form class="search-form" data-search-form action="investigaciones.php" method="get">
        <input
          type="search"
          name="q"
          class="search-input"
          placeholder="Término de Búsqueda"
          data-search-input
          aria-label="Buscar investigaciones">
        <button type="submit" class="search-btn">Buscar</button>
      </form>
    </div>
  </section>

  <section class="research-section">
    <div class="container">
      <div class="research-grid">
        <?php foreach ($destacadas as $item): ?>
          <a class="research-card" data-card
                    data-title="<?php echo htmlspecialchars($item['titulo']); ?>"
                    data-desc="<?php echo htmlspecialchars($item['desc']); ?>"
                    data-category="<?php echo htmlspecialchars($item['categoria']); ?>"
                    href="detalle.php?id=<?php echo $item['id']; ?>">
            <h3 class="research-card__title"><?php echo htmlspecialchars($item['titulo']); ?></h3>
            <p class="research-card__desc"><?php echo htmlspecialchars($item['desc']); ?></p>
            <span class="research-card__tag"><?php echo htmlspecialchars(etiquetaCategoria($item['categoria'])); ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
