<?php
namespace Domain\Interfaces;

use Domain\Entities\Pedido;

/**
 * Interface IPedidoRepository
 *
 * Define el contrato para la persistencia y recuperación de pedidos.
 * Encapsula la lógica de almacenamiento y asegura que las reglas de
 * negocio relacionadas al estado del pedido se respeten.
 */
interface IPedidoRepository
{
    /**
     * Crea un nuevo pedido con todos sus detalles asociados.
     *
     * La implementación debe garantizar que:
     * - La creación del pedido y sus detalles sea atómica (transacción).
     * - El pedido quede persistido en estado pendiente.
     *
     * Devuelve el ID generado para el pedido.
     */    
    public function create(Pedido $pedido): int;

    /**
     * Crea un nuevo pedido con todos sus detalles asociados.
     *
     * La implementación debe garantizar que:
     * - La creación del pedido y sus detalles sea atómica (transacción).
     * - El pedido quede persistido en estado pendiente.
     *
     * Devuelve el ID generado para el pedido.
     */
    public function marcarRecibido(int $pedidoId): void;

    /**
     * Marca un pedido como cancelado.
     *
     * Regla de negocio:
     * - Solo se puede cancelar un pedido pendiente.
     * - Un pedido cancelado no afecta el stock.
     */
    public function cancelar(int $pedidoId): void;

    /**
     * Busca un pedido por su identificador.
     *
     * Devuelve null si no existe.
     */
    public function findById(int $id): ?Pedido;

    /**
     * Devuelve todos los pedidos registrados en el sistema.
     *
     * @return Pedido[]
     */
    public function findAll(): array;
    
}
