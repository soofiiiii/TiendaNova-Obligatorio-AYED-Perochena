<?php
namespace Infrastructure\Repositories;

use Domain\Entities\Categoria;
use Domain\Interfaces\ICategoriaRepository;
use Infrastructure\Database\ConnectionFactory;
use PDO;

final class PdoCategoriaRepository implements ICategoriaRepository
{
    private PDO $pdo;
    /** @var array<int,Categoria> mapa temporal para hidratación */
    private array $cache = [];

    public function __construct()
    {
        $this->pdo = ConnectionFactory::pdo();
    }

    public function getFullTree(): array
    {
        $sql = "SELECT id, nombre, descripcion, parent_id, nivel FROM categorias ORDER BY nivel, id";
        $rows = $this->pdo->query($sql)->fetchAll();

        // 1. Instanciar todas las categorías sin hijos
        $this->cache = [];
        foreach ($rows as $r) {
            $this->cache[(int) $r['id']] = new Categoria(
                (int) $r['id'],
                $r['nombre'],
                $r['descripcion'] ?? null,
                null,
                (int) $r['nivel']
            );
        }

        // 2. Enlazar padres-hijos en memoria
        $roots = [];
        foreach ($rows as $r) {
            $id = (int) $r['id'];
            $parentId = $r['parent_id'] !== null ? (int) $r['parent_id'] : null;
            $node = $this->cache[$id];

            if ($parentId !== null) {
                $padre = $this->cache[$parentId] ?? null;
                if ($padre) {
                    $node->setCategoriaPadre($padre);
                    $padre->agregarSubcategoria($node);
                }
            } else {
                $roots[] = $node; // nodos raíz
            }
        }
        return $roots;
    }

    public function findById(int $id): ?Categoria
    {
        //Aprovechar el árbol ya hidratado 
        $roots = $this->getFullTree();
        foreach ($roots as $root) {
            $f = $root->buscarPorId($id);
            if ($f)
                return $f;
        }
        return null;
    }

    public function findByNombre(string $nombre): ?Categoria
    {
        $nombre = mb_strtolower(trim($nombre));

        // Aprovechamos el árbol ya hidratado
        $roots = $this->getFullTree();

        $dfs = function (Categoria $nodo) use (&$dfs, $nombre): ?Categoria {
            if (mb_strtolower($nodo->getNombre()) === $nombre) {
                return $nodo;
            }

            foreach ($nodo->getSubcategorias() as $hijo) {
                $found = $dfs($hijo);
                if ($found !== null) {
                    return $found;
                }
            }

            return null;
        };

        foreach ($roots as $root) {
            $found = $dfs($root);
            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }


    public function create(string $nombre, ?string $descripcion, ?int $parentId): int
    {
        // calcular nivel = nivel(parent)+1, o 0 si root
        $nivel = 0;
        if ($parentId !== null) {
            $stmt = $this->pdo->prepare("SELECT nivel FROM categorias WHERE id=?");
            $stmt->execute([$parentId]);
            $nivel = ((int) $stmt->fetchColumn()) + 1;
        }
        $stmt = $this->pdo->prepare("INSERT INTO categorias (nombre, descripcion, parent_id, nivel) VALUES (?,?,?,?)");
        $stmt->execute([$nombre, $descripcion, $parentId, $nivel]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, string $nombre, ?string $descripcion): void
    {
        $stmt = $this->pdo->prepare("UPDATE categorias SET nombre=?, descripcion=? WHERE id=?");
        $stmt->execute([$nombre, $descripcion, $id]);
    }

    public function move(int $id, ?int $nuevoParentId): void
    {
        $this->pdo->beginTransaction();

        try {
            /**
             * 1) Calcular nuevo nivel
             */
            $nuevoNivel = 0;
            if ($nuevoParentId !== null) {
                $stmt = $this->pdo->prepare("SELECT nivel FROM categorias WHERE id = ?");
                $stmt->execute([$nuevoParentId]);
                $nivelPadre = $stmt->fetchColumn();

                if ($nivelPadre === false) {
                    throw new \RuntimeException("El parent_id $nuevoParentId no existe.");
                }

                $nuevoNivel = ((int) $nivelPadre) + 1;
            }

            /**
             * 2) Nivel actual del nodo
             */
            $stmt = $this->pdo->prepare("SELECT nivel FROM categorias WHERE id = ?");
            $stmt->execute([$id]);
            $nivelActual = $stmt->fetchColumn();

            if ($nivelActual === false) {
                throw new \RuntimeException("La categoría $id no existe.");
            }

            $nivelActual = (int) $nivelActual;
            $delta = $nuevoNivel - $nivelActual;

            /**
             * 3) Actualizar el nodo movido
             */
            $stmt = $this->pdo->prepare(
                "UPDATE categorias SET parent_id = ?, nivel = ? WHERE id = ?"
            );
            $stmt->execute([$nuevoParentId, $nuevoNivel, $id]);

            /**
             * 4) Obtener todos los descendientes vía CTE RECURSIVA (MariaDB OK)
             */
            $cte = "
            WITH RECURSIVE d AS (
                SELECT id
                FROM categorias
                WHERE id = ?
                UNION ALL
                SELECT c.id
                FROM categorias c
                INNER JOIN d ON c.parent_id = d.id
            )
            SELECT id FROM d
        ";

            $stmt = $this->pdo->prepare($cte);
            $stmt->execute([$id]);
            $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

            /**
             *  IMPORTANTÍSIMO:
             *  El CTE incluye al propio nodo, pero ese ya fue actualizado arriba,
             *  así que lo removemos del array antes de actualizar descendientes.
             */
            $ids = array_filter($ids, fn($v) => (int) $v !== $id);

            /**
             * 5) Actualizar niveles de descendientes (solo si hay)
             */
            if (!empty($ids)) {
                // Generar ?,?,?... dinámicos
                $placeholders = implode(',', array_fill(0, count($ids), '?'));

                $sql = "UPDATE categorias
                    SET nivel = nivel + ?
                    WHERE id IN ($placeholders)";

                $params = array_merge([$delta], $ids);

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            }

            $this->pdo->commit();
        } catch (\Throwable $t) {
            $this->pdo->rollBack();
            throw $t;
        }
    }


    public function delete(int $id): void
    {
        // Validación de eliminación: sin subcategorías ni productos en ninguna profundidad
        $valida = $this->canDelete($id);
        if (!$valida) {
            throw new \DomainException('No se puede eliminar: tiene subcategorías o productos asociados en la rama.');
        }
        $stmt = $this->pdo->prepare("DELETE FROM categorias WHERE id=?");
        $stmt->execute([$id]);
    }


    private function canDelete(int $id): bool
    {
        // ¿Tiene descendientes?
        $sql = "
          WITH RECURSIVE d AS (
            SELECT id FROM categorias WHERE id=?
            UNION ALL
            SELECT c.id FROM categorias c JOIN d ON c.parent_id = d.id
          )
          SELECT
            (SELECT COUNT(*) FROM categorias c2 JOIN d ON c2.parent_id = d.id) AS hijos,
            (SELECT COUNT(*) FROM productos p JOIN d ON p.categoria_id = d.id) AS prod
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return ((int) $row['hijos'] === 0) && ((int) $row['prod'] === 0);
    }


    public function countsByCategoria(): array
    {
        $sql = "SELECT categoria_id AS cid, COUNT(*) AS cnt FROM productos GROUP BY categoria_id";
        $map = [];
        foreach ($this->pdo->query($sql) as $r) {
            $map[(int) $r['cid']] = (int) $r['cnt'];
        }
        return $map;
    }

    public function contarProductosEnRama(int $id): int
    {
        // Traer todas las categorías descendientes (incluyendo la propia)
        $st = $this->pdo->prepare("
        WITH RECURSIVE cte AS (
            SELECT id
            FROM categorias
            WHERE id = ?

            UNION ALL

            SELECT c.id
            FROM categorias c
            INNER JOIN cte ON c.parent_id = cte.id
        )
        SELECT COUNT(*)
        FROM productos
        WHERE categoria_id IN (SELECT id FROM cte)
    ");
        $st->execute([$id]);
        return (int) $st->fetchColumn();
    }

    public function contarSubcategoriasDirectas(int $id): int
    {
        $st = $this->pdo->prepare("
        SELECT COUNT(*)
        FROM categorias
        WHERE parent_id = ?
    ");
        $st->execute([$id]);
        return (int) $st->fetchColumn();
    }



}


