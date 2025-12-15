<?php
/** @var \Domain\Entities\Proveedor $proveedor */
/** @var int|null $cantidadProductos Opcional: productos asociados */

$nombreEmpresa = $proveedor->getNombreEmpresa();
$contacto      = $proveedor->getContacto();
$telefono      = $proveedor->getTelefono();
$email         = $proveedor->getEmail();
$direccion     = $proveedor->getDireccion();

$cantProductos = isset($cantidadProductos) ? (int)$cantidadProductos : null;
?>

<div class="max-w-3xl mx-auto bg-white shadow rounded-xl p-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-4">
        Eliminar proveedor #<?= $proveedor->getId() ?>
    </h1>

    <p class="text-sm text-gray-600 mb-4">
        Estás a punto de eliminar este proveedor. Dependiendo de las restricciones de la base de datos,
        es posible que no se permita borrar proveedores con productos asociados.
    </p>

    <!-- Panel principal -->
    <div class="border border-red-100 bg-red-50 rounded-lg p-4 mb-6">
        <div class="flex items-start justify-between gap-4">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-red-800">
                    <?= htmlspecialchars($nombreEmpresa) ?>
                </h2>

                <p class="text-sm text-red-900/80">
                    <span class="font-medium">Contacto:</span>
                    <?= htmlspecialchars($contacto) ?>
                </p>

                <p class="text-sm text-red-900/80">
                    <span class="font-medium">Teléfono:</span>
                    <?= htmlspecialchars($telefono) ?>
                </p>

                <p class="text-sm text-red-900/80">
                    <span class="font-medium">Email:</span>
                    <?= htmlspecialchars($email) ?>
                </p>
            </div>

            <div class="text-right space-y-2">
                <p class="text-xs uppercase tracking-wide text-red-700/80 font-semibold">
                    ID interno
                </p>
                <p class="font-mono text-sm text-red-900">
                    #<?= $proveedor->getId() ?>
                </p>

                <?php if ($cantProductos !== null): ?>
                    <p class="text-xs uppercase tracking-wide text-red-700/80 font-semibold mt-3">
                        Productos asociados
                    </p>
                    <p class="font-semibold text-red-900">
                        <?= $cantProductos ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-4 text-sm text-red-900/90">
            <p class="text-xs uppercase tracking-wide text-red-700/80 font-semibold mb-1">
                Dirección
            </p>
            <p class="font-medium">
                <?= nl2br(htmlspecialchars($direccion)) ?>
            </p>
        </div>
    </div>

    <!-- Consecuencias / ayuda -->
    <div class="mb-6">
        <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
            <li>
                Si existen productos asociados a este proveedor, la base de datos puede bloquear la eliminación
                (por ejemplo, por claves foráneas).
            </li>
            <li>
                Antes de borrar, es recomendable reasignar o eliminar los productos que dependan de este proveedor.
            </li>
            <li>
                La operación es irreversible una vez confirmada.
            </li>
        </ul>
    </div>

    <!-- Confirmación final -->
    <form method="post" action="<?= BASE_PATH ?>/proveedor/eliminar" class="flex justify-end gap-3">
        <input type="hidden" name="id" value="<?= $proveedor->getId() ?>">
        <input type="hidden" name="confirm" value="1">

        <a href="<?= BASE_PATH ?>/proveedores"
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
            Cancelar
        </a>

        <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg shadow hover:bg-red-700">
            Sí, eliminar definitivamente
        </button>
    </form>
</div>
