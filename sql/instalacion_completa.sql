-- ============================================================
-- QULLQA — INSTALACIÓN COMPLETA BASE DE DATOS (MYSQL / PHPMYADMIN)
-- Copia y pega todo este contenido en la pestaña "SQL" de phpMyAdmin
-- ============================================================

CREATE DATABASE IF NOT EXISTS qullqa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE qullqa;

-- Limpiar tablas si existían previamente para reinicio limpio
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS retroalimentaciones;
DROP TABLE IF EXISTS mensajes_soporte;
DROP TABLE IF EXISTS autores;
DROP TABLE IF EXISTS investigaciones;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS roles;
SET FOREIGN_KEY_CHECKS = 1;

-- 1) roles
CREATE TABLE roles (
    id     INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(40) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO roles (nombre) VALUES
    ('Estudiante'),
    ('Docente'),
    ('Administrador');

-- 2) categorias
CREATE TABLE categorias (
    id     INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(60) NOT NULL,
    slug   VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO categorias (nombre, slug) VALUES
    ('Inteligencia Artificial', 'ia'),
    ('Big Data', 'bigdata'),
    ('Calidad de Software', 'calidad'),
    ('Ciberseguridad', 'ciberseguridad');

-- 3) usuarios
CREATE TABLE usuarios (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100) NOT NULL,
    correo          VARCHAR(120) NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL,
    rol_id          INT NOT NULL,
    fecha_registro  DATE NOT NULL DEFAULT (CURRENT_DATE),
    CONSTRAINT fk_usuarios_rol
        FOREIGN KEY (rol_id) REFERENCES roles(id)
) ENGINE=InnoDB;

-- Cuentas de prueba:
--   Estudiante:    estudiante@unam.edu.pe / clave123
--   Docente:       docente@unam.edu.pe    / clave456
--   Administrador: admin@unam.edu.pe      / admin123
INSERT INTO usuarios (nombre, correo, password, rol_id) VALUES
    ('Leo Enrique Pari Puma', 'estudiante@unam.edu.pe', '$2y$10$jpEEYwVjXtJyHHwrMBgDneOO7I/mllUd6.6R36C6fDIJDCnLOP0sK', 1),
    ('Juan Pari Barrera',     'docente@unam.edu.pe',    '$2y$10$GhgJhikvGRA.3zQ88KU94Obiu26PY4HNCFSk/TtJ7QmJjji745Ela', 2),
    ('Administrador',         'admin@unam.edu.pe',      '$2y$10$8XoYVjfLAuGzrKts4Vz65.PtTOOAp00Hwa4IwiKTq0.TdAOEVMH7C', 3);

-- 4) investigaciones
CREATE TABLE investigaciones (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    titulo             VARCHAR(150) NOT NULL,
    descripcion        TEXT NOT NULL,
    categoria_id       INT NOT NULL,
    usuario_id         INT NOT NULL,
    citas              INT NOT NULL DEFAULT 0,
    archivo_pdf        VARCHAR(255) NULL,
    fecha_publicacion  DATE NOT NULL DEFAULT (CURRENT_DATE),
    CONSTRAINT fk_investigaciones_categoria
        FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    CONSTRAINT fk_investigaciones_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

INSERT INTO investigaciones (titulo, descripcion, categoria_id, usuario_id, citas, archivo_pdf, fecha_publicacion) VALUES
    ('Clasificación Inteligente de Residuos usando CNN',
     'Aplicación de redes neuronales convolucionales para clasificación automática.',
     1, 1, 4, NULL, '2026-02-10'),

    ('Análisis de Datos Climáticos mediante Machine Learning',
     'Predicción de variables ambientales utilizando modelos supervisados.',
     1, 2, 2, NULL, '2026-02-18'),

    ('Data Warehouse para la Gestión Universitaria',
     'Implementación de almacenes de datos y dashboards institucionales.',
     2, 2, 1, NULL, '2026-03-02'),

    ('Detección Temprana de Enfermedades con Visión Artificial',
     'Procesamiento de imágenes médicas mediante inteligencia artificial.',
     1, 1, 7, NULL, '2026-03-15'),

    ('Comparación de modelos de Clustering para segmentar perfiles en estudiantes de la UNAM',
     'Comparación de K-Means, DBSCAN y clustering jerárquico para caracterizar perfiles estudiantiles.',
     1, 1, 0, 'Clustering_segmentar_perfiles_UNAM.pdf', '2026-04-01'),

    ('Evaluación de Calidad de Software con CMMI-DEV',
     'Aplicación del framework CMMI nivel 2 para evaluar procesos de planificación en proyectos académicos.',
     3, 1, 0, NULL, '2026-04-20'),

    ('Contract Testing para Arquitecturas de Microservicios',
     'Implementación de pruebas de contrato consumidor-proveedor para garantizar compatibilidad entre servicios.',
     3, 1, 0, 'ContractTesting.pdf', '2026-05-05'),

    ('Detección de Vulnerabilidades en Aplicaciones Web',
     'Análisis de seguridad y pruebas de penetración sobre sistemas web institucionales.',
     4, 2, 3, 'Deteccion_Vulnerabilidades_Aplicaciones_Web.pdf', '2026-05-22');

-- 5) autores
CREATE TABLE autores (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    investigacion_id  INT NOT NULL,
    nombre_autor      VARCHAR(100) NOT NULL,
    CONSTRAINT fk_autores_investigacion
        FOREIGN KEY (investigacion_id) REFERENCES investigaciones(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO autores (investigacion_id, nombre_autor) VALUES
    (1, 'M. Flores'),
    (1, 'J. Quispe'),
    (2, 'R. Mamani'),
    (3, 'C. Ticona'),
    (3, 'L. Apaza'),
    (4, 'P. Choque'),
    (5, 'Urabi'),
    (6, 'Urabi'),
    (6, 'Equipo SWGIGUNAM'),
    (7, 'Urabi'),
    (8, 'S. Vilca');

-- 6) mensajes_soporte
CREATE TABLE mensajes_soporte (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL,
    correo      VARCHAR(120) NOT NULL,
    asunto      VARCHAR(30) NOT NULL,
    mensaje     TEXT NOT NULL,
    fecha_envio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 7) retroalimentaciones
CREATE TABLE retroalimentaciones (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    investigacion_id  INT NOT NULL,
    docente_id        INT NOT NULL,
    comentario        TEXT NOT NULL,
    fecha             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_feedback_investigacion
        FOREIGN KEY (investigacion_id) REFERENCES investigaciones(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_feedback_docente
        FOREIGN KEY (docente_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;
