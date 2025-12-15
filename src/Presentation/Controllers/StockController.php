<?php
namespace Presentation\Controllers;

use Application\StockService;
use Infrastructure\Database\ConnectionFactory;
use Infrastructure\Repositories\PdoCategoriaRepository;
use Infrastructure\Repositories\PdoProductoRepository;
use Infrastructure\Repositories\PdoStockRepository;

use Core\ViewRenderer;

final class StockController
{
    private StockService $svc;
    private PdoStockRepository $repo;

    public function __construct()
    {
        $this->repo = new PdoStockRepository();
        $this->svc = new StockService($this->repo);
    }

    public function listar(): void
    {
        $pdo = (new ConnectionFactory())::pdo();

        $stockRepo = new PdoStockRepository();
        $catRepo = new PdoCategoriaRepository();

        $prodRepo = new PdoProductoRepository(
            $stockRepo,
            $catRepo
        );

        $items = [];

        foreach ($pdo->query("SELECT * FROM stocks ORDER BY producto_id") as $r) {

            $stock = new \Domain\Entities\Stock(
                (int) $r['id'],
                (int) $r['producto_id'],
                (int) $r['cantidad'],
                $r['ubicacion'],
                new \DateTimeImmutable($r['fecha_ultima_actualizacion']),
                (int) $r['stock_minimo']
            );

            // Obtener producto
            $producto = $prodRepo->findById($stock->getProductoId());

            // Nombre del producto (SIN ID)
            $productoNombre = $producto
                ? $producto->getNombre()
                : 'Producto eliminado';

            // Precio unitario
            $precio = $producto ? $producto->getPrecio() : 0;

            // Valor total del stock
            $valorTotal = $stock->calcularValorTotal($precio);

            $items[] = [
                'id' => $stock->getId(),                 // id del stock
                'producto_id' => $stock->getProductoId(),// ← CLAVE
                'producto' => $productoNombre,
                'cantidad' => $stock->getCantidad(),
                'ubicacion' => $stock->getUbicacion(),
                'ultima_actualizacion' => $stock->getUltimaActualizacion()->format('Y-m-d H:i'),
                'stock_minimo' => $stock->getStockMinimo(),
                'valor_total' => number_format($valorTotal, 2)
            ];

        }

        ViewRenderer::render('stock/listar', [
            'active' => 'stock',
            'entidad' => 'stock',
            'ruta' => 'stock',
            'columnas' => [
                'ID',
                'Producto',
                'Cantidad',
                'Ubicación',
                'Última actualización',
                'Stock mínimo',
                'Valor total'
            ],
            'campos' => [
                'id',
                'producto',
                'cantidad',
                'ubicacion',
                'ultima_actualizacion',
                'stock_minimo',
                'valor_total'
            ],
            'items' => $items
        ]);
    }




    public function ajustarForm(): void
    {
        // Obtener productos desde el repo de productos
        $stockRepo = new PdoStockRepository();
        $catRepo = new PdoCategoriaRepository();

        $prodRepo = new PdoProductoRepository(
            $stockRepo,
            $catRepo
        );

        $productos = $prodRepo->findAll();

        ViewRenderer::render('stock/ajustar', [
            'active' => 'stock',
            'productos' => $productos
        ]);
    }


    public function ajustar(): void
    {
        $productoId = (int) ($_POST['producto_id'] ?? 0);
        $delta = (int) ($_POST['delta'] ?? 0);

        if ($productoId > 0 && $delta !== 0) {
            $this->svc->ajustar($productoId, $delta);
        }

        header('Location: ' . BASE_PATH . '/stock');
        exit;
    }
}