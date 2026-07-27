<?php
/**
 * includes/conexion.php
 *
 * Devuelve una única conexión PDO reutilizada en todo el proyecto
 * (patrón "singleton" simple con una variable static).
 *
 * Configuración por defecto pensada para XAMPP: MySQL en localhost,
 * usuario root sin contraseña. Si tu XAMPP tiene otra configuración,
 * ajusta las constantes de abajo.
 */

const DB_HOST = 'localhost';
const DB_NOMBRE = 'qullqa';
const DB_USUARIO = 'root';
const DB_PASSWORD = '';

function obtenerConexion(): PDO
{
    static $conexion = null;

    if ($conexion === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NOMBRE . ';charset=utf8mb4';

        try {
            $conexion = new PDO($dsn, DB_USUARIO, DB_PASSWORD, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $error) {
            die(
                '<div style="font-family:sans-serif; max-width:640px; margin:60px auto; ' .
                'padding:24px; border:1px solid #EAA; background:#FBEAEA; border-radius:10px;">' .
                '<strong>No se pudo conectar a la base de datos.</strong><br>' .
                'Verifica que MySQL esté corriendo en XAMPP y que hayas importado ' .
                '<code>sql/schema.sql</code> y <code>sql/datos.sql</code> en phpMyAdmin.<br><br>' .
                '<em>Detalle técnico: ' . htmlspecialchars($error->getMessage()) . '</em>' .
                '</div>'
            );
        }
    }

    return $conexion;
}
