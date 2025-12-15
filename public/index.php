<?php
declare(strict_types=1);

// 1) SESIÓN Y ENTORNO

/**
 * Se inicializa la sesión solo si aún no fue iniciada.
 * Esto permite trabajar con sesiones PHP sin generar warnings.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Se define la zona horaria del sistema.
 * Evita inconsistencias al trabajar con fechas (DateTimeImmutable).
 */
date_default_timezone_set('America/Montevideo');


// 2) BOOTSTRAP DE LA APLICACIÓN

/**
 * Se carga el bootstrap general del proyecto.
 *
 * Este archivo se encarga de:
 * - Registrar el autoload
 * - Inicializar la conexión PDO
 * - Configurar el contenedor de dependencias
 */
require __DIR__ . '/../config/bootstrap.php';


// 3) CREACIÓN DEL ROUTER

use Core\Router;

/**
 * Se crea la instancia del Router.
 * El router será el encargado de:
 * - Interpretar la URL
 * - Determinar el controlador y método a ejecutar
 */
$router = new Router();

/**
 * Se cargan todas las rutas del sistema.
 * Las rutas están centralizadas en config/routes.php
 */
require __DIR__ . '/../config/routes.php';

// 4) DESPACHO DE LA SOLICITUD

/**
 * Se despacha la solicitud HTTP.
 *
 * El flujo es:
 * Request → Router → Controller → Dominio → Respuesta
 */
try {
    $router->dispatch();
} catch (Throwable $e) {
    // En caso de error no controlado, se responde con código 500
    http_response_code(500);

    /**
     * Modo desarrollo:
     * Si display_errors está activo, se muestra el detalle del error
     * para facilitar debugging.
     */
    if (filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN)) {
        echo '<h1>Error interno</h1>';
        echo '<pre>', htmlspecialchars($e->getMessage()), "\n\n", htmlspecialchars($e->getTraceAsString()), '</pre>';
    } else {
        /**
         * Modo producción:
         * Se muestra un mensaje genérico para no exponer información sensible.
         */
        echo 'Ocurrió un error interno.';
    }
}
