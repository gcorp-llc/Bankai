<?php
/**
 * Persian / mixed-script text normalizer for SEO comparisons.
 * Pure PHP — no WordPress dependency (testable in isolation).
 *
 * Does NOT mutate stored content; only produces comparison forms.
 *
 * @package Bankai
 */

declare(strict_types=1);

if (!defined('ABSPATH') && !defined('BANKAI_SEO_TEST')) {
    // Allow CLI unit tests without WP bootstrap when BANKAI_SEO_TEST is defined.
}

final class Bankai_SEO_Text_Normalizer
{

    private static function lower(string $text): string
    {
        return function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
    }

    private static function pos(string $haystack, string $needle): int|false
    {
        if (function_exists('mb_strpos')) {
            return mb_strpos($haystack, $needle);
        }
        $p = strpos($haystack, $needle);
        return $p === false ? false : $p;
    }

    private static function substr_count_safe(string $haystack, string $needle): int
    {
        if (function_exists('mb_substr_count')) {
            return mb_substr_count($haystack, $needle);
        }
        return substr_count($haystack, $needle);
    }

    /**
     * Normalize for equality / containment checks (keywords, titles, body).
     */
    public static function normalize(string $text): string
    {
        if ($text === '') {
            return '';
        }

        // Unify Arabic presentation forms commonly mixed into Persian input
        $map = [
            "\xD9\x8A" => "\xDB\x8C", // ي → ی
            "\xD9\x89" => "\xDB\x8C", // ى → ی
            "\xD9\x83" => "\xDA\xA9", // ك → ک
            "\xD9\xA0" => '0', "\xD9\xA1" => '1', "\xD9\xA2" => '2', "\xD9\xA3" => '3', "\xD9\xA4" => '4',
            "\xD9\xA5" => '5', "\xD9\xA6" => '6', "\xD9\xA7" => '7', "\xD9\xA8" => '8', "\xD9\xA9" => '9',
            "\xDB\xB0" => '0', "\xDB\xB1" => '1', "\xDB\xB2" => '2', "\xDB\xB3" => '3', "\xDB\xB4" => '4',
            "\xDB\xB5" => '5', "\xDB\xB6" => '6', "\xDB\xB7" => '7', "\xDB\xB8" => '8', "\xDB\xB9" => '9',
        ];
        $text = strtr($text, $map);

        // ZWNJ / ZWJ / BOM / soft hyphen → space or drop
        $text = str_replace(["\xE2\x80\x8C", "\xE2\x80\x8D", "\xEF\xBB\xBF", "\xC2\xAD"], [' ', '', '', ''], $text);

        // NBSP and other unicode spaces → regular space
        $text = preg_replace('/[\x{00A0}\x{2000}-\x{200B}\x{202F}\x{205F}\x{3000}]/u', ' ', $text) ?? $text;

        // Collapse whitespace
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        $text = trim($text);

        // Case-fold Latin
        $text = self::lower($text);

        return $text;
    }

    /**
     * True if $needle appears inside $haystack after normalization.
     */
    public static function contains(string $haystack, string $needle): bool
    {
        $n = self::normalize($needle);
        if ($n === '') {
            return false;
        }
        $h = self::normalize($haystack);
        return self::pos($h, $n) !== false;
    }

    /**
     * True if normalized haystack starts with normalized needle.
     */
    public static function starts_with(string $haystack, string $needle): bool
    {
        $n = self::normalize($needle);
        if ($n === '') {
            return false;
        }
        $h = self::normalize($haystack);
        return self::pos($h, $n) === 0;
    }

    /**
     * Count non-overlapping occurrences of needle in haystack (normalized).
     */
    public static function count_occurrences(string $haystack, string $needle): int
    {
        $n = self::normalize($needle);
        if ($n === '') {
            return 0;
        }
        $h = self::normalize($haystack);
        if ($h === '') {
            return 0;
        }
        return self::substr_count_safe($h, $n);
    }

    /**
     * Word tokens for density / length (Persian + Latin), after normalize.
     *
     * @return list<string>
     */
    public static function words(string $text): array
    {
        $t = self::normalize($text);
        if ($t === '') {
            return [];
        }
        // Keep letters/numbers in any script; drop pure punctuation tokens
        $parts = preg_split('/\s+/u', $t, -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($parts)) {
            return [];
        }
        $out = [];
        foreach ($parts as $p) {
            $p = preg_replace('/^[\p{P}\p{S}]+|[\p{P}\p{S}]+$/u', '', $p) ?? $p;
            if ($p !== '') {
                $out[] = $p;
            }
        }
        return $out;
    }

    public static function word_count(string $text): int
    {
        return count(self::words($text));
    }
}
