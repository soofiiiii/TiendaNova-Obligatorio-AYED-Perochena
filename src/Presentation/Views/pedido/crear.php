<?php
/** @var \Domain\Entities\Proveedor[] $proveedores */
/** @var \Domain\Entities\Proveedor|null $proveedorSeleccionado */
/** @var \Domain\Entities\Producto[] $productos */

$titulo = "Crear pedido a proveedor";
?>

<div class="max-w-5xl mx-auto bg-white shadow rounded-xl p-8">

    <h1 class="text-2xl font-bold text-gray-900 mb-6"><?= $titulo ?></h1>

    <?php if (!$proveedorSeleccionado): ?>

        <!-- Paso 1: elegir proveedor -->
        <form method="GET" action="<?= BASE_PATH ?>/pedidos/crear" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Seleccione un proveedor
                </label>
                <select name="proveedor_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                    required>
                    <option value=""> Elegir proveedor </option>
                    <?php foreach ($proveedores as $p): ?>
                        <option value="<?= $p->getId() ?>">
                            <?= htmlspecialchars($p->getNombreEmpresa()) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex justify-end">
                <button class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow hover:bg-indigo-700">
                    Continuar
                </button>
            </div>
        </form>

    <?php else: ?>

        <!-- Paso 2: armar pedido con productos del proveedor seleccionado -->
        <div class="mb-4 text-sm text-gray-600">
            Proveedor seleccionado:
            <span class="font-semibold">
                <?= htmlspecialchars($proveedorSeleccionado->getNombreEmpresa()) ?>
            </span>
        </div>

        <form action="<?= BASE_PATH ?>/pedidos/crear" method="POST" class="space-y-6">

            <input type="hidden" name="proveedor_id" value="<?= $proveedorSeleccionado->getId() ?>">

            <div class="border rounded-lg overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-700">Producto</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-700">Precio base</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-700">Cantidad</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-700">Precio unitario pedido</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">

                        <?php if (!$productos): ?>
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-center text-gray-500">
                                    Este proveedor aún no tiene productos asociados.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($productos as $prod): ?>
                                <tr>
                                    <td class="px-4 py-2">
                                        <div class="font-semibold">
                                            <?= htmlspecialchars($prod->getNombre()) ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <?= htmlspecialchars($prod->getCategoria()->getRutaCompleta()) ?>
                                        </div>
                                        <input type="hidden" name="producto_id[]" value="<?= $prod->getId() ?>">
                                    </td>

                                    <td class="px-4 py-2">
                                        $<?= number_format($prod->getPrecio(), 2) ?>
                                    </td>

                                    <td class="px-4 py-2">
                                        <input type="number" name="cantidad[]" class="w-24 border border-gray-300 rounded px-2 py-1"
                                            min="0" value="0">

                                    </td>

                                    <td class="px-4 py-2">
                                        $<?= number_format($prod->getPrecio(), 2) ?>

                                        <input type="hidden" name="precio_unitario[]"
                                            value="<?= number_format($prod->getPrecio(), 2, '.', '') ?>">
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    </tbody>
                </table>
            </div>

            <div class="flex justify-between">
                <a href="<?= BASE_PATH ?>/pedidos/crear"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                    Cambiar proveedor
                </a>

                <button type="submit"
                    class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow hover:bg-indigo-700">
                    Crear pedido
                </button>
            </div>

        </form>

    <?php endif; ?>

</div>