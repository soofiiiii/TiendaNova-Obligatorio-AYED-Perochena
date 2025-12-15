<?php
use Domain\Entities\Categoria;
use Domain\Entities\Producto;

/** @var Producto $prod */
/** @var Categoria[] $cats */

$titulo = "Editar producto";
$action = BASE_PATH . "/productos/editar";
$boton = "Guardar cambios";

$categoriaActual = $prod->getCategoria();
$categoriaActualId = $categoriaActual->getId();

/**
 * Renderiza las opciones del select de categorías con indentación.
 *
 * @param Categoria[] $categorias
 */
$renderOptions = function (array $categorias, int $nivel, int $categoriaActualId) use (&$renderOptions) {
    foreach ($categorias as $c) {
        $indent = str_repeat('— ', $nivel);
        $label = $indent . htmlspecialchars($c->getNombre(), ENT_QUOTES, 'UTF-8');
        $selected = $c->getId() === $categoriaActualId ? 'selected' : '';

        echo '<option value="' . $c->getId() . '" ' . $selected . '>' . $label . '</option>';

        $children = $c->getSubcategorias();
        if (!empty($children)) {
            $renderOptions($children, $nivel + 1, $categoriaActualId);
        }
    }
};

$proveedor = $prod->getProveedor();
$fechaRegistro = $prod->getFechaRegistro()->format('Y-m-d H:i');

$descuentoActual = $prod->getDescuento();
$tieneDescuento = !empty($descuentoActual);

$stock = $prod->getStock();
$ubicacionActual = $stock ? $stock->getUbicacion() : 'DEP-1';

?>


<div class="max-w-4xl mx-auto bg-white shadow rounded-xl p-8">

    <h1 class="text-2xl font-bold text-gray-900 mb-4"><?= $titulo ?></h1>

    <!-- Info rápida del producto -->
    <div class="mb-6 flex flex-wrap gap-2 text-sm text-gray-600">
        <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 border border-gray-200">
            ID: <?= $prod->getId() ?>
        </span>
        <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 border border-gray-200">
            Registrado: <?= htmlspecialchars($fechaRegistro, ENT_QUOTES, 'UTF-8') ?>
        </span>
        <span class="inline-flex items-center px-2 py-1 rounded-full bg-indigo-50 border border-indigo-100">
            Categoría actual: <?= htmlspecialchars($categoriaActual->getRutaCompleta(), ENT_QUOTES, 'UTF-8') ?>
        </span>
        <span class="inline-flex items-center px-2 py-1 rounded-full bg-emerald-50 border border-emerald-100">
            Proveedor actual: <?= htmlspecialchars($proveedor->getNombreEmpresa(), ENT_QUOTES, 'UTF-8') ?>
        </span>
    </div>

    <form action="<?= $action ?>" method="POST" class="space-y-8">

        <input type="hidden" name="id" value="<?= $prod->getId() ?>">

        <!-- Datos básicos -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                <input type="text" name="nombre"
                    value="<?= htmlspecialchars($prod->getNombre(), ENT_QUOTES, 'UTF-8') ?>"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    required>
                <p class="mt-1 text-xs text-gray-500">
                    Nombre comercial que verá el cliente.
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Precio</label>
                <input type="number" name="precio" step="0.01" min="0"
                    value="<?= htmlspecialchars((string) $prod->getPrecio(), ENT_QUOTES, 'UTF-8') ?>"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    required>
                <p class="mt-1 text-xs text-gray-500">
                    Precio unitario del producto (ej. 1499.99).
                </p>
            </div>

        </div>

        <!-- Descripción -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción (opcional)</label>
            <textarea name="descripcion" rows="3"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Descripción breve del producto..."><?= htmlspecialchars($prod->getDescripcion() ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <!-- Clasificación -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Categoría -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                <select name="categoria_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    required>
                    <?php if (!empty($cats)): ?>
                        <?php $renderOptions($cats, 0, $categoriaActualId); ?>
                    <?php endif; ?>
                </select>
                <p class="mt-1 text-xs text-gray-500">
                    Selecciona la categoría donde se mostrará el producto.
                </p>
            </div>

            <!-- Proveedor -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Proveedor</label>

                <select name="proveedor_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    required>

                    <?php foreach ($proveedores as $p): ?>
                        <option value="<?= $p->getId() ?>" <?= $p->getId() === $proveedor->getId() ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p->getNombreEmpresa(), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>

                </select>

                <p class="mt-1 text-xs text-gray-500">
                    Proveedor actual: <b><?= htmlspecialchars($proveedor->getNombreEmpresa()) ?></b>
                </p>
            </div>


            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Ubicación en depósito
                </label>
                <input type="text" name="ubicacion"
                    value="<?= htmlspecialchars($ubicacionActual, ENT_QUOTES, 'UTF-8') ?>"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>


        </div>

        <!-- Aplicar descuento -->
        <div class="mt-6">
            <label class="inline-flex items-center space-x-2">
                <input type="checkbox" id="chk-descuento" name="aplicar_descuento" value="1" <?= $tieneDescuento ? 'checked' : '' ?> class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm text-gray-700">En descuento</span>
            </label>

            <!-- input oculto -->
            <div id="box-descuento" class="<?= $tieneDescuento ? '' : 'hidden' ?> mt-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Porcentaje de descuento
                </label>
                <input type="number" name="descuento" min="1" max="100"
                    value="<?= $tieneDescuento ? htmlspecialchars($descuentoActual) : '' ?>"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ej: 15">
                <p class="mt-1 text-xs text-gray-500">
                    Ingresa el porcentaje a descontar sobre el precio base.
                </p>
            </div>
        </div>

        <script>
            const chk = document.getElementById("chk-descuento");
            const box = document.getElementById("box-descuento");

            if (chk.checked) {
                box.classList.remove("hidden");
            }

            chk.addEventListener("change", function () {
                box.classList.toggle("hidden", !this.checked);
            });
        </script>



        <!-- Estado -->
        <div>
            <label class="inline-flex items-center space-x-2">
                <input type="checkbox" name="activo" value="1" <?= $prod->isActivo() ? 'checked' : '' ?>
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm text-gray-700">Producto activo (visible en el catálogo)</span>
            </label>
        </div>

        <div class="flex justify-end pt-4 space-x-3">
            <a href="<?= BASE_PATH ?>/productos"
                class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                Cancelar
            </a>
            <button class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow hover:bg-indigo-700">
                <?= $boton ?>
            </button>
        </div>

    </form>

</div>