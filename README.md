# Qullqa — Repositorio de Investigaciones UNAM

Proyecto con HTML + CSS + JS + PHP + MySQL/MariaDB, con **3 roles**
(Estudiante, Docente, Administrador), módulo de registro, carga y visor interactivo de PDFs, listo para XAMPP.

## Estructura

```
qullqa-web/
├── index.php                  Home: destacadas (desde la BD) + vista previa interactiva
├── acerca.php                  "Acerca de Qullqa"
├── investigaciones.php         Listado con filtrado SQL (WHERE/AND/OR), ordenamiento y badges de PDF
├── detalle.php                  Ficha completa con visor de PDF integrado, descargas y retroalimentación docente
├── publicar.php                 Publicar investigación con subida de archivo PDF real (solo Estudiante)
├── registro.php                 Crear nueva cuenta de Estudiante o Docente
├── panel_docente.php            Ver trabajos de estudiantes y dejar feedback (solo Docente)
├── panel_admin.php              CRUD completo de usuarios e investigaciones con soporte de PDF (solo Admin)
├── soporte.php                  Formulario de contacto -> INSERT en mensajes_soporte
├── login.php                    Login contra la tabla usuarios (password_hash + sesión + rol)
├── includes/
│   ├── conexion.php             Conexión PDO a MySQL (Singleton)
│   ├── funciones.php             Lógica backend (validar, filtrar, ordenar, registrar, publicar PDF, feedback, CRUD)
│   ├── header.php                Head + navbar dinámico con menú hamburguesa responsivo
│   └── footer.php                 Footer + cierre de HTML
├── data/
│   └── investigaciones.php       Consultas SELECT base (categorías, autores, investigaciones)
├── sql/
│   ├── schema.sql                 CREATE TABLE de las 7 tablas (con PK, FK y columna archivo_pdf)
│   └── datos.sql                   INSERT con usuarios actualizados y PDFs vinculados
├── uploads/                     Carpeta donde se almacenan los archivos PDF de las investigaciones
├── css/style.css                Hoja de estilos con variables de color UNAM, visor PDF y responsivo
├── js/main.js                   Interacciones (menú hamburguesa móvil, dropdowns, vista previa)
└── assets/                      Logos e insignias SVG de la UNAM y Qullqa
```

## Cuentas de prueba

| Rol | Nombre | Correo | Contraseña |
|---|---|---|---|
| Estudiante | **Leo Enrique Pari Puma** | `estudiante@unam.edu.pe` | `clave123` |
| Docente | **Juan Pari Barrera** | `docente@unam.edu.pe` | `clave456` |
| Administrador | **Administrador** | `admin@unam.edu.pe` | `admin123` |

## Características Implementadas

1. **Gestión Real de Archivos PDF:** Subida física con `move_uploaded_file()`, almacenamiento de ruta en MySQL (`archivo_pdf`) y visor interactivo `<iframe>` en [detalle.php](file:///C:/xampp/htdocs/qullqa-web/detalle.php).
2. **Módulo de Registro Público:** Nuevos estudiantes y docentes pueden registrarse desde [registro.php](file:///C:/xampp/htdocs/qullqa-web/registro.php).
3. **Ordenamiento de Investigaciones:** Ordenar por *Más recientes*, *Más citadas* o *Título (A-Z)* en [investigaciones.php](file:///C:/xampp/htdocs/qullqa-web/investigaciones.php).
4. **Diseño Responsivo Ajustado:** Menú hamburguesa para pantallas móviles y badges visuales para identificar investigaciones con PDF disponible.
