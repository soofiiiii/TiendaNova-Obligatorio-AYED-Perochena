<?php
namespace Presentation\Utils;

/**
 * Clase Formatter
 *
 * Utilitario de la capa de presentación para formatear valores
 * antes de mostrarlos en vistas.
 *
 * Centraliza formatos comunes (moneda, fechas, booleanos)
 * evitando duplicación de lógica en el HTML.
 */
final class Formatter
{
    /**
     * Formatea un valor numérico como moneda.
     *
     * @param float  $v      Valor a formatear
     * @param string $prefix Prefijo de moneda (por defecto U$S)
     *
     * @return string Valor formateado con dos decimales
     */
    public static function money(float $v, string $prefix = 'U$S '): string
    {
        return $prefix . number_format($v, 2, '.', ',');
    }

    /**
     * Formatea un objeto DateTime para su visualización.
     *
     * @param \DateTimeInterface $dt  Fecha y hora a formatear
     * @param string             $fmt Formato de salida (por defecto Y-m-d H:i)
     */
    public static function dateTime(\DateTimeInterface $dt, string $fmt = 'Y-m-d H:i'): string
    {
        return $dt->format($fmt);
    }

    /**
     * Devuelve un badge visual a partir de un valor booleano.
     *
     * @param bool   $b   Valor booleano
     * @param string $yes Texto a mostrar si es true
     * @param string $no  Texto a mostrar si es false
     *
     * @return string HTML del badge correspondiente
     */
    public static function boolBadge(bool $b, string $yes='Sí', string $no='No'): string
    {
        // Texto a mostrar según el valor
        $text = $b ? $yes : $no;

        // Clase CSS distinta para estados positivos
        $cls  = $b ? 'pill pill-ok' : 'pill';
        return "<span class=\"$cls\">$text</span>";
    }
}
