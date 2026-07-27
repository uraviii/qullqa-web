<?php
require_once __DIR__ . '/data/investigaciones.php';
require_once __DIR__ . '/includes/funciones.php';

$tituloPagina = 'Panel Admin | Qullqa';
require __DIR__ . '/includes/header.php';

if (($_SESSION['rol'] ?? '') !== 'Administrador') {
    echo '<section class="content-section"><div class="container">';
    echo '<p class="empty-state is-visible">Esta sección es solo para cuentas de Administrador. <a href="login.php">Inicia sesión</a> con una cuenta de ese tipo.</p>';
    echo '</div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$mensaje = '';
$errorMensaje = '';

// --- Acciones por POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        case 'actualizar_usuario':
            actualizarUsuario((int) $_POST['id'], $_POST);
            $mensaje = 'Usuario actualizado.';
            break;

        case 'eliminar_usuario':
            if (eliminarUsuario((int) $_POST['id'])) {
                $mensaje = 'Usuario eliminado.';
            } else {
                $errorMensaje = 'No se pudo eliminar: este usuario tiene investigaciones publicadas.';
            }
            break;

        case 'actualizar_investigacion':
            $nombreArchivoPdf = null;
            if (isset($_FILES['archivo_pdf']) && $_FILES['archivo_pdf']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['archivo_pdf']['name'], PATHINFO_EXTENSION));
                if ($ext === 'pdf') {
                    $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($_FILES['archivo_pdf']['name'], PATHINFO_FILENAME));
                    $nombreArchivoPdf = $nombreLimpio . '_' . uniqid() . '.pdf';
                    move_uploaded_file($_FILES['archivo_pdf']['tmp_name'], __DIR__ . '/uploads/' . $nombreArchivoPdf);
                }
            }
            actualizarInvestigacion((int) $_POST['id'], $_POST, $nombreArchivoPdf);
            $mensaje = 'Investigación actualizada.';
            break;

        case 'eliminar_investigacion':
            eliminarInvestigacion((int) $_POST['id']);
            $mensaje = 'Investigación eliminada.';
            break;
    }
}

$seccion = $_GET['seccion'] ?? 'usuarios';

$usuarios = obtenerUsuarios();
$roles    = obtenerRoles();

$investigaciones = obtenerInvestigaciones();

$usuarioEditando = isset($_GET['editar_usuario']) ? obtenerUsuarioPorId((int) $_GET['editar_usuario']) : null;
$investigacionEditando = isset($_GET['editar_investigacion']) ? obtenerInvestigacionPorId((int) $_GET['editar_investigacion']) : null;

$categorias = ['ia' => 'Inteligencia Artificial', 'bigdata' => 'Big Data', 'calidad' => 'Calidad de Software', 'ciberseguridad' => 'Ciberseguridad'];
?>

  <section class="page-hero">
    <div class="container">
      <h1>Panel Administrador</h1>
      <p>Gestiona usuarios e investigaciones de la plataforma.</p>
    </div>
  </section>

  <section class="content-section">
    <div class="container">

      <?php if ($mensaje !== ''): ?>
        <p class="form-success is-visible"><?php echo htmlspecialchars($mensaje); ?></p>
      <?php endif; ?>
      <?php if ($errorMensaje !== ''): ?>
        <p class="form-success" style="display:block; background:#FBEAEA; color:#8A2C2C;">⚠ <?php echo htmlspecialchars($errorMensaje); ?></p>
      <?php endif; ?>

      <div class="filter-bar">
        <a class="filter-btn <?php echo $seccion === 'usuarios' ? 'is-active' : ''; ?>" href="panel_admin.php?seccion=usuarios">Usuarios</a>
        <a class="filter-btn <?php echo $seccion === 'investigaciones' ? 'is-active' : ''; ?>" href="panel_admin.php?seccion=investigaciones">Investigaciones</a>
      </div>

      <?php if ($seccion === 'usuarios'): ?>

        <?php if ($usuarioEditando): ?>
          <div class="form-card" style="margin:24px auto;">
            <h3>Editar usuario</h3>
            <form method="post" action="panel_admin.php?seccion=usuarios">
              <input type="hidden" name="accion" value="actualizar_usuario">
              <input type="hidden" name="id" value="<?php echo $usuarioEditando['id']; ?>">

              <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuarioEditando['nombre']); ?>" required>
              </div>

              <div class="form-group">
                <label for="correo">Correo</label>
                <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($usuarioEditando['correo']); ?>" required>
              </div>

              <div class="form-group">
                <label for="rol_id">Rol</label>
                <select id="rol_id" name="rol_id" required>
                  <?php foreach ($roles as $rol): ?>
                    <option value="<?php echo $rol['id']; ?>" <?php echo (int) $usuarioEditando['rol_id'] === (int) $rol['id'] ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($rol['nombre']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <button type="submit" class="form-submit">Guardar cambios</button>
              <p class="form-note"><a href="panel_admin.php?seccion=usuarios">Cancelar</a></p>
            </form>
          </div>
        <?php endif; ?>

        <table style="width:100%; border-collapse:collapse; margin-top:20px;">
          <thead>
            <tr style="text-align:left; border-bottom:2px solid var(--color-pill);">
              <th style="padding:10px;">Usuario</th>
              <th style="padding:10px;">Correo</th>
              <th style="padding:10px;">Rol</th>
              <th style="padding:10px;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($usuarios as $u): ?>
              <tr style="border-bottom:1px solid var(--color-pill);">
                <td style="padding:10px;">
                  <a href="perfil.php?id=<?php echo $u['id']; ?>" style="font-weight:600; color:var(--color-purple);">
                    <?php echo htmlspecialchars($u['nombre']); ?>
                  </a>
                </td>
                <td style="padding:10px;"><?php echo htmlspecialchars($u['correo']); ?></td>
                <td style="padding:10px;">
                  <span class="user-role-tag"><?php echo htmlspecialchars($u['rol']); ?></span>
                </td>
                <td style="padding:10px;">
                  <a href="perfil.php?id=<?php echo $u['id']; ?>" style="color:var(--color-purple); font-weight:600;">Ver perfil</a>
                  &nbsp;·&nbsp;
                  <a href="panel_admin.php?seccion=usuarios&editar_usuario=<?php echo $u['id']; ?>">Editar</a>
                  &nbsp;·&nbsp;
                  <form method="post" action="panel_admin.php?seccion=usuarios" style="display:inline;"
                        onsubmit="return confirm('¿Eliminar a <?php echo htmlspecialchars($u['nombre']); ?>?');">
                    <input type="hidden" name="accion" value="eliminar_usuario">
                    <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                    <button type="submit" style="background:none; border:none; color:#B33; cursor:pointer; padding:0;">Eliminar</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

      <?php else: ?>

        <?php if ($investigacionEditando): ?>
          <div class="form-card" style="margin:24px auto;">
            <h3>Editar investigación</h3>
            <form method="post" action="panel_admin.php?seccion=investigaciones" enctype="multipart/form-data">
              <input type="hidden" name="accion" value="actualizar_investigacion">
              <input type="hidden" name="id" value="<?php echo $investigacionEditando['id']; ?>">

              <div class="form-group">
                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($investigacionEditando['titulo']); ?>" required>
              </div>

              <div class="form-group">
                <label for="categoria">Categoría</label>
                <select id="categoria" name="categoria" required>
                  <?php foreach ($categorias as $slug => $nombre): ?>
                    <option value="<?php echo $slug; ?>" <?php echo $investigacionEditando['categoria'] === $slug ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($nombre); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label for="citas">Citas</label>
                <input type="number" id="citas" name="citas" min="0" value="<?php echo $investigacionEditando['citas']; ?>" required>
              </div>

              <div class="form-group">
                <label for="resumen">Resumen</label>
                <textarea id="resumen" name="resumen" required><?php echo htmlspecialchars($investigacionEditando['desc']); ?></textarea>
              </div>

              <div class="form-group">
                <label for="archivo_pdf">Reemplazar o subir PDF (Opcional)</label>
                <input type="file" id="archivo_pdf" name="archivo_pdf" accept=".pdf,application/pdf">
                <?php if (!empty($investigacionEditando['archivo_pdf'])): ?>
                  <span style="font-size:0.82rem; color:var(--color-purple);">PDF actual: <a href="uploads/<?php echo htmlspecialchars($investigacionEditando['archivo_pdf']); ?>" target="_blank"><?php echo htmlspecialchars($investigacionEditando['archivo_pdf']); ?></a></span>
                <?php endif; ?>
              </div>

              <button type="submit" class="form-submit">Guardar cambios</button>
              <p class="form-note"><a href="panel_admin.php?seccion=investigaciones">Cancelar</a></p>
            </form>
          </div>
        <?php endif; ?>

        <table style="width:100%; border-collapse:collapse; margin-top:20px;">
          <thead>
            <tr style="text-align:left; border-bottom:2px solid var(--color-pill);">
              <th style="padding:10px;">Título</th>
              <th style="padding:10px;">Categoría</th>
              <th style="padding:10px;">Citas</th>
              <th style="padding:10px;">PDF</th>
              <th style="padding:10px;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($investigaciones as $inv): ?>
              <tr style="border-bottom:1px solid var(--color-pill);">
                <td style="padding:10px;"><a href="detalle.php?id=<?php echo $inv['id']; ?>"><?php echo htmlspecialchars($inv['titulo']); ?></a></td>
                <td style="padding:10px;"><?php echo htmlspecialchars(etiquetaCategoria($inv['categoria'])); ?></td>
                <td style="padding:10px;"><?php echo $inv['citas']; ?></td>
                <td style="padding:10px;">
                  <?php if (!empty($inv['archivo_pdf'])): ?>
                    <a href="uploads/<?php echo htmlspecialchars($inv['archivo_pdf']); ?>" target="_blank" style="color:var(--color-purple); font-weight:600;">📄 Ver PDF</a>
                  <?php else: ?>
                    <span style="color:#999; font-size:.85rem;">—</span>
                  <?php endif; ?>
                </td>
                <td style="padding:10px;">
                  <a href="panel_admin.php?seccion=investigaciones&editar_investigacion=<?php echo $inv['id']; ?>">Editar</a>
                  &nbsp;·&nbsp;
                  <form method="post" action="panel_admin.php?seccion=investigaciones" style="display:inline;"
                        onsubmit="return confirm('¿Eliminar esta investigación?');">
                    <input type="hidden" name="accion" value="eliminar_investigacion">
                    <input type="hidden" name="id" value="<?php echo $inv['id']; ?>">
                    <button type="submit" style="background:none; border:none; color:#B33; cursor:pointer; padding:0;">Eliminar</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

      <?php endif; ?>

    </div>
  </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
