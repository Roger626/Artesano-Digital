<?php

use PHPUnit\Framework\TestCase;
use Models\Carrito;
use Config\Database;

class CarritoTest extends TestCase{
    private $carrito;
    private $pdoMock;
    private $stmtMock;

    protected function setUp(): void{
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);

        $this->pdoMock->method("prepare")->willReturn($this->stmtMock);

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
        $this->carrito = new Carrito();
    }

    protected function tearDown(): void {
        $this->pdoMock = null;
        $this->stmtMock = null;
        $this->carrito = null;
        
        $dbReflection = new ReflectionClass(Database::class);
        $instanciaProp = $dbReflection->getProperty('instancia');
        $instanciaProp->setAccessible(true);
        $instanciaProp->setValue(null, null);
    }

    // 0. obtenerOCrear: Usuario con carrito existente.
    public function testObtenerOCrearCarrito_Existe(){
        $id_usuario = 101;
        $idCarritoEsperado = 110;

        $this->stmtMock->expects($this->once())
             ->method("fetch")
             ->willReturn(["id_carrito" => $idCarritoEsperado]);

        $this->stmtMock->expects($this->once())
             ->method("execute")
             ->with(["id_usuario" => $id_usuario]);

        $idCarrito = $this->carrito->obtenerOCrearCarrito($id_usuario);
        $this->assertEquals($idCarritoEsperado, $idCarrito);
    }

    // 1. obtenerOCrear: Usuario sin carrito (Debe crear uno)
    public function testObtenerOCrearCarrito_Nuevo(){
        $idUsuario = 102;
        $nuevoIdCarrito = 200;

        // 1. Select carrito (returns false)
        $this->stmtMock->method('fetch')->willReturn(false);
        
        $this->pdoMock->method('lastInsertId')->willReturn((string)$nuevoIdCarrito);

        // Expect 2 executes: Select and Insert
        $this->stmtMock->expects($this->exactly(2))->method('execute');

        $id = $this->carrito->obtenerOCrearCarrito($idUsuario);
        $this->assertEquals($nuevoIdCarrito, $id);
    }

    // 2. obtenerOCrear: Error de BD al buscar.
    public function testObtenerOCrearCarrito_ErrorBD(){
        $this->stmtMock->method('execute')->will($this->throwException(new Exception("DB Error")));
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Error al gestionar carrito");
        
        $this->carrito->obtenerOCrearCarrito(999);
    }

    // 3. agregar: Producto nuevo, cantidad=1, stock OK.
    public function testAgregarProducto_Nuevo_StockOK(){
        $idUsuario = 1;
        $idProducto = 10;
        $cantidad = 1;
        $stock = 5;

        // Sequence of fetches:
        // 1. Producto::obtenerPorId -> returns product data
        // 2. Carrito::obtenerOCrearCarrito -> returns cart data (assuming exists)
        // 3. Check if product in cart -> returns false
        
        $this->stmtMock->method('fetch')->willReturnOnConsecutiveCalls(
            ['id_producto' => $idProducto, 'stock' => $stock, 'precio' => 100], // Producto
            ['id_carrito' => 50], // Carrito existe
            false // No está en carrito
        );

        $this->stmtMock->method('execute')->willReturn(true); // For Insert

        $resultado = $this->carrito->agregarProducto($idUsuario, $idProducto, $cantidad);
        
        $this->assertTrue($resultado['exitoso']);
        $this->assertEquals('Producto agregado al carrito', $resultado['mensaje']);
    }

    // 4. agregar: Producto ya en carrito, stock OK.
    public function testAgregarProducto_Existente_StockOK(){
        $idUsuario = 1;
        $idProducto = 11;
        $cantidad = 3;
        $stock = 10;
        $cantidadEnCarrito = 1;

        // Fetches:
        // 1. Producto
        // 2. Carrito
        // 3. ProductoEnCarrito
        
        $this->stmtMock->method('fetch')->willReturnOnConsecutiveCalls(
            ['id_producto' => $idProducto, 'stock' => $stock], 
            ['id_carrito' => 50], 
            ['cantidad' => $cantidadEnCarrito] 
        );

        $this->stmtMock->method('execute')->willReturn(true); // For Update

        $resultado = $this->carrito->agregarProducto($idUsuario, $idProducto, $cantidad);
        
        $this->assertTrue($resultado['exitoso']);
    }

    // 5. agregar: Producto Inexistente.
    public function testAgregarProducto_Inexistente(){
        // Fetch 1: Producto -> false
        $this->stmtMock->method('fetch')->willReturn(false);

        $resultado = $this->carrito->agregarProducto(1, 9999, 1);
        
        $this->assertFalse($resultado['exitoso']);
        $this->assertEquals('Producto no encontrado', $resultado['mensaje']);
    }

    // 6. agregar: Cantidad inicial > stock disponible.
    public function testAgregarProducto_StockInsuficiente_Inicial(){
        // Fetch 1: Producto -> stock 5
        $this->stmtMock->method('fetch')->willReturn(['stock' => 5]);

        $resultado = $this->carrito->agregarProducto(1, 12, 10);
        
        $this->assertFalse($resultado['exitoso']);
        $this->assertEquals('Stock insuficiente', $resultado['mensaje']);
    }

    // 7. agregar: Intentar sumar y superar stock total.
    public function testAgregarProducto_StockInsuficiente_Suma(){
        // Fetches:
        // 1. Producto (stock 10)
        // 2. Carrito
        // 3. ProductoEnCarrito (cantidad 9)
        
        $this->stmtMock->method('fetch')->willReturnOnConsecutiveCalls(
            ['stock' => 10],
            ['id_carrito' => 50],
            ['cantidad' => 9]
        );

        $resultado = $this->carrito->agregarProducto(1, 13, 2);
        
        $this->assertFalse($resultado['exitoso']);
        $this->assertEquals('Stock insuficiente para la cantidad solicitada', $resultado['mensaje']);
    }

    // 8. agregar: Sumar 1 unidad justo en el límite de stock.
    public function testAgregarProducto_LimiteStock(){
        // Fetches:
        // 1. Producto (stock 10)
        // 2. Carrito
        // 3. ProductoEnCarrito (cantidad 9)
        
        $this->stmtMock->method('fetch')->willReturnOnConsecutiveCalls(
            ['stock' => 10],
            ['id_carrito' => 50],
            ['cantidad' => 9]
        );
        
        $this->stmtMock->method('execute')->willReturn(true);

        $resultado = $this->carrito->agregarProducto(1, 14, 1);
        
        $this->assertTrue($resultado['exitoso']);
    }

    // 9. obtenerProd: Carrito lleno (2+ ítems).
    public function testObtenerProductos_Lleno(){
        // obtenerProductos calls:
        // 1. obtenerOCrearCarrito -> fetch
        // 2. SELECT ... -> fetchAll
        
        $productosSimulados = [
            ['nombre' => 'P1', 'precio' => 10],
            ['nombre' => 'P2', 'precio' => 20]
        ];

        $this->stmtMock->method('fetch')->willReturn(['id_carrito' => 50]);
        $this->stmtMock->method('fetchAll')->willReturn($productosSimulados);

        $productos = $this->carrito->obtenerProductos(101);
        
        $this->assertCount(2, $productos);
        $this->assertEquals('P1', $productos[0]['nombre']);
    }
}
