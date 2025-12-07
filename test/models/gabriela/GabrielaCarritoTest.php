<?php
use PHPUnit\Framework\TestCase;
use Config\Database;
use Models\Carrito;

class GabrielaCarritoTest extends TestCase {
    private $carrito;
    private $pdoMock;
    private $stmtMock;
    private $currentStmt;

    protected function setUp(): void{
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);

        // Usar un callback que devuelve la sentencia actualmente configurada
        $this->currentStmt = $this->stmtMock;
        $that = $this;
        $this->pdoMock->method("prepare")->willReturnCallback(function($sql) use ($that) {
            return $that->currentStmt;
        });

        // Comportamientos por defecto para el statement mock
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(['id_carrito' => 105]);
        $this->stmtMock->method('fetchAll')->willReturn([]);

        // Reiniciar la instancia Singleton de Database a null antes de cada prueba
        $dbReflection = new ReflectionClass(Database::class);
        $instanciaProp = $dbReflection->getProperty('instancia');
        $instanciaProp->setAccessible(true);
        $instanciaProp->setValue(null, null);

        $dbMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbMock->method("obtenerConexion")->willReturn($this->pdoMock);

        $instanciaProp->setValue(null, $dbMock);

    }

    protected function tearDown(): void {
        // Limpiar para la siguiente prueba
        $this->pdoMock = null;
        $this->stmtMock = null;
        $this->carrito = null;
        
        // Es crucial limpiar la instancia estática de Database para no contaminar otras pruebas
        $dbReflection = new ReflectionClass(Database::class);
        $instanciaProp = $dbReflection->getProperty('instancia');
        $instanciaProp->setAccessible(true);
        $instanciaProp->setValue(null, null);
    }
    
    // ----------------------------------------------------------------------
    // --- MÉTODOS DE PRUEBA (TC-011 a TC-020) ---
    // ----------------------------------------------------------------------

    /** * TC-011: Carrito vacío. (STUB) */
    public function testTC011_ObtenerProductos_CarritoVacio()
    {
        // STUB: Simular que el Statement no encuentra filas
        $this->stmtMock->method('fetchAll')->willReturn([]); 

        $this->carrito = new Carrito();
        $resultado = $this->carrito->obtenerProductos(103);
        
        $this->assertIsArray($resultado); 
        $this->assertEmpty($resultado, "Debe retornar un array vacío.");
    }

    /** * TC-012: Producto inactivo. (STUB) */
    public function testTC012_ObtenerProductos_ProductoInactivoExcluido()
    {
        // STUB: Simular la respuesta de la BD con el filtro ya aplicado por el SQL del modelo
        // En una prueba de Caja Negra, validamos que la consulta (interna) sea correcta, o que la lógica post-consulta funcione.
        
        // Simularemos que la consulta de Carrito solo devuelve los activos
        $datosEsperados = [
            ['id' => 'P1', 'nombre' => 'Producto Activo'],
        ];

        $stmtLocal = $this->createMock(PDOStatement::class);
        $stmtLocal->method('fetchAll')->willReturnCallback(function($mode = null) use ($datosEsperados) {
            return $datosEsperados;
        });
        $stmtLocal->method('execute')->willReturn(true);

        // Aplicar el statement local para esta prueba
        $this->currentStmt = $stmtLocal;

        $this->carrito = new Carrito();
        $resultado = $this->carrito->obtenerProductos(104);
        
        $this->assertCount(1, $resultado, "Solo debe retornar productos activos (1 ítem)");
    }

    /** * TC-013: Incrementar de 1 a 5 (con stock OK). (MOCK) */
    public function testTC013_ActualizarCantidad_IncrementoExitoso()
    {
        // STUB (simulando stock > 5 y que la actualización fue exitosa)
        // Se asume que el método 'actualizarCantidad' retorna true si rowCount es > 0
        $stmtLocal = $this->createMock(PDOStatement::class);
        // Secuencia: obtener carrito, verificar stock (producto), (posible) fetch vacía
        $stmtLocal->method('fetch')->willReturnOnConsecutiveCalls(['id_carrito' => 105], ['stock' => 100], []);
        $stmtLocal->method('execute')->willReturn(true);
        $stmtLocal->method('rowCount')->willReturn(1);
        $this->pdoMock->method('prepare')->willReturn($stmtLocal);

        $this->carrito = new Carrito();
        // Inyectar un mock de Producto que devuelva stock suficiente
        $productoMock = $this->getMockBuilder(\Producto::class)->disableOriginalConstructor()->getMock();
        $productoMock->method('verificarStock')->willReturn(true);

        $resultado = $this->carrito->actualizarCantidad(1, 17, 5, $productoMock);
        
        $this->assertTrue($resultado['exitoso'], "Debe retornar exitoso=true.");
    }

    /** * TC-014: Cantidad = 1 (mantener). (MOCK) */
    public function testTC014_ActualizarCantidad_MantieneUno()
    {
        // STUB (simulando que la actualización fue exitosa)
        $stmtLocal = $this->createMock(PDOStatement::class);
        $stmtLocal->method('fetch')->willReturnOnConsecutiveCalls(['id_carrito' => 105], ['stock' => 100], []);
        $stmtLocal->method('execute')->willReturn(true);
        $stmtLocal->method('rowCount')->willReturn(1);
        $this->pdoMock->method('prepare')->willReturn($stmtLocal);

        $this->carrito = new Carrito();
        $productoMock = $this->getMockBuilder(\Producto::class)->disableOriginalConstructor()->getMock();
        $productoMock->method('verificarStock')->willReturn(true);

        $resultado = $this->carrito->actualizarCantidad(1, 18, 1, $productoMock);
        
        $this->assertTrue($resultado['exitoso'], "Debe retornar exitoso=true.");
    }

    /** * TC-015: Cantidad = 0 (Debe eliminar el producto). (MOCK & STUB) */
    public function testTC015_ActualizarCantidad_CeroLlamaEliminar()
    {
        // MOCK (Expectativa): Aquí la complejidad es que el SUT (Carrito) llamará internamente a eliminarProducto().

        // STUB: Simular que la operación de DELETE fue exitosa (1 fila afectada)
        $this->stmtMock->method('rowCount')->willReturn(1); 

        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('rowCount')->willReturn(1);

        $this->carrito = new Carrito();
        $resultado = $this->carrito->actualizarCantidad(1, 18, 0);

        $this->assertTrue($resultado['exitoso'], "Retorna exitoso=true si el producto se elimina.");
    }

    /** * TC-016: Cantidad > stock disponible. (STUB) */
    public function testTC016_ActualizarCantidad_StockInsuficiente()
    {
        // STUB: Simular que la consulta de stock devuelve 10 (cuando se pide 15)
        // Esto asume que el modelo hace una consulta SELECT antes de intentar el UPDATE
        $this->stmtMock->method('fetch')->willReturn(['stock' => 10]); 
        
        // Nota: Esta prueba depende de la implementación interna de tu Carrito.
        // Se simula que la consulta de verificación de stock devuelve 10
        $this->stmtMock->method('fetch')->willReturn(['stock' => 10]); 
        $this->stmtMock->method('execute')->willReturn(true);

        $this->carrito = new Carrito();
        $productoMock = $this->getMockBuilder(\Producto::class)->disableOriginalConstructor()->getMock();
        $productoMock->method('verificarStock')->willReturn(false);

        $resultado = $this->carrito->actualizarCantidad(1, 19, 15, $productoMock);
        
        $this->assertFalse($resultado['exitoso'] ?? false, "Debe retornar exitoso=false.");
    }

    /** * TC-017: Producto no existe en carrito. (STUB) */
    public function testTC017_ActualizarCantidad_ProductoNoEnCarrito()
    {
        // STUB: Simular que la consulta de UPDATE (o SELECT inicial) afecta 0 filas.
        $stmtLocal = $this->createMock(PDOStatement::class);
        $stmtLocal->method('fetch')->willReturnOnConsecutiveCalls(['id_carrito' => 105]);
        $stmtLocal->method('execute')->willReturn(true);
        $stmtLocal->method('rowCount')->willReturn(0);
        $this->pdoMock->method('prepare')->willReturn($stmtLocal);

        // El TC dice "Retorna exitoso=true y mensaje: 'Error al actualizar cantidad'"
        $this->carrito = new Carrito();
        $productoMock = $this->getMockBuilder(\Producto::class)->disableOriginalConstructor()->getMock();
        $productoMock->method('verificarStock')->willReturn(true);

        $resultado = $this->carrito->actualizarCantidad(1, 20, 5, $productoMock);
        
        $this->assertTrue($resultado['exitoso']); 
    }

    /** * TC-018: Eliminar producto existente. (MOCK) */
    public function testTC018_EliminarProducto_Existente()
    {
        // STUB: Simular que se eliminó 1 fila
        $this->stmtMock->method('rowCount')->willReturn(1); 

        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('rowCount')->willReturn(1);

        $this->carrito = new Carrito();
        $resultado = $this->carrito->eliminarProducto(1, 21);
        
        $this->assertTrue($resultado['exitoso'], "Debe retornar exitoso=true.");
    }

    /** * TC-019: Eliminar producto no existente. (STUB) */
    public function testTC019_EliminarProducto_NoExistente()
    {
        // STUB: Simular que se afectó 0 filas (producto no estaba)
        $this->stmtMock->method('rowCount')->willReturn(0); 
        
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('rowCount')->willReturn(0);

        $this->carrito = new Carrito();
        $resultado = $this->carrito->eliminarProducto(1, 99);
        
        $this->assertTrue($resultado['exitoso'], "La consulta DELETE se ejecuta sin error y retorna true.");
    }

    /** * TC-020: Vaciar Carrito. (MOCK) */
    public function testTC020_VaciarCarrito()
    {
        // STUB: Simular que se eliminaron 3 filas
        $this->stmtMock->method('rowCount')->willReturn(3); 

        // Simular execution y que no queden filas luego del DELETE
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('rowCount')->willReturn(3);
        $this->stmtMock->method('fetchColumn')->willReturn(0);

        $this->carrito = new Carrito();
        $resultado = $this->carrito->vaciarCarrito(105);

        $this->assertTrue($resultado, "Debe retornar true.");
    }
}