<aside
    class="w-64 bg-white border-r border-gray-200 p-4 flex flex-col space-y-8 h-full hidden lg:flex shadow-xl fixed z-30">

    <!-- Logo -->
    <div class="flex items-center space-x-2 text-2xl font-extrabold text-indigo-700 pb-4 border-b border-gray-100">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253M12 6.253C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253">
            </path>
        </svg>
        <span>TiendaNova</span>
    </div>

    <?php
    // Garantizar que la variable existe
    $active = $active ?? '';

    // Función para aplicar la clase activa
    function navClass(string $key, string $active): string
    {
        return 'nav-link-light ' . ($active === $key ? 'nav-link-active' : '');
    }
    ?>

    <!-- Navegación -->
    <nav class="flex-grow">
        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3 px-3">GESTIÓN</h3>

        <ul class="space-y-1">
            <li>
                <a href="<?= BASE_PATH ?>/" class="<?= navClass('estadisticas', $active) ?>">
                    Estadísticas
                </a>
            </li>

            <li>
                <a href="<?= BASE_PATH ?>/categorias" class="<?= navClass('categorias', $active) ?>">
                    Categorías
                </a>
            </li>

            <li>
                <a href="<?= BASE_PATH ?>/productos" class="<?= navClass('productos', $active) ?>">
                    Productos
                </a>
            </li>

            <li>
                <a href="<?= BASE_PATH ?>/proveedores" class="<?= navClass('proveedores', $active) ?>">
                    Proveedores
                </a>
            </li>

            <li>
                <a href="<?= BASE_PATH ?>/stock" class="<?= navClass('stock', $active) ?>">
                    Stock / Inventario
                </a>
            </li>

            <li>
                <a href="<?= BASE_PATH ?>/pedidos" class="<?= navClass('pedidos', $active) ?>">
                    Pedidos
                </a>
            </li>
        </ul>
    </nav>

    <!-- Acciones rápidas -->
    <div class="mt-auto pt-4 border-t border-gray-100">
        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3 px-3">
            ACCIONES RÁPIDAS
        </h3>

        <a href="<?= BASE_PATH ?>/productos/crear"
            class="btn-primary-light w-full relative flex items-center justify-center mb-2">

            <!-- Ícono fijo -->
            <svg class="w-4 h-4 absolute left-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>

            <!-- Texto centrado -->
            <span class="text-center">Nuevo Producto</span>
        </a>

        <a href="<?= BASE_PATH ?>/pedidos/crear"
            class="btn-primary-light w-full relative flex items-center justify-center">

            <!-- Ícono fijo -->
            <svg class="w-4 h-4 absolute left-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>

            <!-- Texto centrado -->
            <span class="text-center">Nuevo Pedido</span>
        </a>

    </div>

</aside>