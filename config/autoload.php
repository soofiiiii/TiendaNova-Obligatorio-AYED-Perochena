<?php
/**
 * Autoloader del proyecto
 *
 * Se encarga de cargar automáticamente las clases cuando se usan,
 * evitando tener que hacer require/include manuales en cada archivo.
 *
 * Implementa un autoload basado en namespaces (PSR-4 simplificado).
 */
spl_autoload_register(function (string $class): void {
    /**
     * Mapa de namespaces a directorios base.
     *
     * La clave es el prefijo del namespace.
     * El valor es el directorio físico donde se encuentran las clases.
     */
    $map = [
        'Domain\\' => __DIR__ . '/../src/Domain',
        'Application\\' => __DIR__ . '/../src/Application',
        'Infrastructure\\' => __DIR__ . '/../src/Infrastructure',
        'Core\\' => __DIR__ . '/../src/Core',
        'Presentation\\' => __DIR__ . '/../src/Presentation',

    ];

    /**
     * Se recorre el mapa para ver si la clase solicitada
     * pertenece a alguno de los namespaces definidos.
     */
    foreach ($map as $prefix => $baseDir) {

        // Si la clase no empieza con el prefijo actual, se ignora
        if (strncmp($class, $prefix, strlen($prefix)) !== 0)
            continue;

        /**
         * Se obtiene la parte relativa del nombre de la clase
         * quitando el prefijo del namespace.
         *
         * Ejemplo:
         * Domain\Entities\Producto → Entities\Producto
         */
        $rel = substr($class, strlen($prefix));
        
        // Si el archivo existe, se carga y se termina el autoload
        $file = $baseDir . '/' . str_replace('\\', '/', $rel) . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});
