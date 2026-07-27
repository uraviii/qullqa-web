<?php
/**
 * includes/funciones.php
 *
 * Funciones definidas por el usuario que organizan la lógica del backend.
 * Desde que el proyecto usa base de datos, varias de estas funciones ya
 * arman consultas SQL con WHERE, AND/OR y alias (AS) en vez de filtrar
 * arreglos en PHP.
 */

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/../data/investigaciones.php';

/**
 * Valida los datos del formulario de soporte.
 *
 * @param array $datos Se espera $_POST con las claves nombre/correo/asunto/mensaje
 * @return array Lista de mensajes de error (vacía si todo está bien)
 */
function validarFormularioSoporte(array $datos): array
{
    $errores = [];

    $nombre  = trim($datos['nombre'] ?? '');
    $correo  = trim($datos['correo'] ?? '');
    $asunto  = $datos['asunto'] ?? '';
    $mensaje = trim($datos['mensaje'] ?? '');

    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    } elseif (strlen($nombre) < 3) {
        $errores[] = 'El nombre debe tener al menos 3 caracteres.';
    }

    if ($correo === '') {
        $errores[] = 'El correo es obligatorio.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo no tiene un formato válido.';
    }

    if ($asunto === '') {
        $errores[] = 'Selecciona un asunto.';
    }

    if ($mensaje === '') {
        $errores[] = 'El mensaje no puede estar vacío.';
    }

    return $errores;
}

/**
 * Arma el mensaje de confirmación según el asunto elegido en el formulario
 * de soporte.
 */
function construirMensajeConfirmacion(string $asunto): string
{
    switch ($asunto) {
        case 'cuenta':
            return 'Recibimos tu reporte sobre tu cuenta. El equipo de soporte técnico te contactará en menos de 48 horas.';
        case 'publicacion':
            return 'Gracias por tu interés en publicar una investigación. Te enviaremos la guía de publicación a tu correo.';
        case 'tecnico':
            return 'Reportamos tu incidencia técnica al equipo de desarrollo.';
        default:
            return 'Recibimos tu mensaje. Te responderemos a la brevedad.';
    }
}

/**
 * Guarda un mensaje de soporte con INSERT en la tabla mensajes_soporte.
 */
function registrarMensajeSoporte(array $datos): bool
{
    $conexion = obtenerConexion();

    $sentencia = $conexion->prepare(
        'INSERT INTO mensajes_soporte (nombre, correo, asunto, mensaje)
         VALUES (:nombre, :correo, :asunto, :mensaje)'
    );

    return $sentencia->execute([
        'nombre'  => $datos['nombre'],
        'correo'  => $datos['correo'],
        'asunto'  => $datos['asunto'],
        'mensaje' => $datos['mensaje'],
    ]);
}

/**
 * Busca un usuario por correo (SELECT ... WHERE) y verifica su contraseña
 * cifrada con password_verify(). También trae su id y el nombre de su rol.
 *
 * @return array{ok: bool, mensaje: string, id?: int, rol?: string}
 */
function validarCredenciales(string $usuario, string $password): array
{
    if ($usuario === '' || $password === '') {
        return ['ok' => false, 'mensaje' => 'Completa ambos campos.'];
    }

    $conexion = obtenerConexion();

    // SELECT con alias (AS) y WHERE por correo
    $sentencia = $conexion->prepare(
        'SELECT u.id AS usuario_id, u.nombre AS nombre_usuario,
                u.password AS password_hash, r.nombre AS rol,
                u.foto_perfil, u.biografia
         FROM usuarios AS u, roles AS r
         WHERE u.correo = :correo AND u.rol_id = r.id'
    );
    $sentencia->execute(['correo' => $usuario]);
    $fila = $sentencia->fetch();

    if (!$fila) {
        return ['ok' => false, 'mensaje' => 'No existe una cuenta con ese correo.'];
    }

    if (!password_verify($password, $fila['password_hash'])) {
        return ['ok' => false, 'mensaje' => 'Contraseña incorrecta.'];
    }

    return [
        'ok'          => true,
        'mensaje'     => '',
        'id'          => (int) $fila['usuario_id'],
        'nombre'      => $fila['nombre_usuario'],
        'rol'         => $fila['rol'],
        'foto_perfil' => $fila['foto_perfil'],
        'biografia'   => $fila['biografia'],
    ];
}

/**
 * Busca el id de una categoría a partir de su slug (WHERE simple).
 */
function obtenerCategoriaIdPorSlug(string $slug): ?int
{
    $conexion = obtenerConexion();

    $sentencia = $conexion->prepare('SELECT id FROM categorias WHERE slug = :slug');
    $sentencia->execute(['slug' => $slug]);
    $fila = $sentencia->fetch();

    return $fila ? (int) $fila['id'] : null;
}

/**
 * Filtra las investigaciones directamente en SQL, combinando WHERE con
 * AND y OR:
 *  - el texto buscado debe estar en el título O en la descripción
 *  - Y ADEMÁS debe pertenecer a la categoría elegida (si no es "todas")
 */
/**
 * Filtra las investigaciones directamente en SQL, combinando WHERE con
 * AND y OR, y permite ordenar por fecha, citas o título.
 */
function filtrarInvestigaciones(string $busqueda, string $categoriaSlug, string $orden = 'recientes'): array
{
    $conexion = obtenerConexion();
    $categoriasPorId = obtenerCategorias();

    $sql    = 'SELECT id, titulo, descripcion, categoria_id, usuario_id, citas, archivo_pdf,
                      fecha_publicacion AS publicado_el
               FROM investigaciones
               WHERE 1 = 1';
    $params = [];

    if ($busqueda !== '') {
        $sql .= ' AND (titulo LIKE :termino OR descripcion LIKE :termino)';
        $params['termino'] = '%' . $busqueda . '%';
    }

    if ($categoriaSlug !== 'todas') {
        $categoriaId = obtenerCategoriaIdPorSlug($categoriaSlug);
        $sql .= ' AND categoria_id = :categoriaId';
        $params['categoriaId'] = $categoriaId;
    }

    switch ($orden) {
        case 'citados':
            $sql .= ' ORDER BY citas DESC, fecha_publicacion DESC';
            break;
        case 'titulo':
            $sql .= ' ORDER BY titulo ASC';
            break;
        case 'recientes':
        default:
            $sql .= ' ORDER BY fecha_publicacion DESC';
            break;
    }

    $sentencia = $conexion->prepare($sql);
    $sentencia->execute($params);

    $resultado = [];
    foreach ($sentencia->fetchAll() as $fila) {
        $resultado[] = [
            'id'          => (int) $fila['id'],
            'titulo'      => $fila['titulo'],
            'desc'        => $fila['descripcion'],
            'categoria'   => $categoriasPorId[$fila['categoria_id']] ?? 'general',
            'citas'       => (int) $fila['citas'],
            'archivo_pdf' => $fila['archivo_pdf'],
            'autores'     => obtenerAutoresDe((int) $fila['id']),
        ];
    }

    return $resultado;
}

/**
 * Corta un arreglo de resultados en la página que corresponde y calcula
 * cuántas páginas hay en total.
 *
 * @return array{items: array, totalPaginas: int, paginaActual: int}
 */
function paginar(array $items, int $porPagina, int $paginaSolicitada): array
{
    $totalItems   = count($items);
    $totalPaginas = max(1, (int) ceil($totalItems / $porPagina));

    $paginaActual = $paginaSolicitada;
    if ($paginaActual < 1) {
        $paginaActual = 1;
    } elseif ($paginaActual > $totalPaginas) {
        $paginaActual = $totalPaginas;
    }

    $inicio = ($paginaActual - 1) * $porPagina;

    return [
        'items'        => array_slice($items, $inicio, $porPagina),
        'totalPaginas' => $totalPaginas,
        'paginaActual' => $paginaActual,
    ];
}

/**
 * Arma la URL de investigaciones.php conservando los parámetros actuales
 * (búsqueda/categoría/orden) y sobrescribiendo solo los que se indiquen.
 */
function construirUrlInvestigaciones(array $overrides, string $busqueda, string $categoria, string $orden = 'recientes'): string
{
    $params = array_merge([
        'q'     => $busqueda,
        'cat'   => $categoria,
        'orden' => $orden,
    ], $overrides);

    $params = array_filter($params, fn($valor) => $valor !== '' && $valor !== 'todas' && $valor !== 'recientes');

    return 'investigaciones.php' . (empty($params) ? '' : '?' . http_build_query($params));
}

/* ============================================================
 * A partir de aquí: funciones para el detalle de una
 * investigación, la publicación (rol Estudiante), el feedback
 * (rol Docente) y la administración (rol Administrador).
 * ============================================================ */

/**
 * Trae UNA investigación completa por su id, con categoría, PDF y
 * autores ya resueltos. Se usa en detalle.php.
 */
function obtenerInvestigacionPorId(int $id): ?array
{
    $conexion = obtenerConexion();
    $categoriasPorId = obtenerCategorias();

    $sentencia = $conexion->prepare(
        'SELECT id, titulo, descripcion, categoria_id, usuario_id, citas, archivo_pdf,
                fecha_publicacion AS publicado_el
         FROM investigaciones
         WHERE id = :id'
    );
    $sentencia->execute(['id' => $id]);
    $fila = $sentencia->fetch();

    if (!$fila) {
        return null;
    }

    return [
        'id'          => (int) $fila['id'],
        'titulo'      => $fila['titulo'],
        'desc'        => $fila['descripcion'],
        'categoria'   => $categoriasPorId[$fila['categoria_id']] ?? 'general',
        'citas'       => (int) $fila['citas'],
        'archivo_pdf' => $fila['archivo_pdf'],
        'usuario_id'  => (int) $fila['usuario_id'],
        'publicado_el'=> $fila['publicado_el'],
        'autores'     => obtenerAutoresDe($id),
    ];
}

/**
 * Trae la retroalimentación (feedback) de una investigación,
 * junto con el nombre del docente que la escribió.
 */
function obtenerFeedbackDe(int $investigacionId): array
{
    $conexion = obtenerConexion();

    // FROM con dos tablas + WHERE (mismo patrón que el login: sin JOIN)
    $sentencia = $conexion->prepare(
        'SELECT f.comentario AS comentario, f.fecha AS fecha, u.nombre AS docente,
                f.docente_id AS docente_id, u.foto_perfil AS docente_foto
         FROM retroalimentaciones AS f, usuarios AS u
         WHERE f.investigacion_id = :id AND f.docente_id = u.id
         ORDER BY f.fecha DESC'
    );
    $sentencia->execute(['id' => $investigacionId]);

    return $sentencia->fetchAll();
}

/**
 * Inserta un comentario de feedback de un docente sobre una investigación.
 */
function registrarFeedback(int $investigacionId, int $docenteId, string $comentario): bool
{
    $conexion = obtenerConexion();

    $sentencia = $conexion->prepare(
        'INSERT INTO retroalimentaciones (investigacion_id, docente_id, comentario)
         VALUES (:investigacionId, :docenteId, :comentario)'
    );

    return $sentencia->execute([
        'investigacionId' => $investigacionId,
        'docenteId'       => $docenteId,
        'comentario'      => $comentario,
    ]);
}

/**
 * Valida el formulario que usa el estudiante para publicar una
 * investigación nueva.
 */
function validarFormularioPublicacion(array $datos): array
{
    $errores = [];

    $titulo    = trim($datos['titulo'] ?? '');
    $resumen   = trim($datos['resumen'] ?? '');
    $categoria = $datos['categoria'] ?? '';
    $autores   = trim($datos['autores'] ?? '');

    if ($titulo === '') {
        $errores[] = 'El título es obligatorio.';
    } elseif (strlen($titulo) < 8) {
        $errores[] = 'El título debe tener al menos 8 caracteres.';
    }

    if ($resumen === '') {
        $errores[] = 'El resumen es obligatorio.';
    } elseif (strlen($resumen) < 20) {
        $errores[] = 'El resumen debe tener al menos 20 caracteres.';
    }

    if ($categoria === '' || obtenerCategoriaIdPorSlug($categoria) === null) {
        $errores[] = 'Selecciona una categoría válida.';
    }

    if ($autores === '') {
        $errores[] = 'Escribe al menos un autor.';
    }

    return $errores;
}

/**
 * Inserta una nueva investigación (publicada por un estudiante) y sus
 * autores. $datos['autores'] viene como texto separado por comas.
 *
 * @return int El id de la investigación recién creada
 */
/**
 * Inserta una nueva investigación (publicada por un estudiante) y sus
 * autores. $datos['autores'] viene como texto separado por comas.
 *
 * @return int El id de la investigación recién creada
 */
function publicarInvestigacion(array $datos, int $usuarioId, ?string $archivoPdf = null): int
{
    $conexion = obtenerConexion();
    $categoriaId = obtenerCategoriaIdPorSlug($datos['categoria']);

    $sentencia = $conexion->prepare(
        'INSERT INTO investigaciones (titulo, descripcion, categoria_id, usuario_id, archivo_pdf)
         VALUES (:titulo, :descripcion, :categoriaId, :usuarioId, :archivoPdf)'
    );
    $sentencia->execute([
        'titulo'      => $datos['titulo'],
        'descripcion' => $datos['resumen'],
        'categoriaId' => $categoriaId,
        'usuarioId'   => $usuarioId,
        'archivoPdf'  => $archivoPdf,
    ]);

    $investigacionId = (int) $conexion->lastInsertId();

    // Un autor por cada nombre separado por coma
    $listaAutores = explode(',', $datos['autores']);
    $insertarAutor = $conexion->prepare(
        'INSERT INTO autores (investigacion_id, nombre_autor) VALUES (:id, :nombre)'
    );

    for ($i = 0; $i < count($listaAutores); $i++) {
        $nombre = trim($listaAutores[$i]);
        if ($nombre !== '') {
            $insertarAutor->execute(['id' => $investigacionId, 'nombre' => $nombre]);
        }
    }

    return $investigacionId;
}

/**
 * Registra un nuevo usuario en la base de datos (por defecto rol Estudiante, id = 1).
 *
 * @return array{ok: bool, mensaje: string, id?: int}
 */
function registrarUsuario(string $nombre, string $correo, string $password, int $rolId = 1): array
{
    $conexion = obtenerConexion();

    $nombre  = trim($nombre);
    $correo  = trim($correo);

    if ($nombre === '' || strlen($nombre) < 3) {
        return ['ok' => false, 'mensaje' => 'El nombre debe tener al menos 3 caracteres.'];
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'mensaje' => 'Correo electrónico no válido.'];
    }

    if (strlen($password) < 6) {
        return ['ok' => false, 'mensaje' => 'La contraseña debe tener al menos 6 caracteres.'];
    }

    // Verificar si el correo ya existe
    $check = $conexion->prepare('SELECT id FROM usuarios WHERE correo = :correo');
    $check->execute(['correo' => $correo]);
    if ($check->fetch()) {
        return ['ok' => false, 'mensaje' => 'Ya existe una cuenta con este correo institucional.'];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $sentencia = $conexion->prepare(
        'INSERT INTO usuarios (nombre, correo, password, rol_id) VALUES (:nombre, :correo, :password, :rolId)'
    );
    $sentencia->execute([
        'nombre'   => $nombre,
        'correo'   => $correo,
        'password' => $hash,
        'rolId'    => $rolId
    ]);

    return [
        'ok'      => true,
        'id'      => (int) $conexion->lastInsertId(),
        'mensaje' => 'Registro completado con éxito.'
    ];
}

/**
 * Investigaciones publicadas por usuarios con rol Estudiante (para el
 * panel del docente). Combina 3 tablas con WHERE + AND, sin JOIN.
 */
function obtenerInvestigacionesDeEstudiantes(): array
{
    $conexion = obtenerConexion();
    $categoriasPorId = obtenerCategorias();

    $sentencia = $conexion->query(
        'SELECT i.id AS id, i.titulo AS titulo, i.descripcion AS descripcion,
                i.categoria_id AS categoria_id, i.archivo_pdf AS archivo_pdf,
                u.nombre AS autor_cuenta, u.id AS usuario_id
         FROM investigaciones AS i, usuarios AS u, roles AS r
         WHERE i.usuario_id = u.id AND u.rol_id = r.id AND r.nombre = "Estudiante"
         ORDER BY i.fecha_publicacion DESC'
    );

    $resultado = [];
    foreach ($sentencia->fetchAll() as $fila) {
        $resultado[] = [
            'id'          => (int) $fila['id'],
            'titulo'      => $fila['titulo'],
            'desc'        => $fila['descripcion'],
            'categoria'   => $categoriasPorId[$fila['categoria_id']] ?? 'general',
            'archivo_pdf' => $fila['archivo_pdf'],
            'autor_cuenta'=> $fila['autor_cuenta'],
            'usuario_id'  => (int) $fila['usuario_id'],
            'autores'     => obtenerAutoresDe((int) $fila['id']),
            'feedback'    => obtenerFeedbackDe((int) $fila['id']),
        ];
    }

    return $resultado;
}

/**
 * Lista de usuarios con el nombre de su rol, para el panel de administración.
 */
function obtenerUsuarios(): array
{
    $conexion = obtenerConexion();

    $sentencia = $conexion->query(
        'SELECT u.id AS id, u.nombre AS nombre, u.correo AS correo,
                u.rol_id AS rol_id, r.nombre AS rol, u.fecha_registro, u.foto_perfil, u.biografia
         FROM usuarios AS u, roles AS r
         WHERE u.rol_id = r.id
         ORDER BY u.nombre'
    );

    return $sentencia->fetchAll();
}

/**
 * Catálogo de roles (para el <select> del formulario de edición de usuario).
 */
function obtenerRoles(): array
{
    $conexion = obtenerConexion();
    return $conexion->query('SELECT id, nombre FROM roles ORDER BY id')->fetchAll();
}

/**
 * Un usuario puntual por id con información completa para perfil y formularios.
 */
function obtenerUsuarioPorId(int $id): ?array
{
    $conexion = obtenerConexion();

    $sentencia = $conexion->prepare(
        'SELECT u.id, u.nombre, u.correo, u.rol_id, r.nombre AS rol,
                u.fecha_registro, u.foto_perfil, u.biografia
         FROM usuarios AS u, roles AS r
         WHERE u.id = :id AND u.rol_id = r.id'
    );
    $sentencia->execute(['id' => $id]);
    $fila = $sentencia->fetch();

    return $fila ?: null;
}

/**
 * Devuelve la ruta web del avatar de un usuario o el avatar por defecto.
 */
function obtenerUrlAvatar(?string $fotoPerfil): string
{
    if (!empty($fotoPerfil) && file_exists(__DIR__ . '/../uploads/avatars/' . $fotoPerfil)) {
        return 'uploads/avatars/' . $fotoPerfil;
    }
    return 'assets/default-avatar.svg';
}

/**
 * Obtiene todas las investigaciones publicadas por un usuario específico (Estudiante o Docente).
 */
function obtenerInvestigacionesPorUsuario(int $usuarioId): array
{
    $conexion = obtenerConexion();
    $categoriasPorId = obtenerCategorias();

    $sentencia = $conexion->prepare(
        'SELECT id, titulo, descripcion, categoria_id, usuario_id, citas, archivo_pdf,
                fecha_publicacion AS publicado_el
         FROM investigaciones
         WHERE usuario_id = :usuarioId
         ORDER BY fecha_publicacion DESC'
    );
    $sentencia->execute(['usuarioId' => $usuarioId]);

    $resultado = [];
    foreach ($sentencia->fetchAll() as $fila) {
        $resultado[] = [
            'id'          => (int) $fila['id'],
            'titulo'      => $fila['titulo'],
            'desc'        => $fila['descripcion'],
            'categoria'   => $categoriasPorId[$fila['categoria_id']] ?? 'general',
            'citas'       => (int) $fila['citas'],
            'archivo_pdf' => $fila['archivo_pdf'],
            'publicado_el'=> $fila['publicado_el'],
            'autores'     => obtenerAutoresDe((int) $fila['id']),
            'feedback'    => obtenerFeedbackDe((int) $fila['id']),
        ];
    }

    return $resultado;
}

/**
 * Obtiene todas las investigaciones a las que un docente les brindó retroalimentación,
 * junto con el comentario específico realizado por este docente.
 */
function obtenerFeedbackPorDocente(int $docenteId): array
{
    $conexion = obtenerConexion();
    $categoriasPorId = obtenerCategorias();

    $sentencia = $conexion->prepare(
        'SELECT f.id AS feedback_id, f.comentario, f.fecha AS fecha_feedback,
                i.id AS investigacion_id, i.titulo AS investigacion_titulo,
                i.descripcion AS investigacion_desc, i.categoria_id, i.citas,
                i.archivo_pdf, i.fecha_publicacion,
                u.id AS estudiante_id, u.nombre AS estudiante_nombre
         FROM retroalimentaciones AS f, investigaciones AS i, usuarios AS u
         WHERE f.docente_id = :docenteId
           AND f.investigacion_id = i.id
           AND i.usuario_id = u.id
         ORDER BY f.fecha DESC'
    );
    $sentencia->execute(['docenteId' => $docenteId]);

    $resultado = [];
    foreach ($sentencia->fetchAll() as $fila) {
        $resultado[] = [
            'feedback_id'          => (int) $fila['feedback_id'],
            'comentario'           => $fila['comentario'],
            'fecha_feedback'       => $fila['fecha_feedback'],
            'investigacion_id'     => (int) $fila['investigacion_id'],
            'investigacion_titulo' => $fila['investigacion_titulo'],
            'investigacion_desc'   => $fila['investigacion_desc'],
            'categoria'            => $categoriasPorId[$fila['categoria_id']] ?? 'general',
            'citas'                => (int) $fila['citas'],
            'archivo_pdf'          => $fila['archivo_pdf'],
            'estudiante_id'        => (int) $fila['estudiante_id'],
            'estudiante_nombre'    => $fila['estudiante_nombre'],
            'autores'              => obtenerAutoresDe((int) $fila['investigacion_id']),
        ];
    }

    return $resultado;
}

/**
 * Actualiza el perfil de un usuario incluyendo opcionalmente la foto de perfil subida.
 *
 * @return array{ok: bool, mensaje: string}
 */
function actualizarPerfilUsuario(int $usuarioId, array $datos, ?array $archivoFoto = null): array
{
    $conexion = obtenerConexion();

    $nombre    = trim($datos['nombre'] ?? '');
    $correo    = trim($datos['correo'] ?? '');
    $biografia = trim($datos['biografia'] ?? '');

    if ($nombre === '' || strlen($nombre) < 3) {
        return ['ok' => false, 'mensaje' => 'El nombre debe tener al menos 3 caracteres.'];
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'mensaje' => 'Correo electrónico no válido.'];
    }

    // Verificar si el correo ya está registrado por otro usuario
    $check = $conexion->prepare('SELECT id FROM usuarios WHERE correo = :correo AND id != :id');
    $check->execute(['correo' => $correo, 'id' => $usuarioId]);
    if ($check->fetch()) {
        return ['ok' => false, 'mensaje' => 'Ya existe otra cuenta registrada con este correo.'];
    }

    $nombreFotoProcesada = null;

    if ($archivoFoto && $archivoFoto['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($archivoFoto['name'], PATHINFO_EXTENSION));
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

        if (!in_array($ext, $extensionesPermitidas)) {
            return ['ok' => false, 'mensaje' => 'La foto de perfil debe ser un archivo de imagen válido (JPG, PNG, WEBP, SVG).'];
        }

        $directorioAvatars = __DIR__ . '/../uploads/avatars/';
        if (!is_dir($directorioAvatars)) {
            mkdir($directorioAvatars, 0777, true);
        }

        $nombreFotoProcesada = 'avatar_' . $usuarioId . '_' . uniqid() . '.' . $ext;
        $rutaDestino = $directorioAvatars . $nombreFotoProcesada;

        if (!move_uploaded_file($archivoFoto['tmp_name'], $rutaDestino)) {
            return ['ok' => false, 'mensaje' => 'Error al guardar la foto de perfil en el servidor.'];
        }
    }

    if ($nombreFotoProcesada !== null) {
        $sentencia = $conexion->prepare(
            'UPDATE usuarios SET nombre = :nombre, correo = :correo, biografia = :biografia, foto_perfil = :foto WHERE id = :id'
        );
        $sentencia->execute([
            'nombre'    => $nombre,
            'correo'    => $correo,
            'biografia' => $biografia,
            'foto'      => $nombreFotoProcesada,
            'id'        => $usuarioId,
        ]);
    } else {
        $sentencia = $conexion->prepare(
            'UPDATE usuarios SET nombre = :nombre, correo = :correo, biografia = :biografia WHERE id = :id'
        );
        $sentencia->execute([
            'nombre'    => $nombre,
            'correo'    => $correo,
            'biografia' => $biografia,
            'id'        => $usuarioId,
        ]);
    }

    // Actualizar datos en sesión activa si el usuario editado es el actual
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (($_SESSION['usuario_id'] ?? 0) === $usuarioId) {
        $_SESSION['nombre']  = $nombre;
        $_SESSION['usuario'] = $nombre;
        $_SESSION['email']   = $correo;
        if ($nombreFotoProcesada !== null) {
            $_SESSION['foto_perfil'] = $nombreFotoProcesada;
        }
    }

    return ['ok' => true, 'mensaje' => 'Tu perfil ha sido actualizado con éxito.'];
}

/**
 * Actualiza nombre/correo/rol de un usuario (UPDATE).
 */
function actualizarUsuario(int $id, array $datos): bool
{
    $conexion = obtenerConexion();

    $sentencia = $conexion->prepare(
        'UPDATE usuarios
         SET nombre = :nombre, correo = :correo, rol_id = :rolId
         WHERE id = :id'
    );

    return $sentencia->execute([
        'nombre' => $datos['nombre'],
        'correo' => $datos['correo'],
        'rolId'  => $datos['rol_id'],
        'id'     => $id,
    ]);
}

/**
 * Elimina un usuario (DELETE). Si tiene investigaciones publicadas, la
 * base de datos rechaza el borrado por la llave foránea, así que
 * devolvemos false en vez de romper la página.
 */
function eliminarUsuario(int $id): bool
{
    $conexion = obtenerConexion();

    try {
        $sentencia = $conexion->prepare('DELETE FROM usuarios WHERE id = :id');
        return $sentencia->execute(['id' => $id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Actualiza título/resumen/categoría/citas/pdf de una investigación (UPDATE).
 */
function actualizarInvestigacion(int $id, array $datos, ?string $archivoPdf = null): bool
{
    $conexion = obtenerConexion();
    $categoriaId = obtenerCategoriaIdPorSlug($datos['categoria']);

    if ($archivoPdf !== null) {
        $sentencia = $conexion->prepare(
            'UPDATE investigaciones
             SET titulo = :titulo, descripcion = :descripcion,
                 categoria_id = :categoriaId, citas = :citas, archivo_pdf = :archivoPdf
             WHERE id = :id'
        );

        return $sentencia->execute([
            'titulo'      => $datos['titulo'],
            'descripcion' => $datos['resumen'],
            'categoriaId' => $categoriaId,
            'citas'       => (int) $datos['citas'],
            'archivoPdf'  => $archivoPdf,
            'id'          => $id,
        ]);
    } else {
        $sentencia = $conexion->prepare(
            'UPDATE investigaciones
             SET titulo = :titulo, descripcion = :descripcion,
                 categoria_id = :categoriaId, citas = :citas
             WHERE id = :id'
        );

        return $sentencia->execute([
            'titulo'      => $datos['titulo'],
            'descripcion' => $datos['resumen'],
            'categoriaId' => $categoriaId,
            'citas'       => (int) $datos['citas'],
            'id'          => $id,
        ]);
    }
}

/**
 * Elimina una investigación (DELETE). Sus autores y feedback se borran
 * en cascada porque las FK tienen ON DELETE CASCADE.
 */
function eliminarInvestigacion(int $id): bool
{
    $conexion = obtenerConexion();

    $sentencia = $conexion->prepare('DELETE FROM investigaciones WHERE id = :id');
    return $sentencia->execute(['id' => $id]);
}
