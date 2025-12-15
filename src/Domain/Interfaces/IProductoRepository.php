<?php
namespace Domain\Interfaces;
use Domain\Entities\Producto;


/**
 * Interface IProductoRepository
 *
 * Define el contrato para la persistencia y consulta de productos.
 * Centraliza todas las operaciones CRUD y consultas específicas
 * relacionadas al catálogo de productos.
 */
interface IProductoRepository
{
    /**
     * Crea un nuevo producto.
     *
     * La implementación debe:
     * - Asociar el producto a una categoría y un proveedor
     * - Inicializar correctamente su estado y precio
     *
     * Devuelve el ID generado para el producto.
     */
    public function create(string $nombre, ?string $descripcion, float $precio, int $categoriaId, int $proveedorId): int;
    
    /**
     * Actualiza los datos principales de un producto existente.
     *
     * Incluye:
     * - Datos básicos
     * - Relación con categoría y proveedor
     * - Información de descuento y precio original
     */
    public function update(int $id, string $nombre, ?string $descripcion, float $precio, int $categoriaId, int $proveedorId,  ?int $descuento, ?float $precioOriginal): void;
    
    /**
     * Actualiza el estado manual del producto (activo / inactivo).
     *
     * Este método existe para separar la activación manual
     * de otras actualizaciones del producto.
     */
    public function updateActivoManual(int $id, int $activoManual): void;
    
    /**
     * Elimina un producto del sistema.
     *
     * La implementación debe encargarse de:
     * - Eliminar o desvincular el stock asociado
     * - Mantener la coherencia del sistema
     */
    public function delete(int $id): void;

    
    /**
     * Busca un producto por su identificador.
     *
     * Devuelve null si no existe.
     */
    public function findById(int $id): ?Producto;
    
    /**
     * Devuelve todos los productos asociados a un proveedor específico.
     */
    public function findByProveedor(int $proveedorId): array;
    
    /**
     * Devuelve todos los productos del sistema.
     *
     * @return Producto[]
     */
    public function findAll(): array;


   /**
     * Devuelve productos que pertenecen a un conjunto de categorías.
     *
     * Se utiliza principalmente para búsquedas recursivas
     * por ramas del árbol de categorías.
     *
     * @param int[] $ids
     * @return Producto[]
     */
    public function findByCategoriaIds(array $ids): array;
    
    /**
     * Devuelve todos los productos que no tienen stock disponible.
     *  @return Producto[] */ 
    public function findSinStock(): array;


    /**
     * Devuelve productos con precio menor al valor indicado.
     * @return Producto[] */
    public function findByPrecioMenorQue(float $v): array;
    
    /**
     * Devuelve productos con precio mayor al valor indicado.
     *  @return Producto[] */ 
    public function findByPrecioMayorQue(float $v): array;

    /**
     * Reactiva un producto si corresponde según las reglas de negocio.
     *
     * Normalmente se utiliza luego de:
     * - Recibir un pedido
     * - Incrementar stock
     *
     * La implementación debe verificar:
     * - Estado manual
     * - Disponibilidad de stock
     */
    public function reactivarSiCorresponde(int $productoId): void;
    

}
