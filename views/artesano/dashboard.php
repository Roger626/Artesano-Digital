<?php 
// Variables para el layout
$titulo = $titulo ?? 'Dashboard de Artesano - Artesano Digital';
$descripcion = $descripcion ?? 'Panel de administración para artesanos';

// Iniciar captura de contenido
ob_start(); 
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Panel de Artesano</h1>
        <p>Bienvenido, <?= htmlspecialchars($usuario['nombre'] ?? 'Artesano') ?></p>
    </div>
    
    <div class="dashboard-estadisticas">
        <div class="card">
            <div class="card-header">
                <h3>Resumen</h3>
            </div>
            <div class="card-body">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value"><?= $estadisticas['productos_activos'] ?? 0 ?></div>
                        <div class="stat-label">Productos Activos</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $estadisticas['ventas_totales'] ?? 0 ?></div>
                        <div class="stat-label">Ventas</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">$<?= number_format($estadisticas['ingresos_totales'] ?? 0, 2) ?></div>
                        <div class="stat-label">Ingresos Totales</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $estadisticas['valoracion_promedio'] ?? '0.0' ?></div>
                        <div class="stat-label">Valoración</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="dashboard-main">
        <div class="card">
            <div class="card-header">
                <h3>Pedidos Recientes</h3>
                <a href="/artesanoDigital/artesano/pedidos" class="btn btn-sm">Ver todos</a>
            </div>
            <div class="card-body">
                <?php if (empty($pedidos_recientes ?? [])): ?>
                <p class="empty-state">No hay pedidos recientes</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos_recientes as $pedido): ?>
                            <tr>
                                <td>#<?= $pedido['id_pedido'] ?></td>
                                <td><?= htmlspecialchars($pedido['cliente_nombre']) ?></td>
                                <td><?= date('d/m/Y', strtotime($pedido['fecha_pedido'])) ?></td>
                                <td>$<?= number_format($pedido['total'], 2) ?></td>
                                <td>
                                    <span class="badge status-<?= $pedido['estado'] ?>"><?= $pedido['estado'] ?></span>
                                </td>
                                <td>
                                    <a href="/artesanoDigital/artesano/pedidos/<?= $pedido['id_pedido'] ?>" class="btn btn-sm">Ver</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="dashboard-actions">
        <div class="action-cards">
            <a href="/artesanoDigital/artesano/productos/nuevo" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="action-text">
                    <h4>Nuevo Producto</h4>
                    <p>Añade un nuevo producto a tu catálogo</p>
                </div>
            </a>
            <a href="/artesanoDigital/artesano/tienda" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-store"></i>
                </div>
                <div class="action-text">
                    <h4>Mi Tienda</h4>
                    <p>Gestiona la configuración de tu tienda</p>
                </div>
            </a>
            <a href="/artesanoDigital/artesano/ventas" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="action-text">
                    <h4>Análisis de Ventas</h4>
                    <p>Revisa tus estadísticas de ventas</p>
                </div>
            </a>
        </div>
    </div>
</div>

<?php 
// Capturar el contenido y incluir el layout
$contenido = ob_get_clean(); 
include __DIR__ . '/../layouts/base.php'; 
?>

<style>
.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: var(--espaciado-md);
}

.dashboard-header {
    margin-bottom: var(--espaciado-md);
}

.dashboard-header h1 {
    margin-bottom: var(--espaciado-xs);
    color: var(--color-texto-primario);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: var(--espaciado-md);
}

.stat-item {
    text-align: center;
    padding: var(--espaciado-sm);
}

.stat-value {
    font-size: 1.8rem;
    font-weight: bold;
    color: var(--color-acento);
    margin-bottom: var(--espaciado-xs);
}

.stat-label {
    color: var(--color-texto-secundario);
    font-size: 0.9rem;
}

.dashboard-main {
    margin: var(--espaciado-md) 0;
}

.empty-state {
    text-align: center;
    color: var(--color-texto-secundario);
    padding: var(--espaciado-md);
}

.action-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--espaciado-md);
    margin-top: var(--espaciado-md);
}

.action-card {
    display: flex;
    align-items: center;
    padding: var(--espaciado-md);
    border-radius: var(--border-radius);
    background-color: var(--color-fondo-secundario);
    box-shadow: var(--box-shadow-sm);
    transition: all 0.3s ease;
    text-decoration: none;
}

.action-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--box-shadow-md);
}

.action-icon {
    font-size: 2rem;
    color: var(--color-acento);
    margin-right: var(--espaciado-md);
    width: 50px;
    text-align: center;
}

.action-text h4 {
    color: var(--color-texto-primario);
    margin-bottom: var(--espaciado-xs);
}

.action-text p {
    color: var(--color-texto-secundario);
    font-size: 0.9rem;
    margin: 0;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: var(--border-radius);
    font-size: 0.8rem;
}

.status-nuevo {
    background-color: #e3f2fd;
    color: #0d47a1;
}

.status-confirmado {
    background-color: #e8f5e9;
    color: #2e7d32;
}

.status-enviado {
    background-color: #fff3e0;
    color: #e65100;
}

.status-entregado {
    background-color: #e8f5e9;
    color: #2e7d32;
}

.status-cancelado {
    background-color: #ffebee;
    color: #b71c1c;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .action-cards {
        grid-template-columns: 1fr;
    }
}
</style>
