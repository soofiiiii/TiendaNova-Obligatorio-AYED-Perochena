<?php
/** @var \Domain\Entities\Pedido $pedido */
$prov = $pedido->getProveedor();
?>

<div class="max-w-4xl mx-auto bg-white shadow rounded-xl p-8">

    <h1 class="text-2xl font-bold mb-4">
        Pedido #<?= $pedido->getId() ?>
    </h1>

    <p class="text-sm text-gray-700 mb-2">
        Fecha: <b><?= $pedido->getFecha()->format('Y-m-d H:i') ?></b>
    </p>

    <p class="text-sm text-gray-700 mb-2">
        Proveedor:
        <b>#<?= $prov->getId() ?> – <?= htmlspecialchars($prov->getNombreEmpresa()) ?></b>
    </p>

    <p class="text-sm text-gray-700 mb-4">
        Estado:
        <span class="inline-flex px-2 py-1 rounded-full text-xs
            <?= $pedido->getEstado() === 'pendiente' ? 'bg-yellow-100 text-yellow-800' : '' ?>
            <?= $pedido->getEstado() === 'recibido'  ? 'bg-green-100 text-green-800' : '' ?>
            <?= $pedido->getEstado() === 'cancelado' ? 'bg-red-100 text-red-800'   : '' ?>">
            <?= ucfirst($pedido->getEstado()) ?>
        </span>
    </p>

    <!-- Detalles -->
    <table class="min-w-full text-sm mb-4">
        <thead class="bg-gray-50">
        <tr>
            <th class="px-4 py-2 text-left">Producto</th>
            <th class="px-4 py-2 text-left">Cantidad</th>
            <th class="px-4 py-2 text-left">Precio unitario</th>
            <th class="px-4 py-2 text-left">Subtotal</th>
        </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">

        <?php foreach ($pedido->getDetalles() as $d): ?>
            <?php $prod = $d->getProducto(); ?>
            <tr>
                <td class="px-4 py-2">
                    #<?= $prod->getId() ?> – <?= htmlspecialchars($prod->getNombre()) ?>
                </td>
                <td class="px-4 py-2"><?= $d->getCantidad() ?></td>
                <td class="px-4 py-2">$<?= number_format($d->getPrecioUnitario(), 2) ?></td>
                <td class="px-4 py-2 font-semibold">
                    $<?= number_format($d->subtotal(), 2) ?>
                </td>
            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>

    <p class="text-right text-lg font-bold">
        Total: $<?= number_format($pedido->getTotal(), 2) ?>
    </p>

    <!-- Acciones -->
    <div class="mt-6 flex justify-between items-center">
        <a href="<?= BASE_PATH ?>/pedidos"
           class="text-sm text-gray-600 hover:text-gray-800">
            Volver al listado
        </a>

        <?php if ($pedido->getEstado() === 'pendiente'): ?>
            <div class="space-x-3">

                <!-- Recibir (modal compartido) -->
                <button type="button"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                        onclick="abrirModalRecibir(<?= $pedido->getId() ?>)">
                    Marcar recibido
                </button>

                <form id="form-recibir-<?= $pedido->getId() ?>"
                      action="<?= BASE_PATH ?>/pedidos/recibir"
                      method="POST"
                      class="hidden">
                    <input type="hidden" name="id" value="<?= $pedido->getId() ?>">
                </form>

                <!-- Cancelar (patrón eliminar) -->
                <a href="<?= BASE_PATH ?>/pedidos/cancelar?id=<?= $pedido->getId() ?>"
                   class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                    Cancelar pedido
                </a>

            </div>
        <?php endif; ?>
    </div>
</div>


<div id="modalRecibir"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40">

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
                class="px-4 py-2 text-sm rounded-lg border text-gray-700 hover:bg-gray-50">
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
