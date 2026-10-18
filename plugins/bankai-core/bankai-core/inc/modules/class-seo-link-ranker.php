<?php
/**
 * Deterministic internal-link ranking (Phase E).
 * Pure PHP — no WordPress dependency for unit tests.
 *
 * Weights (documented, sum ≈ 100 when all fire):
 *  - exact_focus_in_target_body: 28
 *  - secondary_in_target_body:   14
 *  - phrase_overlap_headings:    16
 *  - focus_or_phrase_in_title:   12
 *  - taxonomy_overlap:           10
 *  - target_focus_match_source:  10
 *  - anchor_natural_in_source:   10
 *
 * Exclusions handled by caller: self, unpublished, already-linked.
 *
 * @package Bankai
 */

declare(strict_types=1);

final class Bankai_SEO_Link_Ranker
{
    private static function ulen(string $s): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($s, 'UTF-8');
        }
        return preg_match_all('/./us', $s) ?: 0;
    }

    private static function upos(string $h, string $n)
    {
        if (function_exists('mb_stripos')) {
            return mb_stripos($h, $n);
        }
        return stripos($h, $n);
    }

    public const WEIGHTS = [
        'exact_focus_in_target_body' => 28,
        'secondary_in_target_body'   => 14,
        'phrase_overlap_headings'    => 16,
        'focus_or_phrase_in_title'   => 12,
        'taxonomy_overlap'           => 10,
        'target_focus_match_source'  => 10,
        'anchor_natural_in_source'   => 10,
    ];

    /**
     * @param array{
     *   title?:string,content?:string,focus?:string,secondary?:list<string>,
     *   headings?:list<string>,existing_urls?:list<string>
     * } $source
     * @param array{
     *   id?:int,title?:string,content?:string,focus?:string,secondary?:list<string>,
     *   headings?:list<string>,categories?:list<string>,tags?:list<string>,permalink?:string
     * } $target
     * @return array{score:float,factors:array<string,float>,reasons:list<string>,anchor:string,evidence:array<string,mixed>}
     */
    public static function score(array $source, array $target): array
    {
        $N = class_exists('Bankai_SEO_Text_Normalizer') ? Bankai_SEO_Text_Normalizer::class : null;

        $contains = static function (string $hay, string $needle) use ($N): bool {
            $needle = trim($needle);
            if ($needle === '') {
                return false;
            }
            if ($N) {
                return $N::contains($hay, $needle);
            }
            return self::upos($hay, $needle) !== false;
        };

        $norm = static function (string $s) use ($N): string {
            return $N ? $N::normalize($s) : (function_exists('mb_strtolower') ? mb_strtolower(trim($s)) : strtolower(trim($s)));
        };

        $src_title   = (string) ($source['title'] ?? '');
        $src_content = (string) ($source['content'] ?? '');
        $src_focus   = trim((string) ($source['focus'] ?? ''));
        $src_sec     = array_values(array_filter(array_map('strval', $source['secondary'] ?? [])));
        $src_heads   = array_values(array_filter(array_map('strval', $source['headings'] ?? [])));

        $tgt_title   = (string) ($target['title'] ?? '');
        $tgt_content = (string) ($target['content'] ?? '');
        $tgt_focus   = trim((string) ($target['focus'] ?? ''));
        $tgt_sec     = array_values(array_filter(array_map('strval', $target['secondary'] ?? [])));
        $tgt_heads   = array_values(array_filter(array_map('strval', $target['headings'] ?? [])));
        $tgt_cats    = array_values(array_filter(array_map('strval', $target['categories'] ?? [])));
        $tgt_tags    = array_values(array_filter(array_map('strval', $target['tags'] ?? [])));

        $factors = [];
        $reasons = [];
        $evidence = [];

        // 1) Focus keyword in target body
        $w = self::WEIGHTS['exact_focus_in_target_body'];
        if ($src_focus !== '' && $contains($tgt_content, $src_focus)) {
            $factors['exact_focus_in_target_body'] = (float) $w;
            $reasons[] = 'کلمه کلیدی اصلی مبدأ در متن مقصد یافت شد.';
            $evidence['focus_in_body'] = true;
        } else {
            $factors['exact_focus_in_target_body'] = 0.0;
            $evidence['focus_in_body'] = false;
        }

        // 2) Secondary keywords in target body
        $w = self::WEIGHTS['secondary_in_target_body'];
        $sec_hits = 0;
        $sec_hit_list = [];
        foreach (array_slice($src_sec, 0, 8) as $sk) {
            if ($contains($tgt_content, $sk) || $contains($tgt_title, $sk)) {
                $sec_hits++;
                $sec_hit_list[] = $sk;
            }
        }
        if ($sec_hits > 0) {
            $frac = min(1.0, $sec_hits / max(1, min(3, count($src_sec) ?: 1)));
            $factors['secondary_in_target_body'] = round($w * $frac, 2);
            $reasons[] = sprintf('%d کلیدواژه فرعی در مقصد تطبیق داده شد.', $sec_hits);
            $evidence['secondary_hits'] = $sec_hit_list;
        } else {
            $factors['secondary_in_target_body'] = 0.0;
            $evidence['secondary_hits'] = [];
        }

        // 3) Phrase / heading overlap
        $w = self::WEIGHTS['phrase_overlap_headings'];
        $phrases = array_merge($src_heads, self::extract_phrases($src_title . ' ' . (function_exists('mb_substr') ? mb_substr($src_content, 0, 1200) : substr($src_content, 0, 1200)), $N));
        $phrases = array_values(array_unique(array_filter($phrases, static fn($p) => self::ulen(trim((string) $p)) >= 4)));
        $phrase_hits = 0;
        $phrase_examples = [];
        foreach (array_slice($phrases, 0, 12) as $ph) {
            if ($contains($tgt_content, $ph) || $contains($tgt_title, $ph) || self::heading_hit($tgt_heads, $ph, $contains)) {
                $phrase_hits++;
                if (count($phrase_examples) < 3) {
                    $phrase_examples[] = $ph;
                }
            }
        }
        if ($phrase_hits > 0) {
            $frac = min(1.0, $phrase_hits / 3);
            $factors['phrase_overlap_headings'] = round($w * $frac, 2);
            $reasons[] = 'عبارت‌های مشترک موضوعی بین مبدأ و مقصد شناسایی شد.';
            $evidence['phrase_examples'] = $phrase_examples;
        } else {
            $factors['phrase_overlap_headings'] = 0.0;
            $evidence['phrase_examples'] = [];
        }

        // 4) Focus or important phrase in target title
        $w = self::WEIGHTS['focus_or_phrase_in_title'];
        $title_hit = ($src_focus !== '' && $contains($tgt_title, $src_focus));
        if (!$title_hit) {
            foreach (array_slice($phrases, 0, 6) as $ph) {
                if ($contains($tgt_title, $ph)) {
                    $title_hit = true;
                    break;
                }
            }
        }
        // Title-only similarity without body evidence is weak — half credit if only title
        if ($title_hit && ($factors['exact_focus_in_target_body'] > 0 || $factors['phrase_overlap_headings'] > 0 || $factors['secondary_in_target_body'] > 0)) {
            $factors['focus_or_phrase_in_title'] = (float) $w;
            $reasons[] = 'عنوان مقصد با موضوع مبدأ هم‌پوشانی دارد.';
        } elseif ($title_hit) {
            $factors['focus_or_phrase_in_title'] = round($w * 0.35, 2);
            $reasons[] = 'تنها عنوان مقصد شبیه است؛ همپوشانی محتوایی ضعیف است.';
            $evidence['title_only_weak'] = true;
        } else {
            $factors['focus_or_phrase_in_title'] = 0.0;
        }

        // 5) Taxonomy overlap
        $w = self::WEIGHTS['taxonomy_overlap'];
        $src_tax = array_merge(
            array_map('strval', $source['categories'] ?? []),
            array_map('strval', $source['tags'] ?? [])
        );
        $tgt_tax = array_merge($tgt_cats, $tgt_tags);
        $tax_hits = 0;
        foreach ($src_tax as $st) {
            $ns = $norm($st);
            if ($ns === '') {
                continue;
            }
            foreach ($tgt_tax as $tt) {
                if ($norm($tt) === $ns) {
                    $tax_hits++;
                    break;
                }
            }
        }
        if ($tax_hits > 0) {
            $factors['taxonomy_overlap'] = (float) min($w, $w * min(1.0, $tax_hits / 2));
            $reasons[] = sprintf('%d دسته/برچسب مشترک.', $tax_hits);
            $evidence['taxonomy_hits'] = $tax_hits;
        } else {
            $factors['taxonomy_overlap'] = 0.0;
        }

        // 6) Target focus keyword relates to source
        $w = self::WEIGHTS['target_focus_match_source'];
        if ($tgt_focus !== '' && (
            ($src_focus !== '' && ($contains($src_focus, $tgt_focus) || $contains($tgt_focus, $src_focus) || $norm($src_focus) === $norm($tgt_focus)))
            || $contains($src_content, $tgt_focus)
            || $contains($src_title, $tgt_focus)
        )) {
            $factors['target_focus_match_source'] = (float) $w;
            $reasons[] = 'کلمه کلیدی مقصد با محتوای مبدأ مرتبط است.';
            $evidence['target_focus'] = $tgt_focus;
        } else {
            $factors['target_focus_match_source'] = 0.0;
        }

        // 7) Natural anchor in source content
        $w = self::WEIGHTS['anchor_natural_in_source'];
        $anchor = self::pick_anchor($source, $target, $contains, $N);
        if ($anchor !== '' && $contains($src_content, $anchor)) {
            $factors['anchor_natural_in_source'] = (float) $w;
            $reasons[] = 'عبارت لنگر در متن مبدأ وجود دارد.';
            $evidence['anchor_in_source'] = true;
        } elseif ($anchor !== '') {
            $factors['anchor_natural_in_source'] = round($w * 0.4, 2);
            $evidence['anchor_in_source'] = false;
        } else {
            $factors['anchor_natural_in_source'] = 0.0;
            $evidence['anchor_in_source'] = false;
        }

        $score = 0.0;
        foreach ($factors as $v) {
            $score += $v;
        }
        $score = round(min(100.0, $score), 2);

        // Penalize title-only weak matches further if body empty of signals
        if (!empty($evidence['title_only_weak']) && $score < 20) {
            $score = round($score * 0.5, 2);
        }

        return [
            'score'    => $score,
            'factors'  => $factors,
            'reasons'  => $reasons,
            'anchor'   => $anchor,
            'evidence' => $evidence,
        ];
    }

    /**
     * @param callable $contains
     * @param class-string|null $N
     */
    private static function pick_anchor(array $source, array $target, callable $contains, $N): string
    {
        $src_content = (string) ($source['content'] ?? '');
        $src_focus   = trim((string) ($source['focus'] ?? ''));
        $src_sec     = array_values(array_filter(array_map('strval', $source['secondary'] ?? [])));
        $tgt_focus   = trim((string) ($target['focus'] ?? ''));
        $tgt_title   = trim((string) ($target['title'] ?? ''));

        $candidates = [];
        if ($src_focus !== '' && $contains($src_content, $src_focus)) {
            $candidates[] = $src_focus;
        }
        if ($tgt_focus !== '' && $contains($src_content, $tgt_focus)) {
            $candidates[] = $tgt_focus;
        }
        foreach ($src_sec as $sk) {
            if ($contains($src_content, $sk)) {
                $candidates[] = $sk;
            }
        }
        // Prefer shorter descriptive phrases already in source
        if ($tgt_title !== '' && $contains($src_content, $tgt_title)) {
            $candidates[] = $tgt_title;
        }
        // Partial title words (2+ word slice)
        if ($tgt_title !== '') {
            $parts = preg_split('/\s+/u', $tgt_title) ?: [];
            if (count($parts) >= 2) {
                $slice = implode(' ', array_slice($parts, 0, min(4, count($parts))));
                if ($contains($src_content, $slice)) {
                    $candidates[] = $slice;
                }
            }
        }

        $candidates = array_values(array_unique(array_filter(array_map('trim', $candidates))));
        if ($candidates) {
            // Prefer focus if present
            foreach ($candidates as $c) {
                if ($src_focus !== '' && ($N ? $N::normalize($c) === $N::normalize($src_focus) : (function_exists('mb_strtolower') ? mb_strtolower($c) === mb_strtolower($src_focus) : strtolower($c) === strtolower($src_focus)))) {
                    return $c;
                }
            }
            return $candidates[0];
        }

        // Fallback: target focus or short title — may need user edit
        if ($tgt_focus !== '') {
            return $tgt_focus;
        }
        if ($src_focus !== '') {
            return $src_focus;
        }
        return $tgt_title !== '' ? $tgt_title : '';
    }

    /**
     * @param list<string> $heads
     * @param callable $contains
     */
    private static function heading_hit(array $heads, string $ph, callable $contains): bool
    {
        foreach ($heads as $h) {
            if ($contains($h, $ph) || $contains($ph, $h)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Lightweight phrase extraction from text (no WP).
     *
     * @param class-string|null $N
     * @return list<string>
     */
    private static function extract_phrases(string $text, $N): array
    {
        $text = wp_strip_all_tags_safe($text);
        $text = $N ? $N::normalize($text) : (function_exists('mb_strtolower') ? mb_strtolower($text) : strtolower($text));
        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $out = [];
        $n = count($words);
        for ($i = 0; $i < $n - 1 && count($out) < 20; $i++) {
            $bigram = $words[$i] . ' ' . $words[$i + 1];
            if (self::ulen($words[$i]) >= 3 && self::ulen($words[$i + 1]) >= 3) {
                $out[] = $bigram;
            }
        }
        return array_values(array_unique($out));
    }
}

/** Minimal strip tags without requiring WP in tests */
function wp_strip_all_tags_safe(string $text): string
{
    if (function_exists('wp_strip_all_tags')) {
        return wp_strip_all_tags($text);
    }
    return trim(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}
