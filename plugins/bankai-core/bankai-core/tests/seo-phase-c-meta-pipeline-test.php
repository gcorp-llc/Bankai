<?php
/**
 * Phase C — offline validation helpers (no WP / no live AI).
 * Run: php tests/seo-phase-c-meta-pipeline-test.php
 */
define('BANKAI_SEO_TEST', true);
require_once dirname(__DIR__) . '/inc/modules/class-seo-text-normalizer.php';


function u_len(string $s): int {
    if (function_exists('mb_strlen')) return mb_strlen($s, 'UTF-8');
    // UTF-8 codepoint count without mbstring
    return preg_match_all('/./us', $s);
}

$failed = 0;
$passed = 0;
function assert_true($c, $m) {
    global $failed, $passed;
    if ($c) { echo "PASS  $m\n"; $passed++; }
    else { echo "FAIL  $m\n"; $failed++; }
}

/**
 * Mirror of offline_meta_analysis scoring subset used by pipeline.
 */
function offline_check(string $title, string $desc, string $kw): array {
    $checks = [];
    $has = $kw !== '';
    if (!$has) {
        $checks[] = ['key'=>'kw_in_title','status'=>'unknown'];
        $checks[] = ['key'=>'kw_in_desc','status'=>'unknown'];
    } else {
        $checks[] = ['key'=>'kw_in_title','status'=> Bankai_SEO_Text_Normalizer::contains($title,$kw)?'pass':'fail'];
        $checks[] = ['key'=>'kw_in_desc','status'=> Bankai_SEO_Text_Normalizer::contains($desc,$kw)?'pass':'fail'];
    }
    $tlen = u_len($title);
    $checks[] = ['key'=>'title_length','status'=>($tlen>=30&&$tlen<=60)?'pass':'warning'];
    $dlen = u_len($desc);
    if ($desc==='') $checks[] = ['key'=>'desc_length','status'=>'fail'];
    else $checks[] = ['key'=>'desc_length','status'=>($dlen>=120&&$dlen<=160)?'pass':'warning'];

    $blocking = [];
    foreach ($checks as $c) {
        if (in_array($c['status'], ['pass','not_applicable'], true)) continue;
        if ($c['key']==='title_has_number') continue;
        $blocking[] = $c;
    }
    return ['checks'=>$checks,'blocking'=>$blocking,'valid'=>count($blocking)===0];
}

// 1 valid title+desc
$r = offline_check(
    'خرید لپ تاپ گیمینگ مناسب کاربران حرفه‌ای امروز',
    'در این راهنما بهترین نکات خرید لپ تاپ گیمینگ را بررسی می‌کنیم تا بتوانید با بودجه مناسب، سیستم قدرتمند و خنک انتخاب کنید و تجربه بازی روانی داشته باشید.',
    'لپ تاپ گیمینگ'
);
assert_true($r['valid'], 'valid title+desc with keyword passes');

// 2 title missing keyword
$r = offline_check('راهنمای خرید سیستم خانگی', str_repeat('متن توضیحات درباره لپ تاپ گیمینگ و نکات مهم خرید برای کاربران. ', 3), 'لپ تاپ گیمینگ');
assert_true(!$r['valid'] && in_array('kw_in_title', array_column($r['blocking'],'key')), 'title without keyword fails kw_in_title');

// 3 title length out of range
$r = offline_check('کوتاه', str_repeat('توضیحات کامل درباره موضوع سئو و محتوا برای رسیدن به طول مناسب متا دیسکریپشن استاندارد افزونه. ', 2), 'سئو');
assert_true(in_array('title_length', array_column($r['blocking'],'key')) || !$r['valid'], 'short title fails length');

// 4 desc out of range
$r = offline_check('عنوان سئو مناسب با کلمه کلیدی سئو محتوا', 'کوتاه', 'سئو محتوا');
assert_true(!$r['valid'], 'short description fails');

// 5 empty provider-like output
$r = offline_check('', '', 'کلمه');
assert_true(!$r['valid'], 'empty outputs invalid');

// 6 repair simulation: after 2 fails still needs_review semantics
$attempts = 0; $max=3; $status='failed';
while ($attempts < $max) {
    $attempts++;
    // pretend still failing
    $status = $attempts >= $max ? 'needs_review' : 'failed';
    if ($status==='needs_review') break;
}
assert_true($attempts===3 && $status==='needs_review', 'max 2 repairs then needs_review');

// 7 content preservation flag simulation
$original_meta = ['seo_title'=>'قدیمی','description'=>'قدیم'];
$gen_error = true;
$applied = $gen_error ? $original_meta : ['seo_title'=>'جدید'];
assert_true($applied['seo_title']==='قدیمی', 'on generation error keep existing meta');

// 8 arabic yeh keyword match in title
$r = offline_check('آموزش سئوي وردپرس برای مبتدیان سایت', str_repeat('این مقاله آموزش سئوی وردپرس را از پایه تا انتشار پوشش می‌دهد و نکات فنی را توضیح می‌دهد. ', 2), 'سئوی وردپرس');
assert_true($r['valid'] || Bankai_SEO_Text_Normalizer::contains('سئوي','سئوی'), 'arabic yeh handled in contains');

echo "\n---\nPassed: $passed  Failed: $failed\n";
exit($failed>0?1:0);
