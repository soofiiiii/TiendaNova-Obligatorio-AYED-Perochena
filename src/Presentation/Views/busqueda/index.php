<?php
/** @var string $query */
/** @var ?\Domain\Entities\Categoria $categoriaExacta */
/** @var \Domain\Entities\Categoria[] $categoriasRelacionadas */
/** @var \Domain\Entities\Producto[] $productosRama */
/** @var \Domain\Entities\Producto[] $productosCoincidentes */

$titulo = $query !== ''
    ? 'Resultados de búsqueda para: "' . htmlspecialchars($query, ENT_QUOTES, 'UTF-8') . '"'
    : 'Búsqueda en TiendaNova';
?>

<div class="flex justify-between items-center mb-8">
    <h1 class="text-3xl font-bold text-gray-900"><?= $titulo ?></h1>
</div>

<?php if ($query === ''): ?>
    <div class="bg-white shadow rounded-xl p-6">
        <p class="text-gray-600">
            Escribí un término en la barra de búsqueda para ver resultados.
        </p>
    </div>
    <?php return; ?>
<?php endif; ?>

<?php
$modoStockBajo = $modoStockBajo ?? false;
?>


<?php if ($categoriaExacta): ?>
    <section class="mb-8">
        <div class="bg-white shadow rounded-xl p-6 border-l-4 border-indigo-500">
            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                Categoría principal encontrada
            </h2>
            <p class="text-sm text-gray-600 mb-1">
                Ruta:
                <span class="font-mono text-gray-800">
                    <?= htmlspecialchars($categoriaExacta->getRutaCompleta(), ENT_QUOTES, 'UTF-8') ?>
                </span>
            </p>
            <p class="text-sm text-gray-600">
                ID: <?= $categoriaExacta->getId() ?> · Nivel: <?= $categoriaExacta->getNivel() ?>
            </p>
        </div>

        <?php if (!empty($productosRama)): ?>
            <div class="mt-4 bg-white shadow rounded-xl overflow-hidden">
                <div class="px-6 py-3 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700">
                        Productos dentro de la rama de
                        "<?= htmlspecialchars($categoriaExacta->getNombre(), ENT_QUOTES, 'UTF-8') ?>"
                    </h3>
                </div>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-2 text-left">ID</th>
                            <th class="px-4 py-2 text-left">Nombre</th>
                            <th class="px-4 py-2 text-left">Precio</th>
                            <th class="px-4 py-2 text-left">Categoría</th>
                            <th class="px-4 py-2 text-left">Proveedor</th>
                            <th class="px-4 py-2 text-center">Stock</th>
                            <th class="px-4 py-2 text-left">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 text-sm">
                        <?php foreach ($productosRama as $p): ?>
                            <?php $stock = $p->getStock(); ?>
                            <tr>
                                <td class="px-4 py-2 text-gray-700"><?= $p->getId() ?></td>
                                <td class="px-4 py-2 text-gray-900">
                                    <?= htmlspecialchars($p->getNombre(), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="px-4 py-2 text-gray-700">
                                    $<?= number_format($p->getPrecio(), 2, ',', '.') ?>
                                </td>
                                <td class="px-4 py-2 text-gray-700">
                                    <?= htmlspecialchars($p->getCategoria()->getNombre(), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="px-4 py-2 text-gray-700">
                                    <?= htmlspecialchars($p->getProveedor()->getNombreEmpresa(), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="<?= $stock->verificarStockBajo() ? 'text-red-600 font-semibold' : '' ?>">
                                            <?= $stock->getCantidad() ?>
                                        </span>

                                        <?php if ($stock->verificarStockBajo()): ?>
                                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-red-100 text-red-700">
                                                STOCK BAJO
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <?php if ($p->isActivo()): ?>
                                        <span class="px-2 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="mt-3 text-sm text-gray-600">
                No hay productos cargados en la rama de esta categoría.
            </p>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php if (!empty($categoriasRelacionadas)): ?>
    <section class="mb-8">
        <div class="bg-white shadow rounded-xl p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">
                Categorías relacionadas
            </h2>
            <ul class="space-y-1 text-sm text-gray-700">
                <?php foreach ($categoriasRelacionadas as $cat): ?>
                    <li class="flex items-center justify-between">
                        <span><?= htmlspecialchars($cat->getRutaCompleta(), ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="text-xs text-gray-400">
                            ID: <?= $cat->getId() ?> · Nivel: <?= $cat->getNivel() ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
<?php endif; ?>

<?php if (!empty($productosCoincidentes)): ?>
    <section class="mb-8">
        <div class="bg-white shadow rounded-xl overflow-hidden">
            <div class="px-6 py-3 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">
                    Productos que coinciden con "<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>"
                </h2>
            </div>

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">ID</th>
                        <th class="px-4 py-2 text-left">Nombre</th>
                        <th class="px-4 py-2 text-left">Precio</th>
                        <th class="px-4 py-2 text-left">Categoría</th>
                        <th class="px-4 py-2 text-left">Proveedor</th>
                        <th class="px-4 py-2 text-center">Stock</th>
                        <th class="px-4 py-2 text-left">Estado</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                    <?php foreach ($productosCoincidentes as $p): ?>
                        <?php $stock = $p->getStock(); ?>

                        <tr>
                            <td class="px-4 py-2 text-gray-700"><?= $p->getId() ?></td>

                            <td class="px-4 py-2 text-gray-900">
                                <?= htmlspecialchars($p->getNombre(), ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td class="px-4 py-2 text-gray-700">
                                $<?= number_format($p->getPrecio(), 2, ',', '.') ?>
                            </td>

                            <td class="px-4 py-2 text-gray-700">
                                <?= htmlspecialchars($p->getCategoria()->getNombre(), ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td class="px-4 py-2 text-gray-700">
                                <?= htmlspecialchars($p->getProveedor()->getNombreEmpresa(), ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <!-- STOCK + ALERTA -->
                            <td class="px-4 py-2 text-center">
                                <?php $stock = $p->getStock(); ?>

                                <?php if ($stock): ?>
                                    <div class="flex items-center justify-center gap-2">

                                        <?php if ($modoStockBajo): ?>
                                            <span class="text-red-600 font-bold">
                                                <?= $stock->getCantidad() ?>
                                            </span>
                                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-red-100 text-red-700">
                                                BAJO
                                            </span>
                                        <?php else: ?>
                                            <span><?= $stock->getCantidad() ?></span>
                                        <?php endif; ?>

                                    </div>
                                <?php endif; ?>
                            </td>



                            <td class="px-4 py-2">
                                <?php if ($p->isActivo()): ?>
                                    <span class="px-2 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Activo
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        Inactivo
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>


<?php if (!$categoriaExacta && empty($categoriasRelacionadas) && empty($productosRama) && empty($productosCoincidentes)): ?>
    <div class="bg-white shadow rounded-xl p-6">
        <p class="text-gray-600">
            No se encontraron resultados para este término.
        </p>
    </div>
<?php endif; ?>