<?php
namespace Presentation\Controllers;

use Application\ProductoService;
use Application\CategoriaService;
use Infrastructure\Repositories\PdoCategoriaRepository;
use Infrastructure\Repositories\PdoProductoRepository;
use Infrastructure\Repositories\PdoProveedorRepository;
use Domain\Interfaces\IStockRepository;
use Core\ViewRenderer;
use Presentation\Helpers\Validator;

final class ProductoController
{
    private ProductoService $svc;
    private CategoriaService $catSvc;

    public function __construct(IStockRepository $stockRepo)
    {
        $catRepo = new PdoCategoriaRepository();
        $prodRepo = new PdoProductoRepository($stockRepo, $catRepo);

        $this->svc = new ProductoService($catRepo, $prodRepo, $stockRepo);
        $this->catSvc = new CategoriaService($catRepo);
    }

    public function listar(): void
    {
        // Detectar si viene un proveedor_id
        $proveedorId = isset($_GET['proveedor_id']) ? (int) $_GET['proveedor_id'] : null;

        if ($proveedorId) {
            // Listar solo productos de ese proveedor
            $productosObj = $this->svc->listarPorProveedor($proveedorId);
            $titulo = "Productos del proveedor #" . $proveedorId;
        } else {
            // Listado normal
            $productosObj = $this->svc->listarTodos();
            $titulo = "Listado de productos";
        }

        // Convertir a array para la vista
        $productos = array_map(function ($p) {

            $stock = $this->svc->obtenerStockPorProducto($p->getId());

            return [
                'id' => $p->getId(),
                'nombre' => $p->getNombre(),
                'categoria' => $p->getCategoria()
                    ? $p->getCategoria()->getRutaCompleta()
                    : '-',
                'precio' => $p->getPrecio(),
                'stock' => $stock,
                'descuento' => (int) ($p->getDescuento() ?? 0),
                'activo_manual' => (int) $p->isActivoManual(),
                'activo' => ($p->isActivoManual() && $stock > 0),
            ];
        }, $productosObj);


        ViewRenderer::render('producto/listar', [
            'active' => 'productos',
            'entidad' => 'producto',
            'ruta' => 'productos',
            'titulo' => $titulo,
            'columnas' => ['ID', 'Nombre', 'Categoría', 'Precio', 'Stock', 'Estado'],
            'campos' => ['id', 'nombre', 'categoria', 'precio', 'stock', 'activo'],
            'items' => $productos
        ]);
    }



    public function crearForm(): void
    {
        $cats = $this->catSvc->obtenerArbolCompleto();

        $proveedores = (new PdoProveedorRepository())->findAll();

        ViewRenderer::render('producto/crear', [
            'cats' => $cats,
            'proveedores' => $proveedores,
            'active' => 'productos',
        ]);
    }


    public function crear(): void
    {
        Validator::requireFields($_POST, ['nombre', 'precio', 'categoria_id', 'proveedor_id']);

        $dto = [
            'nombre' => trim($_POST['nombre']),
            'descripcion' => $_POST['descripcion'] ?? null,
            'precio' => $_POST['precio'],
            'categoria_id' => (int) $_POST['categoria_id'],
            'proveedor_id' => (int) $_POST['proveedor_id'],
            'ubicacion' => $_POST['ubicacion'] ?? 'DEP-1',
            'stock_inicial' => $_POST['stock_inicial'] ?? 0,
            'stock_minimo' => $_POST['stock_minimo'] ?? 0,
        ];

        $this->svc->crear($dto);

        header('Location: ' . BASE_PATH . '/productos');
        exit;
    }

    public function editarForm(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo "ID inválido";
            return;
        }

        $prod = $this->svc->obtenerPorId($id);
        if (!$prod) {
            http_response_code(404);
            echo "Producto no encontrado";
            return;
        }

        $cats = $this->catSvc->obtenerArbolCompleto();
        $proveedores = (new PdoProveedorRepository())->findAll();

        ViewRenderer::render('producto/editar', [
            'prod' => $prod,
            'cats' => $cats,
            'proveedores' => $proveedores,
            'active' => 'productos',
        ]);
    }


    public function editar(): void
    {
        Validator::requireFields($_POST, ['id', 'nombre', 'precio', 'categoria_id', 'proveedor_id']);

        $id = (int) $_POST['id'];

        $dto = [
            'nombre' => trim($_POST['nombre']),
            'descripcion' => $_POST['descripcion'] ?? null,
            'precio' => $_POST['precio'],
            'categoria_id' => (int) $_POST['categoria_id'],
            'proveedor_id' => (int) $_POST['proveedor_id'],
            'ubicacion' => $_POST['ubicacion'] ?? 'DEP-1', 
            'descuento' => $_POST['descuento'] ?? null,
            'aplicar_descuento' => isset($_POST['aplicar_descuento'])
        ];


        $this->svc->actualizar($id, $dto);

        header('Location: ' . BASE_PATH . '/productos');
        exit;
    }

    public function eliminar(): void
    {
        // 1) ID desde GET o POST
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_PATH . '/productos');
            exit;
        }

        // 2) Si es POST y viene el botón de confirmación → eliminar
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['confirm']) && $_POST['confirm'] === '1') {
                $this->svc->eliminar($id);
            }
            header('Location: ' . BASE_PATH . '/productos');
            exit;
        }

        // 3) Si es GET → mostrar pantalla de confirmación
        $producto = $this->svc->obtenerPorId($id);
        if (!$producto) {
            header('Location: ' . BASE_PATH . '/productos');
            exit;
        }

        ViewRenderer::render('producto/eliminar', [
            'active' => 'productos',
            'producto' => $producto,
        ]);
    }


    // Opcional: búsqueda por rama (usando mismo listado)
    public function buscarPorCategoria(): void
    {
        $catId = (int) ($_GET['cat'] ?? 0);
        $productos = $catId > 0 ? $this->svc->buscarPorRama($catId) : [];

        $items = [];
        foreach ($productos as $p) {
            $items[] = [
                'id' => $p->getId(),
                'nombre' => $p->getNombre(),
                'precio' => $p->getPrecio(),
                'categoria' => $p->getCategoria()->getRutaCompleta(),
                'proveedor' => $p->getProveedor()->getNombreEmpresa(),
                'activo' => $p->isActivo(),
            ];
        }

        $columnas = ['ID', 'Nombre', 'Categoría', 'Precio', 'Stock', 'Estado'];
        $campos = ['id', 'nombre', 'categoria', 'precio', 'stock', 'activo'];

        ViewRenderer::render('producto/listar', [
            'entidad' => 'productos',
            'ruta' => 'productos',
            'columnas' => $columnas,
            'campos' => $campos,
            'items' => $items,
        ]);
    }

    public function sinStock(): void
    {
        $productos = $this->svc->productosSinStock();

        $items = [];
        foreach ($productos as $p) {
            $items[] = [
                'id' => $p->getId(),
                'nombre' => $p->getNombre(),
                'precio' => $p->getPrecio(),
                'categoria' => $p->getCategoria()->getNombre(),
                'proveedor' => $p->getProveedor()->getNombreEmpresa(),
                'activo' => $p->isActivo(),
            ];
        }

        $columnas = ['ID', 'Nombre', 'Categoría', 'Precio', 'Stock', 'Estado'];
        $campos = ['id', 'nombre', 'categoria', 'precio', 'stock', 'activo'];

        ViewRenderer::render('producto/listar', [
            'entidad' => 'productos sin stock',
            'ruta' => 'productos',
            'columnas' => $columnas,
            'campos' => $campos,
            'items' => $items,
            'active' => 'productos',
        ]);
    }

    public function toggleActivo(): void
    {
        header('Content-Type: application/json');

        try {
            $id = (int) ($_POST['id'] ?? 0);
            $activoManual = (int) ($_POST['activo_manual'] ?? -1);

            if ($id <= 0 || !in_array($activoManual, [0, 1], true)) {
                throw new \RuntimeException('Datos inválidos');
            }

            $estado = $this->svc->actualizarActivoManual($id, $activoManual);

            echo json_encode([
                'ok' => true,
                ...$estado
            ]);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'error' => $e->getMessage()
            ]);
        }
    }



}