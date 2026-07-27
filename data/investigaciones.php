<?php
/**
 * data/investigaciones.php
 *
 * Antes esta función devolvía un array fijo escrito a mano. Ahora consulta
 * la base de datos (tablas investigaciones, categorias y autores) por PDO,
 * pero sigue devolviendo el mismo "shape" de arreglo que usaban index.php
 * e investigaciones.php, así que esas páginas no tuvieron que cambiar.
 */

require_once __DIR__ . '/../includes/conexion.php';

/**
 * Trae el catálogo de categorías como un arreglo id => slug.
 * Se usa como "diccionario" para no tener que hacer un JOIN:
 * primero se consulta esta tabla pequeña una sola vez, y luego
 * se busca en el arreglo en memoria mientras se recorren las
 * investigaciones.
 */
function obtenerCategorias(): array
{
    $conexion = obtenerConexion();

    // SELECT con alias (AS): renombramos "slug" a "codigo" al leerlo
    $consulta = $conexion->query(
        'SELECT id, nombre, slug AS codigo FROM categorias'
    );

    $categoriasPorId = [];
    foreach ($consulta->fetchAll() as $fila) {
        $categoriasPorId[$fila['id']] = $fila['codigo'];
    }

    return $categoriasPorId;
}

/**
 * Trae los autores (nombre_autor) de una investigación puntual.
 */
function obtenerAutoresDe(int $investigacionId): array
{
    $conexion = obtenerConexion();

    $consulta = $conexion->prepare(
        'SELECT nombre_autor AS autor FROM autores WHERE investigacion_id = :id'
    );
    $consulta->execute(['id' => $investigacionId]);

    return array_column($consulta->fetchAll(), 'autor');
}

/**
 * Devuelve TODAS las investigaciones, con su categoría (slug) y su
 * lista de autores ya armada, en el mismo formato que usaban las
 * páginas cuando los datos venían de un array fijo.
 */
function obtenerInvestigaciones(): array
{
    $conexion = obtenerConexion();
    $categoriasPorId = obtenerCategorias();

    $consulta = $conexion->query(
        'SELECT id, titulo, descripcion, categoria_id, usuario_id, citas, archivo_pdf,
                fecha_publicacion AS publicado_el
         FROM investigaciones
         ORDER BY fecha_publicacion DESC'
    );

    $investigaciones = [];

    foreach ($consulta->fetchAll() as $fila) {
        $investigaciones[] = [
            'id'          => (int) $fila['id'],
            'titulo'      => $fila['titulo'],
            'desc'        => $fila['descripcion'],
            'categoria'   => $categoriasPorId[$fila['categoria_id']] ?? 'general',
            'citas'       => (int) $fila['citas'],
            'archivo_pdf' => $fila['archivo_pdf'],
            'autores'     => obtenerAutoresDe((int) $fila['id']),
        ];
    }

    return $investigaciones;
}

/**
 * Devuelve el nombre legible de una categoría (por su slug).
 * Sigue siendo un switch en PHP puro, sin tocar la base de datos:
 * los slugs son fijos y pocos, así que no vale la pena una consulta extra.
 */
function etiquetaCategoria(string $categoria): string
{
    switch ($categoria) {
        case 'ia':
            return 'Inteligencia Artificial';
        case 'bigdata':
            return 'Big Data';
        case 'calidad':
            return 'Calidad de Software';
        case 'ciberseguridad':
            return 'Ciberseguridad';
        default:
            return 'General';
    }
}

/**
 * Convierte el arreglo de autores en un texto legible:
 * "M. Flores y J. Quispe" o "Urabi".
 */
function listarAutores(array $autores): string
{
    if (empty($autores)) {
        return 'Sin autor registrado';
    }

    if (count($autores) === 1) {
        return $autores[0];
    }

    $ultimo = array_pop($autores);
    return implode(', ', $autores) . ' y ' . $ultimo;
}
