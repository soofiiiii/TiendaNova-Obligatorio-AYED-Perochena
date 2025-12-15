<?php
namespace Presentation\Controllers;

use Application\BusquedaService;
use Infrastructure\Repositories\PdoCategoriaRepository;
use Infrastructure\Repositories\PdoProductoRepository;
use Core\ViewRenderer;

/**
 * BusquedaController
 *
 * Controlador encargado de manejar la búsqueda global del sistema.
 *
 * Actúa como intermediario entre:
 * - La request HTTP
 * - El servicio de aplicación BusquedaService
 * - La vista de resultados
 */
final class BusquedaController
{
    /**
     * Servicio de aplicación que resuelve la búsqueda.
     */
    private BusquedaService $svc;

    /**
     * Constructor del controlador.
     *
     * Se encarga de:
     * - Resolver repositorios necesarios
     * - Crear el servicio de búsqueda
     *
     * En este caso, se hace la inyección manual de dependencias.
     */
    public function __construct()
    {
        // Repositorio de categorías (árbol jerárquico)
        $catRepo = new PdoCategoriaRepository();

        // Repositorio de stock resuelto desde el contenedor global
        $stockRepo = $GLOBALS['container']['stockRepo']();

        /**
         * Repositorio de productos.
         *
         * Depende de:
         * - StockRepository (para estado y disponibilidad)
         * - CategoriaRepository (para búsquedas por rama)
         */
        $prodRepo = new PdoProductoRepository(
            $stockRepo,
            $catRepo
        );

        /**
         * Servicio de búsqueda global.
         *
         * Centraliza toda la lógica de interpretación
         * y resolución de búsquedas.
         */
        $this->svc = new BusquedaService(
            $catRepo,
            $prodRepo,
            $stockRepo
        );
    }


    /**
     * Endpoint principal para:
     * /buscar?query=...
     *
     * Se encarga de:
     * - Leer el término de búsqueda
     * - Delegar la lógica al servicio
     * - Renderizar la vista con los resultados
     */
    public function buscar(): void
    {
        /**
         * Se obtiene el término de búsqueda desde la query string.
         * Se aplica trim para evitar espacios innecesarios.
         */
        $query = isset($_GET['query']) ? trim((string) $_GET['query']) : '';

        /**
         * Se ejecuta la búsqueda global.
         *
         * El servicio devuelve un array estructurado
         * con distintos tipos de resultados.
         */
        $resultados = $this->svc->buscarGlobal($query);

        /**
         * Render de la vista de resultados.
         *
         * Se pasan únicamente los datos necesarios
         * para que la vista se mantenga simple.
         */
        ViewRenderer::render('busqueda/index', [
            'query' => $query,
            'categoriaExacta' => $resultados['categoriaExacta'],
            'productosRama' => $resultados['productosRama'],
            'categoriasRelacionadas' => $resultados['categoriasRelacionadas'],
            'productosCoincidentes' => $resultados['productosCoincidentes'],
            'modoStockBajo' => $resultados['modoStockBajo'] ?? false,
            'active' => '',
        ]);

    }
}
