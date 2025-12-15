<?php
namespace Core;

/**
 * Clase Router
 *
 * Encargada de:
 * - Registrar rutas HTTP (GET / POST)
 * - Resolver la URL solicitada
 * - Instanciar el controlador correspondiente
 * - Ejecutar la acción asociada
 */
final class Router
{
    /**
     * Estructura de rutas.
     *
     * Se organizan por método HTTP:
     * [
     *   'GET'  => [ '/ruta' => [Controller::class, 'metodo'] ],
     *   'POST' => [ '/ruta' => [Controller::class, 'metodo'] ]
     * ]
     */
    private array $routes = ['GET' => [], 'POST' => []];

    /**
     * Registra una ruta GET.
     */
    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    /**
     * Registra una ruta POST.
     */
    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    /**
     * Resuelve y despacha la solicitud HTTP actual.
     *
     * Flujo:
     * - Detecta método HTTP
     * - Normaliza la URL
     * - Busca el handler correspondiente
     * - Instancia el controlador
     * - Ejecuta la acción
     */
    public function dispatch(): void
    {
        // Método HTTP actual (GET o POST)
        $method = $_SERVER['REQUEST_METHOD'];

        // URI solicitada (sin query string)
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        /**
         * BASE PATH
         *
         * Permite que el proyecto funcione correctamente
         * tanto en la raíz del servidor como en subcarpetas.
         */
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        
        // Ruta normalizada que se usará para buscar en el router
        $path = '/' . ltrim(substr($uri, strlen($base)), '/');

        // Se busca el handler correspondiente a método + ruta
        $handler = $this->routes[$method][$path] ?? null;

        // Si no existe la ruta, se responde 404
        if (!$handler) {
            http_response_code(404);
            echo "404";
            return;
        }

        // El handler siempre es [ClaseController, metodo]
        [$class, $action] = $handler;

        /**
         * Se resuelve el controlador teniendo en cuenta
         * sus dependencias (inyección manual).
         */
        $controller = $this->resolveController($class);

        // Se ejecuta la acción del controlador
        $controller->$action();
    }


    // RESOLUCIÓN DE CONTROLADORES

    /**
     * Instancia el controlador correspondiente.
     *
     * Este método centraliza la inyección de dependencias
     * evitando que el router tenga lógica dispersa.
     */
    private function resolveController(string $class)
    {
        /**
         * ProductoController requiere acceso a StockRepository
         * para manejar activaciones y reactivaciones.
         */
        if ($class === \Presentation\Controllers\ProductoController::class) {
            $stockRepo = $GLOBALS['container']['stockRepo']();
            return new $class($stockRepo);
        }

        /**
         * StockController necesita StockRepository
         * para ajustes y consultas de inventario.
         */
        if ($class === \Presentation\Controllers\StockController::class) {
            $stockRepo = $GLOBALS['container']['stockRepo']();
            return new $class($stockRepo);
        }

        /**
         * PedidoController necesita StockRepository
         * para actualizar stock al recibir pedidos.
         */
        if ($class === \Presentation\Controllers\PedidoController::class) {
            $stockRepo = $GLOBALS['container']['stockRepo']();
            return new $class($stockRepo);
        }

        /**
         * Para el resto de los controladores,
         * no se requieren dependencias externas.
         */
        return new $class();
    }

}
