<?php
namespace Infrastructure\Repositories;

use Domain\Entities\Producto;
use Domain\Entities\Proveedor;
use Domain\Interfaces\IProveedorRepository;
use Infrastructure\Database\ConnectionFactory;
use PDO;

final class PdoProveedorRepository implements IProveedorRepository
{

    private PDO $pdo;
    public function __construct()
    {
        $this->pdo = ConnectionFactory::pdo();
    }

    public function create(string $empresa, string $contacto, string $tel, string $email, string $dir): int
    {
        $st = $this->pdo->prepare("INSERT INTO proveedores (nombre_empresa, contacto, telefono, email, direccion) VALUES (?,?,?,?,?)");
        $st->execute([$empresa, $contacto, $tel, $email, $dir]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, string $empresa, string $contacto, string $tel, string $email, string $dir): void
    {
        $st = $this->pdo->prepare("UPDATE proveedores SET nombre_empresa=?, contacto=?, telefono=?, email=?, direccion=? WHERE id=?");
        $st->execute([$empresa, $contacto, $tel, $email, $dir, $id]);
    }

    public function delete(int $id): void
    {
        $st = $this->pdo->prepare("DELETE FROM proveedores WHERE id=?");
        $st->execute([$id]);
    }

    public function findById(int $id): ?Proveedor
    {
        $st = $this->pdo->prepare("SELECT * FROM proveedores WHERE id=?");
        $st->execute([$id]);
        $r = $st->fetch();

        if (!$r)
            return null;

        $prov = new Proveedor(
            (int) $r['id'],
            $r['nombre_empresa'],
            $r['contacto'],
            $r['telefono'],
            $r['email'],
            $r['direccion']
        );

        // Cargar productos asociados
        $prov->setProductos($this->getProductos($prov->getId()));

        return $prov;
    }


    public function findAll(): array
    {
        $out = [];
        foreach ($this->pdo->query(
            "SELECT * FROM proveedores ORDER BY id ASC"
        ) as $r) {
            $out[] = new Proveedor(
                (int) $r['id'],
                $r['nombre_empresa'],
                $r['contacto'],
                $r['telefono'],
                $r['email'],
                $r['direccion']
            );
        }
        return $out;
    }


    public function contarProductosAsociados(int $id): int
    {
        $st = $this->pdo->prepare("
        SELECT COUNT(*) 
        FROM productos 
        WHERE proveedor_id = ?
    ");
        $st->execute([$id]);
        return (int) $st->fetchColumn();
    }

    public function getProductos(int $proveedorId): array
    {
        $st = $this->pdo->prepare("
        SELECT p.* 
        FROM productos p
        WHERE p.proveedor_id = ?
    ");
        $st->execute([$proveedorId]);

        $productos = [];
        foreach ($st->fetchAll() as $r) {
            $productos[] = Producto::crearDesdeProveedor(
                (int) $r['id'],
                $r['nombre'],
                $r['descripcion'],
                (float) $r['precio'],
                $r['fecha_registro'],
                (bool) $r['activo']
            );
        }
        return $productos;
    }




}
