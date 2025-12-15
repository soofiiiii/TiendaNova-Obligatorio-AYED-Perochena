<?php $titulo = "Listado de " . $entidad; ?>

<div class="flex justify-between items-center mb-8">
    <h1 class="text-3xl font-bold text-gray-900"><?= $titulo ?></h1>

    <a href="<?= BASE_PATH ?>/<?= $ruta ?>/crear"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg shadow hover:bg-indigo-700 transition">
        + Nuevo <?= ucfirst($entidad) ?>
    </a>
</div>

<!-- Tabla -->
<div class="bg-white shadow rounded-xl overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <?php foreach ($columnas as $col): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <?= $col ?>
                    </th>
                <?php endforeach; ?>

                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Acciones
                </th>
            </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-200">

            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="<?= count($columnas) + 1 ?>" class="px-6 py-4 text-center text-gray-500">
                        No hay registros para mostrar.
                    </td>
                </tr>
            <?php else: ?>

                <?php foreach ($items as $i): ?>
                    <tr>
                        <?php foreach ($campos as $c): ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <?= htmlspecialchars($i[$c]) ?>
                            </td>
                        <?php endforeach; ?>

                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">

                            <a href="<?= BASE_PATH ?>/productos?proveedor_id=<?= $i['id'] ?>"
                                class="text-blue-600 hover:text-blue-900 mr-4">
                                Ver productos
                            </a>

                            <a href="<?= BASE_PATH ?>/<?= $ruta ?>/editar?id=<?= $i['id'] ?>"
                                class="text-indigo-600 hover:text-indigo-900 mr-4">
                                Editar
                            </a>

                            <a href="#" class="text-red-600 hover:text-red-800 eliminar-btn"
                                data-delete-url="<?= BASE_PATH ?>/proveedor/eliminar?id=<?= $i['id'] ?>">
                                Eliminar
                            </a>

                        </td>
                    </tr>
                <?php endforeach; ?>


            <?php endif; ?>

        </tbody>
    </table>
</div>