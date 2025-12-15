<?php
namespace Application;

use Domain\Interfaces\IProveedorRepository;
use Domain\Entities\Proveedor;

/**
 * Clase ProveedorService
 *
 * Servicio de aplicación encargado de orquestar
 * los casos de uso relacionados con proveedores.
 *
 * Esta capa:
 * - Recibe datos desde controladores (DTOs)
 * - Coordina operaciones
 * - Delega persistencia al repositorio
 *
 * No contiene lógica de infraestructura ni de presentación.
 */
final class ProveedorService
{
    /**
     * El servicio depende de la interfaz del repositorio,
     * no de una implementación concreta.
     *
     * Esto mantiene el desacoplamiento y permite cambiar
     * la forma de persistencia sin afectar la lógica.
     */
    public function __construct(private IProveedorRepository $repo)
    {
    }

    /**
     * Devuelve todos los proveedores del sistema.
     *
     * @return Proveedor[]
     */
    public function listarTodos(): array
    {
        return $this->repo->findAll();
    }


    /**
     * Obtiene un proveedor por su ID.
     *
     * Devuelve null si no existe.
     */
    public function obtenerPorId(int $id): ?Proveedor
    {
        return $this->repo->findById($id);
    }


    /**
     * Crea un nuevo proveedor a partir de un DTO.
     *
     * El DTO suele provenir de un formulario o request.
     * Este método se encarga de mapear los datos al repositorio.
     */
    public function crear(array $dto): int
    {
        return $this->repo->create(
            $dto['nombre_empresa'],
            $dto['contacto'],
            $dto['telefono'],
            $dto['email'],
            $dto['direccion']
        );
    }

    /**
     * Actualiza los datos de un proveedor existente.
     *
     * Recibe un DTO con los nuevos valores.
     */
    public function actualizar(int $id, array $dto): void
    {
        $this->repo->update(
            $id,
            $dto['nombre_empresa'],
            $dto['contacto'],
            $dto['telefono'],
            $dto['email'],
            $dto['direccion']
        );
    }


    /**
     * Elimina un proveedor.
     *
     * Las validaciones previas (por ejemplo,
     * productos asociados) se delegan al repositorio.
     */
    public function eliminar(int $id): void
    {
        $this->repo->delete($id);
    }

    /**
     * Cuenta la cantidad de productos asociados a un proveedor.
     *
     * Útil para:
     * - Validar eliminación
     * - Mostrar estadísticas
     */
    public function contarProductosAsociados(int $id): int
    {
        return $this->repo->contarProductosAsociados($id);
    }

    /**
     * Devuelve todos los productos de un proveedor.
     */
    public function productosDeProveedor(int $id): array
    {
        return $this->repo->getProductos($id);
    }

    /**
     * Devuelve la cantidad de productos asociados a un proveedor.
     *
     * Esta versión calcula el total a partir del listado,
     * útil cuando ya se necesitan los productos en memoria.
     */
    public function contarProductos(int $id): int
    {
        return count($this->repo->getProductos($id));
    }

}