<?php
namespace Core;

use Application\StockAlertaService;

/**
 * Clase ViewRenderer
 *
 * Encargada de renderizar vistas PHP dentro de un layout maestro.
 * Centraliza:
 * - Carga de vistas
 * - Paso de datos a la vista
 * - Uso de layout común
 * - Lógica global de UI (alertas de stock)
 */
final class ViewRenderer
{
     /**
     * Renderiza una vista dentro del layout principal.
     *
     * @param string $view  Ruta de la vista (relativa a Presentation/Views)
     * @param array  $data  Datos a exponer a la vista
     */
    public static function render(string $view, array $data = []): void
    {
         /**
         * Extrae el array $data a variables individuales.
         *
         * Ejemplo:
         * ['productos' => $productos] → $productos disponible en la vista
         *
         * EXTR_OVERWRITE permite que los datos pasados
         * sobrescriban variables existentes si es necesario.
         */
        extract($data, EXTR_OVERWRITE);

        // RESOLUCIÓN DE RUTAS

        /**
         * Directorio base donde viven todas las vistas.
         */
        $base = dirname(__DIR__, 1) . '/Presentation/Views/';


        /**
         * Archivo de la vista solicitada.
         */
        $viewFile = $base . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new \Exception("Vista no encontrada: $viewFile");
        }

        /**
         * Layout maestro del sistema.
         * Todas las vistas se renderizan dentro de este layout.
         */
        $layout = $base . 'layout/layout.php';

        // Validación defensiva: el layout debe existir
        if (!file_exists($layout)) {
            throw new \Exception("Layout no encontrado: $layout");
        }

        // RENDER DE LA VISTA

        /**
         * Se usa output buffering para capturar
         * el HTML generado por la vista.
         *
         * El contenido resultante se guarda en $content
         * y luego se inyecta dentro del layout.
         */
        ob_start();
        require $viewFile;
        $content = ob_get_clean();


        // ALERTAS DE STOCK (GLOBAL)

        /**
         * Estas variables estarán disponibles en el layout
         * para mostrar alertas globales de stock bajo.
         */
        $alertasStock = [];
        $hayAlertasStock = false;

        /**
         * Se verifica que el contenedor tenga disponibles
         * los repositorios necesarios antes de crear el servicio.
         */
        if (isset($GLOBALS['container']['stockRepo'], $GLOBALS['container']['prodRepo'])) {

            // Se resuelven dependencias desde el contenedor
            $stockRepo = $GLOBALS['container']['stockRepo']();
            $prodRepo = $GLOBALS['container']['prodRepo']();

            /**
             * Servicio de aplicación encargado de calcular
             * alertas de stock bajo combinando stock + productos.
             */
            $alertaSvc = new StockAlertaService($stockRepo, $prodRepo);
            
            // Se obtienen las alertas
            $alertasStock = $alertaSvc->obtenerAlertas();

            // Flag útil para el layout (mostrar badge, banner, etc.)
            $hayAlertasStock = !empty($alertasStock);
        }

        // RENDER FINAL (LAYOUT)

        /**
         * Se renderiza el layout maestro.
         *
         * El layout tendrá acceso a:
         * - $content (HTML de la vista)
         * - $alertasStock
         * - $hayAlertasStock
         * - Variables extraídas desde $data
         */
        require $layout;
    }
}
