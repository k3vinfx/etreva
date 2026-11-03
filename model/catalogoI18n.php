<?php
/*
 * model/catalogoI18n.php
 * Fuente de atracciones/categorías según el idioma de la sesión.
 * Ver sql_atracciones_i18n.sql (tabla catalogo.atracciones_traducciones + función atracciones_i18n).
 */
class CatalogoI18n
{
    public static function idioma(): string
    {
        $l = $_SESSION['lang'] ?? 'es';
        return class_exists('Lang') ? Lang::normalizar($l) : (in_array($l, ['es', 'en', 'pt', 'fr', 'de'], true) ? $l : 'es');
    }

    /** Para usar en FROM / JOIN: "catalogo.atracciones" o "catalogo.atracciones_i18n('pt')" */
    public static function atracciones(): string
    {
        $l = self::idioma(); // validado contra la lista fija de idiomas: seguro para interpolar
        return $l === 'es' ? 'catalogo.atracciones' : "catalogo.atracciones_i18n('{$l}')";
    }

    public static function categorias(): string
    {
        $l = self::idioma();
        return $l === 'es' ? 'catalogo.atraccion_categorias' : "catalogo.atracciones_categorias_{$l}";
    }
}
