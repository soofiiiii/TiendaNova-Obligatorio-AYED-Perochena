<?php
/** @var \Domain\Entities\Pedido[] $pedidos */
$titulo = "Listado de pedidos";
?>

<div class="flex justify-between items-center mb-8">
    <h1 class="text-3xl font-bold text-gray-900"><?= $titulo ?></h1>

    <a href="<?= BASE_PATH ?>/pedidos/crear"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg shadow hover:bg-indigo-700 transition">
        + Nuevo pedido
    </a>
</div>

<div class="flex justify-between items-center mb-4">
    <form method="GET" action="<?= BASE_PATH ?>/pedidos" class="flex items-center space-x-2">
        <label class="text-sm font-medium text-gray-700">Estado:</label>

        <select name="estado" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-1 text-sm">

            <option value="pendiente" <?= ($estadoActual ?? '') === 'pendiente' ? 'selected' : '' ?>>
                Pendiente
            </option>
            <option value="recibido" <?= ($estadoActual ?? '') === 'recibido' ? 'selected' : '' ?>>
                Recibido
            </option>
            <option value="cancelado" <?= ($estadoActual ?? '') === 'cancelado' ? 'selected' : '' ?>>
                Cancelado
            </option>
            <option value="todos" <?= ($estadoActual ?? '') === 'todos' ? 'selected' : '' ?>>
                Todos
            </option>
        </select>
    </form>
</div>


<div class="bg-white shadow rounded-xl overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Proveedor</th>
                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Total</th>
                <th class="px-6 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-200">

            <?php if (empty($pedidos)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        No hay pedidos registrados.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($pedidos as $p): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            #<?= $p->getId() ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= $p->getFecha()->format('Y-m-d H:i') ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= htmlspecialchars($p->getProveedor()->getNombreEmpresa()) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 rounded-full text-xs
                            <?= $p->getEstado() === 'pendiente' ? 'bg-yellow-100 text-yellow-800' : '' ?>
                            <?= $p->getEstado() === 'recibido' ? 'bg-green-100 text-green-800' : '' ?>
                            <?= $p->getEstado() === 'cancelado' ? 'bg-red-100 text-red-800' : '' ?>">
                                <?= ucfirst($p->getEstado()) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-semibold">
                            $<?= number_format($p->getTotal(), 2) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">

                            <a href="<?= BASE_PATH ?>/pedidos/ver?id=<?= $p->getId() ?>"
                                class="text-indigo-600 hover:text-indigo-900">
                                Ver
                            </a>

                            <?php if ($p->getEstado() === 'pendiente'): ?>

                                <!-- Recibir (modal) -->
                                <button type="button" class="text-green-600 hover:text-green-800"
                                    onclick="abrirModalRecibir(<?= $p->getId() ?>)">
                                    Recibir
                                </button>

                                <form id="form-recibir-<?= $p->getId() ?>" action="<?= BASE_PATH ?>/pedidos/recibir" method="POST"
                                    class="hidden">
                                    <input type="hidden" name="id" value="<?= $p->getId() ?>">
                                </form>

                                <!-- Cancelar (patrón eliminar) -->
                                <a href="<?= BASE_PATH ?>/pedidos/cancelar?id=<?= $p->getId() ?>"
                                    class="text-red-600 hover:text-red-800">
                                    Cancelar
                                </a>

                            <?php endif; ?>
                        </td>

                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

        </tbody>
    </table>
</div>



<div id="modalRecibir" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40">

    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-2">
            Confirmar recepción
        </h2>

        <p class="text-sm text-gray-600 mb-6">
            Al marcar el pedido como recibido, el stock de los productos será actualizado.
            Esta acción no se puede deshacer.
        </p>

        <div class="flex justify-end space-x-3">
            <button onclick="cerrarModalRecibir()"
                class="px-4 py-2 text-sm rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                Cancelar
            </button>

            <button onclick="confirmarRecibir()"
                class="px-4 py-2 text-sm rounded-lg bg-green-600 text-white hover:bg-green-700">
                Sí, marcar recibido
            </button>
        </div>
    </div>
</div>



<script>
    let pedidoARecibir = null;

    function abrirModalRecibir(id) {
        pedidoARecibir = id;
        const modal = document.getElementById('modalRecibir');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function cerrarModalRecibir() {
        pedidoARecibir = null;
        const modal = document.getElementById('modalRecibir');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function confirmarRecibir() {
        if (!pedidoARecibir) return;

        const form = document.getElementById('form-recibir-' + pedidoARecibir);
        if (form) {
            form.submit();
        }
    }
</script>