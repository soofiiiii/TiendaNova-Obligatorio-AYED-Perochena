<?php
/** @var \Domain\Entities\Producto $producto */
$cat   = $producto->getCategoria();
$prov  = $producto->getProveedor();
?>

<div class="max-w-3xl mx-auto bg-white shadow rounded-xl p-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-4">
        Eliminar producto #<?= $producto->getId() ?>
    </h1>

    <p class="text-sm text-gray-600 mb-4">
        Estás a punto de eliminar este producto del catálogo. Esta acción no se puede deshacer.
    </p>

    <div class="border border-red-100 bg-red-50 rounded-lg p-4 mb-6">
        <h2 class="text-sm font-semibold text-red-700 mb-2">
            Resumen del producto
        </h2>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <div>
                <dt class="font-medium text-gray-700">Nombre</dt>
                <dd class="text-gray-900"><?= htmlspecialchars($producto->getNombre()) ?></dd>
            </div>

            <div>
                <dt class="font-medium text-gray-700">Precio</dt>
                <dd class="text-gray-900">$ <?= number_format($producto->getPrecio(), 2, ',', '.') ?></dd>
            </div>

            <div>
                <dt class="font-medium text-gray-700">Categoría</dt>
                <dd class="text-gray-900">
                    #<?= $cat->getId() ?> – <?= htmlspecialchars($cat->getNombre()) ?>
                </dd>
            </div>

            <div>
                <dt class="font-medium text-gray-700">Proveedor</dt>
                <dd class="text-gray-900">
                    #<?= $prov->getId() ?> – <?= htmlspecialchars($prov->getNombreEmpresa()) ?>
                </dd>
            </div>

            <div>
                <dt class="font-medium text-gray-700">Fecha de alta</dt>
                <dd class="text-gray-900">
                    <?= $producto->getFechaRegistro()->format('Y-m-d H:i') ?>
                </dd>
            </div>

            <div>
                <dt class="font-medium text-gray-700">Estado</dt>
                <dd>
                    <span class="inline-flex px-2 py-1 rounded-full text-xs
                        <?= $producto->isActivo() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' ?>">
                        <?= $producto->isActivo() ? 'Activo' : 'Inactivo' ?>
                    </span>
                </dd>
            </div>
        </dl>
    </div>

    <form action="<?= BASE_PATH ?>/productos/eliminar" method="POST" class="flex justify-between items-center">
        <input type="hidden" name="id" value="<?= $producto->getId() ?>">
        <input type="hidden" name="confirm" value="1">

        <a href="<?= BASE_PATH ?>/productos"
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
            Cancelar
        </a>

        <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg shadow hover:bg-red-700">
            Sí, eliminar definitivamente
        </button>
    </form>
</div>
