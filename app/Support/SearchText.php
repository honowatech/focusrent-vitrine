<?php

namespace App\Support;

/**
 * Traitement du texte partagé par l'index, le moteur PHP et le suivi.
 */
class SearchText
{
    /**
     * Replie accents et majuscules : « Échéance » → « echeance ».
     */
    public static function fold(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = str_replace(['’', '‘', '`', '´'], "'", $text);

        if (class_exists(\Normalizer::class)) {
            $normalized = \Normalizer::normalize($text, \Normalizer::FORM_D);
            if ($normalized !== false) {
                $text = preg_replace('/\p{Mn}+/u', '', $normalized) ?? $text;
            }
        } else {
            $text = strtr($text, [
                'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a', 'ã' => 'a',
                'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
                'î' => 'i', 'ï' => 'i', 'í' => 'i',
                'ô' => 'o', 'ö' => 'o', 'ó' => 'o', 'õ' => 'o',
                'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ú' => 'u',
                'ç' => 'c', 'ñ' => 'n', 'œ' => 'oe', 'æ' => 'ae',
            ]);
        }

        return $text;
    }

    /**
     * Découpe en mots repliés, sans mots vides.
     *
     * @return list<string>
     */
    public static function tokens(string $text, bool $dropStopWords = true): array
    {
        $folded = self::fold($text);
        $parts = preg_split('/[^a-z0-9]+/', $folded, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (! $dropStopWords) {
            return array_values($parts);
        }

        $stop = array_flip((array) config('search.stop_words', []));

        return array_values(array_map(
            [self::class, 'stem'],
            array_filter($parts, fn (string $part) => ! isset($stop[$part]))
        ));
    }

    /**
     * Racine légère : retire la marque du pluriel (« loyers » → « loyer »),
     * des deux côtés (index et requête), en JS comme en PHP.
     */
    public static function stem(string $word): string
    {
        if (strlen($word) > 3 && ! ctype_digit($word) && ($word[-1] === 's' || $word[-1] === 'x')) {
            return substr($word, 0, -1);
        }

        return $word;
    }

    /**
     * Texte brut : espaces normalisés, Markdown et balises retirés.
     */
    public static function clean(string $text): string
    {
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\[([^\]]+)\]\([^)]*\)/u', '$1', $text) ?? $text;
        $text = preg_replace('/(\*\*|__|`)/u', '', $text) ?? $text;
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    /**
     * Retire les phrases qui annoncent un montant, pour ne jamais afficher
     * un prix hors de sa fiche.
     */
    public static function withoutPrices(string $text): string
    {
        $sentences = preg_split('/(?<=[.!?])\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $kept = array_filter($sentences, fn (string $sentence) => ! self::mentionsPrice($sentence));

        return trim(implode(' ', $kept));
    }

    public static function mentionsPrice(string $text): bool
    {
        return (bool) preg_match('/(\d[\d\s\x{a0}\x{202f},.]*\s?(fcfa|xaf|f\s?cfa)\b)|\b(fcfa|xaf)\b/iu', $text);
    }

    /**
     * Coupe proprement à $max caractères, sur un mot, avec une ellipse.
     */
    public static function limit(string $text, int $max): string
    {
        $text = trim($text);

        if (mb_strlen($text) <= $max) {
            return $text;
        }

        $cut = mb_substr($text, 0, $max - 1);
        $space = mb_strrpos($cut, ' ');
        if ($space !== false && $space > (int) ($max * 0.6)) {
            $cut = mb_substr($cut, 0, $space);
        }

        return rtrim($cut, ' ,;:.').'…';
    }

    /**
     * Identifiant stable à partir d'un libellé : « Gestion de contrats » → « gestion-de-contrats ».
     */
    public static function slug(string $text): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', '-', self::fold($text)) ?? '', '-');
    }
}
