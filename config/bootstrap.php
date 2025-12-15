<?php

// Se carga el autoloader del proyecto
// A partir de acá, las clases se cargan automáticamente por namespace
require __DIR__ . '/autoload.php';

use Infrastructure\Database\ConnectionFactory;
use Infrastructure\Repositories\PdoStockRepository;
use Infrastructure\Repositories\PdoProductoRepository;
use Infrastructure\Repositories\PdoCategoriaRepository;


// DEFINICIÓN DE BASE_PATH

/**
 * BASE_PATH se utiliza para construir URLs relativas
 * independientemente de si el proyecto está en la raíz
 * del servidor o en una subcarpeta.
 */
$scriptName = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
define('BASE_PATH', $scriptName === '' ? '' : $scriptName);


// VARIABLES DE ENTORNO

/**
 * Se definen las variables de entorno necesarias
 * para la conexión a la base de datos.
 *
 * Si no existen variables reales de entorno,
 * se utilizan valores por defecto.
 */
$env = [
    'DB_HOST'    => getenv('DB_HOST')    ?: '127.0.0.1',
    'DB_PORT'    => getenv('DB_PORT')    ?: '3306',        // Puerto explícito
    'DB_NAME'    => getenv('DB_NAME')    ?: 'tiendanova',
    'DB_USER'    => getenv('DB_USER')    ?: 'root',
    'DB_PASS'    => getenv('DB_PASS')    ?: '',
    'DB_CHARSET' => 'utf8mb4',
];

// INICIALIZACIÓN DE PDO


/**
 * Se inicializa la conexión a la base de datos.
 * Este método crea una única instancia PDO compartida
 * por toda la aplicación.
 */
ConnectionFactory::init($env);


// CONTENEDOR DE SERVICIOS (Service Container)


/**
 * Este contenedor permite la inyección de dependencias
 * de forma manual y controlada.
 *
 * Los controladores no necesitan saber cómo se crean
 * los repositorios, solo los solicitan al contenedor.
 */
$container = [];


// REPOSITORIO DE STOCK

/**
 * Se define una fábrica para el repositorio de stock.
 * Cada llamada devuelve una nueva instancia.
 */
$container['stockRepo'] = function () {
    return new PdoStockRepository();
};

// REPOSITORIO DE CATEGORÍAS

/**
 * Fábrica del repositorio de categorías.
 * Encapsula toda la lógica del árbol N-ario.
 */
$container['catRepo'] = function () {
    return new PdoCategoriaRepository();
};

// REPOSITORIO DE PRODUCTOS

/**
 * El repositorio de productos depende de:
 * - StockRepository
 * - CategoriaRepository
 *
 * El contenedor se encarga de resolver esas dependencias.
 */
$container['prodRepo'] = function () use ($container) {
    return new PdoProductoRepository(
        $container['stockRepo'](),
        $container['catRepo']()
    );
};

/**
 * Redefinición explícita del repositorio de productos.
 *
 * Se crea una instancia concreta de CategoriaRepository
 * para evitar inconsistencias y mantener control explícito
 * de las dependencias.
 */
$container['prodRepo'] = function () use ($container) {
    $catRepo = new PdoCategoriaRepository();
    return new PdoProductoRepository(
        $container['stockRepo'](),
        $catRepo
    );
};

// EXPOSICIÓN GLOBAL

/**
 * Se expone el contenedor de servicios de forma global
 * para que pueda ser utilizado desde controladores
 * u otras capas de la aplicación.
 */
$GLOBALS['container'] = $container;