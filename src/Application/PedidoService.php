<?php
namespace Application;

use Domain\Entities\DetallePedido;
use Domain\Entities\Pedido;
use Domain\Interfaces\IPedidoRepository;
use Domain\Interfaces\IProveedorRepository;
use Domain\Interfaces\IProductoRepository;
use Domain\Interfaces\IStockRepository;

final class PedidoService
{
    public function __construct(
        private IPedidoRepository $pedidoRepo,
        private IProveedorRepository $proveedorRepo,
        private IProductoRepository $productoRepo,
        private IStockRepository $stockRepo
    ) {
    }

    /** @return Pedido[] */
    public function listarPorEstado(string $estado): array
    {
        $permitidos = ['pendiente', 'recibido', 'cancelado', 'todos'];

        if (!in_array($estado, $permitidos, true)) {
            $estado = 'pendiente';
        }

        $pedidos = $this->pedidoRepo->findAll();

        if ($estado === 'todos') {
            return $pedidos;
        }

        return array_values(array_filter(
            $pedidos,
            fn(Pedido $p) => $p->getEstado() === $estado
        ));
    }

    /**
     * @param array{
     *   proveedor_id:int,
     *   detalles:array<int, array{producto_id:int,cantidad:int,precio_unitario:float}>
     * } $dto
     */
    public function crear(array $dto): int
    {
        $proveedor = $this->proveedorRepo->findById((int) $dto['proveedor_id']);
        if (!$proveedor) {
            throw new \InvalidArgumentException("Proveedor no encontrado");
        }

        $pedido = new Pedido(
            0,
            new \DateTimeImmutable('now'),
            $proveedor,
            'pendiente'
        );

        foreach ($dto['detalles'] as $d) {

            $producto = $this->productoRepo->findById((int) $d['producto_id']);
            if (!$producto) {
                throw new \InvalidArgumentException("Producto no encontrado");
            }

            // Validación fuerte: producto ↔ proveedor
            if ($producto->getProveedor()->getId() !== $proveedor->getId()) {
                throw new \LogicException(
                    "El producto {$producto->getNombre()} no pertenece al proveedor seleccionado."
                );
            }

            $pedido->agregarDetalle(
                new DetallePedido(
                    0,
                    $producto,
                    (int) $d['cantidad'],
                    (float) $d['precio_unitario']
                )
            );
        }

        return $this->pedidoRepo->create($pedido);
    }

    /** @return Pedido[] */
    public function listarTodos(): array
    {
        return $this->pedidoRepo->findAll();
    }

    public function obtenerPorId(int $id): ?Pedido
    {
        return $this->pedidoRepo->findById($id);
    }

    /**
     * Marca un pedido como recibido:
     *  - Actualiza stock
     *  - Reactiva productos SOLO si no estaban desactivados manualmente
     */
    public function marcarRecibido(int $pedidoId): void
    {
        $pedido = $this->pedidoRepo->findById($pedidoId);
        if (!$pedido) {
            throw new \Exception("Pedido no encontrado");
        }

        if ($pedido->getEstado() !== 'pendiente') {
            throw new \LogicException("Solo se pueden recibir pedidos pendientes");
        }

        foreach ($pedido->getDetalles() as $detalle) {

            $producto = $detalle->getProducto();
            $cantidad = $detalle->getCantidad();

            // 1️. Actualizar stock
            $stock = $this->stockRepo->findByProductoId($producto->getId());
            if (!$stock) {
                throw new \LogicException("Stock inexistente para producto {$producto->getNombre()}");
            }

            $stock->agregarStock($cantidad);

            $this->stockRepo->guardar($stock);

            // 2) Reactivar producto SOLO si estaba inactivo manualmente
            if ($producto->isActivoManual() === false) {
                $this->productoRepo->reactivarSiCorresponde(
                    $producto->getId()
                );
            }
        }

        // 3️. Cambiar estado del pedido
        $this->pedidoRepo->marcarRecibido($pedidoId);
    }

    public function cancelar(int $pedidoId): void
    {
        $pedido = $this->pedidoRepo->findById($pedidoId);
        if (!$pedido) {
            throw new \Exception("Pedido no encontrado");
        }

        if ($pedido->getEstado() !== 'pendiente') {
            throw new \LogicException("Solo se pueden cancelar pedidos pendientes");
        }

        $this->pedidoRepo->cancelar($pedidoId);
    }
}
