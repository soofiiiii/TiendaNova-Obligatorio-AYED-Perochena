<?php
namespace Presentation\Utils;

/**
 * Clase Validator
 *
 * Utilitario de validación para la capa de presentación.
 *
 * A diferencia de validaciones duras (die / excepciones),
 * este validator está pensado para:
 * - Formularios HTML
 * - Mostrar errores al usuario
 * - Mantener el flujo de la aplicación
 */
final class Validator
{
    /**
     * Valida que los campos requeridos existan y no estén vacíos.
     *
     * Devuelve un array de errores en lugar de cortar la ejecución,
     * permitiendo mostrar feedback al usuario.
     *
     * @param array $data   Datos a validar (ej. $_POST)
     * @param array $fields Lista de campos obligatorios
     *
     * @return array Array asociativo campo => mensaje de error
     */
    public static function required(array $data, array $fields): array
    {
        $errors = [];
        foreach ($fields as $f) {
            // Se valida existencia y contenido no vacío
            if (!isset($data[$f]) || trim((string)$data[$f]) === '') $errors[$f] = 'Requerido';
        }
        return $errors;
    }

     /**
     * Valida si un string es un email válido.
     *
     * Utiliza el filtro nativo de PHP para emails.
     */
    public static function email(string $value): bool
    {
        return (bool)filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Valida si un valor es un número positivo.
     *
     * @param string|int|float $value     Valor a validar
     * @param bool             $allowZero Indica si se permite el cero
     */
    public static function positiveNumber(string|int|float $value, bool $allowZero=false): bool
    {
        $n = (float)$value;
        return $allowZero ? $n >= 0 : $n > 0;
    }

    /**
     * Convierte un array de errores en HTML listo para mostrar.
     *
     * Devuelve una lista <ul> con los errores formateados,
     * ideal para mostrar debajo de formularios.
     */
    public static function errorsToHtml(array $errors): string
    {
        // Si no hay errores, no se genera HTML
        if (!$errors) return '';

        /**
         * Se transforma cada error en un <li>.
         * La clave representa el campo.
         */
        $lis = array_map(fn($k,$v)=>"<li><b>$k:</b> $v</li>", array_keys($errors), $errors);
        return '<ul class="hint">'.implode('', $lis).'</ul>';
    }

    
}
