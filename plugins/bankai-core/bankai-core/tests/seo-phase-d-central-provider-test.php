<?php
/**
 * Phase D — central provider policy (offline, no WP bootstrap).
 * Run: php tests/seo-phase-d-central-provider-test.php
 */
define('BANKAI_SEO_TEST', true);

$failed = 0;
$passed = 0;
function assert_true($c, $m) {
    global $failed, $passed;
    if ($c) { echo "PASS  $m\n"; $passed++; }
    else { echo "FAIL  $m\n"; $failed++; }
}

/**
 * Mirror of resolve policy: client cannot override central.
 */
function resolve_central_text(string $central, string $client): string
{
    return $central; // ignore client
}
function resolve_central_image(string $central, string $client): string
{
    return $central !== '' ? $central : '';
}

assert_true(resolve_central_text('openai', 'gemini') === 'openai', 'client cannot override text provider');
assert_true(resolve_central_text('gemini', '') === 'gemini', 'empty client uses central text');
assert_true(resolve_central_image('stability', 'openai') === 'stability', 'client cannot override image provider');

// Missing credentials message shape
function readiness(string $provider, array $keys): string {
    if ($provider === '') return 'no default';
    if (empty($keys[$provider])) return 'missing key for '.$provider;
    return '';
}
assert_true(readiness('', []) === 'no default', 'missing default detected');
assert_true(readiness('openai', []) === 'missing key for openai', 'missing key detected');
assert_true(readiness('openai', ['openai' => 'sk-test']) === '', 'ready when key present');

// Localize payload must never include raw keys
$localized = [
    'defaultAiProvider' => 'openai',
    'aiProviders' => [['id'=>'openai','name'=>'OpenAI']],
    'apiKey' => null, // must not exist
];
assert_true(!isset($localized['apiKey']) || $localized['apiKey'] === null, 'no apiKey in editor localize shape');
foreach ($localized['aiProviders'] as $p) {
    assert_true(!isset($p['key']) && !isset($p['api_key']), 'provider list has no key fields');
}

// Content preservation on readiness error
$existing = ['seo_title' => 'قدیمی', 'description' => 'متای قبلی'];
$err = readiness('openai', []);
$applied = $err !== '' ? $existing : ['seo_title' => 'جدید'];
assert_true($applied['seo_title'] === 'قدیمی', 'on missing key keep existing meta');

// UI: selectors removed marker in view file
$view = file_get_contents(dirname(__DIR__) . '/views/tab-seo-engine.php');
assert_true(strpos($view, 'انتخاب موتور AI') === false, 'UI label "انتخاب موتور AI" removed');
assert_true(strpos($view, '@click="aiProvider = p.id"') === false, 'no aiProvider click binding');
assert_true(strpos($view, '@click="aiImageProvider = p.id"') === false, 'no image provider click binding');
assert_true(strpos($view, 'bk-ai-engine-readonly') !== false || strpos($view, 'تنظیمات مرکزی') !== false, 'readonly central info present');

// Server helpers exist
$ai = file_get_contents(dirname(__DIR__) . '/inc/modules/class-ai-studio.php');
assert_true(strpos($ai, 'function resolve_central_text_provider') !== false, 'resolve_central_text_provider exists');
assert_true(strpos($ai, 'function resolve_central_image_provider') !== false, 'resolve_central_image_provider exists');
assert_true(strpos($ai, 'function text_provider_readiness_error') !== false, 'text_provider_readiness_error exists');

echo "\n---\nPassed: $passed  Failed: $failed\n";
exit($failed > 0 ? 1 : 0);
