<?php
// Variables para el layout
$titulo = $titulo ?? 'Carrito de Compras';

// Iniciar captura de contenido
ob_start();
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8"><?php echo $titulo; ?></h1>

    <div id="carrito-contenedor">
        <?php if (empty($productos)): ?>
            <div class="text-center py-12">
                <p class="text-xl text-gray-600 mb-4">Tu carrito está vacío</p>
                <a href="/productos" class="bg-orange-600 text-white px-6 py-2 rounded hover:bg-orange-700">
                    Explorar Productos
                </a>
            </div>
        <?php else: ?>
            <!-- Listado de productos -->
            <div class="grid grid-cols-1 gap-6">
                <?php foreach ($productos as $producto): ?>
                    <div class="flex items-center border p-4 rounded shadow-sm">
                        <div class="flex-1">
                            <h3 class="font-bold"><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                            <p>Precio: $<?php echo number_format($producto['precio'], 2); ?></p>
                            <p>Cantidad: <?php echo $producto['cantidad']; ?></p>
                        </div>
                        <div class="font-bold text-lg">
                            $<?php echo number_format($producto['precio'] * $producto['cantidad'], 2); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-8 flex justify-end items-center border-t pt-4">
                <div class="text-2xl font-bold mr-8">
                    Total: $<?php echo number_format($total, 2); ?>
                </div>
                <a href="/checkout" class="bg-green-600 text-white px-8 py-3 rounded text-lg hover:bg-green-700">
                    Proceder al Pago
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php 
    use Utils\GestorAutenticacion;
    $auth = GestorAutenticacion::obtenerInstancia();
    if (!$auth->estaAutenticado()): 
    ?>
        <div class="mt-8 p-4 bg-yellow-50 border border-yellow-200 rounded">
            <p class="text-yellow-800">
                <a href="/login" class="font-bold underline">Inicia Sesión para Comprar</a>
            </p>
        </div>
    <?php endif; ?>
</div>

<?php
// Capturar el contenido y incluir el layout
$contenido = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
