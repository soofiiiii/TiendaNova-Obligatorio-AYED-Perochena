<?php 
use Domain\Entities\Categoria;

$titulo = "Crear categoría";
$action = BASE_PATH . "/categorias/crear";
$boton  = "Crear categoría";

/** 
 * @param Categoria[] $categorias 
 * @param int         $nivel
 */
$renderOptions = function(array $categorias, int $nivel = 0) use (&$renderOptions) {
    foreach ($categorias as $c) {
        $indent = str_repeat('— ', $nivel);
        $label  = $indent . htmlspecialchars($c->getNombre(), ENT_QUOTES, 'UTF-8');

        echo '<option value="' . $c->getId() . '">' . $label . '</option>';

        $children = $c->getSubcategorias();
        if (!empty($children)) {
            $renderOptions($children, $nivel + 1);
        }
    }
};
?>

<div class="max-w-3xl mx-auto bg-white shadow rounded-xl p-8">

    <h1 class="text-2xl font-bold text-gray-900 mb-6"><?= $titulo ?></h1>

    <form action="<?= $action ?>" method="POST" class="space-y-6">

        <!-- Nombre -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
            <input type="text" name="nombre"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                   required>
            <p class="mt-1 text-xs text-gray-500">
                Usa un nombre corto y claro, por ejemplo: “Electrónica”, “Ropa Hombre”.
            </p>
        </div>

        <!-- Descripción -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción (opcional)</label>
            <textarea name="descripcion" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="Descripción breve de la categoría..."></textarea>
        </div>

        <!-- Categoría padre -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Categoría padre</label>
            <select name="parent_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Sin categoría padre (será raíz)</option>
                <?php if (!empty($roots)): ?>
                    <?php $renderOptions($roots); ?>
                <?php endif; ?>
            </select>
            <p class="mt-1 text-xs text-gray-500">
                Si eliges un padre, esta categoría quedará como subcategoría dentro de esa rama.
            </p>
        </div>

        <div class="flex justify-end pt-4 space-x-3">
            <a href="<?= BASE_PATH ?>/categorias"
               class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow hover:bg-indigo-700">
                <?= $boton ?>
            </button>
        </div>

    </form>

</div>
