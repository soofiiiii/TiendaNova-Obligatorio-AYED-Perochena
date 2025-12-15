<?php
/** @var \Domain\Entities\Categoria $categoria */
/** @var int|null $totalProductos En la rama, opcional */
/** @var int|null $totalSubcategorias Opcional */

$padre          = $categoria->getCategoriaPadre();
$rutaCompleta   = method_exists($categoria, 'getRutaCompleta')
    ? $categoria->getRutaCompleta()
    : $categoria->getNombre();

$nivel          = $categoria->getNivel();
$descripcion    = $categoria->getDescripcion();
$subcategorias  = $categoria->getSubcategorias();
$cantSubcats    = isset($totalSubcategorias)
    ? (int)$totalSubcategorias
    : count($subcategorias);

$cantProductos  = isset($totalProductos) ? (int)$totalProductos : null;
?>

<div class="max-w-3xl mx-auto bg-white shadow rounded-xl p-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-4">
        Eliminar categoría #<?= $categoria->getId() ?>
    </h1>

    <p class="text-sm text-gray-600 mb-4">
        Estás a punto de eliminar esta categoría del árbol. Esta acción no se puede deshacer.
    </p>

    <!-- Panel principal de información -->
    <div class="border border-red-100 bg-red-50 rounded-lg p-4 mb-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-red-800 mb-1">
                    <?= htmlspecialchars($categoria->getNombre()) ?>
                </h2>
                <?php if ($descripcion): ?>
                    <p class="text-sm text-red-900/80">
                        <?= nl2br(htmlspecialchars($descripcion)) ?>
                    </p>
                <?php else: ?>
                    <p class="text-sm text-red-900/70 italic">
                        (Sin descripción)
                    </p>
                <?php endif; ?>
            </div>

            <div class="text-right space-y-1">
                <p class="text-xs uppercase tracking-wide text-red-700/80 font-semibold">
                    Nivel en el árbol
                </p>
                <p class="text-lg font-semibold text-red-900">
                    <?= $nivel ?>
                </p>

                <p class="text-xs uppercase tracking-wide text-red-700/80 font-semibold mt-3">
                    ID interno
                </p>
                <p class="font-mono text-sm text-red-900">
                    #<?= $categoria->getId() ?>
                </p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-red-900">
            <div>
                <p class="text-xs uppercase tracking-wide text-red-700/80 font-semibold mb-1">
                    Ruta completa
                </p>
                <p class="font-medium">
                    <?= htmlspecialchars($rutaCompleta) ?>
                </p>
            </div>

            <div>
                <p class="text-xs uppercase tracking-wide text-red-700/80 font-semibold mb-1">
                    Categoría padre
                </p>
                <p class="font-medium">
                    <?php if ($padre): ?>
                        <?= htmlspecialchars($padre->getNombre()) ?> (ID #<?= $padre->getId() ?>)
                    <?php else: ?>
                        <span class="italic">Es una categoría raíz</span>
                    <?php endif; ?>
                </p>
            </div>

            <div>
                <p class="text-xs uppercase tracking-wide text-red-700/80 font-semibold mb-1">
                    Subcategorías directas
                </p>
                <p class="font-medium">
                    <?= $cantSubcats ?>
                </p>
            </div>

            <div>
                <p class="text-xs uppercase tracking-wide text-red-700/80 font-semibold mb-1">
                    Productos en la rama
                </p>
                <p class="font-medium">
                    <?php if ($cantProductos !== null): ?>
                        <?= $cantProductos ?>
                    <?php else: ?>
                        <span class="italic text-red-900/70">No disponible</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Mensaje inteligente según restricciones -->
    <div class="mb-6">
        <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
            <li>
                Solo se puede eliminar una categoría que no tenga subcategorías ni productos en ningún nivel.
            </li>
            <li>
                Si la base de datos detecta subcategorías o productos asociados en la rama,
                se bloqueará la eliminación y se mostrará un mensaje de error.
            </li>
            <li>
                Esta operación no afecta a otras ramas del árbol.
            </li>
        </ul>
    </div>

    <!-- Formulario de confirmación final -->
    <form method="post" action="<?= BASE_PATH ?>/categorias/eliminar" class="flex justify-end gap-3">
        <input type="hidden" name="id" value="<?= $categoria->getId() ?>">
        <input type="hidden" name="confirm" value="1">

        <a href="<?= BASE_PATH ?>/categorias"
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
            Cancelar
        </a>

        <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg shadow hover:bg-red-700">
            Sí, eliminar definitivamente
        </button>
    </form>
</div>
