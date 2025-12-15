<?php
namespace Domain\Entities;

/**
 * Clase Proveedor
 *
 * Representa una empresa proveedora de productos.
 * Mantiene sus datos de contacto y la relación con los productos
 * que suministra al sistema.
 */
final class Proveedor
{
    // Identificador único del proveedor
    private int $id;

    // Nombre de la empresa proveedora
    private string $nombreEmpresa;

    // Persona de contacto
    private string $contacto;

    // Teléfono de contacto
    private string $telefono;

    // Correo electrónico
    private string $email;

    // Dirección física
    private string $direccion;

    /**
     * Productos asociados a este proveedor.
     * Esta relación se mantiene en memoria.
     *
     * @var Producto[]
     */
    private array $productos = [];


    /**
     * Constructor
     *
     * Inicializa el proveedor con sus datos básicos.
     */
    public function __construct(
        int $id,
        string $nombreEmpresa,
        string $contacto,
        string $telefono,
        string $email,
        string $direccion
    ) {
        $this->id = $id;
        $this->nombreEmpresa = $nombreEmpresa;
        $this->contacto = $contacto;
        $this->telefono = $telefono;
        $this->email = $email;
        $this->direccion = $direccion;
    }

    // MÉTODOS DE NEGOCIO


    /**
     * Devuelve todos los productos asociados al proveedor.
     */
     public function getProductos(): array
    {
        return $this->productos;
    }

    /**
     * Cuenta la cantidad de productos que suministra el proveedor.
     */
    public function contarProductos(): int
    {
        return count($this->productos);
    }

    /**
     * Asocia un conjunto de productos al proveedor.
     *
     * Este método existe para permitir que el repositorio
     * hidrate la relación proveedor-productos sin romper
     * el encapsulamiento de la entidad.
     */
    public function setProductos(array $productos): void
    {
        $this->productos = $productos;
    }

    
    // Getters y Setters 

    public function getId(): int { return $this->id; }
    public function getNombreEmpresa(): string { return $this->nombreEmpresa; }
    public function getContacto(): string { return $this->contacto; }
    public function getTelefono(): string { return $this->telefono; }
    public function getEmail(): string { return $this->email; }
    public function getDireccion(): string { return $this->direccion; }

    public function setNombreEmpresa(string $v): void { $this->nombreEmpresa = $v; }
    public function setContacto(string $v): void { $this->contacto = $v; }
    public function setTelefono(string $v): void { $this->telefono = $v; }
    public function setEmail(string $v): void { $this->email = $v; }
    public function setDireccion(string $v): void { $this->direccion = $v; }
}
