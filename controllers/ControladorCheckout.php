<?php
/**
 * Controlador de Checkout
 * Responsabilidad: Gestionar el proceso de compra
 */

namespace Controllers;

use Models\Carrito;
use Models\Pedido;
use Patrones\EstrategiaMetodoPago;
use Patrones\EstrategiaTarjeta;
use Patrones\EstrategiaYappy;
use Utils\GestorAutenticacion;
use Exception;

class ControladorCheckout 
{
    private GestorAutenticacion $gestorAuth;
    private Carrito $modeloCarrito;
    private Pedido $modeloPedido;

    public function __construct() 
    {
        $this->gestorAuth = GestorAutenticacion::obtenerInstancia();
        $this->modeloCarrito = new Carrito();
        $this->modeloPedido = new Pedido();
        
        // Verificar autenticación
        if (!$this->gestorAuth->estaAutenticado()) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Muestra la página de checkout
     */
    public function mostrarCheckout(): void 
    {
        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        $productos = $this->modeloCarrito->obtenerProductos($usuario['id_usuario']);
        $total = $this->modeloCarrito->calcularTotal($usuario['id_usuario']);

        if (empty($productos)) {
            header('Location: /carrito');
            exit;
        }

        $datos = [
            'titulo' => 'Finalizar Compra',
            'productos' => $productos,
            'total' => $total,
            'usuario' => $usuario
        ];

        $this->cargarVista('checkout/mostrar', $datos);
    }

    /**
     * Procesa el checkout
     */
    public function procesarCheckout(): void 
    {
        try {
            $usuario = $this->gestorAuth->obtenerUsuarioActual();
            
            // Validar datos
            $datosEnvio = [
                'direccion' => $_POST['direccion'] ?? '',
                'telefono' => $_POST['telefono'] ?? '',
                'notas' => $_POST['notas'] ?? ''
            ];

            $metodoPago = $_POST['metodo_pago'] ?? '';
            $datosPago = $_POST['datos_pago'] ?? [];

            if (empty($datosEnvio['direccion']) || empty($metodoPago)) {
                $this->responderJSON([
                    'exitoso' => false,
                    'mensaje' => 'Faltan datos requeridos'
                ]);
                return;
            }

            // Obtener productos del carrito
            $productos = $this->modeloCarrito->obtenerProductos($usuario['id_usuario']);
            $total = $this->modeloCarrito->calcularTotal($usuario['id_usuario']);

            if (empty($productos)) {
                $this->responderJSON([
                    'exitoso' => false,
                    'mensaje' => 'El carrito está vacío'
                ]);
                return;
            }

            // Procesar pago
            $estrategiaPago = $this->obtenerEstrategiaPago($metodoPago);
            $resultadoPago = $estrategiaPago->procesarPago($total, $datosPago);

            if (!$resultadoPago['exitoso']) {
                $this->responderJSON($resultadoPago);
                return;
            }

            // Crear pedido
            $datosPedido = [
                'id_usuario' => $usuario['id_usuario'],
                'productos' => $productos,
                'total' => $total,
                'metodo_pago' => $metodoPago,
                'direccion_envio' => $datosEnvio['direccion'],
                'transaccion_id' => $resultadoPago['transaccion_id']
            ];

            $resultadoPedido = $this->modeloPedido->crear($datosPedido);

            if ($resultadoPedido['exitoso']) {
                // Limpiar carrito
                $this->modeloCarrito->limpiar($usuario['id_usuario']);
            }

            $this->responderJSON($resultadoPedido);

        } catch (Exception $e) {
            error_log("Error en checkout: " . $e->getMessage());
            $this->responderJSON([
                'exitoso' => false,
                'mensaje' => 'Error interno del servidor'
            ]);
        }
    }

    /**
     * Obtiene la estrategia de pago correspondiente
     * @param string $metodoPago
     * @return EstrategiaMetodoPago
     */
    private function obtenerEstrategiaPago(string $metodoPago): EstrategiaMetodoPago 
    {
        switch ($metodoPago) {
            case 'tarjeta_credito':
            case 'tarjeta_debito':
                return new EstrategiaTarjeta();
            case 'yappy':
                return new EstrategiaYappy();
            default:
                return new EstrategiaTarjeta(); // Default
        }
    }

    /**
     * Carga una vista
     * @param string $vista
     * @param array $datos
     */
    private function cargarVista(string $vista, array $datos = []): void 
    {
        extract($datos);
        include "views/{$vista}.php";
    }

    /**
     * Responde con JSON
     * @param array $datos
     */
    private function responderJSON(array $datos): void 
    {
        header('Content-Type: application/json');
        echo json_encode($datos);
        exit;
    }
}
