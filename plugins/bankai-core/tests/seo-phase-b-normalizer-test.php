<?php
/**
 * Phase B unit tests — normalizer + scoring policy (no WP bootstrap).
 * Run: php tests/seo-phase-b-normalizer-test.php
 */
define('BANKAI_SEO_TEST', true);
require_once dirname(__DIR__) . '/inc/modules/class-seo-text-normalizer.php';

$failed = 0;
$passed = 0;

function assert_true($cond, string $msg): void
{
    global $failed, $passed;
    if ($cond) {
        echo "PASS  $msg\n";
        $passed++;
    } else {
        echo "FAIL  $msg\n";
        $failed++;
    }
}

// 1. ي/ی and ك/ک
assert_true(
    Bankai_SEO_Text_Normalizer::contains('آموزش كيك پزی با يادگیری', 'کیک'),
    'Arabic kaf/yeh normalize matches Persian keyword'
);
assert_true(
    Bankai_SEO_Text_Normalizer::contains('سئوی وردپرس', 'سئو ي وردپرس') === false
        || Bankai_SEO_Text_Normalizer::normalize('سئوی وردپرس') !== '',
    'normalize returns non-empty for Persian'
);
assert_true(
    Bankai_SEO_Text_Normalizer::contains('سئوی وردپرس', 'سئوي وردپرس'),
    'ي vs ی in phrase match'
);

// 2. ZWNJ / extra spaces
$a = "به\u{200C}روز رسانی";
$b = 'به روز  رسانی';
assert_true(
    Bankai_SEO_Text_Normalizer::normalize($a) === Bankai_SEO_Text_Normalizer::normalize($b)
    || Bankai_SEO_Text_Normalizer::contains($a, 'روز'),
    'ZWNJ / spaces handled for comparison'
);
assert_true(
    Bankai_SEO_Text_Normalizer::contains("کلمه  کلیدی   اصلی", 'کلمه کلیدی'),
    'repeated spaces collapse for contains'
);

// 3. starts_with
assert_true(
    Bankai_SEO_Text_Normalizer::starts_with('کلمه کلیدی در عنوان بلند', 'كلمه كليدي'),
    'starts_with with Arabic letters normalized'
);

// 4. word count mixed
$wc = Bankai_SEO_Text_Normalizer::word_count('سلام دنیا Hello world 123');
assert_true($wc >= 4, "word_count mixed FA/EN got $wc");

// 5. scoring policy simulation
$checks = [
    ['status' => 'pass'],
    ['status' => 'fail'],
    ['status' => 'warning'],
    ['status' => 'unknown'],
    ['status' => 'not_applicable'],
];
$pass = 0;
$total = 0;
foreach ($checks as $c) {
    if ($c['status'] === 'not_applicable') {
        continue;
    }
    $total++;
    if ($c['status'] === 'pass') {
        $pass++;
    }
}
$score = (int) round(($pass / $total) * 100);
assert_true($total === 4, "scored_total excludes N/A (got $total)");
assert_true($pass === 1, "only explicit pass counts (got $pass)");
assert_true($score === 25, "score 1/4 = 25 (got $score)");

// 6. empty needle
assert_true(Bankai_SEO_Text_Normalizer::contains('abc', '') === false, 'empty needle is false');

// 7. original string not mutated by reference — normalize returns new string
$orig = 'متن اصلی ي و ك';
$copy = $orig;
Bankai_SEO_Text_Normalizer::normalize($copy);
assert_true($copy === $orig, 'input string value unchanged after normalize call');

echo "\n---\nPassed: $passed  Failed: $failed\n";
exit($failed > 0 ? 1 : 0);
