<?php
namespace Presentation\Helpers;

/**
 * Clase Validator
 *
 * Helper de validación básica para datos de entrada.
 *
 * Se utiliza principalmente en controladores
 * para validar datos provenientes de formularios
 * antes de ejecutar lógica de aplicación.
 */
final class Validator
{
     /**
     * Valida que ciertos campos estén presentes y no vacíos.
     *
     * Si algún campo requerido no existe o está vacío:
     * - Se responde con código HTTP 422 (Unprocessable Entity)
     * - Se interrumpe la ejecución
     *
     * @param array $data   Datos a validar (ej. $_POST)
     * @param array $fields Lista de campos requeridos
     */
    public static function requireFields(array $data, array $fields): void
    {
        foreach ($fields as $f) {

            // Verifica que el campo exista y no esté vacío
            if (!isset($data[$f]) || $data[$f] === '') {
                
                // Código HTTP adecuado para errores de validación
                http_response_code(422);
                
                // Se corta la ejecución informando el campo faltante
                die("Campo requerido: $f");
            }
        }
    }
}
