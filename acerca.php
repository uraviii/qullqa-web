<?php
$tituloPagina = 'Acerca de | Qullqa';
require __DIR__ . '/includes/header.php';

$areas = ['Inteligencia Artificial', 'Big Data', 'Calidad de Software', 'Ciberseguridad'];
?>

  <section class="page-hero">
    <div class="container">
      <h1>Acerca de Qullqa</h1>
      <p>Un espacio para que la comunidad de la UNAM comparta y descubra investigación en tecnología.</p>
    </div>
  </section>

  <section class="content-section">
    <div class="container two-col">
      <div>
        <h2>¿Qué es Qullqa?</h2>
        <p>
          Qullqa —palabra quechua que significa "depósito" o "almacén"— es el repositorio digital
          de investigaciones de la Universidad Nacional de Moquegua. Reúne proyectos, tesis y
          artículos desarrollados por estudiantes y docentes en áreas como inteligencia artificial,
          big data, calidad de software y ciberseguridad, con el objetivo de hacer visible el trabajo
          académico y facilitar su consulta.
        </p>
        <h2>Nuestro objetivo</h2>
        <p>
          Centralizar la producción científica de la universidad en una sola plataforma, con
          búsqueda y filtros por área temática, para que cualquier investigador —dentro o fuera
          de la UNAM— pueda encontrar trabajos relacionados con su propio proyecto.
        </p>
      </div>

      <aside class="info-card">
        <h3>Áreas de investigación activas</h3>
        <ul>
          <?php foreach ($areas as $area): ?>
            <li><?php echo htmlspecialchars($area); ?></li>
          <?php endforeach; ?>
        </ul>
        <p>Plataforma desarrollada como proyecto propio, con HTML, CSS, JavaScript y PHP sobre XAMPP.</p>
      </aside>
    </div>
  </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
