<?php

namespace Presentation\Controllers;

use Application\EstadisticaService;
use Infrastructure\Repositories\PdoProductoRepository;
use Infrastructure\Repositories\PdoCategoriaRepository;
use Infrastructure\Repositories\PdoProveedorRepository;
use Infrastructure\Repositories\PdoPedidoRepository;
use Infrastructure\Repositories\PdoStockRepository;
use Core\ViewRenderer;

/**
 * EstadisticasController
 *
 * Controlador encargado de mostrar el panel de estadísticas
 * (dashboard) del sistema.
 *
 * Su responsabilidad es:
 * - Construir el servicio de estadísticas
 * - Delegar el cálculo de datos
 * - Renderizar la vista correspondiente
 */
final class EstadisticasController
{
    /**
     * Servicio de aplicación que calcula estadísticas.
     */
    private EstadisticaService $svc;


    /**
     * Constructor del controlador.
     *
     * Se encarga de:
     * - Crear los repositorios necesarios
     * - Inyectarlos en el servicio de estadísticas
     *
     * Toda la lógica pesada queda fuera del controlador.
     */
    public function __construct()
    {
        // Repositorio de stock (necesario para pedidos y productos)
        $stockRepo = new PdoStockRepository();

        // Repositorio de categorías (estructura jerárquica)
        $catRepo   = new PdoCategoriaRepository();

         /**
         * Repositorio de productos.
         *
         * Depende de:
         * - StockRepository (para estado y cantidades)
         * - CategoriaRepository (para clasificaciones)
         */
        $prodRepo = new PdoProductoRepository($stockRepo, $catRepo);

        /**
         * Servicio de estadísticas.
         *
         * Centraliza el cálculo de métricas del dashboard:
         * - Cantidades
         * - Totales
         * - Resúmenes generales
         */
        $this->svc = new EstadisticaService(
            $prodRepo,
            $catRepo,
            new PdoProveedorRepository(),
            new PdoPedidoRepository($stockRepo)
        );
    }

    /**
     * Acción principal del dashboard.
     *
     * Corresponde a la ruta "/".
     * Renderiza la vista con todas las estadísticas calculadas.
     */
    public function index(): void
    {
        ViewRenderer::render('estadisticas/index', [
            'stats'  => $this->svc->obtenerDashboard(),
            'active' => 'estadisticas'
        ]);
    }
}
