<?php
/**
 * Router principal del Sistema Artesano Digital
 */

// Cargar autoloader de Composer si existe, sino usar autoloader personalizado
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    // Autoloader personalizado para desarrollo
    spl_autoload_register(function ($clase) {
        // Manejar clases con namespace
        $archivo = __DIR__ . '/' . str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $clase) . '.php';
        if (file_exists($archivo)) {
            require_once $archivo;
            return;
        }
        
        // Manejar clases sin namespace (principalmente en models)
        $nombreClase = basename(str_replace('\\', '/', $clase));
        $archivoModelo = __DIR__ . '/models/' . $nombreClase . '.php';
        if (file_exists($archivoModelo)) {
            require_once $archivoModelo;
            return;
        }
    });
}

// Cargar variables de entorno
if (file_exists(__DIR__ . '/.env')) {
    $lineas = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas as $linea) {
        if (strpos($linea, '=') !== false && strpos($linea, '#') !== 0) {
            list($clave, $valor) = explode('=', $linea, 2);
            $_ENV[trim($clave)] = trim($valor, '"');
        }
    }
}

// Obtener la ruta solicitada
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = parse_url($requestUri, PHP_URL_PATH);
$uri = rtrim($uri, '/');

// Remover el prefijo del directorio si existe
$basePath = '/artesanoDigital';
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Configurar manejo de errores según el tipo de ruta
$esRutaAPI = ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($uri, ['/login', '/registro', '/auth/login', '/auth/registro']));

if ($esRutaAPI) {
    // Para rutas API, ocultar errores para evitar que rompan el JSON
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    
    // Configurar manejador de errores fatal para APIs
    register_shutdown_function(function() {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            // Limpiar cualquier salida previa
            if (ob_get_level()) {
                ob_clean();
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
            exit;
        }
    });
} else {
    // Para rutas normales, mostrar errores en desarrollo
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

if (empty($uri)) {
    $uri = '/';
}

// Router completo
switch ($uri) {
    case '/':
        // Página de inicio
        $titulo = 'Artesano Digital - Panamá Oeste';
        $descripcion = 'Plataforma de comercio electrónico para artesanos de Panamá Oeste';
        include 'views/inicio.php';
        break;
        
    case '/productos':
        // Catálogo de productos
        require_once 'controllers/ControladorProductos.php';
        $controlador = new ControladorProductos();
        $controlador->mostrarCatalogo();
        break;
        
    case '/productos/detalle':
        // Detalle de producto
        require_once 'controllers/ControladorProductos.php';
        $controlador = new ControladorProductos();
        $id = $_GET['id'] ?? null;
        if ($id) {
            $controlador->mostrarDetalle($id);
        } else {
            header('Location: /artesanoDigital/productos');
            exit;
        }
        break;
        
    case '/login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Procesar login
            require_once 'controllers/ControladorAuth.php';
            $controlador = new Controllers\ControladorAuth();
            $controlador->procesarLogin();
        } else {
            // Mostrar formulario de login usando el controlador para generar el token CSRF
            require_once 'controllers/ControladorAuth.php';
            $controlador = new Controllers\ControladorAuth();
            $controlador->mostrarLogin();
        }
        break;
        
    case '/registro':
        require_once 'controllers/ControladorAuth.php';
        $controlador = new Controllers\ControladorAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Procesar registro
            $controlador->procesarRegistro();
        } else {
            // Mostrar formulario de registro
            $controlador->mostrarRegistro();
        }
        break;
        
    case '/auth/login':
        // Alias para compatibilidad
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once 'controllers/ControladorAuth.php';
            $controlador = new Controllers\ControladorAuth();
            $controlador->procesarLogin();
        } else {
            header('Location: /artesanoDigital/login');
            exit;
        }
        break;
        
    case '/auth/registro':
        // Alias para compatibilidad
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once 'controllers/ControladorAuth.php';
            $controlador = new Controllers\ControladorAuth();
            $controlador->procesarRegistro();
        } else {
            header('Location: /artesanoDigital/registro');
            exit;
        }
        break;
        
    case '/logout':
        // Cerrar sesión
        require_once 'controllers/ControladorAuth.php';
        $controlador = new Controllers\ControladorAuth();
        $controlador->logout();
        break;
        
    case '/artesanos':
        include 'views/artesanos/listado.php';
        break;
        
    case '/nosotros':
        include 'views/paginas/nosotros.php';
        break;
        
    case '/como-funciona':
        include 'views/paginas/como-funciona.php';
        break;
        
    case '/contacto':
        include 'views/paginas/contacto.php';
        break;
        
    case '/ayuda':
        include 'views/paginas/ayuda.php';
        break;
        
    case '/terminos':
        include 'views/paginas/terminos.php';
        break;
        
    case '/privacidad':
        include 'views/paginas/privacidad.php';
        break;
        
    case '/devoluciones':
        include 'views/paginas/devoluciones.php';
        break;
        
    case '/api/notificaciones':
        require_once 'controllers/ControladorAPI.php';
        $controlador = new Controllers\ControladorAPI();
        $controlador->obtenerNotificaciones();
        break;
        
    case '/dashboard/artesano':
        // Verificar autenticación
        require_once 'controllers/ControladorArtesano.php';
        $controlador = new Controllers\ControladorArtesano();
        $controlador->mostrarDashboard();
        break;
        
    case '/dashboard/cliente':
        // Verificar autenticación
        require_once 'controllers/ControladorCliente.php';
        $controlador = new Controllers\ControladorCliente();
        $controlador->mostrarDashboard();
        break;
        
    case '/api/debug':
        // Endpoint de depuración para verificar JSON
        require_once 'controllers/ControladorAuth.php';
        $controlador = new Controllers\ControladorAuth();
        $controlador->debugAPI();
        break;
        
    default:
        // 404 - Página no encontrada
        http_response_code(404);
        include 'views/errors/404.php';
        break;
}
?>
