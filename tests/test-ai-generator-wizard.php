<?php
/**
 * Bankai Core - Unit Tests for AI Generator Wizard & State Machine
 */

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
