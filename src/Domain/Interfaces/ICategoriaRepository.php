<?php
namespace Domain\Interfaces;

use Domain\Entities\Categoria;


/**
 * Interface ICategoriaRepository
 *
 * Define el contrato para el acceso y manipulación de categorías.
 * Abstrae completamente la forma en que se almacenan y organizan,
 * permitiendo trabajar con un árbol N-ario en memoria.
 */
interface ICategoriaRepository
{
    /**
     * Devuelve el árbol completo de categorías.
     *
     * Retorna únicamente los nodos raíz, pero cada uno de ellos
     * contiene sus subcategorías completamente hidratadas,
     * formando el árbol N-ario completo en memoria.
     *
     * @return Categoria[]
     */
    public function getFullTree(): array;

     /**
     * Busca una categoría por ID recorriendo el árbol en memoria.
     *
     * Implementa una búsqueda en profundidad (DFS).
     */
    public function findById(int $id): ?Categoria; // usando árbol en memoria
    
    /**
     * Busca una categoría por su nombre.
     *
     * La implementación puede recorrer el árbol completo
     * o usar una estructura auxiliar en memoria.
     */
    public function findByNombre(string $nombre): ?Categoria;

    /**
     * Crea una nueva categoría.
     *
     * - Si parentId es null, se crea como categoría raíz.
     * - Si parentId tiene valor, se crea como subcategoría.
     *
     * Retorna el ID de la categoría creada.
     */
    public function create(string $nombre, ?string $descripcion, ?int $parentId): int;
    
    /**
     * Actualiza los datos básicos de una categoría existente.
     */    
    public function update(int $id, string $nombre, ?string $descripcion): void;
    
    /**
     * Mueve una categoría a otro padre dentro del árbol.
     *
     * Esta operación implica:
     * - Reasignar el padre
     * - Recalcular niveles
     * - Mantener la coherencia del árbol N-ario
     */
    public function move(int $id, ?int $nuevoParentId): void;

    /**
     * Elimina una categoría.
     *
     * La implementación debe validar previamente que:
     * - No tenga subcategorías
     * - No tenga productos asociados
     */
    public function delete(int $id): void;

    /**
     * Devuelve un mapa con la cantidad de productos por categoría.
     *
     * Formato:
     * [ categoria_id => cantidad_de_productos_directos ]
     *
     * Se utiliza para optimizar cálculos recursivos y evitar
     * recomputar conteos repetidamente.
     */
    public function countsByCategoria(): array;

    /**
     * Cuenta la cantidad total de productos en una rama completa
     * del árbol, partiendo desde la categoría indicada.
     *
     * Incluye productos de la categoría y de todas sus subcategorías.
     */
    public function contarProductosEnRama(int $id): int;

    /**
     * Cuenta la cantidad de subcategorías directas de una categoría.
     *
     * No incluye subcategorías en niveles inferiores.
     */
    public function contarSubcategoriasDirectas(int $id): int;

}