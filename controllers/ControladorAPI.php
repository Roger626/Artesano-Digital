<?php
/**
 * Controlador API
 * Responsabilidad: Gestionar endpoints API para AJAX
 */

namespace Controllers;

use Models\Usuario;
use Patrones\DecoradorNotificacion;
use Utils\GestorAutenticacion;
use Exception;

class ControladorAPI 
{
    private GestorAutenticacion $gestorAuth;

    public function __construct() 
    {
        $this->gestorAuth = GestorAutenticacion::obtenerInstancia();
        
        // Configurar headers para API
        header('Content-Type: application/json');
        
        // Endpoints públicos que no requieren autenticación
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $publicEndpoints = [
            '/artesanoDigital/api/notificaciones'
        ];
        
        // Solo verificar autenticación para endpoints protegidos
        if (!in_array($requestUri, $publicEndpoints) && !$this->gestorAuth->estaAutenticado()) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }
    }

    /**
     * Obtiene notificaciones del usuario actual
     */
    public function obtenerNotificaciones(): void 
    {
        try {
            $usuario = $this->gestorAuth->obtenerUsuarioActual();
            
            // Por ahora retornamos datos de prueba
            $notificaciones = [
                [
                    'id' => 1,
                    'tipo' => 'nuevo_pedido',
                    'mensaje' => 'Nuevo pedido recibido por $85.00',
                    'leida' => false,
                    'fecha' => '2024-12-01 10:30:00'
                ],
                [
                    'id' => 2,
                    'tipo' => 'stock_bajo',
                    'mensaje' => 'Stock bajo en Mola Tradicional (2 unidades)',
                    'leida' => false,
                    'fecha' => '2024-12-01 09:15:00'
                ]
            ];

            echo json_encode([
                'exitoso' => true,
                'notificaciones' => $notificaciones
            ]);

        } catch (Exception $e) {
            error_log("Error al obtener notificaciones: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'exitoso' => false,
                'mensaje' => 'Error interno del servidor'
            ]);
        }
    }

    /**
     * Marca una notificación como leída
     */
    public function marcarNotificacionLeida(): void 
    {
        try {
            $idNotificacion = (int)($_POST['id'] ?? 0);
            
            if ($idNotificacion <= 0) {
                http_response_code(400);
                echo json_encode([
                    'exitoso' => false,
                    'mensaje' => 'ID de notificación inválido'
                ]);
                return;
            }

            // Implementar marcado como leída
            // Por ahora simulamos éxito
            echo json_encode([
                'exitoso' => true,
                'mensaje' => 'Notificación marcada como leída'
            ]);

        } catch (Exception $e) {
            error_log("Error al marcar notificación como leída: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'exitoso' => false,
                'mensaje' => 'Error interno del servidor'
            ]);
        }
    }

    /**
     * Obtiene información del carrito (contador de productos)
     */
    public function obtenerInfoCarrito(): void 
    {
        try {
            $usuario = $this->gestorAuth->obtenerUsuarioActual();
            
            // Por ahora retornamos datos de prueba
            echo json_encode([
                'exitoso' => true,
                'cantidad_productos' => 3,
                'total' => 125.00
            ]);

        } catch (Exception $e) {
            error_log("Error al obtener info del carrito: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'exitoso' => false,
                'mensaje' => 'Error interno del servidor'
            ]);
        }
    }

    /**
     * Búsqueda de productos para autocompletado
     */
    public function buscarProductos(): void 
    {
        try {
            $termino = $_GET['q'] ?? '';
            
            if (strlen($termino) < 2) {
                echo json_encode([
                    'exitoso' => true,
                    'productos' => []
                ]);
                return;
            }

            // Por ahora retornamos datos de prueba
            $productos = [
                ['id' => 1, 'nombre' => 'Mola Tradicional', 'precio' => 85.00],
                ['id' => 2, 'nombre' => 'Vasija de Cerámica', 'precio' => 45.00]
            ];

            echo json_encode([
                'exitoso' => true,
                'productos' => $productos
            ]);

        } catch (Exception $e) {
            error_log("Error en búsqueda de productos: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'exitoso' => false,
                'mensaje' => 'Error en búsqueda'
            ]);
        }
    }
}
