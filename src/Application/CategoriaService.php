<?php
namespace Application;

use Domain\Interfaces\ICategoriaRepository;
use Domain\Entities\Categoria;

final class CategoriaService
{
    public function __construct(private ICategoriaRepository $repo)
    {
    }

    /** @return Categoria[] Raíces del árbol completo */
    public function obtenerArbolCompleto(): array
    {
        return $this->repo->getFullTree();
    }

    public function obtenerPorId(int $id): ?Categoria
    {
        return $this->repo->findById($id);
    }

    public function crear(string $nombre, ?string $descripcion, ?int $parentId): int
    {
        return $this->repo->create($nombre, $descripcion, $parentId ?: null);
    }

    public function actualizar(int $id, string $nombre, ?string $descripcion, ?int $parentId): void
    {
        //Actualizo datos propios
        $this->repo->update($id, $nombre, $descripcion);
        // y cambio el padre (si corresponde)
        $this->repo->move($id, $parentId ?: null);
    }

    public function eliminar(int $id): void
    {
        $this->repo->delete($id);
    }

    public function mover(int $id, ?int $nuevoParentId): void
    {
        $this->repo->move($id, $nuevoParentId ?: null);
    }


    /** Retorna los IDs de la categoría dada y todas sus descendientes.
     * Útil para búsquedas de productos por rama
     * @return int[] */
    public function idsDeRama(int $categoriaId): array
    {
        $roots = $this->repo->getFullTree();
        $ids = [];

        $buscar = function (Categoria $nodo) use (&$buscar, $categoriaId, &$ids): bool {
            if ($nodo->getId() === $categoriaId) {
                $this->recolectarIds($nodo, $ids);
            }
            foreach ($nodo->getSubcategorias() as $hijo) {
                if ($buscar($hijo)) {
                    return true;
                }
            }
            return false;
        };

        foreach ($roots as $r) {
            if ($buscar($r))
                break;
        }

        return array_values(array_unique($ids));
    }

    private function recolectarIds(Categoria $nodo, array &$out): void
    {
        $out[] = $nodo->getId();
        foreach ($nodo->getSubcategorias() as $hijo) {
            $this->recolectarIds($hijo, $out);
        }
    }

    public function contarProductosEnRama(int $id): int
    {
        return $this->repo->contarProductosEnRama($id);
    }

    public function contarSubcategoriasDirectas(int $id): int
    {
        return $this->repo->contarSubcategoriasDirectas($id);
    }


}