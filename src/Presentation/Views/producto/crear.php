<?php
use Domain\Entities\Categoria;

$titulo = "Crear producto";
$action = BASE_PATH . "/productos/crear";
$boton = "Crear producto";

/**
 * Armar una estructura de árbol simple para serializarla a JSON.
 *
 * @param Categoria $cat
 * @return array{id:int,nombre:string,nivel:int,children:array}
 */
$buildNode = function (Categoria $cat) use (&$buildNode): array {
    $children = [];
    foreach ($cat->getSubcategorias() as $child) {
        $children[] = $buildNode($child);
    }

    return [
        'id' => $cat->getId(),
        'nombre' => $cat->getNombre(),
        'nivel' => $cat->getNivel(),
        'children' => $children,
    ];
};

$treeData = [];
if (!empty($cats)) {
    foreach ($cats as $root) {
        $treeData[] = $buildNode($root);
    }
}
?>

<div class="max-w-4xl mx-auto bg-white shadow rounded-xl p-8">

    <h1 class="text-2xl font-bold text-gray-900 mb-6"><?= $titulo ?></h1>

    <form action="<?= $action ?>" method="POST" class="space-y-8">

        <!-- Datos básicos -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                <input type="text" name="nombre"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    required>
                <p class="mt-1 text-xs text-gray-500">
                    Nombre comercial del producto.
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Precio</label>
                <input type="number" name="precio" step="0.01" min="0"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    required>
                <p class="mt-1 text-xs text-gray-500">
                    Precio unitario (ej. 1499.99).
                </p>
            </div>

        </div>

        <!-- Descripción -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción (opcional)</label>
            <textarea name="descripcion" rows="3"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Descripción breve del producto..."></textarea>
        </div>

        <!-- Clasificación por categoría: padre + hija -->
        <div class="border-t pt-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Clasificación por categoría</h2>
            <p class="text-xs text-gray-500 mb-4">
                Primero elige una categoría. Si tiene subcategorías, deberás seleccionar una para
                que el producto quede correctamente clasificado.
            </p>

            <!-- Hidden real que consume el backend -->
            <input type="hidden" name="categoria_id" id="categoria-id-hidden">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Categoría padre -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                    <select id="categoria-padre"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        required>
                        <option value="">Selecciona una categoría</option>
                        <?php if (!empty($cats)): ?>
                            <?php foreach ($cats as $root): ?>
                                <option value="<?= $root->getId() ?>">
                                    <?= htmlspecialchars($root->getNombre(), ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        Ejemplo: “Electrónica”, “Ropa”, “Hogar”.
                    </p>
                </div>

                <!-- Categoría hija -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subcategoría</label>
                    <select id="categoria-hija"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        disabled>
                        <option value="">Primero selecciona la categoría</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500" id="categoria-hija-help">
                        Si la categoría no tiene subcategorias, se usará directamente como categoría del producto.
                    </p>
                </div>

            </div>

            <!-- Fallback para navegadores sin JS -->
            <noscript>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Categoría (modo simple, sin JavaScript)
                    </label>
                    <select name="categoria_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm">
                        <?php
                        $renderOptions = function (array $categorias, int $nivel = 0) use (&$renderOptions) {
                            foreach ($categorias as $c) {
                                $indent = str_repeat('— ', $nivel);
                                $label = $indent . htmlspecialchars($c->getNombre(), ENT_QUOTES, 'UTF-8');

                                echo '<option value="' . $c->getId() . '">' . $label . '</option>';

                                $children = $c->getSubcategorias();
                                if (!empty($children)) {
                                    $renderOptions($children, $nivel + 1);
                                }
                            }
                        };
                        if (!empty($cats)) {
                            $renderOptions($cats);
                        }
                        ?>
                    </select>
                </div>
            </noscript>
        </div>

        <!-- Proveedor (por ahora por ID, coherente con el controlador actual) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t pt-6">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Proveedor</label>

                <select name="proveedor_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm
                   focus:ring-indigo-500 focus:border-indigo-500" required>

                    <option value="">Selecciona un proveedor</option>

                    <?php if (!empty($proveedores)): ?>
                        <?php foreach ($proveedores as $prov): ?>
                            <option value="<?= $prov->getId() ?>">
                                <?= htmlspecialchars($prov->getNombreEmpresa(), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </select>

                <p class="mt-1 text-xs text-gray-500">
                    Selecciona la empresa proveedora del producto.
                </p>
            </div>


            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ubicación en depósito</label>
                <input type="text" name="ubicacion"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ej: DEP-1, EST-3-B" value="DEP-1">
            </div>

        </div>

        <!-- Stock inicial -->
        <div class="border-t pt-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Stock inicial</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad inicial</label>
                    <input type="number" name="stock_inicial" min="0"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        value="0">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock mínimo</label>
                    <input type="number" name="stock_minimo" min="0"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        value="0">
                    <p class="mt-1 text-xs text-gray-500">
                        Usado para generar alertas de inventario bajo.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4 space-x-3">
            <a href="<?= BASE_PATH ?>/productos"
                class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit"
                class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow hover:bg-indigo-700">
                <?= $boton ?>
            </button>
        </div>

    </form>

</div>

<!-- Lógica de selección padre/hija -->
<script>
    (function () {
        const CATEGORIES = <?= json_encode($treeData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

        const padreSelect = document.getElementById('categoria-padre');
        const hijaSelect = document.getElementById('categoria-hija');
        const hiddenInput = document.getElementById('categoria-id-hidden');
        const hijaHelpText = document.getElementById('categoria-hija-help');

        if (!padreSelect || !hijaSelect || !hiddenInput) return;

        function findNodeById(id, nodes) {
            for (const n of nodes) {
                if (n.id === id) return n;
                const found = findNodeById(id, n.children || []);
                if (found) return found;
            }
            return null;
        }

        function collectDescendants(node, level, acc) {
            if (!node || !node.children) return;
            for (const child of node.children) {
                acc.push({
                    id: child.id,
                    nombre: child.nombre,
                    level: level
                });
                collectDescendants(child, level + 1, acc);
            }
        }

        function resetHijaSelect(placeholder) {
            hijaSelect.innerHTML = '';
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = placeholder;
            hijaSelect.appendChild(opt);
        }

        padreSelect.addEventListener('change', function () {
            const value = this.value ? parseInt(this.value, 10) : 0;
            hiddenInput.value = '';

            if (!value) {
                hijaSelect.disabled = true;
                hijaSelect.required = false;
                resetHijaSelect('Primero selecciona la categoría padre');
                hijaHelpText.textContent = 'Si la categoría padre no tiene hijas, se usará directamente como categoría del producto.';
                return;
            }

            const node = findNodeById(value, CATEGORIES);
            const descendants = [];
            collectDescendants(node, 1, descendants);

            if (descendants.length === 0) {
                // No tiene hijas: usamos directamente el padre
                hijaSelect.disabled = true;
                hijaSelect.required = false;
                resetHijaSelect('Esta categoría no tiene subcategorías');
                hiddenInput.value = String(value);
                hijaHelpText.textContent = 'Esta categoría no tiene hijas, se usará directamente como categoría del producto.';
            } else {
                // Tiene hijas: obligamos a elegir una
                hijaSelect.disabled = false;
                hijaSelect.required = true;
                resetHijaSelect('Selecciona una subcategoría');

                descendants.forEach(function (item) {
                    const opt = document.createElement('option');
                    const indent = '— '.repeat(item.level);
                    opt.value = String(item.id);
                    opt.textContent = indent + item.nombre;
                    hijaSelect.appendChild(opt);
                });

                hiddenInput.value = '';
                hijaHelpText.textContent = 'Selecciona una subcategoría específica dentro de la rama elegida.';
            }
        });

        hijaSelect.addEventListener('change', function () {
            const value = this.value ? parseInt(this.value, 10) : 0;
            if (value > 0) {
                hiddenInput.value = String(value);
            } else {
                // Si se borra la hija, volvemos al comportamiento de solo padre
                const padreValue = padreSelect.value ? parseInt(padreSelect.value, 10) : 0;
                if (padreValue > 0) {
                    hiddenInput.value = String(padreValue);
                } else {
                    hiddenInput.value = '';
                }
            }
        });
    })();
</script>