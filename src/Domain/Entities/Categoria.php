<?php
namespace Domain\Entities;

/**
 * Clase Categoria
 *
 * Representa un nodo dentro del árbol N-ario de categorías.
 * Cada instancia puede tener una categoría padre y múltiples subcategorías hijas.
 */
final class Categoria
{
    // Identificador único de la categoría
    private int $id;

    // Nombre visible de la categoría
    private string $nombre;

    // Descripción opcional
    private ?string $descripcion;

    // Referencia a la categoría padre (null si es raíz)
    private ?Categoria $categoriaPadre;


    /**
     * Subcategorías hijas.
     * Se almacena como array asociativo para acceso rápido por ID.
     *
     * @var Categoria[]
     */
    private array $subcategorias = [];

    // Nivel de profundidad dentro del árbol (0 = raíz)
    private int $nivel;


    /**
     * Constructor
     *
     * Inicializa la categoría con sus datos básicos y su posición en el árbol.
     */
    public function __construct(int $id, string $nombre, ?string $descripcion = null, ?Categoria $padre = null, int $nivel = 0)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->categoriaPadre = $padre;
        $this->nivel = $nivel;
    }

    // Relaciones en memoria


    /**
     * Agrega una subcategoría hija a esta categoría.
     * La relación se mantiene completamente en memoria.
     */
    public function agregarSubcategoria(Categoria $hija): void
    {
        // Se usa el ID como clave para evitar duplicados
        $this->subcategorias[$hija->id] = $hija;
    }

    /**
     * Elimina una subcategoría hija por su ID.
     */
    public function eliminarSubcategoria(int $id): void
    {
        unset($this->subcategorias[$id]);
    }

    // Algoritmos sobre el árbol

    /**
     * Muestra el árbol completo a partir de este nodo usando recorrido pre-orden.
     *
     * - Primero se muestra el nodo actual
     * - Luego se recorren recursivamente las subcategorías
     *
     * @param int $indent Nivel de indentación visual
     * @param callable|null $getCantidadProductos Callback para obtener productos por categoría
     */
    public function mostrarArbol(
        int $indent = 0,
        ?callable $getCantidadProductos = null
    ): string {
        // Prefijo visual para representar la jerarquía
        $prefix = str_repeat('— ', $indent);

        // Cantidad de productos de esta categoría
        $cantidad = 0;
        if ($getCantidadProductos !== null) {
            $cantidad = $getCantidadProductos($this->id);
        }

        // Se arma la línea correspondiente a este nodo
        $out = sprintf(
            "%s [nivel %d] %s (%d productos)\n",
            $prefix,
            $this->nivel,
            $this->nombre,
            $cantidad
        );

        // Llamada recursiva a cada subcategoría (pre-orden)
        foreach ($this->subcategorias as $hija) {
            $out .= $hija->mostrarArbol($indent + 1, $getCantidadProductos);
        }

        return $out;
    }

    /*
     * Búsqueda en profundidad (DFS) por ID.
     *
     * Recorre el subárbol completo hasta encontrar la categoría buscada.
     */
    public function buscarPorId(int $id): ?Categoria
    {
        // Caso base: el nodo actual es el buscado
        if ($this->id === $id)
            return $this;

        // Caso recursivo: buscar en cada subcategoría
        foreach ($this->subcategorias as $hija) {
            $encontrada = $hija->buscarPorId($id);
            
            // Si se encuentra en alguna rama, se retorna inmediatamente
            if ($encontrada)
                return $encontrada;
        }

        // Si no se encuentra en este subárbol
        return null;
    }

    /**
     * Devuelve la ruta completa desde la raíz hasta esta categoría.
     *
     * Ejemplo:
     * Electrónica / Computadoras / Laptops
     */
    public function getRutaCompleta(): string
    {
        $nodo = $this;
        $ruta = [];

        // Se sube por los padres hasta llegar a la raíz
        while ($nodo) {
            // Se inserta al inicio para mantener el orden raíz → hoja
            array_unshift($ruta, $nodo->nombre);
            $nodo = $nodo->categoriaPadre;
        }
        return implode(' / ', $ruta);
    }


    /**
     * Cuenta recursivamente la cantidad total de productos
     * de esta categoría y todas sus subcategorías.
     */
    public function contarProductosTotales(callable $cantidadProductosPorCategoria): int
    {
        // Productos propios de esta categoría
        $total = $cantidadProductosPorCategoria($this->id) ?? 0;

        // Suma recursiva de los productos de las subcategorías
        foreach ($this->subcategorias as $hija) {
            $total += $hija->contarProductosTotales($cantidadProductosPorCategoria);
        }
        return $total;
    }

    /**
     * Indica si la categoría es hoja (no tiene subcategorías).
     */
    public function esHoja(): bool
    {
        return empty($this->subcategorias);
    }

    // Getters

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
    public function getCategoriaPadre(): ?Categoria
    {
        return $this->categoriaPadre;
    }
    /** @return Categoria[] */
    public function getSubcategorias(): array
    {
        return array_values($this->subcategorias);
    }
    public function getNivel(): int
    {
        return $this->nivel;
    }

    // Setters controlados

    /**
     * Actualiza la referencia al padre (usado al mover categorías).
     */
    public function setCategoriaPadre(?Categoria $padre): void
    {
        $this->categoriaPadre = $padre;
    }

    /**
     * Actualiza el nivel dentro del árbol.
     */
    public function setNivel(int $nivel): void
    {
        $this->nivel = $nivel;
    }
}