(function($) {
    'use strict';

    let selectedSuggestions = [];
    let activeBatchId = '';
    let pollingInterval = null;

    $(document.body).ready(function() {
        initTabs();
        initStep1Events();
        initStep2Events();
        initStep3Events();
    });

    function initTabs() {
        $('.bk-wiz-tab').on('click', function() {
            if ($(this).is(':disabled')) return;
            const step = $(this).data('step');
            switchStep(step);
        });
    }

    function switchStep(step) {
        $('.bk-wiz-tab').removeClass('active').css({'background': '#313244', 'color': '#a6adc8'});
        $(`.bk-wiz-tab[data-step="${step}"]`).addClass('active').css({'background': '#89b4fa', 'color': '#11111b'});

        $('.bk-wiz-step-panel').hide();
        $(`#bk-wiz-step-${step}`).show();

        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }

        if (step === 3) {
            loadProgress();
            pollingInterval = setInterval(function() {
                if (!document.hidden) {
                    loadProgress();
                }
            }, 4000);
        }
    }

    function initStep1Events() {
        $('#bk-btn-suggest-titles').on('click', function() {
            const topic = $('#bk-wiz-topic').val();
            const count = $('#bk-wiz-count').val() || 10;
            const category_id = $('#bk-wiz-cat').val() || 0;

            const $btn = $(this);
            $btn.prop('disabled', true).text('در حال دریافت پیشنهادات AI...');

            $.post(bankaiWizardData.ajaxUrl, {
                action: 'bankai_ai_wizard_suggest_titles',
                nonce: bankaiWizardData.nonce,
                topic: topic,
                count: count,
                category_id: category_id
            }, function(res) {
                $btn.prop('disabled', false).text('✨ دریافت پیشنهاد عنوان از AI');
                if (res.success && res.data.suggestions) {
                    renderSuggestions(res.data.suggestions);
                } else {
                    alert(res.data && res.data.message ? res.data.message : 'خطا در دریافت پیشنهاد عنوان');
                }
            }).fail(function() {
                $btn.prop('disabled', false).text('✨ دریافت پیشنهاد عنوان از AI');
                alert('خطا در ارتباط با سرور.');
            });
        });

        $('#bk-btn-add-manual-title').on('click', function() {
            const title = prompt('عنوان جدید را وارد کنید:');
            if (title && title.trim() !== '') {
                renderSuggestions([{
                    title: title.trim(),
                    focus_keyword: '',
                    search_intent: 'informational',
                    reason: 'ایجاد دستی توسط کاربر'
                }], true);
            }
        });

        $('#bk-chk-toggle-all, #bk-btn-select-all').on('click', function() {
            const $chks = $('.bk-item-chk');
            const allChecked = $chks.filter(':checked').length === $chks.length;
            $chks.prop('checked', !allChecked).trigger('change');
        });

        $(document).on('change', '.bk-item-chk', function() {
            updateSelectedCount();
        });

        $('#bk-btn-goto-step2').on('click', function() {
            if (selectedSuggestions.length === 0) return;
            renderScheduleTable();
            switchStep(2);
        });
    }

    function renderSuggestions(items, append = false) {
        $('#bk-suggestions-wrapper').show();
        const $tbody = $('#bk-suggestions-list');
        if (!append) $tbody.empty();

        items.forEach((item, idx) => {
            const tr = `
                <tr>
                    <td><input type="checkbox" class="bk-item-chk" data-item='${JSON.stringify(item).replace(/'/g, "&apos;")}'></td>
                    <td><input type="text" class="widefat bk-item-title" value="${escapeHtml(item.title)}" style="background:#11111b; border:1px solid #45475a; color:#fff; padding:4px 8px; border-radius:4px;"></td>
                    <td><input type="text" class="widefat bk-item-kw" value="${escapeHtml(item.focus_keyword || '')}" style="background:#11111b; border:1px solid #45475a; color:#fff; padding:4px 8px; border-radius:4px;"></td>
                    <td><span class="badge" style="background:#313244; padding:3px 8px; border-radius:4px; font-size:11px;">${escapeHtml(item.search_intent || 'info')}</span></td>
                    <td><button type="button" class="button button-link-delete bk-btn-del-row" style="color:#f38ba8;">حذف</button></td>
                </tr>
            `;
            $tbody.append(tr);
        });

        $(document).off('click', '.bk-btn-del-row').on('click', '.bk-btn-del-row', function() {
            $(this).closest('tr').remove();
            updateSelectedCount();
        });

        updateSelectedCount();
    }

    function updateSelectedCount() {
        selectedSuggestions = [];
        $('.bk-item-chk:checked').each(function() {
            const $row = $(this).closest('tr');
            selectedSuggestions.push({
                title: $row.find('.bk-item-title').val(),
                focus_keyword: $row.find('.bk-item-kw').val()
            });
        });

        $('#bk-selected-count').text(`${selectedSuggestions.length} عنوان انتخاب شده`);
        $('#bk-btn-goto-step2').prop('disabled', selectedSuggestions.length === 0);
    }

    function initStep2Events() {
        $('#bk-btn-back-step1').on('click', function() {
            switchStep(1);
        });

        $('#bk-btn-auto-distribute').on('click', function() {
            distributeScheduleDates();
        });

        $('#bk-btn-start-generation').on('click', function() {
            startBatchGeneration();
        });
    }

    function renderScheduleTable() {
        const $tbody = $('#bk-schedule-list');
        $tbody.empty();

        const today = new Date().toISOString().split('T')[0];
        $('#bk-wiz-start-date').val(today);

        selectedSuggestions.forEach((item, idx) => {
            const tr = `
                <tr data-idx="${idx}">
                    <td><strong>${escapeHtml(item.title)}</strong></td>
                    <td>${escapeHtml(item.focus_keyword || '-')}</td>
                    <td><input type="datetime-local" class="widefat bk-sched-date" style="background:#11111b; border:1px solid #45475a; color:#fff; padding:4px 8px; border-radius:4px;"></td>
                    <td>
                        <select class="widefat bk-sched-cat" style="background:#11111b; border:1px solid #45475a; color:#fff; padding:4px 8px; border-radius:4px;">
                            ${$('#bk-wiz-cat').html()}
                        </select>
                    </td>
                </tr>
            `;
            $tbody.append(tr);
        });

        distributeScheduleDates();
    }

    function distributeScheduleDates() {
        const startDateStr = $('#bk-wiz-start-date').val() || new Date().toISOString().split('T')[0];
        const intervalDays = parseInt($('#bk-wiz-interval-days').val() || 1, 10);

        let currDate = new Date(startDateStr);
        currDate.setHours(10, 0, 0, 0);

        $('.bk-sched-date').each(function(idx) {
            const d = new Date(currDate.getTime() + (idx * intervalDays * 86400000));
            const formatted = d.toISOString().slice(0, 16);
            $(this).val(formatted);
        });
    }

    function startBatchGeneration() {
        const articles = [];
        $('#bk-schedule-list tr').each(function() {
            const idx = $(this).data('idx');
            const item = selectedSuggestions[idx];
            const dateVal = $(this).find('.bk-sched-date').val();
            const catId = $(this).find('.bk-sched-cat').val();

            articles.push({
                title: item.title,
                focus_keyword: item.focus_keyword,
                category_id: catId,
                scheduled_at: dateVal ? new Date(dateVal).toISOString() : '',
                scheduled_at_local: dateVal || ''
            });
        });

        const options = {
            post_status: $('#bk-wiz-status').val(),
            post_author: $('#bk-wiz-author').val()
        };

        const $btn = $('#bk-btn-start-generation');
        $btn.prop('disabled', true).text('در حال ثبت صف تولید...');

        $.post(bankaiWizardData.ajaxUrl, {
            action: 'bankai_ai_wizard_start_batch',
            nonce: bankaiWizardData.nonce,
            articles: articles,
            options: options
        }, function(res) {
            $btn.prop('disabled', false).text('🚀 تأیید و شروع تولید خودکار مقالات');
            if (res.success) {
                activeBatchId = res.data.batch_id;
                switchStep(3);
            } else {
                alert(res.data && res.data.message ? res.data.message : 'خطا در ساخت صف');
            }
        });
    }

    function initStep3Events() {
        $('#bk-btn-refresh-progress').on('click', function() {
            loadProgress();
        });
    }

    function loadProgress() {
        $.post(bankaiWizardData.ajaxUrl, {
            action: 'bankai_ai_wizard_get_progress',
            nonce: bankaiWizardData.nonce,
            batch_id: activeBatchId
        }, function(res) {
            if (res.success && res.data.jobs) {
                renderProgressTable(res.data.jobs);
            }
        });
    }

    function renderProgressTable(jobs) {
        const $tbody = $('#bk-progress-list');
        $tbody.empty();

        if (jobs.length === 0) {
            $tbody.append('<tr><td colspan="7" style="text-align:center; padding:20px; color:#a6adc8;">هیچ پروژه‌ای یافت نشد.</td></tr>');
            return;
        }

        jobs.forEach(job => {
            const statusBadge = getStatusBadge(job.status);
            const tr = `
                <tr>
                    <td>#${job.id}</td>
                    <td><strong>${escapeHtml(job.title)}</strong></td>
                    <td><span style="background:#313244; padding:3px 8px; border-radius:4px;">${escapeHtml(job.current_step)}</span></td>
                    <td>${statusBadge}</td>
                    <td>${escapeHtml(job.scheduled_at_local || '-')}</td>
                    <td><strong style="color:#a6e3a1;">${job.step_data ? extractSeoScore(job.step_data) : '-'}</strong></td>
                    <td>
                        ${job.status === 'failed' ? `<button class="button button-small bk-btn-job-action" data-id="${job.id}" data-act="retry">تلاش مجدد</button>` : ''}
                        ${job.status === 'pending' || job.status === 'running' ? `<button class="button button-small bk-btn-job-action" data-id="${job.id}" data-act="cancel">لغو</button>` : ''}
                        <button class="button button-small button-link-delete bk-btn-job-action" data-id="${job.id}" data-act="delete">حذف</button>
                    </td>
                </tr>
            `;
            $tbody.append(tr);
        });

        $('.bk-btn-job-action').off('click').on('click', function() {
            const id = $(this).data('id');
            const act = $(this).data('act');
            controlJob(id, act);
        });
    }

    function getStatusBadge(status) {
        switch (status) {
            case 'completed': return '<span style="color:#a6e3a1;">تکمیل‌شده ✓</span>';
            case 'running':   return '<span style="color:#89b4fa;">در حال اجرا...</span>';
            case 'needs_review': return '<span style="color:#f9e2af;">نیازمند بازبینی ⚠️</span>';
            case 'failed':    return '<span style="color:#f38ba8;">ناموفق ❌</span>';
            case 'cancelled': return '<span style="color:#6c7086;">لغو شده</span>';
            default:          return '<span style="color:#cdd6f4;">در صف</span>';
        }
    }

    function controlJob(job_id, action) {
        $.post(bankaiWizardData.ajaxUrl, {
            action: 'bankai_ai_wizard_control_job',
            nonce: bankaiWizardData.nonce,
            job_id: job_id,
            control_action: action
        }, function() {
            loadProgress();
        });
    }

    function extractSeoScore(stepDataStr) {
        try {
            const data = typeof stepDataStr === 'string' ? JSON.parse(stepDataStr) : stepDataStr;
            if (data && data.seo_results && typeof data.seo_results.score !== 'undefined') {
                return data.seo_results.score + '/۱۰۰';
            }
        } catch(e) {}
        return '-';
    }

    function escapeHtml(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

})(jQuery);
