<?php
// Importación de controladores utilizados por el router
use Presentation\Controllers\HomeController;
use Presentation\Controllers\CategoriaController;
use Presentation\Controllers\ProductoController;
use Presentation\Controllers\ProveedorController;
use Presentation\Controllers\StockController;
use Presentation\Controllers\PedidoController;
use Presentation\Controllers\BusquedaController;
use Presentation\Controllers\EstadisticasController;


/** 
 * @var \Core\Router $router
 *
 * El router es el encargado de:
 * - Asociar una URL + método HTTP
 * - Con un controlador y un método concreto
 */

// HOME / DASHBOARD

/**
 * Ruta principal del sistema.
 * Redirige al panel de estadísticas generales.
 */
$router->get('/', [EstadisticasController::class, 'index']);


// CATEGORÍAS

/**
 * CRUD completo de categorías.
 * Incluye operaciones específicas del árbol:
 * - Mover categorías
 * - Validaciones jerárquicas
 */
$router->get ('/categorias',          [CategoriaController::class, 'listar']);
$router->get ('/categorias/crear',    [CategoriaController::class, 'crearForm']);
$router->post('/categorias/crear',    [CategoriaController::class, 'crear']);
$router->get ('/categorias/editar',   [CategoriaController::class, 'editarForm']);
$router->post('/categorias/editar',   [CategoriaController::class, 'editar']);
$router->get ('/categorias/eliminar', [CategoriaController::class, 'eliminar']);
$router->post('/categorias/eliminar', [CategoriaController::class, 'eliminar']);
$router->get ('/categorias/mover',    [CategoriaController::class, 'moverForm']);
$router->post('/categorias/mover',    [CategoriaController::class, 'mover']);



// PRODUCTOS

/**
 * CRUD de productos.
 * Incluye operaciones adicionales:
 * - Activar / desactivar producto
 * - Filtros por categoría y stock
 */
$router->get ('/productos',           [ProductoController::class, 'listar']);
$router->get ('/productos/crear',     [ProductoController::class, 'crearForm']);
$router->post('/productos/crear',     [ProductoController::class, 'crear']);
$router->get ('/productos/editar',    [ProductoController::class, 'editarForm']);
$router->post('/productos/editar',    [ProductoController::class, 'editar']);
$router->get ('/productos/eliminar',  [ProductoController::class, 'eliminar']);
$router->post('/productos/eliminar',  [ProductoController::class, 'eliminar']);

/**
 * Toggle de estado manual del producto.
 * Se utiliza generalmente vía AJAX.
 */
$router->post('/productos/toggle-activo', [ProductoController::class, 'toggleActivo']);


// Filtros y búsquedas
/**
 * Búsqueda de productos por categoría (incluye subcategorías).
 */
$router->get('/productos/por-categoria', [ProductoController::class, 'buscarPorCategoria']);

/**
 * Listado de productos sin stock disponible.
 */
$router->get('/productos/sin-stock',     [ProductoController::class, 'sinStock']);


// PROVEEDORES

/**
 * CRUD de proveedores.
 * Permite gestionar empresas proveedoras y su información de contacto.
 */
$router->get ('/proveedores',          [ProveedorController::class, 'listar']);
$router->get ('/proveedor/crear',      [ProveedorController::class, 'crearForm']);
$router->post('/proveedor/crear',      [ProveedorController::class, 'crear']);
$router->get ('/proveedor/editar',     [ProveedorController::class, 'editarForm']);
$router->post('/proveedor/editar',     [ProveedorController::class, 'editar']);
$router->get ('/proveedor/eliminar',   [ProveedorController::class, 'eliminar']);
$router->post ('/proveedor/eliminar',   [ProveedorController::class, 'eliminar']);

// STOCK

/**
 * Gestión de inventario.
 * Permite:
 * - Ver stock general
 * - Ajustar cantidades manualmente
 */
$router->get ('/stock',            [StockController::class, 'listar']);
$router->get ('/stock/ajustar',    [StockController::class, 'ajustarForm']);
$router->post('/stock/ajustar',    [StockController::class, 'ajustar']);



// PEDIDOS

/**
 * Gestión de pedidos de reposición.
 * Incluye:
 * - Creación
 * - Visualización
 * - Recepción (impacta en stock)
 * - Cancelación
 */
$router->get ('/pedidos',           [PedidoController::class, 'listar']);
$router->get ('/pedidos/crear',     [PedidoController::class, 'crearForm']);
$router->post('/pedidos/crear',     [PedidoController::class, 'crear']);
$router->get ('/pedidos/eliminar', [PedidoController::class, 'eliminarForm']);
$router->get ('/pedidos/ver',       [PedidoController::class, 'ver']);
$router->post('/pedidos/recibir',   [PedidoController::class, 'recibir']);
$router->post('/pedidos/cancelar',  [PedidoController::class, 'cancelar']);

// Se permite acceso GET y POST para cancelación por flexibilidad de UI
$router->get ('/pedidos/cancelar', [PedidoController::class, 'cancelar']);
$router->post('/pedidos/cancelar', [PedidoController::class, 'cancelar']);

// BÚSQUEDA GLOBAL

/**
 * Búsqueda global del sistema.
 * Permite encontrar entidades desde un único punto.
 */
$router->get('/buscar', [BusquedaController::class, 'buscar']);

