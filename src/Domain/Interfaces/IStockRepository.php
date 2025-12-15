<?php
namespace Domain\Interfaces;
use Domain\Entities\Stock;

/**
 * Interface IStockRepository
 *
 * Define el contrato para la gestión de inventario.
 * Centraliza todas las operaciones relacionadas con el stock
 * de los productos y desacopla la lógica de persistencia
 * de la lógica de negocio.
 */
interface IStockRepository
{
    /**
     * Crea un registro de stock para un producto.
     *
     * Devuelve el ID generado para el stock.
     */
    public function create(int $productoId, int $cantidad, string $ubicacion, int $stockMinimo): int;
    
    /**
     * Actualiza completamente el stock de un producto.
     *
     * Se utiliza cuando se editan varios atributos a la vez
     * (cantidad, ubicación, stock mínimo).
     */
    public function update(int $productoId, int $cantidad, string $ubicacion, int $stockMinimo): void;
    
    /**
     * Ajusta el stock aplicando un delta positivo o negativo.
     *
     * Este método es fundamental para:
     * - Recepción de pedidos (delta positivo)
     * - Ventas o salidas (delta negativo)
     */
    public function ajustar(int $productoId, int $delta): void;
    
    /**
     * Busca el stock asociado a un producto.
     *
     * Devuelve null si el producto no tiene stock registrado.
     */
    public function findByProductoId(int $productoId): ?Stock;
    
    
    /**
     * Devuelve todos los registros de stock por debajo del mínimo.
     *
     * Se utiliza para alertas y reportes de inventario.
     *
     * @return Stock[]
     */
    public function findBajoMinimo(): array;

    /**
     * Devuelve los stocks considerados "bajos".
     *
     * Puede coincidir o no con findBajoMinimo dependiendo
     * del criterio de la implementación.
     *
     * @return Stock[]
     */
    public function findStockBajo(): array;

    /**
     * Devuelve únicamente la cantidad disponible de stock
     * para un producto específico.
     *
     * Optimiza consultas simples sin necesidad de hidratar
     * toda la entidad Stock.
     */
    public function obtenerStock(int $productoId): int;

    /**
     * Actualiza únicamente la ubicación física del stock.
     *
     * Se separa del update general para permitir
     * modificaciones específicas y controladas.
     */
    public function updateUbicacion(Stock $stock): void;

    /**
     * Persiste un objeto Stock ya existente.
     *
     * Se utiliza cuando el stock fue modificado en memoria
     * (por ejemplo, luego de recibir un pedido).
     */
    public function guardar(Stock $stock): void;

}
