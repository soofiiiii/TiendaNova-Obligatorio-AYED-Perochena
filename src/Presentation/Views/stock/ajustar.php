<?php
$titulo = "Ajustar stock";
$action = BASE_PATH . "/stock/ajustar";
$boton = "Aplicar ajuste";

// Producto preseleccionado 
$productoSeleccionadoId = isset($_GET['producto_id'])
    ? (int) $_GET['producto_id']
    : null;
?>

<div class="max-w-md mx-auto bg-white shadow rounded-xl p-8">

    <h1 class="text-2xl font-bold text-gray-900 mb-6">
        <?= $titulo ?>
    </h1>

    <form action="<?= $action ?>" method="POST"
          class="max-w-lg mx-auto bg-white p-6 shadow rounded-xl">

        <h2 class="text-xl font-semibold mb-4 text-gray-800">
            Ajuste manual de inventario
        </h2>

        <!-- PRODUCTO -->
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Producto
        </label>

        <select
            name="producto_id"
            <?= $productoSeleccionadoId ? 'disabled' : '' ?>
            required
            class="border rounded-lg px-4 py-2 w-full mb-4
                   focus:ring-indigo-500 focus:border-indigo-500">

            <option value="">Seleccionar producto...</option>

            <?php foreach ($productos as $p): ?>
                <option value="<?= $p->getId() ?>"
                    <?= $productoSeleccionadoId === $p->getId() ? 'selected' : '' ?>>
                    <?= $p->getId() ?> — <?= htmlspecialchars($p->getNombre()) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- INPUT OCULTO SI VIENE PRESELECCIONADO -->
        <?php if ($productoSeleccionadoId): ?>
            <input type="hidden" name="producto_id"
                   value="<?= $productoSeleccionadoId ?>">
        <?php endif; ?>

        <!-- DELTA -->
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Cantidad a ajustar (+ / -)
        </label>

        <input
            type="number"
            name="delta"
            required
            class="border rounded-lg px-4 py-2 w-full mb-6
                   focus:ring-indigo-500 focus:border-indigo-500"
            placeholder="Ejemplo: 10 o -5">

        <!-- BOTÓN -->
        <button
            type="submit"
            class="w-full bg-indigo-600 text-white py-2 rounded-lg
                   hover:bg-indigo-700 transition font-semibold">

            <?= $boton ?>
        </button>

    </form>

</div>
