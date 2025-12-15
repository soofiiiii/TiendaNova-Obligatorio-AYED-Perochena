<?php
namespace Application;

use Domain\Entities\Producto;
use Domain\Interfaces\ICategoriaRepository;
use Domain\Interfaces\IProductoRepository;
use Domain\Interfaces\IStockRepository;

final class ProductoService
{
    public function __construct(
        private ICategoriaRepository $catRepo,
        private IProductoRepository $prodRepo,
        private IStockRepository $stockRepo
    ) {
    }

    /** @return Producto[] */
    public function listarTodos(): array
    {
        return $this->prodRepo->findAll();
    }

    public function obtenerPorId(int $id): ?Producto
    {
        return $this->prodRepo->findById($id);
    }

    //Crea producto + registro de strock 
    public function crear(array $dto): int
    {
        // Validaciones mínimas
        if (empty($dto['nombre'])) {
            throw new \InvalidArgumentException("El nombre del producto es obligatorio.");
        }

        if (!isset($dto['precio']) || $dto['precio'] < 0) {
            throw new \InvalidArgumentException("El precio no puede ser negativo.");
        }

        if (!$this->catRepo->findById((int) $dto['categoria_id'])) {
            throw new \InvalidArgumentException("La categoría especificada no existe.");
        }

        // Crear producto
        $id = $this->prodRepo->create(
            $dto['nombre'],
            $dto['descripcion'] ?? null,
            (float) $dto['precio'],
            (int) $dto['categoria_id'],
            (int) $dto['proveedor_id']
        );

        // Crear registro de stock
        $this->stockRepo->create(
            $id,
            isset($dto['stock_inicial']) ? (int) $dto['stock_inicial'] : 0,
            $dto['ubicacion'] ?? 'DEP-1',
            isset($dto['stock_minimo']) ? (int) $dto['stock_minimo'] : 0
        );

        return $id;
    }


    public function actualizar(int $id, array $dto): void
    {
        if (empty($dto['nombre'])) {
            throw new \InvalidArgumentException("El nombre es obligatorio.");
        }

        if (!isset($dto['precio']) || $dto['precio'] < 0) {
            throw new \InvalidArgumentException("El precio no puede ser negativo.");
        }

        // 1) Obtener producto actual
        $prod = $this->prodRepo->findById($id);
        if (!$prod) {
            throw new \RuntimeException("Producto no encontrado.");
        }

        /* 
           DESCUENTO (tu lógica)
        */

        $descuento = null;
        $precioOriginal = $prod->getPrecioOriginal();

        if (!empty($dto['aplicar_descuento']) && isset($dto['descuento'])) {

            if ($precioOriginal === null) {
                $precioOriginal = (float) $dto['precio'];
            }

            $precio = (float) $dto['precio'];
            $precio -= $precio * ((float) $dto['descuento'] / 100);
            $descuento = (int) $dto['descuento'];

        } else {

            if ($precioOriginal !== null) {
                $precio = $precioOriginal;
                $precioOriginal = null;
            } else {
                $precio = (float) $dto['precio'];
            }
        }

        /*
           PERSISTIR PRODUCTO
          */

        $this->prodRepo->update(
            $id,
            trim($dto['nombre']),
            $dto['descripcion'] ?? null,
            $precio,
            (int) $dto['categoria_id'],
            (int) $dto['proveedor_id'],
            $descuento,
            $precioOriginal
        );

        /* 
           UBICACIÓN (STOCK)
        */

        if (isset($dto['ubicacion'])) {
            $stock = $this->stockRepo->findByProductoId($id);
            if ($stock) {
                $stock->setUbicacion($dto['ubicacion']);
                $this->stockRepo->updateUbicacion($stock);
            }
        }
    }



    public function eliminar(int $id): void
    {
        // Stock se elimina automáticamente por FK ON DELETE CASCADE
        $this->prodRepo->delete($id);
    }

    /** @return Producto[] */
    public function buscarPorRama(int $catId): array
    {
        $catSvc = new CategoriaService($this->catRepo);
        $ids = $catSvc->idsDeRama($catId);
        if (!$ids)
            return [];
        return $this->prodRepo->findByCategoriaIds($ids);
    }

    /** @return Producto[] */
    public function productosSinStock(): array
    {
        return $this->prodRepo->findSinStock();
    }

    public function obtenerStockPorProducto(int $productoId): int
    {
        return $this->stockRepo->obtenerStock($productoId);
    }

    public function aplicarDescuento(int $id, float $porcentaje): void
    {
        $prod = $this->prodRepo->findById($id);
        if (!$prod)
            throw new \Exception("Producto no encontrado");

        // aplicar lógica de dominio
        $prod->aplicarDescuento($porcentaje);

        // persistir (incluyendo el nuevo parámetro $descuento)
        $this->prodRepo->update(
            $prod->getId(),
            $prod->getNombre(),
            $prod->getDescripcion(),
            $prod->getPrecio(),
            $prod->getCategoria()->getId(),
            $prod->getProveedor()->getId(),
            (int) $porcentaje
        );
    }

    public function actualizarActivoManual(int $id, int $activoManual): array
    {
        if (!in_array($activoManual, [0, 1], true)) {
            throw new \InvalidArgumentException('Valor inválido');
        }

        // Regla de negocio: no se puede activar sin stock
        if ($activoManual === 1) {
            $stock = $this->stockRepo->obtenerStock($id);
            if ($stock <= 0) {
                $activoManual = 0;
            }
        }

        // Persistir SOLO el estado manual
        $this->prodRepo->updateActivoManual($id, $activoManual);

        $stockActual = $this->stockRepo->obtenerStock($id);

        return [
            'activo_manual' => $activoManual,
            'activo' => ($activoManual === 1 && $stockActual > 0),
            'stock' => $stockActual
        ];
    }



    public function listarPorProveedor(int $proveedorId): array
    {
        return $this->prodRepo->findByProveedor($proveedorId);
    }


}
