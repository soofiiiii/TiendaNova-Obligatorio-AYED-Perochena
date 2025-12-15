<?php
namespace Domain\Entities;

/**
 * Clase Pedido
 *
 * Representa un pedido de reposición realizado a un proveedor.
 * Contiene múltiples detalles (ítems) y controla su propio estado
 * y el cálculo del monto total.
 */
final class Pedido
{
    // Identificador único del pedido
    private int $id;

    // Fecha en la que se creó el pedido (inmutable para evitar inconsistencias)
    private \DateTimeImmutable $fechaPedido;

    // Proveedor al cual se le realiza el pedido
    private Proveedor $proveedor;

    // Estado actual del pedido: pendiente | recibido | cancelado
    private string $estado;

    /**
     * Detalles del pedido (líneas de productos).
     *
     * @var DetallePedido[]
     */
    private array $detalles = [];

    // Monto total del pedido (suma de subtotales)
    private float $total;

    /**
     * Constructor
     *
     * Inicializa el pedido con estado pendiente y total en cero.
     */
    public function __construct(
        int $id,
        \DateTimeImmutable $fechaPedido,
        Proveedor $proveedor,
        string $estado = 'pendiente'
    ) {
        $this->id = $id;
        $this->fechaPedido = $fechaPedido;
        $this->proveedor = $proveedor;
        $this->estado = $estado;
        $this->total = 0.0;
    }


    // MÉTODOS DE NEGOCIO


    /**
     * Agrega un detalle al pedido.
     *
     * Regla de negocio:
     * - Solo se pueden modificar pedidos en estado "pendiente".
     * - Cada vez que se agrega un ítem, se recalcula el total.
     */
    public function agregarDetalle(DetallePedido $detalle): void
    {
         // Validación de estado
        if ($this->estado !== 'pendiente') {
            throw new \LogicException("No se pueden modificar pedidos no pendientes.");
        }

        // Se agrega el detalle al pedido
        $this->detalles[] = $detalle;

        // Se recalcula el total del pedido
        $this->calcularTotal();
    }

    /**
     * Elimina un detalle del pedido por su ID.
     *
     * Regla de negocio:
     * - Solo se pueden eliminar ítems si el pedido está pendiente.
     */
    public function eliminarDetalle(int $idDetalle): void
    {
        // Validación de estado
        if ($this->estado !== 'pendiente') {
            throw new \LogicException("No se pueden modificar pedidos no pendientes.");
        }

        // Se filtran los detalles, quitando el que coincide con el ID
        $this->detalles = array_filter(
            $this->detalles,
            fn(DetallePedido $d) => $d->getId() !== $idDetalle
        );

        // Se recalcula el total luego de la eliminación
        $this->calcularTotal();
    }

    /**
     * Calcula el monto total del pedido.
     *
     * Se utiliza programación funcional (array_reduce)
     * para sumar los subtotales de cada detalle.
     */
    public function calcularTotal(): void
    {
        $this->total = array_reduce(
            $this->detalles,
            fn(float $acc, DetallePedido $d) => $acc + $d->subtotal(),
            0.0
        );
    }

    /**
     * Cambia el estado del pedido.
     *
     * Reglas de negocio:
     * - El nuevo estado debe ser válido.
     * - El estado solo puede cambiarse una vez (desde pendiente).
     */
    public function cambiarEstado(string $nuevoEstado): void
    {
        $estadosValidos = ['pendiente', 'recibido', 'cancelado'];

        // Validación del estado recibido
        if (!in_array($nuevoEstado, $estadosValidos, true)) {
            throw new \InvalidArgumentException("Estado inválido.");
        }

        // No se permite cambiar el estado si ya fue procesado
        if ($this->estado !== 'pendiente') {
            throw new \LogicException("El estado del pedido no puede modificarse.");
        }

        $this->estado = $nuevoEstado;
    }


    // MÉTODOS DE HIDRATACIÓN


    /**
     * Agrega un detalle sin aplicar reglas de negocio.
     *
     * Se utiliza al reconstruir el objeto desde memoria o sesión,
     * evitando validaciones y cálculos automáticos.
     */
    public function hidratarDetalle(DetallePedido $detalle): void
    {
        $this->detalles[] = $detalle;
    }

    /**
     * Hidrata el total del pedido.
     *
     * Se usa cuando el total ya fue calculado previamente
     * y se quiere restaurar el estado del objeto.
     */
    public function hidratarTotal(float $total): void
    {
        $this->total = $total;
    }




    // GETTERS

    public function getId(): int
    {
        return $this->id;
    }

    public function getFecha(): \DateTimeImmutable
    {
        return $this->fechaPedido;
    }

    public function getProveedor(): Proveedor
    {
        return $this->proveedor;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    /**
     * Devuelve todos los detalles del pedido.
     *
     * @return DetallePedido[]
     */
    public function getDetalles(): array
    {
        return $this->detalles;
    }

    public function getTotal(): float
    {
        return $this->total;
    }
}
