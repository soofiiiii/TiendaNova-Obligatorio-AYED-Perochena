<?php
namespace Infrastructure\Repositories;

use Domain\Entities\DetallePedido;
use Domain\Entities\Pedido;
use Domain\Interfaces\IPedidoRepository;
use Domain\Interfaces\IStockRepository;
use Infrastructure\Database\ConnectionFactory;
use PDO;

final class PdoPedidoRepository implements IPedidoRepository
{
    private PDO $pdo;
    private PdoProveedorRepository $proveedorRepo;
    private PdoProductoRepository $productoRepo;

    private IStockRepository $stockRepo;

    public function __construct(IStockRepository $stockRepo)
    {
        $this->pdo = ConnectionFactory::pdo();

        $catRepo = new PdoCategoriaRepository();

        $this->proveedorRepo = new PdoProveedorRepository();
        $this->productoRepo = new PdoProductoRepository(
            $stockRepo,
            $catRepo
        );

        $this->stockRepo = $stockRepo;
    }


    public function create(Pedido $pedido): int
    {
        $this->pdo->beginTransaction();
        try {
            // 1) Cabecera
            $st = $this->pdo->prepare("
                INSERT INTO pedidos (fecha_pedido, proveedor_id, estado, total)
                VALUES (?,?,?,?)
            ");

            $pedido->calcularTotal();

            $st->execute([
                $pedido->getFecha()->format('Y-m-d H:i:s'),
                $pedido->getProveedor()->getId(),
                $pedido->getEstado(),
                $pedido->getTotal()
            ]);

            $pedidoId = (int) $this->pdo->lastInsertId();

            // 2) Detalles
            $stDet = $this->pdo->prepare("
                INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario)
                VALUES (?,?,?,?)
            ");

            foreach ($pedido->getDetalles() as $d) {
                $stDet->execute([
                    $pedidoId,
                    $d->getProducto()->getId(),
                    $d->getCantidad(),
                    $d->getPrecioUnitario()
                ]);
            }

            $this->pdo->commit();
            return $pedidoId;

        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function marcarRecibido(int $pedidoId): void
    {
        $this->pdo->beginTransaction();

        try {
            // 1) Verificar estado actual
            $st = $this->pdo->prepare(
                "SELECT estado FROM pedidos WHERE id = ?"
            );
            $st->execute([$pedidoId]);
            $estado = $st->fetchColumn();

            if ($estado === false) {
                throw new \RuntimeException("Pedido no encontrado");
            }

            if ($estado !== 'pendiente') {
                // Ya está recibido o cancelado → no tocar stock
                $this->pdo->commit();
                return;
            }

            // 2) Traer detalles del pedido
            $det = $this->pdo->prepare(
                "SELECT producto_id, cantidad
             FROM detalle_pedidos
             WHERE pedido_id = ?"
            );
            $det->execute([$pedidoId]);
            $rows = $det->fetchAll(PDO::FETCH_ASSOC);


            // 3) Marcar pedido como recibido
            $this->pdo->prepare(
                "UPDATE pedidos SET estado = 'recibido' WHERE id = ?"
            )->execute([$pedidoId]);

            $this->pdo->commit();

        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }


    public function cancelar(int $pedidoId): void
    {
        // Opcional: validar que esté pendiente antes de cancelar
        $st = $this->pdo->prepare("SELECT estado FROM pedidos WHERE id=?");
        $st->execute([$pedidoId]);
        $estado = $st->fetchColumn();

        if ($estado === false) {
            throw new \RuntimeException("Pedido no encontrado");
        }
        if ($estado !== 'pendiente') {
            return; // no cancelar si ya está recibido/cancelado
        }

        $this->pdo
            ->prepare("UPDATE pedidos SET estado='cancelado' WHERE id=?")
            ->execute([$pedidoId]);
    }

    private function mapPedido(array $p, array $detallesRows): Pedido
    {
        $proveedor = $this->proveedorRepo->findById((int) $p['proveedor_id']);
        if (!$proveedor) {
            throw new \RuntimeException("Proveedor {$p['proveedor_id']} no encontrado");
        }

        $pedido = new Pedido(
            (int) $p['id'],
            new \DateTimeImmutable($p['fecha_pedido']),
            $proveedor,
            $p['estado']
        );

        $pedido->hidratarTotal((float) $p['total']);



        foreach ($detallesRows as $d) {
            $producto = $this->productoRepo->findById((int) $d['producto_id']);
            if (!$producto) {
                continue; // o throw si querés más estricto
            }
            $pedido->hidratarDetalle(
                new DetallePedido(
                    (int) $d['id'],
                    $producto,
                    (int) $d['cantidad'],
                    (float) $d['precio_unitario']
                )
            );
        }

        return $pedido;
    }

    public function findById(int $id): ?Pedido
    {
        $p = $this->pdo->prepare("SELECT * FROM pedidos WHERE id = ?");
        $p->execute([$id]);
        $row = $p->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $d = $this->pdo->prepare("SELECT * FROM detalle_pedidos WHERE pedido_id = ?");
        $d->execute([$id]);
        $det = $d->fetchAll(PDO::FETCH_ASSOC);

        return $this->mapPedido($row, $det);
    }

    public function findAll(): array
    {
        $out = [];
        $q = $this->pdo->query("SELECT * FROM pedidos ORDER BY id DESC");

        foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $d = $this->pdo->prepare("SELECT * FROM detalle_pedidos WHERE pedido_id = ?");
            $d->execute([(int) $row['id']]);
            $det = $d->fetchAll(PDO::FETCH_ASSOC);

            $out[] = $this->mapPedido($row, $det);
        }

        return $out;
    }
}
