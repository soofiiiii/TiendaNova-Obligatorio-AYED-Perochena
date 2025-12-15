<?php
namespace Domain\Entities;

/**
 * Clase Producto
 *
 * Representa un producto del catálogo.
 * Contiene información comercial, relaciones con categoría y proveedor,
 * y lógica de estado basada en stock y activación manual.
 */
final class Producto
{
    // Identificador único del producto
    private int $id;

    // Nombre visible del producto
    private string $nombre;

    // Descripción opcional
    private ?string $descripcion;

    // Precio actual del producto (puede verse afectado por descuentos)
    private float $precio;

    // Categoría a la que pertenece el producto
    private ?Categoria $categoria;

    // Proveedor que suministra el producto
    private ?Proveedor $proveedor;

    // Fecha de alta en el sistema (inmutable)
    private \DateTimeImmutable $fechaRegistro;

    // Stock asociado al producto (puede no existir aún)
    private ?Stock $stock = null;

    // Porcentaje de descuento aplicado (si existe)
    private ?int $descuento;

    // Precio original antes de aplicar descuentos
    private ?float $precioOriginal;

    // Estado manual del producto (activado/desactivado por el usuario)
    private bool $activoManual;

    
    /**
     * Constructor
     *
     * Inicializa el producto con todas sus propiedades.
     * La fecha de registro se asigna automáticamente si no se provee.
     */
    public function __construct(
        int $id,
        string $nombre,
        ?string $descripcion,
        float $precio,
        ?Stock $stock = null,
        ?Categoria $categoria = null,
        ?Proveedor $proveedor = null,
        ?\DateTimeImmutable $fechaRegistro = null,
        ?int $descuento = null,
        ?float $precioOriginal = null,
        bool $activoManual = true
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->precio = $precio;
        $this->stock = $stock;
        $this->categoria = $categoria;
        $this->proveedor = $proveedor;
        $this->fechaRegistro = $fechaRegistro ?? new \DateTimeImmutable('now');
        $this->descuento = $descuento;
        $this->precioOriginal = $precioOriginal;
        $this->activoManual = $activoManual;
    }

    
    // MÉTODO DE FÁBRICA (uso específico en repositorios)


    /**
     * Crea un producto a partir de datos mínimos provenientes de un proveedor.
     *
     * Se utiliza como método estático para desacoplar la lógica de creación
     * de los repositorios y evitar constructores excesivamente largos.
     */
    public static function crearDesdeProveedor(
        int $id,
        string $nombre,
        ?string $descripcion,
        float $precio,
        string $fechaRegistro,
        bool $activo
    ): self {
        return new self(
            id: $id,
            nombre: $nombre,
            descripcion: $descripcion,
            precio: $precio,
            stock: null,
            categoria: null,
            proveedor: null,
            fechaRegistro: new \DateTimeImmutable($fechaRegistro),
            descuento: null,
            precioOriginal: null
        );
    }



    // MÉTODOS DE NEGOCIO

    /**
     * Cambia la categoría del producto.
     */
    public function cambiarCategoria(Categoria $nuevaCategoria): void
    {
        $this->categoria = $nuevaCategoria;
    }


    /**
     * Aplica un descuento porcentual al precio actual.
     *
     * Ejemplo:
     * 10 → 10% de descuento.
     */
    public function aplicarDescuento(float $porcentaje): void
    {
        // Validación del rango permitido
        if ($porcentaje < 0 || $porcentaje > 100) {
            throw new \InvalidArgumentException("El porcentaje debe ser entre 0 y 100.");
        }

        // Cálculo del monto de descuento
        $descuento = $this->precio * ($porcentaje / 100);

        // Se actualiza el precio final
        $this->precio -= $descuento;
    }


    /**
     * Estado efectivo del producto.
     *
     * Un producto es considerado activo solo si:
     * - Está activado manualmente
     * - Tiene stock asociado
     * - La cantidad de stock es mayor a cero
     */
     public function isActivo(): bool
    {
        // Si fue desactivado manualmente, no está activo
        if (!$this->activoManual) {
            return false;
        }

        // Si no tiene stock asociado, no puede venderse
        if ($this->stock === null) {
            return false;
        }

        // Se verifica que haya unidades disponibles
        return $this->stock->getCantidad() > 0;
    }


    /**
     * Devuelve el estado manual del producto.
     */
    public function isActivoManual(): bool
    {
        return $this->activoManual;
    }

    /**
     * Permite activar o desactivar el producto manualmente.
     */
    public function setActivoManual(bool $v): void
    {
        $this->activoManual = $v;
    }


    // GETTERS Y SETTERS

    public function getId(): int
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function getPrecio(): float
    {
        return $this->precio;
    }

    /**
     * Actualiza el precio del producto.
     * No se permiten valores negativos.
     */
    public function setPrecio(float $precio): void
    {
        if ($precio < 0) {
            throw new \InvalidArgumentException("El precio no puede ser negativo.");
        }
        $this->precio = $precio;
    }


    public function getCategoria(): Categoria
    {
        return $this->categoria;
    }

    public function getProveedor(): Proveedor
    {
        return $this->proveedor;
    }

    public function getFechaRegistro(): \DateTimeImmutable
    {
        return $this->fechaRegistro;
    }

    /**
     * Asocia un stock al producto.
     */
    public function setStock(Stock $stock): void
    {
        $this->stock = $stock;
    }

    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    public function getDescuento(): ?int
    {
        return $this->descuento;
    }

    public function getPrecioOriginal(): ?float
    {
        return $this->precioOriginal;
    }

    public function setPrecioOriginal(?float $v): void
    {
        $this->precioOriginal = $v;
    }

    /**
     * Actualiza el nombre del producto.
     * No se permiten nombres vacíos.
     */
    public function setNombre(string $nombre): void
    {
        if (trim($nombre) === '') {
            throw new \InvalidArgumentException("El nombre no puede estar vacío.");
        }
        $this->nombre = $nombre;
    }

    public function setDescripcion(?string $descripcion): void
    {
        $this->descripcion = $descripcion;
    }

    public function setCategoria(?Categoria $categoria): void
    {
        $this->categoria = $categoria;
    }

    public function setProveedor(?Proveedor $proveedor): void
    {
        $this->proveedor = $proveedor;
    }

    // REPRESENTACIÓN TEXTUAL

    /**
     * Representación textual del producto.
     *
     * Útil para listados, logs o depuración.
     */
    public function __toString(): string
    {
        $categoria = $this->categoria
            ? $this->categoria->getNombre()
            : 'Sin categoría';

        $proveedor = $this->proveedor
            ? $this->proveedor->getNombreEmpresa()
            : 'Sin proveedor';

        return sprintf(
            "%s (#%d) - $%0.2f | Categoría: %s | Proveedor: %s",
            $this->nombre,
            $this->id,
            $this->precio,
            $categoria,
            $proveedor
        );
    }


}
