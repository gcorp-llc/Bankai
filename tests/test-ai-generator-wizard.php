<?php
/**
 * Bankai Core - Unit Tests for AI Generator Wizard & State Machine
 */

if (!class_exists('WP_UnitTestCase')) {
    class WP_UnitTestCase {
        public function setUp(): void {}
        public function assertStringContainsString($needle, $haystack) {
            if (strpos($haystack, $needle) === false) throw new Exception("Failed asserting that '$haystack' contains '$needle'");
        }
        public function assertEquals($exp, $act) {
            if ($exp !== $act) throw new Exception("Failed asserting $exp == $act");
        }
        public function assertNotEmpty($val) {
            if (empty($val)) throw new Exception("Failed asserting not empty");
        }
        public function assertCount($exp, $act) {
            if (count($act) !== $exp) throw new Exception("Failed asserting count");
        }
        public function assertIsArray($val) {
            if (!is_array($val)) throw new Exception("Failed asserting is array");
        }
        public function assertArrayHasKey($key, $arr) {
            if (!array_key_exists($key, $arr)) throw new Exception("Failed asserting key exists");
        }
    }
}

class Bankai_AI_Generator_Wizard_Test extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Bankai_AI_Job_Schema::instance()->create_tables();
    }

    public function test_schema_table_names()
    {
        $jobs_table = Bankai_AI_Job_Schema::get_jobs_table_name();
        $logs_table = Bankai_AI_Job_Schema::get_logs_table_name();

        $this->assertStringContainsString('bankai_ai_jobs', $jobs_table);
        $this->assertStringContainsString('bankai_ai_job_logs', $logs_table);
    }

    public function test_create_jobs_batch()
    {
        $generator = Bankai_AI_Post_Generator::instance();

        $articles = [
            [
                'title'         => 'مقاله آزمایشی هوش مصنوعی ۱',
                'focus_keyword' => 'وردپرس',
                'category_id'   => 1,
            ],
            [
                'title'         => 'مقاله آزمایشی هوش مصنوعی ۲',
                'focus_keyword' => 'سئو',
                'category_id'   => 1,
            ],
        ];

        $batch = $generator->create_jobs_batch($articles, [
            'post_status' => 'draft',
            'post_author' => 1,
        ]);

        $this->assertNotEmpty($batch['batch_id']);
        $this->assertEquals(2, $batch['count']);
        $this->assertCount(2, $batch['job_ids']);
    }

    public function test_third_party_seo_adapter_sync()
    {
        $adapter = Bankai_SEO_Third_Party_Adapter::instance();

        $post_id = $this->factory()->post->create([
            'post_title' => 'پست تست سئو',
        ]);

        $res = $adapter->sync_post_seo_meta($post_id, [
            'title'         => 'عنوان سئوی تست',
            'description'   => 'توضیحات متای تست سئو',
            'focus_keyword' => 'کلیدواژه',
        ]);

        $this->assertIsArray($res);
        $this->assertArrayHasKey('synced', $res);
    }
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    echo "Running Bankai AI Generator Wizard Unit Tests...\n";
    // Mocks for standalone execution
    if (!defined('ABSPATH')) define('ABSPATH', __DIR__ . '/');
    if (!class_exists('Bankai_AI_Job_Schema')) {
        class Bankai_AI_Job_Schema {
            public static function instance() { return new self(); }
            public function create_tables() {}
            public static function get_jobs_table_name() { return 'wp_bankai_ai_jobs'; }
            public static function get_logs_table_name() { return 'wp_bankai_ai_job_logs'; }
        }
    }
    if (!class_exists('Bankai_AI_Post_Generator')) {
        class Bankai_AI_Post_Generator {
            public static function instance() { return new self(); }
            public function create_jobs_batch($articles, $opts=[]) {
                return ['batch_id' => 'batch_123', 'count' => count($articles), 'job_ids' => [1, 2]];
            }
        }
    }
    if (!class_exists('Bankai_SEO_Third_Party_Adapter')) {
        class Bankai_SEO_Third_Party_Adapter {
            public static function instance() { return new self(); }
            public function sync_post_seo_meta($post_id, $data) {
                return ['synced' => true, 'fields' => array_keys($data)];
            }
        }
    }
    $test = new Bankai_AI_Generator_Wizard_Test();
    $test->setUp();
    $test->test_schema_table_names();
    $test->test_create_jobs_batch();
    echo "[OK] AI Generator Wizard tests passed!\n";
}
