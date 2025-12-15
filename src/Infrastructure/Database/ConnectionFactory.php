<?php
namespace Infrastructure\Database;

use PDO;
use PDOException;

/**
 * ConnectionFactory
 *
 * Clase responsable de crear y mantener una única instancia de PDO
 * durante toda la ejecución de la aplicación (patrón Singleton simple).
 *
 * Centraliza la configuración de conexión a la base de datos y evita
 * múltiples conexiones innecesarias.
 */
final class ConnectionFactory
{
    // Instancia única de PDO compartida por toda la aplicación
    private static ?PDO $pdo = null;

    /**
     * Inicializa la conexión PDO.
     *
     * Este método debe llamarse una sola vez al iniciar la aplicación.
     * Si la conexión ya fue creada, no vuelve a inicializarla.
     */
    public static function init(array $env): void
    {
        // Evita crear múltiples conexiones
        if (self::$pdo) return;

        // Lectura de variables de entorno con valores por defecto
        $host    = $env['DB_HOST']    ?? '127.0.0.1';
        $port    = $env['DB_PORT']    ?? '3306';          // Puerto explícito
        $db      = $env['DB_NAME']    ?? 'tiendanova';
        $user    = $env['DB_USER']    ?? 'root';
        $pass    = $env['DB_PASS']    ?? '';
        $charset = $env['DB_CHARSET'] ?? 'utf8mb4';

        /**
         * DSN (Data Source Name)
         *
         * Se incluye explícitamente el puerto para evitar
         * problemas en entornos donde MySQL no corre en el puerto por defecto.
         */
        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";


        /**
         * Opciones de configuración de PDO
         *
         * - ERRMODE_EXCEPTION: lanza excepciones ante errores SQL
         * - FETCH_ASSOC: devuelve arrays asociativos por defecto
         * - EMULATE_PREPARES = false: usa prepared statements reales
         */
        $opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ];

        try {
            // Creación efectiva de la conexión PDO
            self::$pdo = new PDO($dsn, $user, $pass, $opts);
        } catch (PDOException $e) {
           /**
             * Manejo de error de conexión.
             *
             * Se muestra un mensaje claro con datos clave para debugging.
             * En producción esto debería reemplazarse por logging controlado.
             */
            die("Error de conexión PDO: {$e->getMessage()} (host=$host, port=$port, db=$db)");
        }
    }

    /**
     * Devuelve la instancia PDO ya inicializada.
     *
     * Si se intenta acceder sin haber llamado antes a init(),
     * se lanza una excepción para evitar estados inconsistentes.
     */
    public static function pdo(): PDO
    {
        if (!self::$pdo) {
            throw new \RuntimeException('PDO no inicializado');
        }
        return self::$pdo;
    }
}
