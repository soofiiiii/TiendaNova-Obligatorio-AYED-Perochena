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
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            <?= $col ?>
          </th>
        <?php endforeach; ?>

        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
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
              <!-- NOMBRE -->
              <?php if ($c === 'nombre'): ?>
                <td class="px-4 py-4 text-sm text-gray-800 max-w-xs break-words">
                  <?= htmlspecialchars($i['nombre']) ?>
                </td>

                <!-- PRECIO + DESCUENTO -->
              <?php elseif ($c === 'precio'): ?>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                  <span class="font-medium">
                    $<?= number_format((float) $i['precio'], 2) ?>
                  </span>

                  <?php if (isset($i['descuento']) && (int) $i['descuento'] > 0): ?>
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full
                         bg-red-100 text-red-700 text-xs font-semibold tracking-wide">
                      <?= (int) $i['descuento'] ?>% OFF
                    </span>
                  <?php endif; ?>
                </td>


                <!-- ESTADO -->
              <?php elseif ($c === 'activo'): ?>
                <td class="px-4 py-4 whitespace-nowrap text-sm">
                  <span class="estado-badge inline-flex px-2 py-1 rounded-full text-xs font-semibold
                                        <?= !empty($i['activo'])
                                          ? 'bg-green-100 text-green-800'
                                          : 'bg-orange-100 text-orange-800' ?> ?>" data-id="<?= (int) $i['id'] ?>">
                    <?= !empty($i['activo']) ? 'Activo' : 'Inactivo' ?>
                  </span>
                </td>

                <!-- OTROS CAMPOS -->
              <?php else: ?>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                  <?= htmlspecialchars($i[$c]) ?>
                </td>
              <?php endif; ?>
            <?php endforeach; ?>

            <!-- ACCIONES -->
            <td class="px-4 py-4 whitespace-nowrap text-right text-sm flex items-center justify-end gap-4">

              <!-- Toggle Inactivo manual -->
              <?php
              $stock = (int) ($i['stock'] ?? 0);
              $activoManual = (int) ($i['activo_manual'] ?? 1);

              // checked = INACTIVO (activo_manual = 0)
              $checked = ($activoManual === 0);

              // disabled si stock == 0 (no puede activarse)
              $disabled = ($stock <= 0);
              ?>
              <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" class="toggle-activo" data-id="<?= $i['id'] ?>" <?= $checked ? 'checked' : '' ?>
                  <?= $disabled ? 'disabled' : '' ?>>
                <span class="text-xs text-gray-700">
                  <?= $disabled ? 'Sin stock' : 'Inactivo' ?>
                </span>
              </label>

              <!-- Editar -->
              <a href="<?= BASE_PATH ?>/<?= $ruta ?>/editar?id=<?= $i['id'] ?>"
                class="text-indigo-600 hover:text-indigo-900">
                Editar
              </a>

              <!-- Eliminar -->
              <a href="#" data-delete-url="<?= BASE_PATH ?>/productos/eliminar?id=<?= $i['id'] ?>"
                class="text-red-600 hover:text-red-800 eliminar-btn">
                Eliminar
              </a>

            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal confirmación -->
<div id="modal-confirmar" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50">

  <div class="bg-white rounded-xl shadow-lg max-w-sm w-full p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-2">
      Confirmar cambio de estado
    </h2>

    <p id="modal-texto" class="text-sm text-gray-700 mb-6">
      ¿Desea cambiar el estado del producto?
    </p>

    <div class="flex justify-end gap-3">
      <button id="modal-cancelar" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300">
        Cancelar
      </button>

      <button id="modal-confirmar-btn" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
        Confirmar
      </button>
    </div>
  </div>
</div>


<script>
  const modal = document.getElementById('modal-confirmar');
  const modalTexto = document.getElementById('modal-texto');
  const btnCancelar = document.getElementById('modal-cancelar');
  const btnConfirmar = document.getElementById('modal-confirmar-btn');

  let checkboxActual = null;
  let estadoOriginal = null;

  document.querySelectorAll('.toggle-activo').forEach(cb => {
    cb.addEventListener('click', function (e) {

      // Si está disabled, no hacer nada
      if (this.disabled) return;

      // Frenar el cambio automático
      e.preventDefault();

      checkboxActual = this;
      estadoOriginal = this.checked;

      const nombre = this.closest('tr')
        .querySelector('td')
        ?.textContent.trim() ?? 'este producto';

      modalTexto.textContent = estadoOriginal
        ? `¿Desea DESACTIVAR ${nombre}?`
        : `¿Desea ACTIVAR ${nombre}?`;

      modal.classList.remove('hidden');
    });
  });

  // Cancelar → rollback visual
  btnCancelar.addEventListener('click', () => {
    if (checkboxActual) {
      checkboxActual.checked = estadoOriginal;
    }
    cerrarModal();
  });

  // Confirmar → recién acá se persiste
  btnConfirmar.addEventListener('click', async () => {
    if (!checkboxActual) return;

    const id = checkboxActual.dataset.id;
    const activoManual = estadoOriginal ? 0 : 1;

    try {
      const res = await fetch('<?= BASE_PATH ?>/productos/toggle-activo', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          id: id,
          activo_manual: activoManual
        })
      });

      const data = await res.json();
      if (!res.ok || !data.ok) {
        throw new Error(data.error || 'Error');
      }

      // Checkbox
      checkboxActual.checked = (data.activo_manual === 0);

      // Badge
      const badge = document.querySelector(`.estado-badge[data-id="${id}"]`);
      if (badge) {
        badge.textContent = data.activo ? 'Activo' : 'Inactivo';
        badge.className =
          'estado-badge inline-flex px-2 py-1 rounded-full text-xs font-semibold ' +
          (data.activo
            ? 'bg-green-100 text-green-800'
            : 'bg-orange-100 text-orange-800');
      }

      // Stock
      const label = checkboxActual.parentElement.querySelector('span');
      if (data.stock <= 0) {
        checkboxActual.disabled = true;
        checkboxActual.checked = true;
        if (label) label.textContent = 'Sin stock';
      } else {
        checkboxActual.disabled = false;
        if (label) label.textContent = 'Inactivo';
      }

    } catch (e) {
      alert('No se pudo actualizar el estado');
      checkboxActual.checked = estadoOriginal;
    } finally {
      cerrarModal();
    }
  });

  function cerrarModal() {
    modal.classList.add('hidden');
    checkboxActual = null;
    estadoOriginal = null;
  }
</script>