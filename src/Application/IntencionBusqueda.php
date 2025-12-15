<?php
namespace Application;

/**
 * Clase IntencionBusqueda
 *
 * Representa la intención de una búsqueda realizada por el usuario.
 *
 * NO es una entidad del dominio ni se persiste en base de datos.
 * Es un objeto simple de transferencia que encapsula criterios
 * de búsqueda ya interpretados.
 */
final class IntencionBusqueda
{
    /**
     * Constructor de la intención de búsqueda.
     *
     * Cada propiedad indica una condición o filtro
     * que luego será interpretado por un servicio
     * de aplicación o un controlador.
     */
    public function __construct(

        /**
         * Indica si la búsqueda está relacionada con stock.
         * Ejemplo: "productos sin stock", "stock bajo".
         */
        public bool $esStock,

        /**
         * Nombre de la categoría buscada (si aplica).
         * Puede venir de texto libre.
         */
        public ?string $categoria,

        /**
         * ID concreto de la categoría, si fue resuelta.
         * Permite búsquedas directas sin ambigüedad.
         */
        public ?int $categoriaId,

        /**
         * Nombre del proveedor buscado (si aplica).
         */
        public ?string $proveedor,

        /**
         * Indica si la búsqueda apunta específicamente
         * a productos con stock bajo.
         */
        public bool $esStockBajo = false
    ) {
    }
}
