<header class="sticky top-0 z-20 bg-white shadow-sm h-16 flex items-center justify-between px-6 border-b border-gray-200">

    <!-- BUSCADOR -->
    <form action="<?= BASE_PATH ?>/buscar"
          method="get"
          class="relative flex items-center w-full max-w-lg mr-4">

        <svg class="w-5 h-5 text-gray-400 absolute left-3"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 1 1-14 0 7 7 0 0 1 14 0z" />
        </svg>

        <input
            type="text"
            name="query"
            value="<?= htmlspecialchars($_GET['query'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            placeholder="Buscar..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-full
                   focus:outline-none focus:ring-2 focus:ring-indigo-500
                   focus:border-indigo-500 transition text-sm"
        />
    </form>

    <!-- DERECHA -->
    <div class="relative flex items-center space-x-4">

        <!-- CAMPANA -->
        <button id="btn-alertas"
                type="button"
                class="relative p-2 rounded-full text-gray-500
                       hover:bg-gray-100 hover:text-indigo-600 transition">

            <!-- SVG CORRECTO (campana completa) -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 17h5l-1.405-1.405
                         A2.032 2.032 0 0118 14.158V11
                         a6.002 6.002 0 00-4-5.659V5
                         a2 2 0 10-4 0v.341
                         C7.67 6.165 6 8.388 6 11v3.159
                         c0 .538-.214 1.055-.595 1.436L4 17h5
                         m6 0v1a3 3 0 11-6 0v-1
                         m6 0H9" />
            </svg>

            <?php if (!empty($alertasStock)): ?>
                <span class="absolute top-1 right-1 h-2 w-2 rounded-full
                             bg-red-500 ring-2 ring-white"></span>
            <?php endif; ?>
        </button>

        <!-- PANEL ALERTAS -->
        <div id="panel-alertas"
             class="hidden absolute right-0 top-12 w-96 bg-white
                    shadow-xl rounded-xl border border-gray-200 z-50">

            <div class="px-4 py-3 border-b font-semibold text-gray-800">
                Alertas de stock
            </div>

            <?php if (empty($alertasStock)): ?>
                <p class="p-4 text-sm text-gray-500">
                    No hay alertas de stock.
                </p>
            <?php else: ?>
                <ul class="divide-y text-sm">
                    <?php foreach ($alertasStock as $a): ?>
                        <li class="p-3 flex justify-between items-center">

                            <div>
                                <p class="font-medium">
                                    <?= htmlspecialchars($a['producto']->getNombre(), ENT_QUOTES, 'UTF-8') ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    Stock: <?= $a['cantidad'] ?> / Mín: <?= $a['minimo'] ?>
                                </p>
                            </div>

                            <?php if ($a['nivel'] === 'critico'): ?>
                                <span class="px-2 py-1 text-xs rounded-full
                                             bg-red-100 text-red-700 font-semibold">
                                    CRÍTICO
                                </span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs rounded-full
                                             bg-orange-100 text-orange-700 font-semibold">
                                    BAJO
                                </span>
                            <?php endif; ?>

                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- USUARIO -->
        <div class="flex items-center space-x-2 cursor-pointer">
            <img class="h-9 w-9 rounded-full border-2 border-gray-300"
                 src="https://placehold.co/36x36/8b5cf6/ffffff?text=U"
                 alt="Usuario">

            <div class="hidden sm:block">
                <p class="text-sm font-semibold text-gray-800">
                    Admin TiendaNova
                </p>
                <p class="text-xs text-gray-500">
                    Administrador
                </p>
            </div>
        </div>

    </div>
</header>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btn-alertas');
    const panel = document.getElementById('panel-alertas');

    btn?.addEventListener('click', (e) => {
        e.stopPropagation();
        panel.classList.toggle('hidden');
    });

    document.addEventListener('click', () => {
        panel.classList.add('hidden');
    });
});
</script>

