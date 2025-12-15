<?php 
use Domain\Entities\Categoria;

/** @var Categoria $cat */
/** @var Categoria[] $roots */

$titulo = "Editar categoría";
$action = BASE_PATH . "/categorias/editar";
$boton  = "Guardar cambios";

$parentActual = $cat->getCategoriaPadre();
$parentIdActual = $parentActual ? $parentActual->getId() : null;

/**
 * Devuelve true si $id está en el subárbol cuya raíz es $raiz
 * (incluyendo la propia raíz).
 */
$enSubarbol = function (Categoria $raiz, int $id) use (&$enSubarbol): bool {
    if ($raiz->getId() === $id) {
        return true;
    }
    foreach ($raiz->getSubcategorias() as $hija) {
        if ($enSubarbol($hija, $id)) {
            return true;
        }
    }
    return false;
};

/**
 * Renderiza las opciones del select de categorías con indentación,
 * excluyendo la categoría actual y todo su subárbol.
 *
 * @param Categoria[] $categorias
 */
$renderOptions = function (
    array $categorias, 
    int $nivel, 
    ?int $parentIdActual, 
    Categoria $actual,
    callable $enSubarbol
) use (&$renderOptions) {
    foreach ($categorias as $c) {
        // No permitir elegir como padre ni a la propia categoría ni a sus hijos
        if ($enSubarbol($actual, $c->getId())) {
            continue;
        }

        $indent   = str_repeat('— ', $nivel);
        $label    = $indent . htmlspecialchars($c->getNombre(), ENT_QUOTES, 'UTF-8');
        $selected = $parentIdActual === $c->getId() ? 'selected' : '';

        echo '<option value="' . $c->getId() . '" ' . $selected . '>' . $label . '</option>';

        $children = $c->getSubcategorias();
        if (!empty($children)) {
            $renderOptions($children, $nivel + 1, $parentIdActual, $actual, $enSubarbol);
        }
    }
};
?>

<div class="max-w-3xl mx-auto bg-white shadow rounded-xl p-8">

    <h1 class="text-2xl font-bold text-gray-900 mb-4"><?= $titulo ?></h1>

    <!-- Info rápida de la categoría -->
    <div class="mb-6 flex flex-wrap gap-2 text-sm text-gray-600">
        <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 border border-gray-200">
            ID: <?= $cat->getId() ?>
        </span>
        <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 border border-gray-200">
            Nivel: <?= $cat->getNivel() ?>
        </span>
        <span class="inline-flex items-center px-2 py-1 rounded-full bg-indigo-50 border border-indigo-100">
            Ruta: <?= htmlspecialchars($cat->getRutaCompleta(), ENT_QUOTES, 'UTF-8') ?>
        </span>
    </div>

    <form action="<?= $action ?>" method="POST" class="space-y-6">

        <input type="hidden" name="id" value="<?= $cat->getId() ?>">

        <!-- Nombre -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Nombre
            </label>
            <input type="text"
                   name="nombre"
                   value="<?= htmlspecialchars($cat->getNombre(), ENT_QUOTES, 'UTF-8') ?>"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                   required>
            <p class="mt-1 text-xs text-gray-500">
                Usa un nombre claro y representativo para esta categoría.
            </p>
        </div>

        <!-- Descripción -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Descripción (opcional)
            </label>
            <textarea name="descripcion"
                      rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="Descripción breve de la categoría..."><?= htmlspecialchars($cat->getDescripcion() ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <!-- Categoría padre -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Categoría padre
            </label>
            <select name="parent_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Sin categoría padre (será raíz)</option>

                <?php if (!empty($roots)): ?>
                    <?php $renderOptions($roots, 0, $parentIdActual, $cat, $enSubarbol); ?>
                <?php endif; ?>
            </select>
            <p class="mt-1 text-xs text-gray-500">
                Elige en qué rama del árbol debe vivir esta categoría. 
                No se permite moverla dentro de su propia rama para evitar ciclos.
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
