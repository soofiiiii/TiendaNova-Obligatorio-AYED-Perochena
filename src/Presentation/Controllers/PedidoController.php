<?php
namespace Presentation\Controllers;

use Application\PedidoService;
use Core\ViewRenderer;
use Infrastructure\Repositories\PdoCategoriaRepository;
use Infrastructure\Repositories\PdoPedidoRepository;
use Infrastructure\Repositories\PdoProveedorRepository;
use Infrastructure\Repositories\PdoProductoRepository;
use Domain\Interfaces\IStockRepository;


final class PedidoController
{
    private PedidoService $svc;
    private PdoProveedorRepository $provRepo;
    private PdoProductoRepository $prodRepo;

    public function __construct(IStockRepository $stockRepo)
    {
        $catRepo = new PdoCategoriaRepository();

        $pedidoRepo = new PdoPedidoRepository($stockRepo);
        $provRepo = new PdoProveedorRepository();
        $prodRepo = new PdoProductoRepository($stockRepo, $catRepo);

        $this->svc = new PedidoService(
            $pedidoRepo,
            $provRepo,
            $prodRepo,
            $stockRepo // ← ESTE ERA EL FALTANTE
        );

        $this->provRepo = $provRepo;
        $this->prodRepo = $prodRepo;
    }


    public function listar(): void
    {
        $estado = $_GET['estado'] ?? 'pendiente';

        $pedidos = $this->svc->listarPorEstado($estado);

        ViewRenderer::render('pedido/listar', [
            'active' => 'pedidos',
            'pedidos' => $pedidos,
            'estadoActual' => $estado,
        ]);
    }


    public function crearForm(): void
    {
        $proveedorId = isset($_GET['proveedor_id']) ? (int) $_GET['proveedor_id'] : null;
        $proveedores = $this->provRepo->findAll();
        $proveedorSeleccionado = null;
        $productos = [];

        if ($proveedorId > 0) {
            $proveedorSeleccionado = $this->provRepo->findById($proveedorId);
            if ($proveedorSeleccionado) {
                $productos = $this->prodRepo->findByProveedor($proveedorId);
            }
        }

        ViewRenderer::render('pedido/crear', [
            'active' => 'pedidos',
            'proveedores' => $proveedores,
            'proveedorSeleccionado' => $proveedorSeleccionado,
            'productos' => $productos,
        ]);
    }

    public function crear(): void
    {
        $proveedorId = (int) ($_POST['proveedor_id'] ?? 0);
        if ($proveedorId <= 0) {
            http_response_code(422);
            echo "Debe seleccionar un proveedor válido.";
            return;
        }

        $productoIds = $_POST['producto_id'] ?? [];
        $cantidades = $_POST['cantidad'] ?? [];
        $preciosUnit = $_POST['precio_unitario'] ?? [];

        $detalles = [];
        $n = count($productoIds);

        for ($i = 0; $i < $n; $i++) {
            $pid = (int) $productoIds[$i];
            $cant = (int) ($cantidades[$i] ?? 0);
            $pu = (float) ($preciosUnit[$i] ?? 0);

            if ($pid <= 0 || $cant <= 0) {
                continue;
            }

            $detalles[] = [
                'producto_id' => $pid,
                'cantidad' => $cant,
                'precio_unitario' => $pu,
            ];
        }

        if (!$detalles) {
            http_response_code(422);
            echo "Debe ingresar al menos un producto con cantidad mayor a cero.";
            return;
        }

        $this->svc->crear([
            'proveedor_id' => $proveedorId,
            'detalles' => $detalles,
        ]);

        header('Location: ' . BASE_PATH . '/pedidos');
        exit;
    }

    public function ver(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo "ID inválido";
            return;
        }

        $pedido = $this->svc->obtenerPorId($id);
        if (!$pedido) {
            http_response_code(404);
            echo "Pedido no encontrado";
            return;
        }

        ViewRenderer::render('pedido/ver', [
            'active' => 'pedidos',
            'pedido' => $pedido,
        ]);
    }

    public function recibir(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->svc->marcarRecibido($id);
        }
        header('Location: ' . BASE_PATH . '/pedidos');
        exit;
    }

    public function cancelar(): void
    {
        // 1) ID desde GET o POST
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_PATH . '/pedidos');
            exit;
        }

        // 2) Si es POST y viene confirmación → CANCELAR
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['confirm']) && $_POST['confirm'] === '1') {
                $this->svc->cancelar($id);
            }
            header('Location: ' . BASE_PATH . '/pedidos');
            exit;
        }

        // 3) Si es GET → mostrar pantalla de confirmación
        $pedido = $this->svc->obtenerPorId($id);
        if (!$pedido) {
            header('Location: ' . BASE_PATH . '/pedidos');
            exit;
        }

        // Regla de negocio: solo pendientes
        if ($pedido->getEstado() !== 'pendiente') {
            header('Location: ' . BASE_PATH . '/pedidos');
            exit;
        }

        ViewRenderer::render('pedido/cancelar', [
            'active' => 'pedidos',
            'pedido' => $pedido,
        ]);
    }

}
