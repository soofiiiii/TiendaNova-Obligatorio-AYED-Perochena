<?php
namespace Infrastructure\Repositories;

use Domain\Entities\Categoria;
use Domain\Entities\Producto;
use Domain\Entities\Proveedor;
use Domain\Interfaces\ICategoriaRepository;
use Domain\Interfaces\IProductoRepository;
use Domain\Interfaces\IStockRepository;
use Infrastructure\Database\ConnectionFactory;
use PDO;

final class PdoProductoRepository implements IProductoRepository
{

    private PDO $pdo;
    private IStockRepository $stockRepo;
    private ICategoriaRepository $catRepo;

    public function __construct(
        IStockRepository $stockRepo,
        ICategoriaRepository $catRepo
    ) {
        $this->pdo = ConnectionFactory::pdo();
        $this->stockRepo = $stockRepo;
        $this->catRepo = $catRepo;
    }


    public function create(string $nombre, ?string $descripcion, float $precio, int $categoriaId, int $proveedorId): int
    {
        $st = $this->pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, categoria_id, proveedor_id) VALUES (?,?,?,?,?)");
        $st->execute([$nombre, $descripcion, $precio, $categoriaId, $proveedorId]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, string $nombre, ?string $descripcion, float $precio, int $categoriaId, int $proveedorId, ?int $descuento, ?float $precioOriginal): void
    {
        $st = $this->pdo->prepare("
        UPDATE productos
        SET nombre=?, descripcion=?, precio=?, precio_original=?, categoria_id=?, proveedor_id=?, descuento=?
        WHERE id=?


    ");
        $st->execute([
            $nombre,
            $descripcion,
            $precio,
            $precioOriginal,
            $categoriaId,
            $proveedorId,
            $descuento,
            $id
        ]);

    }

    public function updateActivoManual(int $id, int $activoManual): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE productos SET activo_manual = ? WHERE id = ?"
        );

        $stmt->execute([$activoManual, $id]);
    }



    public function delete(int $id): void
    {
        $st = $this->pdo->prepare("DELETE FROM productos WHERE id=?");
        $st->execute([$id]);
    }

    private function mapRow(array $r): Producto
    {
        // CATEGORÍA: buscarla en el árbol, NO crearla
        $cat = $this->catRepo->findById((int) $r['c_id']);

        // Proveedor (puede quedar así si no necesitás su árbol)
        $prov = new Proveedor(
            (int) $r['pr_id'],
            $r['pr_nombre'],
            '',
            '',
            '',
            ''
        );

        $stock = $this->stockRepo->findByProductoId($r['id']);

        $descuento = $r['descuento'] !== null ? (int) $r['descuento'] : null;
        $precioOriginal = $r['precio_original'] !== null ? (float) $r['precio_original'] : null;

        $activoManual = array_key_exists('activo_manual', $r)
            ? (bool) $r['activo_manual']
            : true;

        return new Producto(
            id: (int) $r['id'],
            nombre: (string) $r['nombre'],
            descripcion: $r['descripcion'] !== null ? (string) $r['descripcion'] : null,
            precio: (float) $r['precio'],
            stock: $stock,
            categoria: $cat,
            proveedor: $prov,
            fechaRegistro: new \DateTimeImmutable($r['fecha_registro']),
            descuento: $descuento,
            precioOriginal: $precioOriginal,
            activoManual: $activoManual
        );


    }

    public function findById(int $id): ?Producto
    {
        $sql = "SELECT p.*, c.id c_id, c.nombre c_nombre, pr.id pr_id, pr.nombre_empresa pr_nombre
                FROM productos p
                JOIN categorias c ON p.categoria_id=c.id
                JOIN proveedores pr ON p.proveedor_id=pr.id
                WHERE p.id=?";
        $st = $this->pdo->prepare($sql);
        $st->execute([$id]);
        $r = $st->fetch();
        return $r ? $this->mapRow($r) : null;
    }

    public function findAll(): array
    {
        $sql = "SELECT p.*, c.id c_id, c.nombre c_nombre, pr.id pr_id, pr.nombre_empresa pr_nombre
                FROM productos p
                JOIN categorias c ON p.categoria_id=c.id
                JOIN proveedores pr ON p.proveedor_id=pr.id
                ORDER BY p.id DESC";
        $out = [];
        foreach ($this->pdo->query($sql) as $r) {
            $out[] = $this->mapRow($r);
        }
        return $out;
    }

    public function findByCategoriaIds(array $ids): array
    {
        if (!$ids)
            return [];
        $in = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT p.*, c.id c_id, c.nombre c_nombre, pr.id pr_id, pr.nombre_empresa pr_nombre
                FROM productos p
                JOIN categorias c ON p.categoria_id=c.id
                JOIN proveedores pr ON p.proveedor_id=pr.id
                WHERE p.categoria_id IN ($in)";
        $st = $this->pdo->prepare($sql);
        $st->execute($ids);
        $out = [];
        foreach ($st->fetchAll() as $r)
            $out[] = $this->mapRow($r);
        return $out;
    }


    public function findSinStock(): array
    {
        $sql = "SELECT p.*, c.id c_id, c.nombre c_nombre, pr.id pr_id, pr.nombre_empresa pr_nombre
                FROM productos p
                LEFT JOIN stocks s ON s.producto_id = p.id
                JOIN categorias c ON p.categoria_id=c.id
                JOIN proveedores pr ON p.proveedor_id=pr.id
                WHERE COALESCE(s.cantidad,0)=0";
        $out = [];
        foreach ($this->pdo->query($sql) as $r)
            $out[] = $this->mapRow($r);
        return $out;
    }

    public function findByPrecioMenorQue(float $v): array
    {
        $st = $this->pdo->prepare("SELECT p.*, c.id c_id, c.nombre c_nombre, pr.id pr_id, pr.nombre_empresa pr_nombre
            FROM productos p JOIN categorias c ON p.categoria_id=c.id JOIN proveedores pr ON p.proveedor_id=pr.id
            WHERE p.precio < ? ORDER BY p.precio ASC");
        $st->execute([$v]);
        return array_map(fn($r) => $this->mapRow($r), $st->fetchAll());
    }

    public function findByPrecioMayorQue(float $v): array
    {
        $st = $this->pdo->prepare("SELECT p.*, c.id c_id, c.nombre c_nombre, pr.id pr_id, pr.nombre_empresa pr_nombre
            FROM productos p JOIN categorias c ON p.categoria_id=c.id JOIN proveedores pr ON p.proveedor_id=pr.id
            WHERE p.precio > ? ORDER BY p.precio DESC");
        $st->execute([$v]);
        return array_map(fn($r) => $this->mapRow($r), $st->fetchAll());
    }

    public function findByProveedor(int $proveedorId): array
    {
        $sql = "SELECT p.*, 
                   c.id   AS c_id, 
                   c.nombre AS c_nombre, 
                   pr.id  AS pr_id, 
                   pr.nombre_empresa AS pr_nombre
            FROM productos p
            JOIN categorias c ON p.categoria_id = c.id
            JOIN proveedores pr ON p.proveedor_id = pr.id
            WHERE pr.id = ?
            ORDER BY p.nombre";

        $st = $this->pdo->prepare($sql);
        $st->execute([$proveedorId]);

        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        $out = [];

        foreach ($rows as $r) {
            $out[] = $this->mapRow($r);
        }

        return $out;
    }

    public function reactivarSiCorresponde(int $productoId): void
    {
        $st = $this->pdo->prepare(
            "UPDATE productos
         SET activo_manual = 1
         WHERE id = ? AND activo_manual = 0"
        );

        $st->execute([$productoId]);
    }


}