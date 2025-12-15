<?php
namespace Presentation\Helpers;

/**
 * HtmlHelper
 *
 * Clase utilitaria para generar fragmentos HTML reutilizables
 * desde la capa de presentación.
 *
 * Su objetivo es:
 * - Evitar duplicación de HTML en las vistas
 * - Mantener las vistas más limpias y legibles
 */
use Domain\Entities\Categoria;

final class HtmlHelper
{
    /**
     * Imprime una lista de <option> para un <select> de categorías.
     *
     * Recorre un árbol de categorías y genera las opciones
     * con indentación visual según el nivel jerárquico.
     *
     * @param Categoria[] $roots      Categorías raíz del árbol
     * @param int|null    $selectedId ID de la categoría seleccionada (opcional)
     */
    public static function renderCategoriaOptions(array $roots, ?int $selectedId = null): void
    {
        /**
         * Función recursiva interna.
         *
         * Recorre el árbol de categorías en profundidad (DFS)
         * y va imprimiendo las opciones con indentación.
         */
        $pinta = function (Categoria $n, int $indent = 0) use (&$pinta, $selectedId) {

            // Prefijo visual según el nivel del nodo
            $pad = str_repeat('— ', $indent);

            // Marca la opción como seleccionada si coincide el ID
            $sel = ($selectedId !== null && $n->getId() === $selectedId) ? ' selected' : '';

            // Se imprime la opción HTML correspondiente a la categoría
            echo '<option value="' . $n->getId() . '"' . $sel . '>' . $pad . htmlspecialchars($n->getNombre()) . '</option>';

            // Se recorren recursivamente las subcategorías
            foreach ($n->getSubcategorias() as $h)
                $pinta($h, $indent + 1);
        };

        // Se inicia el recorrido desde las categorías raíz
        foreach ($roots as $r)
            $pinta($r);
    }

    /**
     * Genera un badge visual (pill) con texto.
     *
     * Se utiliza para etiquetas pequeñas como:
     * - estados
     * - categorías
     * - indicadores visuales
     */
    public static function badge(string $text): string
    {
        return '<span class="pill">' . htmlspecialchars($text) . '</span>';
    }
}
