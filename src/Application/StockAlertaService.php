<?php

namespace Application;

use Domain\Interfaces\IStockRepository;
use Domain\Interfaces\IProductoRepository;


/**
 * Clase StockAlertaService
 *
 * Servicio de aplicación encargado de generar alertas de stock bajo.
 *
 * Combina información de:
 * - Stock (cantidades y mínimos)
 * - Producto (datos del producto)
 *
 * para construir una estructura lista para ser consumida
 * por la capa de presentación.
 */
final class StockAlertaService
{
    /**
     * El servicio depende únicamente de interfaces,
     * manteniendo desacoplamiento total de la infraestructura.
     */
    public function __construct(
        private IStockRepository $stockRepo,
        private IProductoRepository $prodRepo
    ) {
    }

     /**
     * Obtiene las alertas de stock bajo.
     *
     * Reglas aplicadas:
     * - advertencia: cantidad <= mínimo + 5
     * - crítico:     cantidad <= mínimo + 2
     *
     * Devuelve un array de alertas con información enriquecida
     * (producto + nivel de alerta).
     */    
    public function obtenerAlertas(): array
    {
        $alertas = [];

        /**
         * Se recorren únicamente los stocks considerados "bajos".
         * Este filtrado previo lo realiza el repositorio.
         *
         * @var \Domain\Entities\Stock $stock
         */
        foreach ($this->stockRepo->findStockBajo() as $stock) {

            // Cantidad actual disponible
            $cantidad = $stock->getCantidad();

            // Stock mínimo configurado
            $minimo = $stock->getStockMinimo();

            // Diferencia entre stock actual y mínimo
            $diff = $cantidad - $minimo;

            /**
             * Si la diferencia es menor o igual a 5,
             * se considera que debe generarse una alerta.
             */
            if ($diff <= 5) {

                /**
                 * Se busca el producto asociado al stock.
                 * Esto permite mostrar información legible
                 * (nombre, categoría, etc.) en la interfaz.
                 */
                $producto = $this->prodRepo->findById(
                    $stock->getProductoId()
                );

                // Si por algún motivo el producto no existe, se omite
                if ($producto === null) {
                    continue;
                }

                /**
                 * Se construye la alerta con:
                 * - Producto asociado
                 * - Cantidad actual
                 * - Stock mínimo
                 * - Nivel de alerta
                 */
                $alertas[] = [
                    'producto' => $producto,
                    'cantidad' => $cantidad,
                    'minimo' => $minimo,

                    // Nivel crítico si la diferencia es <= 2
                    'nivel' => $diff <= 2 ? 'critico' : 'advertencia',
                ];
            }
        }

        return $alertas;
    }

}
