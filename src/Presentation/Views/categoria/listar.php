<?php $titulo = "Listado de " . $entidad; ?>

<div class="flex justify-between items-center mb-8">
    <h1 class="text-3xl font-bold text-gray-900"><?= $titulo ?></h1>

    <a href="<?= BASE_PATH ?>/categorias/crear"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg shadow hover:bg-indigo-700 transition">
        + Nueva Categoría
    </a>
</div>

<!-- Tabla -->
<div class="bg-white shadow rounded-xl overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Descripción
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nivel</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Productos Totales
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Acciones
                </th>
            </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-200">

            <?php if (empty($roots)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        No hay categorías registradas.
                    </td>
                </tr>
            <?php else: ?>

                <?php
                /**
                 * Renderiza una categoría y sus subcategorías en filas de tabla
                 */
                function renderCategoriaFila(\Domain\Entities\Categoria $cat, int $nivel, $svc)
                {

                    $totalProductos = $svc->contarProductosEnRama($cat->getId());

                    // Texto del nivel (presentación)
                    $nivelNum = $cat->getNivel();

                    if ($nivelNum === 0) {
                        $nivelTexto = 'Categoría';
                        $nivelClase = 'bg-gray-100 text-gray-800';
                    } elseif ($nivelNum === 1) {
                        $nivelTexto = 'Subcategoría';
                        $nivelClase = 'bg-blue-100 text-blue-800';
                    } else {
                        $nivelTexto = 'Subcategoría ' . $nivelNum;
                        $nivelClase = 'bg-teal-100 text-teal-800';
                    }

                    ?>
                    <tr>
                        <!-- ID -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <?= $cat->getId() ?>
                        </td>

                        <!-- Nombre con indentación visual -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <?= htmlspecialchars($cat->getNombre()) ?>
                        </td>


                        <!-- Descripción -->
                        <td class="px-6 py-4 text-sm text-gray-700">
                            <div class="max-w-md break-words whitespace-normal">
                                <?= htmlspecialchars($cat->getDescripcion() ?? '') ?>
                            </div>
                        </td>

                        <!-- Nivel (texto semántico) -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium <?= $nivelClase ?>">
                                <?= $nivelTexto ?>
                            </span>
                        </td>



                        <!-- Productos Totales -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <?= $totalProductos ?>
                        </td>

                        <!-- Acciones -->
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <a href="<?= BASE_PATH ?>/categorias/editar?id=<?= $cat->getId() ?>"
                                class="text-indigo-600 hover:text-indigo-900 mr-4">
                                Editar
                            </a>

                            <a href="#" class="text-red-600 hover:text-red-800 eliminar-btn"
                                data-delete-url="<?= BASE_PATH ?>/categorias/eliminar?id=<?= $cat->getId() ?>">
                                Eliminar
                            </a>
                        </td>
                    </tr>

                    <?php
                    // Render recursivo de subcategorías
                    foreach ($cat->getSubcategorias() as $hijo) {
                        renderCategoriaFila($hijo, $nivel + 1, $svc);
                    }
                }
                ?>

                <!-- Llamar recursivamente desde las raíces -->
                <?php foreach ($roots as $r): ?>
                    <?php renderCategoriaFila($r, 0, $svc); ?>
                <?php endforeach; ?>

            <?php endif; ?>

        </tbody>
    </table>
</div>