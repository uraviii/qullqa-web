<?php
/**
 * includes/header.php
 * Se espera que la página que lo incluye defina $tituloPagina antes del include.
 * Aquí usamos un switch para decidir qué link del navbar queda marcado como activo.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/funciones.php';

$paginaActual = basename($_SERVER['PHP_SELF']);

switch ($paginaActual) {
    case 'index.php':
        $navActivo = 'inicio';
        break;
    case 'acerca.php':
        $navActivo = 'acerca';
        break;
    case 'investigaciones.php':
        $navActivo = 'investigaciones';
        break;
    case 'soporte.php':
        $navActivo = 'soporte';
        break;
    case 'login.php':
        $navActivo = 'login';
        break;
    case 'publicar.php':
        $navActivo = 'publicar';
        break;
    case 'panel_docente.php':
        $navActivo = 'panel_docente';
        break;
    case 'panel_admin.php':
        $navActivo = 'panel_admin';
        break;
    case 'perfil.php':
        $navActivo = 'perfil';
        break;
    default:
        $navActivo = '';
}

if (!isset($tituloPagina)) {
    $tituloPagina = 'Qullqa';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($tituloPagina); ?></title>
  <link rel="icon" href="assets/logo-qullqa.svg" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body>

  <div class="top-bar"></div>

  <header class="site-header">
    <div class="container header-inner">
      <div class="brand">
        <a href="index.php" style="display:flex; align-items:center; gap:14px;">
          <img src="assets/logo-qullqa.svg" alt="" class="brand-logo">
          <span class="brand-name">Qullqa</span>
        </a>
        <span class="brand-divider"></span>
        <span class="brand-tagline"><strong>Creé</strong> en tu investigador interior</span>
      </div>

      <div class="header-right-group">
        <div class="unam-badge">
          <img src="assets/unam-mark.svg" alt="" class="unam-mark">
          <span class="unam-text">UNAM<span>Universidad Nacional de Moquegua</span></span>
        </div>
        <button class="mobile-toggle" id="mobileMenuBtn" aria-label="Abrir menú de navegación">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>

    <nav class="main-nav container" id="mainNav" aria-label="Navegación principal">
      <ul class="nav-list">
        <li>
          <a href="index.php" class="nav-link <?php if ($navActivo === 'inicio') echo 'is-active'; ?>">Inicio</a>
        </li>
        <li>
          <a href="acerca.php" class="nav-link <?php if ($navActivo === 'acerca') echo 'is-active'; ?>">Acerca de</a>
        </li>
        <li class="has-dropdown <?php if ($navActivo === 'investigaciones') echo 'is-open'; ?>">
          <a href="investigaciones.php" class="nav-link <?php if ($navActivo === 'investigaciones') echo 'is-active'; ?>">Investigaciones</a>
          <ul class="dropdown">
            <li><a href="investigaciones.php?cat=ia">Inteligencia Artificial</a></li>
            <li><a href="investigaciones.php?cat=bigdata">Big Data</a></li>
            <li><a href="investigaciones.php?cat=calidad">Calidad de Software</a></li>
            <li><a href="investigaciones.php?cat=ciberseguridad">Ciberseguridad</a></li>
          </ul>
        </li>
        <li>
          <a href="soporte.php" class="nav-link <?php if ($navActivo === 'soporte') echo 'is-active'; ?>">Soporte</a>
        </li>
        <?php if (($_SESSION['rol'] ?? '') === 'Estudiante'): ?>
          <li>
            <a href="publicar.php" class="nav-link <?php if ($navActivo === 'publicar') echo 'is-active'; ?>">Publicar Investigación</a>
          </li>
        <?php elseif (($_SESSION['rol'] ?? '') === 'Docente'): ?>
          <li>
            <a href="panel_docente.php" class="nav-link <?php if ($navActivo === 'panel_docente') echo 'is-active'; ?>">Panel Docente</a>
          </li>
        <?php elseif (($_SESSION['rol'] ?? '') === 'Administrador'): ?>
          <li>
            <a href="panel_admin.php" class="nav-link <?php if ($navActivo === 'panel_admin') echo 'is-active'; ?>">Panel Admin</a>
          </li>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['usuario'])): ?>
          <li class="nav-item-user">
            <a href="perfil.php" class="nav-link <?php if ($navActivo === 'perfil') echo 'is-active'; ?>" title="Ver mi perfil">
              <strong><?php echo htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['usuario']); ?></strong>
              <?php if (isset($_SESSION['rol'])): ?>
                <span class="user-role-tag"><?php echo htmlspecialchars($_SESSION['rol']); ?></span>
              <?php endif; ?>
            </a>
          </li>
          <li>
            <a href="login.php?logout=1" class="nav-link nav-btn-logout" title="Cerrar Sesión" style="color:var(--color-text-light);">
              Cerrar sesión
            </a>
          </li>
        <?php else: ?>
          <li>
            <a href="login.php" class="nav-link <?php if ($navActivo === 'login') echo 'is-active'; ?>">Iniciar Sesión</a>
          </li>
          <li class="nav-item-register">
            <a href="registro.php" class="nav-link btn-header-register <?php if ($navActivo === 'registro') echo 'is-active'; ?>">Registrarse</a>
          </li>
        <?php endif; ?>
      </ul>
    </nav>
  </header>
