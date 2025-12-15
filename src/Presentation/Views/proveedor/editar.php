<?php 
$titulo = "Editar proveedor";
$action = BASE_PATH . "/proveedor/editar";
$boton  = "Guardar cambios";
?>

<div class="max-w-3xl mx-auto bg-white shadow rounded-xl p-8">

    <h1 class="text-2xl font-bold text-gray-900 mb-4"><?= $titulo ?></h1>

    <div class="mb-4 text-sm text-gray-600">
        Editando proveedor 
        <span class="font-semibold">#<?= $prov->getId() ?> - <?= htmlspecialchars($prov->getNombreEmpresa(), ENT_QUOTES, 'UTF-8') ?></span>
    </div>

    <form action="<?= $action ?>" method="POST" class="space-y-6">

        <input type="hidden" name="id" value="<?= $prov->getId() ?>">

        <!-- Nombre empresa -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la empresa</label>
            <input type="text" 
                   name="nombre_empresa"
                   value="<?= htmlspecialchars($prov->getNombreEmpresa(), ENT_QUOTES, 'UTF-8') ?>"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                   required>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Contacto -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Persona de contacto</label>
                <input type="text" 
                       name="contacto"
                       value="<?= htmlspecialchars($prov->getContacto(), ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                       required>
            </div>

            <!-- Teléfono -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                <input type="text"
                       name="telefono"
                       value="<?= htmlspecialchars($prov->getTelefono(), ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                       required>
            </div>

        </div>

        <!-- Email -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email"
                   name="email"
                   value="<?= htmlspecialchars($prov->getEmail(), ENT_QUOTES, 'UTF-8') ?>"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                   required>
        </div>

        <!-- Dirección -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
            <input type="text"
                   name="direccion"
                   value="<?= htmlspecialchars($prov->getDireccion(), ENT_QUOTES, 'UTF-8') ?>"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                   required>
        </div>

        <div class="flex justify-end pt-4 space-x-3">
            <a href="<?= BASE_PATH ?>/proveedores"
               class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                Cancelar
            </a>
            <button class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow hover:bg-indigo-700">
                <?= $boton ?>
            </button>
        </div>

    </form>

</div>
