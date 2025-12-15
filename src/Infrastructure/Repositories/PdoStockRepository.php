<?php
namespace Infrastructure\Repositories;

use Domain\Entities\Stock;
use Domain\Interfaces\IStockRepository;
use Infrastructure\Database\ConnectionFactory;
use PDO;

final class PdoStockRepository implements IStockRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = ConnectionFactory::pdo();
    }

    public function create(
        int $productoId,
        int $cantidad,
        string $ubicacion,
        int $stockMinimo
    ): int {
        $st = $this->pdo->prepare(
            "INSERT INTO stocks (producto_id, cantidad, ubicacion, stock_minimo)
             VALUES (?,?,?,?)"
        );
        $st->execute([$productoId, $cantidad, $ubicacion, $stockMinimo]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(
        int $productoId,
        int $cantidad,
        string $ubicacion,
        int $stockMinimo
    ): void {
        $st = $this->pdo->prepare(
            "UPDATE stocks
             SET cantidad=?, ubicacion=?, stock_minimo=?
             WHERE producto_id=?"
        );
        $st->execute([$cantidad, $ubicacion, $stockMinimo, $productoId]);
    }

    public function ajustar(int $productoId, int $delta): void
    {
        $st = $this->pdo->prepare(
            "UPDATE stocks
             SET cantidad = GREATEST(cantidad + ?, 0)
             WHERE producto_id=?"
        );
        $st->execute([$delta, $productoId]);
    }

    public function findByProductoId(int $productoId): ?Stock
    {
        $st = $this->pdo->prepare(
            "SELECT * FROM stocks WHERE producto_id=?"
        );
        $st->execute([$productoId]);
        $r = $st->fetch(PDO::FETCH_ASSOC);

        return $r ? new Stock(
            (int) $r['id'],
            (int) $r['producto_id'],
            (int) $r['cantidad'],
            $r['ubicacion'],
            new \DateTimeImmutable($r['fecha_ultima_actualizacion']),
            (int) $r['stock_minimo']
        ) : null;
    }

    private function mapRow(array $r): Stock
    {
        return new Stock(
            (int) $r['id'],
            (int) $r['producto_id'],
            (int) $r['cantidad'],
            $r['ubicacion'],
            new \DateTimeImmutable($r['fecha_ultima_actualizacion']),
            (int) $r['stock_minimo']
        );
    }


    public function findBajoMinimo(): array
    {
        $out = [];

        $stmt = $this->pdo->query(
            "SELECT *
         FROM stocks
         WHERE cantidad < stock_minimo"
        );

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $out[] = $this->mapRow($row);
        }

        return $out;
    }


    public function obtenerStock(int $productoId): int
    {
        $st = $this->pdo->prepare(
            "SELECT COALESCE(cantidad, 0)
             FROM stocks
             WHERE producto_id = ?"
        );
        $st->execute([$productoId]);
        $stock = $st->fetchColumn();

        return $stock !== false ? (int) $stock : 0;
    }

    public function findStockBajo(): array
    {
        $out = [];

        $stmt = $this->pdo->query(
            "
        SELECT *
        FROM stocks
        WHERE cantidad <= stock_minimo + 2
        "
        );

        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $out[] = new Stock(
                (int) $r['id'],
                (int) $r['producto_id'],
                (int) $r['cantidad'],
                $r['ubicacion'],
                new \DateTimeImmutable($r['fecha_ultima_actualizacion']),
                (int) $r['stock_minimo']
            );
        }

        return $out;
    }

    public function updateUbicacion(Stock $stock): void
    {
        $st = $this->pdo->prepare(
            "UPDATE stocks SET ubicacion = ? WHERE producto_id = ?"
        );

        $st->execute([
            $stock->getUbicacion(),
            $stock->getProductoId()
        ]);
    }

    public function guardar(Stock $stock): void
    {
        $st = $this->pdo->prepare(
            "UPDATE stocks
         SET cantidad = ?,
             ubicacion = ?,
             stock_minimo = ?,
             fecha_ultima_actualizacion = NOW()
         WHERE producto_id = ?"
        );

        $st->execute([
            $stock->getCantidad(),
            $stock->getUbicacion(),
            $stock->getStockMinimo(),
            $stock->getProductoId()
        ]);
    }




}
