<?php
/**
 * View 6: AI Content Studio & LLMs
 * views/admin/ai-studio.php
 */
$is_rtl = function_exists('is_rtl') && is_rtl();
?>

<div class="space-y-6">
    <div class="bg-[#111827] border border-slate-800 rounded-2xl p-6 shadow-xl">
        <h2 class="text-lg font-bold text-slate-100 mb-1"><?php echo $is_rtl ? 'استودیو محتوای هوش مصنوعی و مدل‌ها' : 'AI Content Studio & Multi-LLM Orchestrator'; ?></h2>
        <p class="text-xs text-slate-400 mb-6"><?php echo $is_rtl ? 'اتصال کلیدهای API پرووایدرهای OpenAI, Gemini, Claude و Groq' : 'Configure API keys for OpenAI, Anthropic Claude, Gemini, and Groq.'; ?></p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-slate-900/80 border border-slate-800 p-4 rounded-xl space-y-2">
                <label class="text-xs font-bold text-slate-300">OpenAI API Key</label>
                <input type="password" value="sk-proj-****************" class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-lg p-2.5 font-mono">
            </div>

            <div class="bg-slate-900/80 border border-slate-800 p-4 rounded-xl space-y-2">
                <label class="text-xs font-bold text-slate-300">Google Gemini API Key</label>
                <input type="password" value="AIzaSy****************" class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-lg p-2.5 font-mono">
            </div>

            <div class="bg-slate-900/80 border border-slate-800 p-4 rounded-xl space-y-2">
                <label class="text-xs font-bold text-slate-300">Anthropic Claude API Key</label>
                <input type="password" value="sk-ant-****************" class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-lg p-2.5 font-mono">
            </div>

            <div class="bg-slate-900/80 border border-slate-800 p-4 rounded-xl space-y-2">
                <label class="text-xs font-bold text-slate-300">Groq High-Speed LLM Key</label>
                <input type="password" value="gsk_****************" class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-lg p-2.5 font-mono">
            </div>
        </div>

        <div class="mt-6">
            <button class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs px-5 py-2.5 rounded-xl font-semibold shadow-lg shadow-indigo-600/30 transition-all">
                <?php echo $is_rtl ? 'ذخیره کلیدهای هوش مصنوعی' : 'Save AI Credentials'; ?>
            </button>
        </div>
    </div>
</div>
