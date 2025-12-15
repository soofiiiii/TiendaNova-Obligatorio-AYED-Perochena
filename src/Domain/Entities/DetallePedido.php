<?php
namespace Domain\Entities;


/**
 * Clase DetallePedido
 *
 * Representa una línea o ítem dentro de un pedido de reposición.
 * Cada detalle asocia un producto con una cantidad y un precio unitario
 * en el momento en que se realiza el pedido.
 */
final class DetallePedido
{
    // Identificador único del detalle dentro del pedido
    private int $id;

    // Producto asociado a este ítem del pedido
    private Producto $producto;

    // Cantidad solicitada de ese producto
    private int $cantidad;

    // Precio unitario del producto en el momento del pedido
    // Se guarda para evitar que cambios posteriores afecten pedidos históricos
    private float $precioUnitario;


    /**
     * Constructor
     *
     * Inicializa el detalle del pedido con todos sus datos.
     */
    public function __construct(
        int $id,
        Producto $producto,
        int $cantidad,
        float $precioUnitario
    ) {
        $this->id             = $id;
        $this->producto       = $producto;
        $this->cantidad       = $cantidad;
        $this->precioUnitario = $precioUnitario;
    }

    /**
     * Calcula el subtotal de este detalle.
     *
     * Subtotal = cantidad × precio unitario
     * Este método encapsula el cálculo para que no se replique la lógica
     * en la clase Pedido.
     */
    public function subtotal(): float
    {
        return $this->cantidad * $this->precioUnitario;
    }

    // Getters
    
    public function getId(): int
    {
        return $this->id;
    }

    public function getProducto(): Producto
    {
        return $this->producto;
    }

    public function getCantidad(): int
    {
        return $this->cantidad;
    }

    public function getPrecioUnitario(): float
    {
        return $this->precioUnitario;
    }
}
