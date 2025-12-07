<?php
// Asegurarnos de que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title><?= $titulo ?? 'Sistema Artesano Digital' ?></title>
    
    <!-- Estilos CSS -->
    <link rel="stylesheet" href="/artesanoDigital/assets/css/estilos.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Forzar tema claro -->
    <style>
        html { color-scheme: light !important; }
        body { background-color: #faf8f5 !important; color: #2c2c2c !important; }
        
        /* Estilos para el menú desplegable */
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-toggle {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .dropdown-toggle::after {
            content: '';
            display: inline-block;
            margin-left: 0.5em;
            vertical-align: 0.2em;
            border-top: 0.3em solid;
            border-right: 0.3em solid transparent;
            border-bottom: 0;
            border-left: 0.3em solid transparent;
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            z-index: 1000;
            display: none;
            min-width: 10rem;
            padding: 0.5rem 0;
            margin: 0.125rem 0 0;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 0.25rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .dropdown-menu.activo {
            display: block;
        }
        
        .dropdown-item {
            display: block;
            width: 100%;
            padding: 0.5rem 1.25rem;
            clear: both;
            font-weight: 400;
            color: #212529;
            text-align: inherit;
            white-space: nowrap;
            background-color: transparent;
            border: 0;
            text-decoration: none;
        }
        
        .dropdown-item:hover, .dropdown-item:focus {
            color: #16181b;
            text-decoration: none;
            background-color: #f8f9fa;
        }
        
        .text-danger {
            color: #dc3545 !important;
        }
    </style>
    
    <!-- Meta tags adicionales -->
    <meta name="description" content="<?= $descripcion ?? 'Plataforma de comercio electrónico para artesanos de Panamá Oeste' ?>">
    <meta name="keywords" content="artesanías, Panamá, comercio electrónico, molas, cerámica, textiles">
    <meta name="author" content="Sistema Artesano Digital">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/artesanoDigital/public/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/artesanoDigital/public/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/artesanoDigital/public/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/artesanoDigital/public/favicon-16x16.png">
</head>
<body class="<?= $claseBody ?? '' ?>">
    <!-- Header -->
    <header class="header-principal">
        <nav class="navbar">
            <div class="contenedor">
                <div class="navbar-contenido">
                    <!-- Logo -->
                    <div class="navbar-marca">
                        <a href="/artesanoDigital/" class="logo">
                            <img src="/artesanoDigital/public/placeholder-logo.png" alt="Artesano Digital" class="logo-img">
                            <span class="logo-texto">Artesano Digital</span>
                        </a>
                    </div>

                    <!-- Navegación principal -->
                    <div class="navbar-nav">
                        <a href="/artesanoDigital/" class="nav-link">Inicio</a>
                        <a href="/artesanoDigital/productos" class="nav-link">Productos</a>
                        <a href="/artesanoDigital/artesanos" class="nav-link">Artesanos</a>
                        <a href="/artesanoDigital/nosotros" class="nav-link">Nosotros</a>
                    </div>

                    <!-- Acciones del usuario -->
                    <div class="navbar-acciones">
                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <!-- Usuario autenticado -->
                            <div class="dropdown">
                                <button class="btn btn-outline dropdown-toggle" data-dropdown="user-dropdown">
                                    <span class="usuario-nombre"><?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?></span>
                                </button>
                                <div id="user-dropdown" class="dropdown-menu">
                                    <?php if ($_SESSION['usuario_tipo'] === 'artesano'): ?>
                                        <a href="/artesanoDigital/dashboard/artesano" class="dropdown-item">Mi Panel</a>
                                    <?php else: ?>
                                        <a href="/artesanoDigital/dashboard/cliente" class="dropdown-item">Mi Panel</a>
                                    <?php endif; ?>
                                    <a href="/artesanoDigital/perfil" class="dropdown-item">Mi Perfil</a>
                                    <a href="/artesanoDigital/logout" class="dropdown-item text-danger">Cerrar Sesión</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Usuario no autenticado -->
                            <div class="auth-botones">
                                <a href="/artesanoDigital/login" class="btn btn-outline">Iniciar Sesión</a>
                                <a href="/artesanoDigital/registro" class="btn btn-primary">Registrarse</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Botón menú móvil -->
                    <button class="btn-menu-movil" id="btnMenuMovil">
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                    </button>
                </div>
            </div>
        </nav>
    </header>

    <!-- Contenido principal -->
    <main class="main-contenido">
        <?= $contenido ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="footer-principal">
        <div class="contenedor">
            <div class="footer-contenido">
                <div class="footer-seccion">
                    <h4>Artesano Digital</h4>
                    <p>Conectando artesanos de Panamá Oeste con el mundo digital.</p>
                    <div class="redes-sociales">
                        <a href="#" class="red-social">Facebook</a>
                        <a href="#" class="red-social">Instagram</a>
                        <a href="#" class="red-social">WhatsApp</a>
                    </div>
                </div>
                
                <div class="footer-seccion">
                    <h4>Enlaces Útiles</h4>
                    <ul class="footer-links">
                        <li><a href="/artesanoDigital/productos">Productos</a></li>
                        <li><a href="/artesanoDigital/artesanos">Artesanos</a></li>
                        <li><a href="/artesanoDigital/como-funciona">Cómo Funciona</a></li>
                        <li><a href="/artesanoDigital/contacto">Contacto</a></li>
                    </ul>
                </div>
                
                <div class="footer-seccion">
                    <h4>Soporte</h4>
                    <ul class="footer-links">
                        <li><a href="/artesanoDigital/ayuda">Centro de Ayuda</a></li>
                        <li><a href="/artesanoDigital/terminos">Términos de Uso</a></li>
                        <li><a href="/artesanoDigital/privacidad">Política de Privacidad</a></li>
                        <li><a href="/artesanoDigital/devoluciones">Devoluciones</a></li>
                    </ul>
                </div>
                
                <div class="footer-seccion">
                    <h4>Contacto</h4>
                    <div class="contacto-info">
                        <p>📧 info@artesanodigital.com</p>
                        <p>📞 +507 6000-0000</p>
                        <p>📍 Panamá Oeste, Panamá</p>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Sistema Artesano Digital. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript principal -->
    <script src="/artesanoDigital/assets/js/main.js"></script>
    <script src="/artesanoDigital/assets/js/notificaciones.js"></script>
    
    <!-- Scripts adicionales por página -->
    <?= $scriptsAdicionales ?? '' ?>

    <!-- Sistema de notificaciones toast -->
    <div id="toast-container" class="toast-container"></div>
</body>
</html>
