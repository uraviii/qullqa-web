<?php
require_once __DIR__ . '/data/investigaciones.php';
require_once __DIR__ . '/includes/funciones.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$mensajePerfil = '';
$errorPerfil   = '';

// Determinar el ID del usuario a consultar
$usuarioIdConsulta = isset($_GET['id']) ? (int) $_GET['id'] : ($_SESSION['usuario_id'] ?? 0);

if ($usuarioIdConsulta <= 0) {
    header('Location: login.php');
    exit;
}

$usuario = obtenerUsuarioPorId($usuarioIdConsulta);
$esPerfilPropio = (isset($_SESSION['usuario_id']) && (int) $_SESSION['usuario_id'] === $usuarioIdConsulta);

// --- Procesar actualización de perfil (Solo si es perfil propio) ---
if ($esPerfilPropio && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_perfil'])) {
    $archivoFoto = $_FILES['foto_perfil'] ?? null;
    $resPerfil = actualizarPerfilUsuario($usuarioIdConsulta, $_POST, $archivoFoto);
    if ($resPerfil['ok']) {
        $mensajePerfil = $resPerfil['mensaje'];
        // Recargar datos actualizados del usuario
        $usuario = obtenerUsuarioPorId($usuarioIdConsulta);
    } else {
        $errorPerfil = $resPerfil['mensaje'];
    }
}

$tituloPagina = $usuario ? 'Perfil de ' . $usuario['nombre'] . ' | Qullqa' : 'Perfil no encontrado | Qullqa';
require __DIR__ . '/includes/header.php';

// Obtener datos según el rol
$investigacionesPublicadas = [];
$feedbackDadoPorDocente    = [];
$todosLosFeedbacksRecibidos = [];
$totalCitasAcumuladas       = 0;

if ($usuario) {
    $investigacionesPublicadas = obtenerInvestigacionesPorUsuario($usuario['id']);
    foreach ($investigacionesPublicadas as $inv) {
        $totalCitasAcumuladas += (int) $inv['citas'];
        foreach ($inv['feedback'] as $fb) {
            $fb['investigacion_titulo'] = $inv['titulo'];
            $fb['investigacion_id']     = $inv['id'];
            $todosLosFeedbacksRecibidos[] = $fb;
        }
    }

    if ($usuario['rol'] === 'Docente') {
        $feedbackDadoPorDocente = obtenerFeedbackPorDocente($usuario['id']);
    }
}
?>

  <?php if (!$usuario): ?>
    <section class="page-hero">
      <div class="container">
        <h1>Perfil no encontrado</h1>
        <p>El usuario solicitado no está registrado o no se encuentra disponible.</p>
      </div>
    </section>
    <section class="content-section">
      <div class="container">
        <a class="filter-btn" href="index.php">Volver al inicio</a>
      </div>
    </section>
  <?php else: ?>

    <!-- Encabezado Limpio del Perfil (Sin foto gigante) -->
    <section class="profile-hero-section">
      <div class="container">
        <div class="profile-header-card" style="display:flex; align-items:flex-start; justify-content:space-between; gap:20px;">
          
          <div class="profile-header-info" style="flex:1;">
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px; flex-wrap:wrap;">
              <h1 class="profile-name" style="margin:0;"><?php echo htmlspecialchars($usuario['nombre']); ?></h1>
              <span class="profile-role-badge role-<?php echo strtolower($usuario['rol']); ?>">
                <?php echo htmlspecialchars($usuario['rol']); ?>
              </span>
            </div>

            <p class="profile-email" style="margin-bottom:12px;">
              Correo institucional: <strong><?php echo htmlspecialchars($usuario['correo']); ?></strong>
              <span class="profile-date-joined">· Registrado el <?php echo htmlspecialchars($usuario['fecha_registro'] ?? '2026'); ?></span>
            </p>

            <?php if (!empty($usuario['biografia'])): ?>
              <p class="profile-bio"><?php echo nl2br(htmlspecialchars($usuario['biografia'])); ?></p>
            <?php else: ?>
              <p class="profile-bio profile-bio-empty">Sin presentación académica registrada.</p>
            <?php endif; ?>
          </div>

          <?php if ($esPerfilPropio): ?>
            <div class="profile-actions">
              <button type="button" class="filter-btn is-active" id="btnToggleEditProfile">
                Editar perfil
              </button>
            </div>
          <?php endif; ?>

        </div>

        <?php if ($esPerfilPropio): ?>
          <!-- Contenedor Independiente de Edición de Perfil -->
          <div class="profile-edit-card" id="editProfileCard" style="<?php echo ($errorPerfil !== '' || $mensajePerfil !== '') ? 'display:block;' : 'display:none;'; ?> margin-top: 20px;">
            <div class="profile-edit-header">
              <h3>Actualizar información de perfil</h3>
              <p class="form-note" style="text-align:left; margin:4px 0 16px;">Modifica tus datos institucionales o biografía académica.</p>
            </div>

            <?php if ($mensajePerfil !== ''): ?>
              <p class="form-success is-visible"><?php echo htmlspecialchars($mensajePerfil); ?></p>
            <?php endif; ?>
            <?php if ($errorPerfil !== ''): ?>
              <p class="form-success" style="display:block; background:#FBEAEA; color:#8A2C2C;"><?php echo htmlspecialchars($errorPerfil); ?></p>
            <?php endif; ?>

            <form method="post" action="perfil.php?id=<?php echo $usuario['id']; ?>" enctype="multipart/form-data">
              <input type="hidden" name="accion_perfil" value="1">

              <div class="form-group">
                <label for="nombre">Nombre completo</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
              </div>

              <div class="form-group">
                <label for="correo">Correo institucional</label>
                <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
              </div>

              <div class="form-group">
                <label for="biografia">Resumen académico / Presentación</label>
                <textarea id="biografia" name="biografia" placeholder="Áreas de investigación o especialidad..."><?php echo htmlspecialchars($usuario['biografia'] ?? ''); ?></textarea>
              </div>

              <div style="display:flex; gap:12px; margin-top:18px;">
                <button type="submit" class="form-submit" style="width:auto; padding:10px 24px;">Guardar cambios</button>
                <button type="button" class="filter-btn" id="btnCancelEditProfile">Cancelar</button>
              </div>
            </form>
          </div>
        <?php endif; ?>

      </div>
    </section>

    <!-- Contenido Principal Dividido en Cajas / Contenedores Lado a Lado -->
    <section class="content-section">
      <div class="container">

        <!-- Barra de Métricas -->
        <div class="profile-stats-grid">
          <?php if ($usuario['rol'] === 'Estudiante'): ?>
            <div class="stat-card">
              <div class="stat-info">
                <span class="stat-number"><?php echo count($investigacionesPublicadas); ?></span>
                <span class="stat-label">Investigaciones publicadas</span>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-info">
                <span class="stat-number"><?php echo count($todosLosFeedbacksRecibidos); ?></span>
                <span class="stat-label">Evaluaciones recibidas</span>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-info">
                <span class="stat-number"><?php echo $totalCitasAcumuladas; ?></span>
                <span class="stat-label">Citas bibliográficas</span>
              </div>
            </div>
          <?php elseif ($usuario['rol'] === 'Docente'): ?>
            <div class="stat-card">
              <div class="stat-info">
                <span class="stat-number"><?php echo count($feedbackDadoPorDocente); ?></span>
                <span class="stat-label">Retroalimentaciones emitidas</span>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-info">
                <?php 
                  $investigacionesUnicasRevisadas = array_unique(array_column($feedbackDadoPorDocente, 'investigacion_id'));
                ?>
                <span class="stat-number"><?php echo count($investigacionesUnicasRevisadas); ?></span>
                <span class="stat-label">Investigaciones revisadas</span>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-info">
                <span class="stat-number"><?php echo count($investigacionesPublicadas); ?></span>
                <span class="stat-label">Publicaciones del docente</span>
              </div>
            </div>
          <?php else: ?>
            <div class="stat-card">
              <div class="stat-info">
                <span class="stat-number">Administrador</span>
                <span class="stat-label">Acceso de gestión de sistema</span>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-info">
                <span class="stat-number"><?php echo count($investigacionesPublicadas); ?></span>
                <span class="stat-label">Publicaciones institucionales</span>
              </div>
            </div>
          <?php endif; ?>
        </div>


        <!-- ESTRUCTURA EN DOS CONTENEDORES LADO A LADO (Flexbox/Grid asistido) -->
        <div class="profile-dashboard-grid" style="display:flex; gap:28px; align-items:flex-start; flex-wrap:wrap;">

          <!-- CONTENEDOR 1 (IZQUIERDA: Trabajos e Investigaciones - 62%) -->
          <div class="profile-main-column" style="flex:1; min-width:320px;">
            
            <?php if ($usuario['rol'] === 'Estudiante'): ?>
              <div class="profile-block-container" style="background:#FFF; border:1.5px solid #E5E0EE; border-radius:18px; padding:24px;">
                <div class="profile-block-header" style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #F0ECF7; padding-bottom:12px; margin-bottom:18px;">
                  <h2 class="section-title" style="margin:0; font-size:1.2rem; color:var(--color-purple);">Trabajos e investigaciones publicadas</h2>
                  <span class="profile-count-pill" style="background:var(--color-pill); color:var(--color-purple); font-weight:700; padding:4px 12px; border-radius:999px; font-size:.8rem;">
                    <?php echo count($investigacionesPublicadas); ?> documentos
                  </span>
                </div>

                <?php if (empty($investigacionesPublicadas)): ?>
                  <div class="profile-empty-card" style="background:#F9F8FC; border:1px dashed #DCD7E5; border-radius:10px; padding:28px; text-align:center; color:var(--color-text-light);">
                    <p style="margin:0;">El estudiante no registra investigaciones publicadas hasta la fecha.</p>
                    <?php if ($esPerfilPropio): ?>
                      <a href="publicar.php" class="filter-btn is-active" style="display:inline-block; margin-top:12px;">Publicar investigación</a>
                    <?php endif; ?>
                  </div>
                <?php else: ?>
                  <div class="profile-research-list" style="display:flex; flex-direction:column; gap:18px;">
                    <?php foreach ($investigacionesPublicadas as $inv): ?>
                      <div class="profile-item-card" style="background:#FAFAFC; border:1px solid #E6E1EE; border-radius:10px; padding:20px;">
                        <div class="profile-item-header" style="display:flex; justify-content:space-between; align-items:flex-start; gap:14px;">
                          <div>
                            <span class="research-card__tag" style="display:inline-block; margin-bottom:6px;">
                              <?php echo htmlspecialchars(etiquetaCategoria($inv['categoria'])); ?>
                            </span>
                            <h3 class="profile-item-title" style="margin:4px 0;">
                              <a href="detalle.php?id=<?php echo $inv['id']; ?>" style="color:var(--color-purple-dark); font-weight:700;"><?php echo htmlspecialchars($inv['titulo']); ?></a>
                            </h3>
                            <p class="profile-item-meta" style="font-size:0.83rem; color:var(--color-text-light); margin:0;">
                              Publicado el: <?php echo htmlspecialchars($inv['publicado_el']); ?> · Citas acumuladas: <?php echo $inv['citas']; ?>
                              <?php if (!empty($inv['autores'])): ?>
                                · Autores: <?php echo htmlspecialchars(listarAutores($inv['autores'])); ?>
                              <?php endif; ?>
                            </p>
                          </div>

                          <?php if (!empty($inv['archivo_pdf'])): ?>
                            <a href="uploads/<?php echo htmlspecialchars($inv['archivo_pdf']); ?>" target="_blank" class="filter-btn" style="white-space:nowrap; padding:6px 14px; font-size:.8rem;">
                              Ver PDF
                            </a>
                          <?php endif; ?>
                        </div>

                        <p class="profile-item-desc" style="font-size:0.92rem; margin:12px 0 0; line-height:1.5; color:var(--color-text);"><?php echo nl2br(htmlspecialchars($inv['desc'])); ?></p>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>

            <?php elseif ($usuario['rol'] === 'Docente'): ?>
              
              <div class="profile-block-container" style="background:#FFF; border:1.5px solid #E5E0EE; border-radius:18px; padding:24px;">
                <div class="profile-block-header" style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #F0ECF7; padding-bottom:12px; margin-bottom:18px;">
                  <h2 class="section-title" style="margin:0; font-size:1.2rem; color:var(--color-purple);">Investigaciones evaluadas por el docente</h2>
                  <span class="profile-count-pill" style="background:var(--color-pill); color:var(--color-purple); font-weight:700; padding:4px 12px; border-radius:999px; font-size:.8rem;">
                    <?php echo count($feedbackDadoPorDocente); ?> revisiones
                  </span>
                </div>

                <?php if (empty($feedbackDadoPorDocente)): ?>
                  <div class="profile-empty-card" style="background:#F9F8FC; border:1px dashed #DCD7E5; border-radius:10px; padding:28px; text-align:center; color:var(--color-text-light);">
                    <p style="margin:0;">El docente aún no ha registrado retroalimentación en proyectos académicos de estudiantes.</p>
                    <?php if ($esPerfilPropio): ?>
                      <a href="panel_docente.php" class="filter-btn is-active" style="display:inline-block; margin-top:12px;">Ir al Panel Docente</a>
                    <?php endif; ?>
                  </div>
                <?php else: ?>
                  <div class="profile-research-list" style="display:flex; flex-direction:column; gap:18px;">
                    <?php foreach ($feedbackDadoPorDocente as $fbItem): ?>
                      <div class="profile-item-card" style="background:#FAFAFC; border:1px solid #E6E1EE; border-radius:10px; padding:20px;">
                        <div class="profile-item-header" style="display:flex; justify-content:space-between; align-items:flex-start; gap:14px;">
                          <div>
                            <span class="research-card__tag" style="display:inline-block; margin-bottom:6px;">
                              <?php echo htmlspecialchars(etiquetaCategoria($fbItem['categoria'])); ?>
                            </span>
                            <h3 class="profile-item-title" style="margin:4px 0;">
                              <a href="detalle.php?id=<?php echo $fbItem['investigacion_id']; ?>" style="color:var(--color-purple-dark); font-weight:700;"><?php echo htmlspecialchars($fbItem['investigacion_titulo']); ?></a>
                            </h3>
                            <p class="profile-item-meta" style="font-size:0.83rem; color:var(--color-text-light); margin:0;">
                              Estudiante autor: 
                              <a href="perfil.php?id=<?php echo $fbItem['estudiante_id']; ?>" style="color:var(--color-purple); font-weight:600; text-decoration:underline;">
                                <?php echo htmlspecialchars($fbItem['estudiante_nombre']); ?>
                              </a>
                              · Citas: <?php echo $fbItem['citas']; ?>
                            </p>
                          </div>

                          <?php if (!empty($fbItem['archivo_pdf'])): ?>
                            <a href="uploads/<?php echo htmlspecialchars($fbItem['archivo_pdf']); ?>" target="_blank" class="filter-btn" style="white-space:nowrap; padding:6px 14px; font-size:.8rem;">
                              Ver PDF
                            </a>
                          <?php endif; ?>
                        </div>

                        <div class="profile-feedback-quote" style="margin-top:14px; background:#FFF; border:1px solid #E2DCED; border-left:4px solid var(--color-gold); border-radius:6px; padding:12px 14px;">
                          <div class="feedback-quote-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                            <span class="feedback-quote-title" style="font-weight:700; font-size:0.8rem; color:var(--color-purple); text-transform:uppercase;">Comentario emitido por el docente:</span>
                            <span class="feedback-date" style="font-size:0.75rem; color:#958FA3;"><?php echo htmlspecialchars($fbItem['fecha_feedback']); ?></span>
                          </div>
                          <p class="feedback-quote-body" style="margin:0; font-size:0.9rem; color:var(--color-text); line-height:1.45; font-style:italic;">"<?php echo nl2br(htmlspecialchars($fbItem['comentario'])); ?>"</p>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>

            <?php else: ?>

              <div class="profile-block-container" style="background:#FFF; border:1.5px solid #E5E0EE; border-radius:18px; padding:24px;">
                <div class="profile-block-header">
                  <h2 class="section-title" style="margin:0; font-size:1.2rem; color:var(--color-purple);">Registro de publicaciones del administrador</h2>
                </div>
                <?php if (!empty($investigacionesPublicadas)): ?>
                  <div class="profile-research-list" style="display:flex; flex-direction:column; gap:18px;">
                    <?php foreach ($investigacionesPublicadas as $inv): ?>
                      <div class="profile-item-card" style="background:#FAFAFC; border:1px solid #E6E1EE; border-radius:10px; padding:20px;">
                        <h3 class="profile-item-title">
                          <a href="detalle.php?id=<?php echo $inv['id']; ?>"><?php echo htmlspecialchars($inv['titulo']); ?></a>
                        </h3>
                        <p class="profile-item-desc"><?php echo nl2br(htmlspecialchars($inv['desc'])); ?></p>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <div class="profile-empty-card" style="background:#F9F8FC; border:1px dashed #DCD7E5; border-radius:10px; padding:28px; text-align:center; color:var(--color-text-light);">
                    <p style="margin:0;">Cuenta administrativa con acceso de gestión institucional.</p>
                  </div>
                <?php endif; ?>
              </div>

            <?php endif; ?>

          </div>

          <!-- CONTENEDOR 2 (DERECHA: Panel Lateral Separado de Feedback / Publicaciones - 380px) -->
          <aside class="profile-sidebar-column" style="width:340px; min-width:280px;">

            <?php if ($usuario['rol'] === 'Estudiante'): ?>
              <!-- Panel Derecho Estudiante: Retroalimentación de Docentes -->
              <div class="profile-sidebar-card" style="background:#FFF; border:1.5px solid #E5E0EE; border-radius:18px; padding:22px;">
                <h3 class="sidebar-card-title" style="font-family:var(--font-heading); font-size:1.08rem; color:var(--color-purple); margin:0 0 14px; border-bottom:2px solid #F0ECF7; padding-bottom:10px;">
                  Retroalimentación docente recibida
                </h3>

                <?php if (empty($todosLosFeedbacksRecibidos)): ?>
                  <p class="sidebar-empty-text" style="font-size:0.86rem; color:var(--color-text-light); margin:0;">
                    Aún no se registran comentarios de evaluación docente en los trabajos publicados.
                  </p>
                <?php else: ?>
                  <div class="sidebar-feedback-list" style="display:flex; flex-direction:column; gap:14px;">
                    <?php foreach ($todosLosFeedbacksRecibidos as $fb): ?>
                      <div class="sidebar-feedback-item" style="background:#F9F8FC; border:1px solid #E2DCED; border-radius:10px; padding:14px;">
                        <div class="sidebar-feedback-header" style="margin-bottom:8px;">
                          <a href="perfil.php?id=<?php echo $fb['docente_id']; ?>" class="sidebar-user-name" style="font-weight:700; font-size:0.9rem; color:var(--color-purple);">
                            <?php echo htmlspecialchars($fb['docente']); ?>
                          </a>
                          <span class="sidebar-user-role" style="display:block; font-size:0.74rem; color:var(--color-text-light);">Docente evaluador</span>
                        </div>
                        
                        <p class="sidebar-paper-ref" style="font-size:0.8rem; color:var(--color-text-light); margin:0 0 6px;">
                          En: <a href="detalle.php?id=<?php echo $fb['investigacion_id']; ?>" style="color:var(--color-purple-dark); font-weight:600;"><?php echo htmlspecialchars($fb['investigacion_titulo']); ?></a>
                        </p>
                        
                        <p class="sidebar-comment-text" style="font-size:0.86rem; color:var(--color-text); margin:0 0 6px; line-height:1.45;"><?php echo nl2br(htmlspecialchars($fb['comentario'])); ?></p>
                        <span class="sidebar-date" style="font-size:0.75rem; color:#958FA3; display:block;"><?php echo htmlspecialchars($fb['fecha']); ?></span>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>

            <?php elseif ($usuario['rol'] === 'Docente'): ?>
              <!-- Panel Derecho Docente: Publicaciones Propias -->
              <div class="profile-sidebar-card" style="background:#FFF; border:1.5px solid #E5E0EE; border-radius:18px; padding:22px;">
                <h3 class="sidebar-card-title" style="font-family:var(--font-heading); font-size:1.08rem; color:var(--color-purple); margin:0 0 14px; border-bottom:2px solid #F0ECF7; padding-bottom:10px;">
                  Investigaciones del docente
                </h3>

                <?php if (empty($investigacionesPublicadas)): ?>
                  <p class="sidebar-empty-text" style="font-size:0.86rem; color:var(--color-text-light); margin:0;">
                    El docente no ha registrado investigaciones propias publicadas.
                  </p>
                <?php else: ?>
                  <div class="sidebar-paper-list" style="display:flex; flex-direction:column; gap:12px;">
                    <?php foreach ($investigacionesPublicadas as $inv): ?>
                      <div class="sidebar-paper-item" style="background:#F9F8FC; border:1px solid #E6E1EE; border-radius:8px; padding:12px;">
                        <span class="research-card__tag" style="font-size:0.72rem; padding:2px 8px;"><?php echo htmlspecialchars(etiquetaCategoria($inv['categoria'])); ?></span>
                        <h4 class="sidebar-paper-title" style="font-family:var(--font-heading); font-size:0.92rem; margin:4px 0 2px;">
                          <a href="detalle.php?id=<?php echo $inv['id']; ?>" style="color:var(--color-purple-dark);"><?php echo htmlspecialchars($inv['titulo']); ?></a>
                        </h4>
                        <p class="sidebar-date" style="font-size:0.75rem; color:#958FA3; margin:0;">Publicado: <?php echo htmlspecialchars($inv['publicado_el']); ?></p>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>

            <?php else: ?>
              <div class="profile-sidebar-card" style="background:#FFF; border:1.5px solid #E5E0EE; border-radius:18px; padding:22px;">
                <h3 class="sidebar-card-title" style="font-family:var(--font-heading); font-size:1.08rem; color:var(--color-purple); margin:0 0 14px; border-bottom:2px solid #F0ECF7; padding-bottom:10px;">
                  Gestión de sistema
                </h3>
                <p class="sidebar-empty-text" style="font-size:0.86rem; color:var(--color-text-light); margin:0;">Panel para administración de usuarios y contenido.</p>
                <?php if ($esPerfilPropio): ?>
                  <a href="panel_admin.php" class="filter-btn is-active" style="display:block; text-align:center; margin-top:12px;">Ir al Panel Admin</a>
                <?php endif; ?>
              </div>
            <?php endif; ?>

          </aside>

        </div>

      </div>
    </section>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        var btnToggle = document.getElementById('btnToggleEditProfile');
        var btnCancel = document.getElementById('btnCancelEditProfile');
        var editCard  = document.getElementById('editProfileCard');

        if (btnToggle && editCard) {
          btnToggle.addEventListener('click', function () {
            if (editCard.style.display === 'none' || editCard.style.display === '') {
              editCard.style.display = 'block';
              editCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
              editCard.style.display = 'none';
            }
          });
        }

        if (btnCancel && editCard) {
          btnCancel.addEventListener('click', function () {
            editCard.style.display = 'none';
          });
        }
      });
    </script>

  <?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
