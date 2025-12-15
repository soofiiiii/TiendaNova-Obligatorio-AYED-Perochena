<?php
/**
 * @var array $reporte Datos del reporte: ['nombre' => string, 'total' => float, 'desglose' => array]
 * @var Categoria[] $categoriasRaiz Lista de categorías raíz para el selector.
 * @var int $categoriaSeleccionadaId ID de la categoría actualmente mostrada.
 */
$titulo = "Reporte de Inventario: " . htmlspecialchars($reporte['nombre'] ?? 'Seleccionar Categoría');

// Función auxiliar para imprimir el desglose recursivamente
function imprimirDesglose(array $desglose, int $nivel = 0): string {
    $html = '<ul class="border-l border-gray-200 ml-4 pl-4">';
    $indentacion = 'ml-' . (4 * $nivel + 4);
    
    foreach ($desglose as $nombre => $data) {
        $total = $data['total'] ?? $data; // Puede ser un total directo o un array con subcategorias
        $esSubrama = is_array($data) && isset($data['subcategorias']);
        
        // Determinar el estilo basado en si es una subcategoría o el total directo de productos
        $estiloTotalDirecto = $nombre === 'Productos Directos' ? 'text-sm text-gray-500 italic' : 'font-medium text-gray-700';

        $html .= '<li class="py-2">';
        $html .= '<div class="flex justify-between items-center">';
        $html .= '<span class="' . $estiloTotalDirecto . '">' . htmlspecialchars($nombre) . '</span>';
        $html .= '<span class="font-semibold text-gray-900">USD ' . number_format($total, 2) . '</span>';
        $html .= '</div>';
        
        // Si tiene subcategorías, imprimir recursivamente
        if ($esSubrama && !empty($data['subcategorias'])) {
            $html .= imprimirDesglose($data['subcategorias'], $nivel + 1);
        }
        $html .= '</li>';
    }
    $html .= '</ul>';
    return $html;
}
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900"><?= $titulo ?></h1>
</div>

<div class="bg-white rounded-xl shadow-lg p-6 mb-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
        <div class="mb-4 sm:mb-0">
            <label for="categoria_select" class="block text-sm font-medium text-gray-700 mb-1">
                Seleccionar Categoría Raíz
            </label>
            <select id="categoria_select" 
                    onchange="window.location.href = '/estadisticas/reporte/' + this.value"
                    class="appearance-none bg-gray-50 border border-gray-300 text-gray-900 text-base py-2 px-4 pr-8 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">-- Seleccionar --</option>
                <?php foreach ($categoriasRaiz as $cat): ?>
                    <option 
                        value="<?= $cat->getId() ?>"
                        <?= $cat->getId() == $categoriaSeleccionadaId ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($cat->getNombre()) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
            </div>
        </div>
        
        <div class="text-right">
            <p class="text-sm font-medium text-indigo-600 mb-1">Total de Inventario de la Rama</p>
            <p class="text-4xl font-extrabold text-gray-900">
                USD <?= number_format($reporte['total'] ?? 0, 2) ?>
            </p>
        </div>
    </div>
</div>

<?php if (isset($reporte['desglose'])): ?>
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Desglose Detallado</h3>
        
        <div class="max-h-96 overflow-y-auto pr-4">
            <?= imprimirDesglose($reporte['desglose']) ?>
        </div>
        
        <p class="text-xs text-gray-400 mt-4">
            * El desglose se basa en el valor provisional de `getPrecio()`.
        </p>
    </div>
<?php elseif ($categoriaSeleccionadaId): ?>
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 p-4 rounded-xl shadow">
        <p class="font-semibold flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.332 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            No se encontraron productos ni subcategorías para la rama: **<?= htmlspecialchars($reporte['nombre'] ?? 'N/A') ?>**
        </p>
    </div>
<?php endif; ?>