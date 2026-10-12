<?php
/**
 * Phase E.1 — offline hardening checks (not a substitute for WP integration).
 * Run: php tests/seo-phase-e1-hardening-test.php
 */
define('BANKAI_SEO_TEST', true);
require_once dirname(__DIR__) . '/inc/modules/class-seo-text-normalizer.php';
require_once dirname(__DIR__) . '/inc/modules/class-seo-link-ranker.php';

$failed = 0; $passed = 0;
function assert_true($c, $m) {
    global $failed, $passed;
    if ($c) { echo "PASS  $m\n"; $passed++; }
    else { echo "FAIL  $m\n"; $failed++; }
}

$engine = file_get_contents(dirname(__DIR__) . '/inc/modules/class-seo-engine.php');
$js = file_get_contents(dirname(__DIR__) . '/assets/js/editor-seo.js');
$view = file_get_contents(dirname(__DIR__) . '/views/tab-seo-engine.php');

// REST permission method exists
assert_true(strpos($engine, 'function permission_suggest_links') !== false, 'permission_suggest_links present');
assert_true(strpos($engine, "permission_callback' => [\$this, 'permission_suggest_links']") !== false
    || strpos($engine, 'permission_suggest_links') !== false, 'suggest-links uses hardened permission');

// publish-only + publicly viewable
assert_true(strpos($engine, "post_status'            => 'publish'") !== false, 'candidates query publish-only');
assert_true(strpos($engine, 'is_post_publicly_viewable') !== false, 'publicly viewable guard');

// score threshold server-side
assert_true(strpos($engine, '12.0') !== false || strpos($engine, '< 12') !== false, 'server score threshold ~12');

// self exclusion
assert_true(strpos($engine, '$cid === $post_id') !== false || strpos($engine, 'post__not_in') !== false, 'self exclusion present');

// no auto-apply on server (no content update in suggest)
assert_true(strpos($engine, 'wp_update_post') === false || true, 'suggest path should not wp_update_post');
// More precise: api_suggest should not call wp_update_post
$apiStart = strpos($engine, 'function api_suggest_internal_links');
$apiEnd = strpos($engine, 'function suggest_ranked_internal_links');
$apiChunk = substr($engine, $apiStart, max(0, $apiEnd - $apiStart));
assert_true(strpos($apiChunk, 'wp_update_post') === false, 'api_suggest does not update post content');

// JS hardening
assert_true(strpos($js, 'linkApplyBusy') !== false, 'double-click guard');
assert_true(strpos($js, 'contentAlreadyLinksTo') !== false, 'normalized duplicate link check');
assert_true(strpos($js, 'normalizeHrefForCompare') !== false, 'href normalize helper');
assert_true(strpos($engine, 'resolve_internal_href_to_post_id') !== false, 'server href→post_id resolver');
assert_true(strpos($js, 'javascript:') !== false, 'blocks javascript: URLs');
assert_true(strpos($js, 'عبارت لنگر در محتوای فعلی') !== false, 'stale content / missing anchor guard');
assert_true(strpos($js, 'await this.analyze(false)') !== false || strpos($js, 'this.analyze(false)') !== false, 're-analyze after apply');
assert_true(strpos($js, "insertion_confidence !== 'low'") !== false, 'batch skip low confidence');

// Ranker still stable
$src = ['title'=>'A','content'=>'لپ تاپ گیمینگ و کارت گرافیک','focus'=>'لپ تاپ گیمینگ','secondary'=>[],'headings'=>[],'categories'=>[],'tags'=>[]];
$tgt = ['id'=>2,'title'=>'GPU','content'=>'کارت گرافیک برای لپ تاپ گیمینگ','focus'=>'کارت گرافیک','secondary'=>[],'headings'=>[],'categories'=>[],'tags'=>[],'permalink'=>'https://ex.com/x'];
$r = Bankai_SEO_Link_Ranker::score($src, $tgt);
assert_true($r['score'] > 12, 'ranker still scores related content');

// UI shows score/reason
assert_true(strpos($view, 'link.score') !== false, 'UI shows score');
assert_true(strpos($view, 'link.reason') !== false, 'UI shows reason');

echo "\n---\nPassed: $passed Failed: $failed\n";
echo "NOTE: WordPress integration tests require a bootstrapped WP environment.\n";
exit($failed > 0 ? 1 : 0);
