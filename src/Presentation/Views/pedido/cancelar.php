<?php
/** @var \Domain\Entities\Pedido $pedido */
?>

<div class="max-w-3xl mx-auto bg-white shadow rounded-xl p-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-4">
        Cancelar pedido #<?= $pedido->getId() ?>
    </h1>

    <p class="text-sm text-gray-600 mb-6">
        Estás a punto de cancelar este pedido. El pedido quedará marcado como cancelado
        y no podrá ser modificado.
    </p>

    <form method="post" action="<?= BASE_PATH ?>/pedidos/cancelar" class="flex justify-end gap-3">
        <input type="hidden" name="id" value="<?= $pedido->getId() ?>">
        <input type="hidden" name="confirm" value="1">

        <a href="<?= BASE_PATH ?>/pedidos"
           class="px-4 py-2 border rounded-lg text-sm text-gray-700 hover:bg-gray-50">
            Volver
        </a>

        <button type="submit"
                class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700">
            Sí, cancelar pedido
        </button>
    </form>
</div>
