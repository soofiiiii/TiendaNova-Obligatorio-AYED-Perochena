// UTILIDADES BÁSICAS

/**
 * Atajo para document.querySelector
 * Devuelve el primer elemento que coincide con el selector.
 */
const $ = sel => document.querySelector(sel);

/**
 * Atajo para document.querySelectorAll
 * Devuelve un array real de elementos (no NodeList).
 */
const $$ = sel => Array.from(document.querySelectorAll(sel));

/**
 * Todo el código se ejecuta una vez que el DOM
 * fue completamente cargado.
 */
document.addEventListener('DOMContentLoaded', () => {

  // AGREGAR FILAS DINÁMICAS EN PEDIDOS (crear pedido)

  /**
   * Se busca el tbody de la tabla de ítems del pedido.
   * Solo se ejecuta si la tabla existe en la vista actual.
   */
  const tbl = $('#tbl tbody');

  if (tbl) {
    // Se crea dinámicamente el botón "Agregar ítem"
    const addBtn = document.createElement('button');
    addBtn.type = 'button';
    addBtn.className = 'btn';
    addBtn.textContent = 'Agregar ítem';

    // Se inserta el botón luego de la tabla
    tbl.parentElement.after(addBtn);

    /**
     * Al hacer clic en el botón:
     * - Se agrega una nueva fila a la tabla
     * - Se crean inputs para producto, cantidad y precio
     */
    addBtn.addEventListener('click', () => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><input type="number" name="producto_id[]" min="1"></td>
        <td><input type="number" name="cantidad[]" min="1" value="1"></td>
        <td><input type="number" step="0.01" name="precio_unitario[]" min="0"></td>`;
      
      // Se agrega la fila al final de la tabla
      tbl.appendChild(tr);
    });
  }

  // CONFIRMACIONES ESTÁNDAR (ELIMINAR, CANCELAR, ETC.)

  /**
   * Se buscan todos los botones y links que tengan
   * el atributo data-confirm.
   */
  $$('button[data-confirm], a[data-confirm]').forEach(el => {
    el.addEventListener('click', (e) => {
      /**
       * Se muestra un confirm() con el texto definido
       * en el atributo data-confirm.
       *
       * Si el usuario cancela, se evita la acción.
       */
      if (!confirm(el.getAttribute('data-confirm'))) e.preventDefault();
    });
  });

  // FORMATEO AUTOMÁTICO DE PRECIOS

  /**
   * Para todos los inputs numéricos con step="0.01",
   * se fuerza el formato a dos decimales al salir del campo.
   */
  $$('input[step="0.01"]').forEach(inp => {
    inp.addEventListener('change', () => {
      // Solo se formatea si el input no está vacío
      if (inp.value !== '') inp.value = Number(inp.value).toFixed(2);
    });
  });
});
