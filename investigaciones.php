<?php
require_once __DIR__ . '/data/investigaciones.php';
require_once __DIR__ . '/includes/funciones.php';

$tituloPagina = 'Investigaciones | Qullqa';
require __DIR__ . '/includes/header.php';

// --- Lectura de parámetros GET ---
$busqueda  = isset($_GET['q']) ? trim($_GET['q']) : '';
$categoria = isset($_GET['cat']) ? $_GET['cat'] : 'todas';
$orden     = isset($_GET['orden']) ? $_GET['orden'] : 'recientes';
$paginaNum = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;

// --- Filtrado (SQL con WHERE/AND/OR), ordenamiento y paginación ---
$resultados = filtrarInvestigaciones($busqueda, $categoria, $orden);
$pagina     = paginar($resultados, 4, $paginaNum);

$totalItems       = count($resultados);
$resultadosPagina = $pagina['items'];
$totalPaginas     = $pagina['totalPaginas'];
$paginaActual     = $pagina['paginaActual'];

// Categorías disponibles para los botones de filtro
$categorias = ['todas', 'ia', 'bigdata', 'calidad', 'ciberseguridad'];
?>

  <section class="page-hero">
    <div class="container">
      <h1>Investigaciones</h1>
      <p>Explora los proyectos de investigación registrados por la comunidad UNAM.</p>
    </div>
  </section>

  <section class="content-section">
    <div class="container">

      <div class="search-sort-bar">
        <form class="search-form" method="get" action="investigaciones.php" style="max-width:480px; margin:0;">
          <?php if ($categoria !== 'todas'): ?>
            <input type="hidden" name="cat" value="<?php echo htmlspecialchars($categoria); ?>">
          <?php endif; ?>
          <?php if ($orden !== 'recientes'): ?>
            <input type="hidden" name="orden" value="<?php echo htmlspecialchars($orden); ?>">
          <?php endif; ?>
          <input
            type="search"
            name="q"
            class="search-input"
            placeholder="Buscar por título o palabra clave"
            value="<?php echo htmlspecialchars($busqueda); ?>"
            aria-label="Buscar investigaciones">
        </form>

        <div class="sort-selector">
          <label for="sortOrder">Ordenar por:</label>
          <select id="sortOrder" onchange="location = this.value;">
            <option value="<?php echo construirUrlInvestigaciones(['orden' => 'recientes', 'pagina' => 1], $busqueda, $categoria, 'recientes'); ?>" <?php echo $orden === 'recientes' ? 'selected' : ''; ?>>Más Recientes</option>
            <option value="<?php echo construirUrlInvestigaciones(['orden' => 'citados', 'pagina' => 1], $busqueda, $categoria, 'citados'); ?>" <?php echo $orden === 'citados' ? 'selected' : ''; ?>>Más Citadas</option>
            <option value="<?php echo construirUrlInvestigaciones(['orden' => 'titulo', 'pagina' => 1], $busqueda, $categoria, 'titulo'); ?>" <?php echo $orden === 'titulo' ? 'selected' : ''; ?>>Título (A-Z)</option>
          </select>
        </div>
      </div>

      <div class="filter-bar" role="group" aria-label="Filtrar por categoría">
        <?php foreach ($categorias as $cat): ?>
          <?php
            switch ($cat) {
                case 'todas':
                    $etiqueta = 'Todas';
                    break;
                default:
                    $etiqueta = etiquetaCategoria($cat);
            }
            $activo = ($cat === $categoria) ? 'is-active' : '';
          ?>
          <a class="filter-btn <?php echo $activo; ?>" href="<?php echo construirUrlInvestigaciones(['cat' => $cat, 'pagina' => 1], $busqueda, $cat, $orden); ?>">
            <?php echo htmlspecialchars($etiqueta); ?>
          </a>
        <?php endforeach; ?>
      </div>

      <p class="results-count">
        Mostrando <?php echo count($resultadosPagina); ?> de <?php echo $totalItems; ?> resultado<?php echo $totalItems === 1 ? '' : 's'; ?>
      </p>

      <?php if (empty($resultadosPagina)): ?>

        <p class="empty-state is-visible">No se encontraron investigaciones con ese criterio de búsqueda.</p>

      <?php else: ?>

        <div class="research-grid" style="margin-top:0;">
          <?php foreach ($resultadosPagina as $item): ?>
            <a class="research-card" href="detalle.php?id=<?php echo $item['id']; ?>">
              <div class="card-top-tags">
                <span class="research-card__tag"><?php echo htmlspecialchars(etiquetaCategoria($item['categoria'])); ?></span>
                <?php if (!empty($item['archivo_pdf'])): ?>
                  <span class="card-pdf-badge" title="PDF disponible">📄 PDF</span>
                <?php endif; ?>
              </div>
              <h3 class="research-card__title"><?php echo htmlspecialchars($item['titulo']); ?></h3>
              <p class="research-card__desc"><?php echo htmlspecialchars($item['desc']); ?></p>
              <p class="research-card__desc" style="opacity:.8; font-size:.82rem; margin-top:auto;">
                Autor<?php echo count($item['autores']) > 1 ? 'es' : ''; ?>: <?php echo htmlspecialchars(listarAutores($item['autores'])); ?>
                · <?php echo $item['citas']; ?> cita<?php echo $item['citas'] === 1 ? '' : 's'; ?>
              </p>
            </a>
          <?php endforeach; ?>
        </div>

        <?php if ($totalPaginas > 1): ?>
          <div class="filter-bar" style="margin-top:28px;" aria-label="Paginación">
            <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
              <a class="filter-btn <?php echo $p === $paginaActual ? 'is-active' : ''; ?>"
                 href="<?php echo construirUrlInvestigaciones(['pagina' => $p], $busqueda, $categoria, $orden); ?>">
                <?php echo $p; ?>
              </a>
            <?php endfor; ?>
          </div>
        <?php endif; ?>

      <?php endif; ?>

    </div>
  </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
