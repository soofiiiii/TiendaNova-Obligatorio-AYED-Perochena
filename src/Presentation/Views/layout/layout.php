<?php require __DIR__ . '/head.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require __DIR__ . '/sidebar.php'; ?>

    <div class="flex-1 overflow-y-auto lg:ml-64">
        <?php require __DIR__ . '/navbar.php'; ?>

        <main class="p-6 md:p-8">
            <?= $content ?? '' ?>
        </main>

        <?php require __DIR__ . '/footer.php'; ?>
    </div>
</div>


<!-- MODAL GLOBAL DE ELIMINACIÓN -->
<div id="modal-eliminar" class="fixed inset-0 hidden items-center justify-center z-50">

    <!-- Fondo oscuro suave -->
    <div id="modal-overlay"
        class="absolute inset-0 bg-black bg-opacity-0 backdrop-blur-sm opacity-0 transition-all duration-300">
    </div>

    <!-- Modal compacto -->
    <div id="modal-content" class="relative bg-white text-gray-900 rounded-xl shadow-xl w-full max-w-sm
                opacity-0 scale-95 transition-all duration-300 border border-gray-200">

        <!-- HEADER -->
        <div class="p-4 flex items-start gap-3">

            <!-- Icono compacto -->
            <div class="flex-shrink-0 pt-0.5">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 17c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <div class="space-y-1">
                <h2 class="text-lg font-semibold">Eliminar registro</h2>

                <p class="text-sm text-gray-600 leading-snug">
                    ¿Seguro que deseas eliminar este registro?
                    Esta acción no se puede deshacer.
                </p>
            </div>
        </div>

        <!-- DIVISOR -->
        <div class="border-t border-gray-200"></div>

        <!-- FOOTER -->
        <div class="px-4 py-3 bg-gray-50 flex justify-end gap-2 rounded-b-xl">

            <button id="cancelar-eliminar" class="px-3 py-1.5 rounded-lg border border-gray-300 text-gray-800 bg-white 
                           hover:bg-gray-100 transition text-sm">
                Cancelar
            </button>

            <a id="confirmar-eliminar" href="#" class="px-3 py-1.5 rounded-lg bg-red-600 text-white text-sm font-medium 
                      hover:bg-red-700 transition">
                Sí, eliminar
            </a>
        </div>

    </div>
</div>




<!-- SCRIPT PARA CONTROLAR EL MODAL -->
<script>
    document.addEventListener("DOMContentLoaded", () => {

        const modal = document.getElementById("modal-eliminar");
        const overlay = document.getElementById("modal-overlay");
        const content = document.getElementById("modal-content");
        const btnCancelar = document.getElementById("cancelar-eliminar");
        const btnConfirmar = document.getElementById("confirmar-eliminar");

        let deleteUrl = "";

        // Abrir modal
        function openModal(url) {
            deleteUrl = url;
            btnConfirmar.href = deleteUrl;

            modal.classList.remove("hidden");
            modal.classList.add("flex");

            // Animación fondo
            setTimeout(() => {
                overlay.classList.remove("opacity-0");
                overlay.classList.add("bg-opacity-50", "opacity-100");

                // Animación contenido
                content.classList.remove("opacity-0", "scale-95");
                content.classList.add("opacity-100", "scale-100");
            }, 10);
        }

        // Cerrar modal con animación
        function closeModal() {
            // Reversa animaciones
            overlay.classList.add("opacity-0");
            overlay.classList.remove("opacity-100");

            content.classList.add("opacity-0", "scale-95");
            content.classList.remove("opacity-100", "scale-100");

            // Esperar que termine la animación antes de ocultar
            setTimeout(() => {
                modal.classList.remove("flex");
                modal.classList.add("hidden");
            }, 250);
        }

        // Evento en links de eliminar
        document.querySelectorAll(".eliminar-btn").forEach(btn => {
            btn.addEventListener("click", e => {
                e.preventDefault();
                openModal(btn.dataset.deleteUrl);
            });
        });

        // Botón cancelar
        btnCancelar.addEventListener("click", closeModal);

        // Cerrar clickeando fuera (solo fondo)
        overlay.addEventListener("click", closeModal);

    });
</script>