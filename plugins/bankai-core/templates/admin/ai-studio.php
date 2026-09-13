<?php
/**
 * Bankai Core Admin - View 6: AI Content Studio & Multi-LLM Orchestrator
 *
 * @package BankaiCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

$ai_modules = array(
    array(
        'id'          => 'auto_meta_alt',
        'title'       => 'Auto Meta & ALT Text Engine',
        'badge'       => 'AUTOMATED',
        'badge_color' => '#10B981',
        'icon'        => '✨',
        'description' => 'Automatically generates SEO titles, meta descriptions, and image alt texts on post save or media upload using chosen LLM.',
        'enabled'     => true,
    ),
    array(
        'id'          => 'content_outline_studio',
        'title'       => 'Content Outline & Article Studio',
        'badge'       => 'AI DRAFT',
        'badge_color' => '#6366F1',
        'icon'        => '📝',
        'description' => 'Interactive prompt wizard for generating structured article outlines and full-length drafts directly in WordPress.',
        'enabled'     => true,
    ),
    array(
        'id'          => 'brand_persona_builder',
        'title'       => 'Brand Persona & Tone Builder',
        'badge'       => 'SYSTEM PROMPTS',
        'badge_color' => '#38BDF8',
        'icon'        => '🎭',
        'description' => 'Customizable system prompts (Professional, Technical, Casual, Persuasive) and language/style guardrails.',
        'enabled'     => true,
    ),
    array(
        'id'          => 'ai_interlinking_guard',
        'title'       => 'AI Interlinking & Density Guard',
        'badge'       => 'CONTEXTUAL',
        'badge_color' => '#F59E0B',
        'icon'        => '🔗',
        'description' => 'Contextual internal link suggestions and keyword density protection against over-optimization penalties.',
        'enabled'     => true,
    ),
    array(
        'id'          => 'content_repurposer',
        'title'       => 'Content Repurposing Engine',
        'badge'       => 'MULTI-CHANNEL',
        'badge_color' => '#10B981',
        'icon'        => '🔄',
        'description' => 'Converts long-form posts into social media threads, newsletter summaries, or FAQ schema JSON blocks.',
        'enabled'     => true,
    ),
    array(
        'id'          => 'prompt_manifests',
        'title'       => 'Prompt Manifests & Workflows',
        'badge'       => 'TAG HELPERS',
        'badge_color' => '#6366F1',
        'icon'        => '📑',
        'description' => 'Custom prompt template builder with variable tag helpers ({post_title}, {post_content}, {target_keyword}).',
        'enabled'     => true,
    ),
);

$providers = array(
    array( 'id' => 'openai', 'name' => 'OpenAI', 'badge' => 'Connected', 'color' => '#10B981', 'models' => 'GPT-4o / o3-mini' ),
    array( 'id' => 'anthropic', 'name' => 'Anthropic Claude', 'badge' => 'Connected', 'color' => '#10B981', 'models' => 'Claude 3.5 / 3.7 Sonnet' ),
    array( 'id' => 'deepseek', 'name' => 'DeepSeek', 'badge' => 'Active', 'color' => '#38BDF8', 'models' => 'DeepSeek V3 / R1' ),
    array( 'id' => 'gemini', 'name' => 'Google Gemini', 'badge' => 'Active', 'color' => '#38BDF8', 'models' => 'Gemini 2.0 Flash' ),
    array( 'id' => 'openrouter', 'name' => 'OpenRouter', 'badge' => 'Fallback', 'color' => '#6366F1', 'models' => '200+ Open Models' ),
);
?>

<div x-show="activeTab === 'ai'" x-transition>
    <!-- View 6 Header & AI Token Telemetry Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 20px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 4px;">
                <h2 style="font-size: 18px; font-weight: 800; color: #F8FAFC; margin: 0; display: flex; align-items: center; gap: 8px;">
                    🤖 <?php esc_html_e( 'Generative AI Studio & Multi-LLM Orchestrator', 'bankai-core' ); ?>
                </h2>
                <span style="background-color: rgba(56, 189, 248, 0.15); border: 1px solid #38BDF8; color: #38BDF8; font-size: 11px; padding: 2px 10px; border-radius: 12px; font-weight: 700;">
                    <?php esc_html_e( '142,500 Tokens Used This Month (~$0.28)', 'bankai-core' ); ?>
                </span>
            </div>
            <p style="font-size: 12px; color: #94A3B8; margin: 0;">
                <?php esc_html_e( 'Orchestrate GPT-4o, Claude 3.7, DeepSeek R1, and Gemini 2.0 with custom prompt templates and automated workflow triggers.', 'bankai-core' ); ?>
            </p>
        </div>

        <button @click="testAiConnections()"
                style="background-color: #10B981; border: none; color: #FFFFFF; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3); transition: all 0.2s;">
            ⚡ <?php esc_html_e( 'Test API Connections', 'bankai-core' ); ?>
        </button>
    </div>

    <!-- Provider Connection Badges Grid -->
    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-bottom: 24px;">
        <?php foreach ( $providers as $prov ) : ?>
            <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 10px; padding: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <span style="font-size: 12px; font-weight: 700; color: #F8FAFC;"><?php echo esc_html( $prov['name'] ); ?></span>
                    <span style="background-color: <?php echo esc_attr( $prov['color'] ); ?>20; color: <?php echo esc_attr( $prov['color'] ); ?>; font-size: 9px; font-weight: 700; padding: 1px 6px; border-radius: 4px;">
                        <?php echo esc_html( $prov['badge'] ); ?>
                    </span>
                </div>
                <div style="font-size: 10px; color: #94A3B8;"><?php echo esc_html( $prov['models'] ); ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Multi-LLM Model & API Provider Configuration Panel -->
    <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 24px; margin-bottom: 24px;">
        <h3 style="font-size: 15px; font-weight: 700; color: #F8FAFC; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
            🔑 <?php esc_html_e( 'Multi-LLM API Keys & Orchestration Rules', 'bankai-core' ); ?>
        </h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px;">
                    OpenAI API Key
                </label>
                <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                    <input :type="showApiKeys ? 'text' : 'password'" value="sk-proj-********************************"
                           style="flex: 1; background-color: #0B0F19; border: 1px solid #1E2D4A; color: #F8FAFC; padding: 8px 12px; border-radius: 6px; font-size: 12px;">
                    <button @click="showApiKeys = !showApiKeys" style="background-color: #1E2D4A; border: none; color: #38BDF8; padding: 8px 12px; border-radius: 6px; font-size: 11px; cursor: pointer;">
                        <span x-text="showApiKeys ? 'Hide' : 'Show'">Show</span>
                    </button>
                </div>

                <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px;">
                    Anthropic Claude API Key
                </label>
                <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                    <input :type="showApiKeys ? 'text' : 'password'" value="sk-ant-********************************"
                           style="flex: 1; background-color: #0B0F19; border: 1px solid #1E2D4A; color: #F8FAFC; padding: 8px 12px; border-radius: 6px; font-size: 12px;">
                    <button @click="showApiKeys = !showApiKeys" style="background-color: #1E2D4A; border: none; color: #38BDF8; padding: 8px 12px; border-radius: 6px; font-size: 11px; cursor: pointer;">
                        <span x-text="showApiKeys ? 'Hide' : 'Show'">Show</span>
                    </button>
                </div>
            </div>

            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px;">
                    Primary Default Engine Model
                </label>
                <select x-model="aiStudio.defaultModel"
                        style="width: 100%; background-color: #0B0F19; border: 1px solid #1E2D4A; color: #F8FAFC; padding: 9px 12px; border-radius: 6px; font-size: 12px; margin-bottom: 12px;">
                    <option value="gpt-4o">OpenAI GPT-4o (High Speed &amp; Accuracy)</option>
                    <option value="claude-3-7-sonnet">Anthropic Claude 3.7 Sonnet (Best Reasoning)</option>
                    <option value="deepseek-r1">DeepSeek R1 (High Reasoning &amp; Cost Efficiency)</option>
                    <option value="gemini-2-flash">Google Gemini 2.0 Flash (Ultra Low Latency)</option>
                </select>

                <div style="background-color: #0B0F19; border: 1px solid #1E2D4A; padding: 10px 14px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 12px; color: #E2E8F0;">Automatic Fallback Routing (If primary model rate-limited)</span>
                    <label class="bankai-switch">
                        <input type="checkbox" checked>
                        <span class="bankai-slider"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Modular Feature Grid (6 Cards) -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 32px;">
        <?php foreach ( $ai_modules as $mod ) : ?>
            <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; transition: border-color 0.2s;"
                 onmouseover="this.style.borderColor='#38BDF8';"
                 onmouseout="this.style.borderColor='#1E2D4A';">
                
                <div>
                    <!-- Top title + badge + switch -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 20px;"><?php echo esc_html( $mod['icon'] ); ?></span>
                            <div>
                                <div style="font-weight: 700; font-size: 14px; color: #F8FAFC; display: flex; align-items: center; gap: 6px;">
                                    <?php echo esc_html( $mod['title'] ); ?>
                                </div>
                                <span style="background-color: <?php echo esc_attr( $mod['badge_color'] ); ?>20; color: <?php echo esc_attr( $mod['badge_color'] ); ?>; font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 4px; display: inline-block; margin-top: 2px;">
                                    <?php echo esc_html( $mod['badge'] ); ?>
                                </span>
                            </div>
                        </div>

                        <label class="bankai-switch">
                            <input type="checkbox"
                                   x-model="aiState.<?php echo esc_attr( $mod['id'] ); ?>"
                                   @change="toggleAiModule('<?php echo esc_js( $mod['id'] ); ?>')">
                            <span class="bankai-slider"></span>
                        </label>
                    </div>

                    <!-- Description -->
                    <p style="font-size: 12px; color: #94A3B8; line-height: 1.5; margin: 0 0 16px 0;">
                        <?php echo esc_html( $mod['description'] ); ?>
                    </p>
                </div>

                <!-- Action / Settings Drawer Trigger -->
                <div style="border-top: 1px solid #1E2D4A; padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 11px; color: #64748B;"
                          x-text="aiState.<?php echo esc_attr( $mod['id'] ); ?> ? 'Active' : 'Disabled'">
                        Active
                    </span>

                    <button @click="openAiDrawer('<?php echo esc_js( $mod['id'] ); ?>', '<?php echo esc_js( $mod['title'] ); ?>')"
                            style="background-color: #1E2D4A; border: none; color: #38BDF8; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px; cursor: pointer;">
                        ⚙️ <?php esc_html_e( 'Configure Prompt', 'bankai-core' ); ?> &rarr;
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- AI Module Settings Modal Drawer (Alpine.js x-show) -->
    <div x-show="aiDrawer.show" class="bankai-modal-overlay" style="display: none;" x-transition.opacity>
        <div @click.away="aiDrawer.show = false"
             style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 16px; width: 560px; max-width: 90%; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.8); position: relative;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #1E2D4A; padding-bottom: 16px;">
                <h3 style="font-size: 18px; font-weight: 800; color: #F8FAFC; margin: 0; display: flex; align-items: center; gap: 8px;">
                    ⚙️ <span x-text="aiDrawer.title + ' Configuration'"></span>
                </h3>
                <button @click="aiDrawer.show = false" style="background: none; border: none; color: #64748B; font-size: 20px; cursor: pointer;">&times;</button>
            </div>

            <div style="margin-bottom: 24px; display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px;">
                        System Prompt Template (Available Tags: {post_title}, {post_content}, {target_keyword})
                    </label>
                    <textarea rows="4" style="width: 100%; background-color: #0B0F19; border: 1px solid #1E2D4A; color: #F8FAFC; padding: 10px; border-radius: 8px; font-size: 12px; font-family: monospace;">You are Bankai AI SEO Architect. Analyze the provided post title "{post_title}" and draft a high-converting 155-character meta description targeting "{target_keyword}". Keep tone professional and persuasive.</textarea>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px;">
                        Creativity Temperature (0.0 = Deterministic, 1.0 = Creative)
                    </label>
                    <input type="range" min="0" max="1" step="0.1" value="0.7" style="width: 100%; accent-color: #10B981;">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button @click="aiDrawer.show = false" style="background-color: #1E2D4A; border: none; color: #F8FAFC; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;">
                    Cancel
                </button>
                <button @click="saveAiDrawerSettings()" style="background-color: #10B981; border: none; color: #FFFFFF; padding: 10px 20px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 14px rgba(16,185,129,0.3);">
                    Save AI Prompt Rules
                </button>
            </div>
        </div>
    </div>
</div>
