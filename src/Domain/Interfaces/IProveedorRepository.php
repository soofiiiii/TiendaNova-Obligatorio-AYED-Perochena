<?php
namespace Domain\Interfaces;
use Domain\Entities\Proveedor;

/**
 * Interface IProveedorRepository
 *
 * Define el contrato para la persistencia y consulta de proveedores.
 * Centraliza las operaciones CRUD y las consultas relacionadas
 * con los productos que suministra cada proveedor.
 */
interface IProveedorRepository
{
    /**
     * Crea un nuevo proveedor.
     *
     * Devuelve el ID generado para el proveedor.
     */
    public function create(string $empresa, string $contacto, string $tel, string $email, string $dir): int;
    
    /**
     * Actualiza los datos de un proveedor existente.
     */
    public function update(int $id, string $empresa, string $contacto, string $tel, string $email, string $dir): void;
    
    /**
     * Elimina un proveedor del sistema.
     *
     * La implementación debe validar previamente que:
     * - No existan productos asociados
     * - O que se manejen correctamente las relaciones
     */
    public function delete(int $id): void;

    /**
     * Busca un proveedor por su identificador.
     *
     * Devuelve null si no existe.
     */
    public function findById(int $id): ?Proveedor;

    /**
     * Devuelve todos los proveedores registrados.
     *
     * @return Proveedor[]
     */
    public function findAll(): array;

    /**
     * Cuenta la cantidad de productos asociados a un proveedor.
     *
     * Se utiliza para estadísticas o validaciones
     * previas a operaciones críticas (como eliminación).
     */
    public function contarProductosAsociados(int $id): int;

    /**
     * Devuelve todos los productos asociados a un proveedor.
     *
     * Permite listar el catálogo suministrado por una empresa
     * sin exponer directamente la lógica interna del dominio.
     */
    public function getProductos(int $proveedorId): array;

}
