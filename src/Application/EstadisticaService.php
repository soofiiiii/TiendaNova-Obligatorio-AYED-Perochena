<?php

namespace Application;

use Domain\Interfaces\ICategoriaRepository;
use Domain\Interfaces\IProductoRepository;
use Domain\Interfaces\IProveedorRepository;
use Domain\Interfaces\IPedidoRepository;
use Domain\Entities\Categoria;

final class EstadisticaService
{
    public function __construct(
        private IProductoRepository $prodRepo,
        private ICategoriaRepository $catRepo,
        private IProveedorRepository $provRepo,
        private IPedidoRepository $pedidoRepo
    ) {
    }

    public function obtenerDashboard(): array
    {
        $productos   = $this->prodRepo->findAll();
        $categorias  = $this->catRepo->getFullTree();
        $proveedores = $this->provRepo->findAll();
        $pedidos     = $this->pedidoRepo->findAll();

        /* =========================
         * PRODUCTOS
         * ========================= */
        $totalProductos     = count($productos);
        $productosActivos   = array_filter($productos, fn($p) => $p->isActivo());
        $productosInactivos = $totalProductos - count($productosActivos);

        /* =========================
         * CATEGORÍAS
         * ========================= */
        $categoriasConProd = [];
        foreach ($productos as $p) {
            $categoriasConProd[$p->getCategoria()->getId()] = true;
        }

        $totalCategorias         = count($categorias);
        $categoriasConProductos  = count($categoriasConProd);
        $categoriasSinProductos  = $totalCategorias - $categoriasConProductos;

        /* =========================
         * PEDIDOS / VENTAS
         * ========================= */
        $pedPendientes = 0;
        $pedRecibidos  = 0;
        $pedCancelados = 0;

        $ventasTotales      = 0;
        $ventasPorProducto  = [];
        $ventasPorDia       = [];

        foreach ($pedidos as $p) {
            switch ($p->getEstado()) {
                case 'pendiente':
                    $pedPendientes++;
                    break;

                case 'cancelado':
                    $pedCancelados++;
                    break;

                case 'recibido':
                    $pedRecibidos++;
                    $ventasTotales += $p->getTotal();

                    $dia = $p->getFecha()->format('D');
                    $ventasPorDia[$dia] ??= 0;
                    $ventasPorDia[$dia] += $p->getTotal();

                    foreach ($p->getDetalles() as $d) {
                        $nombre = $d->getProducto()->getNombre();
                        $ventasPorProducto[$nombre] ??= 0;
                        $ventasPorProducto[$nombre] +=
                            $d->getCantidad() * $d->getPrecioUnitario();
                    }
                    break;
            }
        }

        /* =========================
         * STOCK POR CATEGORÍA
         * ========================= */
        $productosPorCategoria = [];
        foreach ($productos as $p) {
            $nombreCat = $p->getCategoria()->getNombre();
            $productosPorCategoria[$nombreCat] ??= 0;
            $productosPorCategoria[$nombreCat] +=
                $p->getStock()->getCantidad();
        }

        /* =========================
         * INVENTARIO POR RAMA
         * ========================= */
        $valorPorRama     = $this->calcularValorPorRama($categorias, $productos);
        $valorInventario  = array_sum($valorPorRama);

        return [
            // productos
            'totalProductos'     => $totalProductos,
            'productosActivos'   => count($productosActivos),
            'productosInactivos' => $productosInactivos,

            // categorías
            'totalCategorias'        => $totalCategorias,
            'categoriasConProductos' => $categoriasConProductos,
            'categoriasSinProductos' => $categoriasSinProductos,

            // proveedores
            'totalProveedores' => count($proveedores),

            // pedidos
            'pedidosPendientes' => $pedPendientes,
            'pedidosRecibidos'  => $pedRecibidos,
            'pedidosCancelados' => $pedCancelados,

            // ventas
            'ventasTotales'     => $ventasTotales,
            'ventasPorProducto' => $ventasPorProducto,
            'ventasPorDia'      => $ventasPorDia,

            // inventario
            'valorInventario'       => $valorInventario,
            'valorPorRama'          => $valorPorRama,
            'productosPorCategoria' => $productosPorCategoria,

            // distribución
            'distActivos' => [
                'activos'   => count($productosActivos),
                'inactivos' => $productosInactivos,
            ],

            // alertas (preparado)
            'alertasStock' => []
        ];
    }

    private function calcularValorPorRama(array $categorias, array $productos): array
    {
        $productosPorCat = [];
        foreach ($productos as $p) {
            $productosPorCat[$p->getCategoria()->getId()][] = $p;
        }

        $calcular = function (Categoria $cat) use (&$calcular, $productosPorCat) {
            $total = 0;

            foreach ($productosPorCat[$cat->getId()] ?? [] as $p) {
                $total += $p->getPrecio() * $p->getStock()->getCantidad();
            }

            foreach ($cat->getSubcategorias() as $sub) {
                $total += $calcular($sub);
            }

            return $total;
        };

        $resultado = [];
        foreach ($categorias as $cat) {
            if ($cat->getCategoriaPadre() === null) {
                $resultado[$cat->getNombre()] = $calcular($cat);
            }
        }

        return $resultado;
    }
}
