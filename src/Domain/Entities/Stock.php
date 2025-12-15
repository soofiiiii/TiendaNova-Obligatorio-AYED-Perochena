<?php
namespace Domain\Entities;

/**
 * Clase Stock
 *
 * Representa el inventario asociado a un producto.
 * Controla cantidades disponibles, mínimos, ubicación física
 * y la fecha de la última actualización.
 */
final class Stock
{
    // Identificador único del registro de stock
    private int $id;

    // ID del producto al que pertenece este stock
    // Se maneja por ID para evitar acoplamiento directo con la entidad Producto
    private int $productoId;

    // Cantidad disponible actualmente
    private int $cantidad;

    // Ubicación física del producto en el depósito
    private string $ubicacion;

    // Fecha de la última modificación del stock
    private \DateTimeImmutable $ultimaActualizacion;

    // Cantidad mínima antes de considerarse stock bajo
    private int $stockMinimo;

    /**
     * Constructor
     *
     * Inicializa el estado completo del stock.
     */
    public function __construct(
        int $id,
        int $productoId,
        int $cantidad,
        string $ubicacion,
        \DateTimeImmutable $ultimaActualizacion,
        int $stockMinimo
    ){
        $this->id = $id;
        $this->productoId = $productoId;
        $this->cantidad = $cantidad;
        $this->ubicacion = $ubicacion;
        $this->ultimaActualizacion = $ultimaActualizacion;
        $this->stockMinimo = $stockMinimo;
    }

    // GETTERS Y SETTERS BÁSICOS

    public function getId(): int { return $this->id; }
    public function getProductoId(): int { return $this->productoId; }
    public function getCantidad(): int { return $this->cantidad; }
    public function getUbicacion(): string { return $this->ubicacion; }
    public function getUltimaActualizacion(): \DateTimeImmutable { return $this->ultimaActualizacion; }
    public function getStockMinimo(): int { return $this->stockMinimo; }
    public function setUbicacion(string $u): void { $this->ubicacion = $u; }
    public function setStockMinimo(int $m): void { $this->stockMinimo = $m; }


    // MÉTODOS DE NEGOCIO


    /**
     * Incrementa la cantidad disponible de stock.
     *
     * No se permiten valores cero o negativos.
     * Cada modificación actualiza la fecha.
     */
    public function agregarStock(int $cantidad): void
    {
        if ($cantidad <= 0) {
            // No tiene sentido agregar 0 o valores negativos
            return; 
        }

        $this->cantidad += $cantidad;
        $this->ultimaActualizacion = new \DateTimeImmutable();
    }

    /**
     * Reduce la cantidad disponible de stock.
     *
     * Nunca permite que el stock quede en valores negativos.
     */
    public function descontarStock(int $cantidad): void
    {
        if ($cantidad <= 0) {
            // No se descuenta con valores inválidos
            return;
        }

        // Se asegura que el stock nunca sea menor a 0
        $this->cantidad = max(0, $this->cantidad - $cantidad);
        $this->ultimaActualizacion = new \DateTimeImmutable();
    }

    /**
     * Verifica si el stock está por debajo del mínimo configurado.
     *
     * Útil para alertas o reportes de inventario.
     */
    public function verificarStockBajo(): bool
    {
        return $this->cantidad < $this->stockMinimo;
    }

    /**
     * Calcula el valor total del stock.
     *
     * Fórmula:
     * cantidad × precio unitario
     *
     * El precio se recibe como parámetro para evitar
     * acoplar Stock con la entidad Producto.
     */
    public function calcularValorTotal(float $precioUnitario): float
    {
        return $this->cantidad * $precioUnitario;
    }

    // MÉTODO INTERNO DE AJUSTE

    /**
     * Ajusta el stock aplicando un delta positivo o negativo.
     *
     * Método de uso interno o técnico.
     * Garantiza que el stock nunca quede negativo.
     */
    public function ajustar(int $delta): void
    {
        $this->cantidad += $delta;

        // Corrección defensiva: el stock no puede ser negativo
        if ($this->cantidad < 0) {
            $this->Cantidad = 0;
        }
        $this->ultimaActualizacion = new \DateTimeImmutable();
    }
}
