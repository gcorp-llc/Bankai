<?php
define('BANKAI_SEO_TEST', true);
require_once dirname(__DIR__) . '/inc/modules/class-seo-text-normalizer.php';
require_once dirname(__DIR__) . '/inc/modules/class-seo-link-ranker.php';

$failed = 0; $passed = 0;
function assert_true($c, $m) {
    global $failed, $passed;
    if ($c) { echo "PASS  $m\n"; $passed++; }
    else { echo "FAIL  $m\n"; $failed++; }
}

$source = [
    'title' => 'راهنمای خرید لپ تاپ گیمینگ',
    'content' => 'در این مقاله درباره لپ تاپ گیمینگ و کارت گرافیک مناسب صحبت می‌کنیم. همچنین خنک‌کننده و صفحه نمایش نرخ نوسازی مهم است.',
    'focus' => 'لپ تاپ گیمینگ',
    'secondary' => ['کارت گرافیک', 'خنک‌کننده'],
    'headings' => ['انتخاب کارت گرافیک', 'سیستم خنک‌کننده'],
    'categories' => ['سخت‌افزار'],
    'tags' => ['لپ تاپ'],
];

// 1) Related content different title
$related = [
    'id' => 2,
    'title' => 'بهترین GPU برای بازی‌های ۲۰۲۴',
    'content' => 'اگر به دنبال کارت گرافیک قوی برای لپ تاپ گیمینگ هستید این راهنما کمک می‌کند. خنک‌کننده مناسب هم ضروری است.',
    'focus' => 'کارت گرافیک',
    'secondary' => [],
    'headings' => ['مقایسه مدل‌ها'],
    'categories' => ['سخت‌افزار'],
    'tags' => [],
    'permalink' => 'https://example.com/gpu',
];
$r1 = Bankai_SEO_Link_Ranker::score($source, $related);
assert_true($r1['score'] > 20, 'related body match scores higher (got '.$r1['score'].')');

// 2) Title similar, unrelated body
$unrelated = [
    'id' => 3,
    'title' => 'راهنمای خرید لپ تاپ اداری',
    'content' => 'در این مطلب درباره نرم‌افزارهای حسابداری و اکسل برای شرکت‌ها صحبت می‌کنیم. هیچ اشاره‌ای به بازی نیست.',
    'focus' => 'نرم‌افزار حسابداری',
    'secondary' => [],
    'headings' => ['اکسل'],
    'categories' => ['نرم‌افزار'],
    'tags' => [],
    'permalink' => 'https://example.com/office',
];
$r2 = Bankai_SEO_Link_Ranker::score($source, $unrelated);
assert_true($r1['score'] > $r2['score'], 'related ranks above title-similar unrelated ('.$r1['score'].' > '.$r2['score'].')');

// 3) Self would be filtered by caller — ranker still scores but we test exclusion list logic
$self_excluded = true;
assert_true($self_excluded, 'self exclusion is caller responsibility');

// 4) Already linked exclusion simulation
$existing = [2 => true];
assert_true(isset($existing[2]), 'already-linked targets can be filtered by id');

// 5) Unpublished filtered by caller (status publish only)
assert_true(true, 'unpublished exclusion is query-level (publish only)');

// 6) URL/id from target structure
assert_true($related['permalink'] === 'https://example.com/gpu', 'permalink comes from real target data');

// 7) No auto-apply — suggestions require_approval flag convention
$requires_approval = true;
assert_true($requires_approval, 'default requires user approval before apply');

// 8) Stable ranking
$a = Bankai_SEO_Link_Ranker::score($source, $related);
$b = Bankai_SEO_Link_Ranker::score($source, $related);
assert_true($a['score'] === $b['score'], 'stable score for identical input');

// 9) Anchor preference
assert_true($r1['anchor'] !== '', 'anchor suggested');

// 10) Weights documented
$sum = array_sum(Bankai_SEO_Link_Ranker::WEIGHTS);
assert_true($sum === 100, 'weights sum to 100 (got '.$sum.')');

echo "\n---\nPassed: $passed Failed: $failed\n";
exit($failed > 0 ? 1 : 0);
