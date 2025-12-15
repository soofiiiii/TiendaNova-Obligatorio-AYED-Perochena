<?php $this->layout('layouts/base', ['active' => 'categorias']) ?>

<h1 class="text-3xl font-bold mb-6">Mover Categoría</h1>

<form action="<?= BASE_PATH ?>/categorias/mover" method="POST" 
      class="space-y-6 max-w-xl">

    <div>
        <label class="block font-semibold mb-1">Categoría a mover</label>
        <select name="categoria_id" class="w-full border rounded px-3 py-2" required>
            <?php foreach ($tree as $r): ?>
                <option value="<?= $r->getId() ?>"><?= $r->getNombre() ?></option>
            <?php endforeach ?>
        </select>
    </div>

    <div>
        <label class="block font-semibold mb-1">Nuevo padre</label>
        <select name="nuevo_parent_id" class="w-full border rounded px-3 py-2">
            <option value="">(Ninguno)</option>
            <?php foreach ($tree as $r): ?>
                <option value="<?= $r->getId() ?>"><?= $r->getNombre() ?></option>
            <?php endforeach ?>
        </select>
    </div>

    <button class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
        Mover categoría
    </button>
</form>
