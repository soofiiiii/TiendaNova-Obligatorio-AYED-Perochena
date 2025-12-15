<?php
namespace Application;

use Domain\Interfaces\ICategoriaRepository;
use Domain\Interfaces\IProductoRepository;
use Domain\Entities\Categoria;
use Domain\Interfaces\IStockRepository;

final class BusquedaService
{
    public function __construct(
        private ICategoriaRepository $catRepo,
        private IProductoRepository $prodRepo,
        private IStockRepository $stockRepo
    ) {
    }


    /**
     * Búsqueda global con interpretación de intención.
     */
    public function buscarGlobal(string $termino): array
    {
        $termino = trim($termino);

        $resultadoBase = [
            'categoriaExacta' => null,
            'productosRama' => [],
            'categoriasRelacionadas' => [],
            'productosCoincidentes' => [],
            'hayResultados' => false,
        ];

        if ($termino === '') {
            return $resultadoBase;
        }

        // 1. Detectar intención
        $intencion = $this->detectarIntencion($termino);

        // CASO 0: PRODUCTOS CON STOCK BAJO (máxima prioridad)
        // CASO 0: PRODUCTOS CON STOCK BAJO (máxima prioridad)
        if ($intencion->esStockBajo) {

            $productos = [];

            foreach ($this->stockRepo->findStockBajo() as $stock) {
                $producto = $this->prodRepo->findById(
                    $stock->getProductoId()
                );

                if ($producto !== null) {
                    $productos[] = $producto;
                }
            }

            return [
                'categoriaExacta' => null,
                'productosRama' => [],
                'categoriasRelacionadas' => [],
                'productosCoincidentes' => $productos,
                'hayResultados' => !empty($productos),


                'modoStockBajo' => true,
            ];
        }



        // CASO 1: BÚSQUEDA DE STOCK (PRIORIDAD MÁXIMA)
        if ($intencion->esStock) {

            // 1.1 Intentar interpretar como CATEGORÍA
            $categoria = $this->buscarCategoria(
                $intencion->categoria,
                $intencion->categoriaId
            );

            if ($categoria !== null) {
                // Stock por categoría (rama completa)
                $productos = $this->obtenerStockPorCategoria($categoria);

                return [
                    'categoriaExacta' => $categoria,
                    'productosRama' => $productos,
                    'categoriasRelacionadas' => [],
                    'productosCoincidentes' => [],
                    'hayResultados' => !empty($productos),
                ];
            }

            // 1.2 Si NO es categoría → stock por PRODUCTO
            $term = mb_strtolower($intencion->categoria ?? '');

            $productos = [];
            foreach ($this->prodRepo->findAll() as $p) {
                $nombre = mb_strtolower($p->getNombre());
                $desc = $p->getDescripcion()
                    ? mb_strtolower($p->getDescripcion())
                    : '';

                if (
                    str_contains($nombre, $term) ||
                    str_contains($desc, $term)
                ) {
                    $productos[] = $p;
                }
            }

            if (!empty($productos)) {
                return [
                    'categoriaExacta' => null,
                    'productosRama' => [],                 // ← clave
                    'categoriasRelacionadas' => [],
                    'productosCoincidentes' => $productos,         // ← clave
                    'hayResultados' => true,
                ];
            }

            // Si es stock pero no hubo resultados, NO seguir
            return $resultadoBase;
        }

        // CASO 2: CATEGORÍA POR ID
        if ($intencion->categoriaId !== null) {
            $cat = $this->catRepo->findById($intencion->categoriaId);

            if ($cat !== null) {
                $productos = $this->obtenerStockPorCategoria($cat);

                return [
                    'categoriaExacta' => $cat,
                    'productosRama' => $productos,
                    'categoriasRelacionadas' => [],
                    'productosCoincidentes' => [],
                    'hayResultados' => true,
                ];
            }
        }

        // CASO 3: CATEGORÍA POR NOMBRE
        $categoria = $this->catRepo->findByNombre($termino);

        if ($categoria !== null) {
            $productos = $this->obtenerStockPorCategoria($categoria);

            return [
                'categoriaExacta' => $categoria,
                'productosRama' => $productos,
                'categoriasRelacionadas' => [],
                'productosCoincidentes' => [],
                'hayResultados' => true,
            ];
        }

        // CASO 4: FILTRAR POR PROVEEDOR
        // CASO: Filtrar por proveedor
        if ($intencion->proveedor !== null) {
            $productos = [];

            // Se agregan todos los productos que coincidan con el proveedor (en minúsculas)
            foreach ($this->prodRepo->findAll() as $p) {
                if (
                    mb_strtolower($p->getProveedor()->getNombreEmpresa()) ===
                    mb_strtolower($intencion->proveedor)
                ) {
                    $productos[] = $p;
                }
            }

            // Si encontramos productos, los devolvemos
            return [
                'categoriaExacta' => null,
                'productosRama' => [],
                'categoriasRelacionadas' => [],
                'productosCoincidentes' => $productos,
                'hayResultados' => !empty($productos),
            ];
        }


        // CASO 5: BÚSQUEDA GENÉRICA (fallback)

        $terminoLower = mb_strtolower($termino);

        $roots = $this->catRepo->getFullTree();

        $categoriaExacta = null;
        $categoriasRelacionadas = [];

        // DFS sobre árbol
        $dfs = function (Categoria $nodo) use (&$dfs, $terminoLower, &$categoriaExacta, &$categoriasRelacionadas) {
            $nombre = mb_strtolower($nodo->getNombre());

            if ($categoriaExacta === null && $nombre === $terminoLower) {
                $categoriaExacta = $nodo;
            }

            if (str_contains($nombre, $terminoLower)) {
                $categoriasRelacionadas[$nodo->getId()] = $nodo;
            }

            foreach ($nodo->getSubcategorias() as $hijo) {
                $dfs($hijo);
            }
        };

        foreach ($roots as $r) {
            $dfs($r);
        }

        $productosRama = [];
        if ($categoriaExacta !== null) {
            $productosRama = $this->obtenerStockPorCategoria($categoriaExacta);
            unset($categoriasRelacionadas[$categoriaExacta->getId()]);
        }

        $productosCoinc = [];
        foreach ($this->prodRepo->findAll() as $p) {
            $nombre = mb_strtolower($p->getNombre());
            $desc = $p->getDescripcion()
                ? mb_strtolower($p->getDescripcion())
                : '';
            $proveedor = mb_strtolower($p->getProveedor()->getNombreEmpresa());

            if (
                str_contains($nombre, $terminoLower) ||
                str_contains($desc, $terminoLower) ||
                str_contains($proveedor, $terminoLower)   // 👈 CLAVE
            ) {
                $productosCoinc[$p->getId()] = $p;
            }
        }


        foreach ($productosRama as $p) {
            unset($productosCoinc[$p->getId()]);
        }

        return [
            'categoriaExacta' => $categoriaExacta,
            'productosRama' => array_values($productosRama),
            'categoriasRelacionadas' => array_values($categoriasRelacionadas),
            'productosCoincidentes' => array_values($productosCoinc),
            'hayResultados' =>
                $categoriaExacta !== null ||
                !empty($productosRama) ||
                !empty($categoriasRelacionadas) ||
                !empty($productosCoinc),
        ];
    }


    // MÉTODOS PRIVADOS

    private function detectarIntencion(string $termino): IntencionBusqueda
    {
        $t = mb_strtolower($termino);

        $esStockBajo =
            str_contains($t, 'stock bajo') ||
            str_contains($t, 'poco stock') ||
            str_contains($t, 'stock minimo') ||
            str_contains($t, 'stock mínimo') ||
            str_contains($t, 'productos con stock bajo');

        // OJO: stock bajo NO es stock normal
        $esStock = !$esStockBajo && str_contains($t, 'stock');

        $categoria = null;
        $categoriaId = null;

        if (preg_match('/stock de ([a-záéíóúñ ]+)/u', $t, $m)) {
            $categoria = trim($m[1]);
        }

        if (preg_match('/categor[ií]a\s+(\d+)/u', $t, $m)) {
            $categoriaId = (int) $m[1];
        }

        $proveedor = null;
        if (preg_match('/proveedor\s+([a-záéíóúñ ]+)/u', $t, $m)) {
            $proveedor = trim($m[1]);
        }

        return new IntencionBusqueda(
            $esStock,
            $categoria,
            $categoriaId,
            $proveedor,
            $esStockBajo
        );
    }


    private function buscarCategoria(?string $nombre, ?int $id): ?Categoria
    {
        if ($id !== null) {
            return $this->catRepo->findById($id);
        }

        if ($nombre !== null) {
            return $this->catRepo->findByNombre($nombre);
        }

        return null;
    }

    private function obtenerStockPorCategoria(Categoria $cat): array
    {
        $catSvc = new CategoriaService($this->catRepo);
        $ids = $catSvc->idsDeRama($cat->getId());

        return $this->prodRepo->findByCategoriaIds($ids);
    }
}
