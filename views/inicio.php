<?php 
// Variables para el layout
$titulo = $titulo ?? 'Artesano Digital - Panamá Oeste';
$descripcion = $descripcion ?? 'Plataforma de comercio electrónico para artesanos de Panamá Oeste';

// Iniciar captura de contenido
ob_start(); 
?>

<!-- Hero Section -->
<section class="hero">
    <div class="contenedor">
        <div class="hero-contenido">
            <h1 class="hero-titulo">Artesano Digital</h1>
            <h2 class="hero-subtitulo">Panamá Oeste</h2>
            <p class="hero-descripcion">
                Descubre las mejores artesanías locales creadas por talentosos artesanos de Panamá Oeste. 
                Productos únicos con historia y tradición.
            </p>
            <div class="hero-acciones">
                <a href="/artesanoDigital/productos" class="btn btn-primario">
                    Explorar Productos
                </a>
                <a href="/artesanoDigital/registro" class="btn btn-secundario">
                    Únete como Artesano
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Categorías Destacadas -->
<section class="categorias-destacadas seccion">
    <div class="contenedor">
        <h2 class="seccion-titulo">Categorías Populares</h2>
        <div class="categorias-grid">
            <div class="categoria-tarjeta">
                <img src="/artesanoDigital/public/placeholder.jpg" alt="Textiles" class="categoria-imagen">
                <h3>Textiles</h3>
                <p>Molas, huipiles y más</p>
            </div>
            <div class="categoria-tarjeta">
                <img src="/artesanoDigital/public/placeholder.jpg" alt="Cerámica" class="categoria-imagen">
                <h3>Cerámica</h3>
                <p>Vasijas y decoraciones</p>
            </div>
            <div class="categoria-tarjeta">
                <img src="/artesanoDigital/public/placeholder.jpg" alt="Joyería" class="categoria-imagen">
                <h3>Joyería</h3>
                <p>Accesorios únicos</p>
            </div>
            <div class="categoria-tarjeta">
                <img src="/artesanoDigital/public/placeholder.jpg" alt="Madera" class="categoria-imagen">
                <h3>Madera</h3>
                <p>Tallados y muebles</p>
            </div>
        </div>
    </div>
</section>

<!-- Productos Destacados -->
<section class="productos-destacados seccion">
    <div class="contenedor">
        <h2 class="seccion-titulo">Productos Destacados</h2>
        <div class="productos-grid">
            <!-- Productos dinámicos aquí -->
            <div class="producto-tarjeta">
                <img src="/artesanoDigital/public/placeholder.jpg" alt="Producto" class="producto-imagen">
                <div class="producto-info">
                    <h3>Mola Tradicional</h3>
                    <p class="producto-precio">$45.00</p>
                    <p class="producto-artesano">Por María González</p>
                </div>
            </div>
            <div class="producto-tarjeta">
                <img src="/artesanoDigital/public/placeholder.jpg" alt="Producto" class="producto-imagen">
                <div class="producto-info">
                    <h3>Vasija de Cerámica</h3>
                    <p class="producto-precio">$35.00</p>
                    <p class="producto-artesano">Por Carlos Pérez</p>
                </div>
            </div>
            <div class="producto-tarjeta">
                <img src="/artesanoDigital/public/placeholder.jpg" alt="Producto" class="producto-imagen">
                <div class="producto-info">
                    <h3>Collar de Semillas</h3>
                    <p class="producto-precio">$25.00</p>
                    <p class="producto-artesano">Por Ana López</p>
                </div>
            </div>
        </div>
        <div class="seccion-accion">
            <a href="/artesanoDigital/productos" class="btn btn-outline">Ver Todos los Productos</a>
        </div>
    </div>
</section>

<!-- Sobre Nosotros -->
<section class="sobre-nosotros seccion">
    <div class="contenedor">
        <div class="sobre-contenido">
            <div class="sobre-texto">
                <h2>Conectando Tradición con Tecnología</h2>
                <p>
                    Artesano Digital es más que una plataforma de ventas. Somos un puente entre 
                    la rica tradición artesanal de Panamá Oeste y el mundo digital moderno.
                </p>
                <p>
                    Cada producto cuenta una historia, cada artesano preserva técnicas ancestrales, 
                    y cada compra apoya el desarrollo económico local.
                </p>
                <ul class="beneficios-lista">
                    <li>✓ Productos auténticos y únicos</li>
                    <li>✓ Apoyo directo a artesanos locales</li>
                    <li>✓ Preservación de tradiciones culturales</li>
                    <li>✓ Comercio justo y transparente</li>
                </ul>
            </div>
            <div class="sobre-imagen">
                <img src="/artesanoDigital/public/placeholder.jpg" alt="Artesano trabajando" class="imagen-redonda">
            </div>
        </div>
    </div>
</section>

<?php 
// Capturar el contenido y incluir el layout
$contenido = ob_get_clean(); 
include __DIR__ . '/layouts/base.php'; 
?>
