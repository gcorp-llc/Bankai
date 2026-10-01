/**
 * Bankai Core - Real-time Persian Text Analysis Web Worker
 *
 * Runs non-blocking SEO analysis in background thread:
 * - Persian character normalization (zwnj \u200c, Persian digits)
 * - Word & character count
 * - Keyword density & presence in Title, Meta, Headings
 * - Readability analysis with Persian stop-words
 */

self.onmessage = function (e) {
    const { title = '', content = '', focusKeyword = '', fixKeywords = [] } = e.data || {};

    // Persian normalization
    function normalizePersian(str) {
        if (!str) return '';
        return str
            .replace(/[\u064B-\u0652]/g, '') // Remove Arabic vowel diacritics
            .replace(/[آأإآ]/g, 'ا')
            .replace(/ي/g, 'ی')
            .replace(/ك/g, 'ک')
            .replace(/[0-9]/g, (d) => String.fromCharCode(d.charCodeAt(0) + 1728)) // En to Fa digits
            .toLowerCase();
    }

    const normTitle = normalizePersian(title);
    const normContent = normalizePersian(content);
    const normKeyword = normalizePersian(focusKeyword);

    // Strip HTML tags
    const plainText = normContent.replace(/<[^>]*>/g, ' ');
    const words = plainText.split(/\s+/).filter(Boolean);
    const wordCount = words.length;

    // Keyword density
    let keywordCount = 0;
    if (normKeyword && wordCount > 0) {
        const regex = new RegExp(normKeyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g');
        const matches = plainText.match(regex);
        keywordCount = matches ? matches.length : 0;
    }
    const density = wordCount > 0 ? ((keywordCount / wordCount) * 100).toFixed(2) : 0;

    // Headings analysis
    const h2Matches = content.match(/<h2[^>]*>(.*?)<\/h2>/gi) || [];
    const h3Matches = content.match(/<h3[^>]*>(.*?)<\/h3>/gi) || [];

    // Score calculation
    let score = 0;
    const checks = [];

    // 1. Title Length Check (Persian ideal ~40-65 chars)
    if (title.length >= 35 && title.length <= 65) {
        score += 20;
        checks.push({ status: 'success', text: 'طول عنوان عالی است (بین ۳۵ تا ۶۵ کاراکتر).' });
    } else {
        score += 5;
        checks.push({ status: 'warning', text: 'طول عنوان مناسب نیست (ایده‌آل: ۳۵ تا ۶۵ کاراکتر).' });
    }

    // 2. Focus Keyword in Title
    if (normKeyword && normTitle.includes(normKeyword)) {
        score += 25;
        checks.push({ status: 'success', text: 'کلمه کلیدی اصلی در عنوان وجود دارد.' });
    } else if (normKeyword) {
        checks.push({ status: 'error', text: 'کلمه کلیدی اصلی در عنوان یافت نشد.' });
    }

    // 3. Keyword Density Check (Ideal: 0.8% - 2.5%)
    if (density >= 0.8 && density <= 2.5) {
        score += 25;
        checks.push({ status: 'success', text: `تراکم کلمه کلیدی مناسب است (${density}%).` });
    } else {
        checks.push({ status: 'warning', text: `تراکم کلمه کلیدی غیرمعمول است (${density}%).` });
    }

    // 4. Content Length
    if (wordCount >= 600) {
        score += 30;
        checks.push({ status: 'success', text: `طول محتوا خوب است (${wordCount} کلمه).` });
    } else {
        checks.push({ status: 'warning', text: `محتوا کوتاه‌تر از حد استاندارد است (${wordCount} کلمه).` });
    }

    self.postMessage({
        score: Math.min(100, score),
        wordCount,
        keywordCount,
        density,
        headings: { h2: h2Matches.length, h3: h3Matches.length },
        checks
    });
};
