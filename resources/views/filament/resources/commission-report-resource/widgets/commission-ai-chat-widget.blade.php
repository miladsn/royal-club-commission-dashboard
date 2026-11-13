<x-filament-widgets::widget>
    <style>
        .commission-ai-chat { border-radius: 1rem; overflow: clip; }
        .commission-ai-chat__body { background: #f2f5f9; overflow-anchor: none; }
        .commission-ai-chat__questions { position: sticky; top: var(--commission-ai-sticky-top, 0px); z-index: 10; border-radius: 1rem; overflow: hidden; background: #fff; box-shadow: 0 4px 12px rgba(15, 23, 42, .12); }
        .commission-ai-chat__questions-header { width: 100%; cursor: pointer; text-align: right; margin: 0; padding: .85rem 1rem; background: #dbe7f5; color: #153b68; }
        .commission-ai-chat__questions-list { padding: 1rem; }
        .commission-ai-chat__questions-spacer { margin: 0 !important; pointer-events: none; }
        .commission-ai-chat__messages { border: 0; border-radius: 1rem; background: transparent; box-shadow: none; }
        .dark .commission-ai-chat__body { background: #111827; }
        .dark .commission-ai-chat__questions { background: #1f2937; }
        .dark .commission-ai-chat__questions-header { background: #263d59; color: #e5eef9; }
        .commission-ai-chat__bubble { border-radius: 1rem; overflow-wrap: anywhere; line-height: 2; }
        .commission-ai-chat__bubble--assistant { background: transparent; border: 0; box-shadow: none; padding: .25rem 0; }
        .commission-ai-chat__copy { display: inline-flex; align-items: center; gap: .3rem; margin-top: .5rem; padding: .25rem .4rem; border-radius: .4rem; font-size: .75rem; opacity: .75; cursor: pointer; }
        .commission-ai-chat__copy:hover, .commission-ai-chat__copy:focus-visible { opacity: 1; background: rgba(127, 127, 127, .15); }
        .commission-ai-chat__content { white-space: pre-line; }
        .commission-ai-chat__content--formatted { white-space: normal; }
        .commission-ai-chat__content--formatted p { margin: 0 0 .75rem; }
        .commission-ai-chat__content--formatted p:last-child { margin-bottom: 0; }
        .commission-ai-chat__content--formatted h1, .commission-ai-chat__content--formatted h2, .commission-ai-chat__content--formatted h3, .commission-ai-chat__content--formatted h4 { font-weight: 700; margin: 1rem 0 .4rem; }
        .commission-ai-chat__content--formatted h1:first-child, .commission-ai-chat__content--formatted h2:first-child, .commission-ai-chat__content--formatted h3:first-child, .commission-ai-chat__content--formatted h4:first-child { margin-top: 0; }
        .commission-ai-chat__content--formatted ul, .commission-ai-chat__content--formatted ol { padding-inline-start: 1.5rem; margin: .5rem 0 .75rem; }
        .commission-ai-chat__content--formatted ul { list-style: disc; }
        .commission-ai-chat__content--formatted ol { list-style: decimal; }
        .commission-ai-chat__content--formatted li { margin-bottom: .3rem; }
        .commission-ai-chat__content--formatted strong { font-weight: 700; }
        .commission-ai-chat__latest { position: absolute; bottom: 100%; left: 0; width: 2.75rem; height: 2.75rem; display: flex; align-items: center; justify-content: center; margin-bottom: .5rem; border-radius: 9999px; background: #fff; color: #153b68; box-shadow: 0 2px 8px rgba(15, 23, 42, .18); cursor: pointer; }
        .dark .commission-ai-chat__latest { background: #374151; color: #f3f4f6; }
        .commission-ai-chat__composer { position: sticky; bottom: 0; z-index: 10; background: #f2f5f9; padding: .25rem 0 .75rem; }
        .dark .commission-ai-chat__composer { background: #111827; }
        .commission-ai-chat__form { display: flex; align-items: center; gap: .75rem; direction: rtl; margin-top: 1rem; }
        .commission-ai-chat__input-wrap { min-width: 0; flex: 1; display: flex; align-items: center; gap: .4rem; border: 1px solid #d1d5db; border-radius: 9999px; background: #fff; padding: .3rem; }
        .commission-ai-chat__input-wrap:focus-within { outline: 2px solid rgb(var(--primary-500)); outline-offset: 2px; }
        .commission-ai-chat__input { min-width: 0; width: 100%; flex: 1; border: 0; background: transparent; padding: .45rem .7rem; color: #111827; box-shadow: none; }
        .commission-ai-chat__input:focus { outline: none; box-shadow: none; }
        .commission-ai-chat__filter { position: relative; flex: none; }
        .commission-ai-chat__filter-trigger { display: flex; align-items: center; gap: .3rem; padding: .5rem .7rem; border-radius: 9999px; background: #f1f5f9; color: #475569; font-size: .75rem; white-space: nowrap; cursor: pointer; }
        .commission-ai-chat__filter-trigger[aria-pressed="true"] { background: #dbeafe; color: #1e40af; }
        .commission-ai-chat__filter-menu { position: absolute; left: 0; bottom: calc(100% + .6rem); width: min(18rem, calc(100vw - 3rem)); padding: .35rem; border: 1px solid #e2e8f0; border-radius: .9rem; background: #fff; box-shadow: 0 10px 25px rgba(15, 23, 42, .16); z-index: 30; }
        .commission-ai-chat__filter-choice { display: flex; width: 100%; align-items: flex-start; justify-content: space-between; gap: .5rem; padding: .65rem .75rem; border-radius: .6rem; color: #1f2937; text-align: right; cursor: pointer; }
        .commission-ai-chat__filter-choice:hover, .commission-ai-chat__filter-choice[aria-checked="true"] { background: #f1f5f9; }
        .commission-ai-chat__filter-choice strong { display: block; font-size: .8rem; }
        .commission-ai-chat__filter-choice small { display: block; margin-top: .2rem; color: #64748b; font-size: .7rem; line-height: 1.6; }
        .commission-ai-chat__filter-check { flex: none; width: 1rem; height: 1rem; color: #2563eb; }
        .dark .commission-ai-chat__input-wrap, .dark .commission-ai-chat__filter-menu { background: #1f2937; border-color: #374151; }
        .dark .commission-ai-chat__input { color: #f3f4f6; }
        .dark .commission-ai-chat__filter-trigger { background: #374151; color: #e5e7eb; }
        .dark .commission-ai-chat__filter-trigger[aria-pressed="true"] { background: #1e3a5f; color: #bfdbfe; }
        .dark .commission-ai-chat__filter-choice { color: #f3f4f6; }
        .dark .commission-ai-chat__filter-choice:hover, .dark .commission-ai-chat__filter-choice[aria-checked="true"] { background: #374151; }
        .dark .commission-ai-chat__filter-choice small { color: #cbd5e1; }
        @media (max-width: 480px) { .commission-ai-chat__filter-trigger-label { display: none; } }
        .commission-ai-chat__send { flex: none; width: 2.75rem; height: 2.75rem; border-radius: 9999px; display: flex; align-items: center; justify-content: center; }
    </style>
    <div class="commission-ai-chat rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden" dir="rtl">

        <div class="bg-primary-600 px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-white/10 rounded-xl">
                    <x-filament::icon icon="heroicon-o-sparkles" class="w-6 h-6 text-white" />
                </div>
                <div>
                    <h2 class="text-base font-bold">دستیار هوشمند و تحلیلگر گزارش‌ها</h2>
                    <p class="text-xs text-primary-100 opacity-90 mt-0.5">برای پرسش مستقیم درباره تراکنش‌ها و کمیسیون‌ها یا انتخاب تحلیل‌های آماده، گزینه‌های زیر را انتخاب کنید.</p>
                </div>
            </div>
            <div class="hidden sm:block">
                <x-filament::icon icon="heroicon-m-information-circle" class="w-6 h-6 text-primary-200" />
            </div>
        </div>

        <div class="commission-ai-chat__body p-6 space-y-5" x-data="{
            inputQuestion: '',
            useTableFilters: false,
            filterMenuOpen: false,
            copyStatus: {},
            questionsOpen: true,
            showLatest: false,
            questionsPinned: false,
            questionsManuallyToggled: false,
            questionsListHeight: 0,
            scrollAnimation: null,
            followAnimation: null,
            followPaused: false,
            messages: [
                { role: 'assistant', text: 'سلام! آماده‌ام تا داده‌های کمیسیون و صرافی‌ها را برات تحلیل کنم. می‌تونی از سوالات آماده بالا انتخاب کنی یا مستقیم سوالت رو بنویسی.' }
            ],
            loading: false,
            typing: false,

            async copyMessage(index, button) {
                const text = button.parentElement.querySelector('.commission-ai-chat__content')?.innerText || '';
                if (!text) return;
                let copied = false;
                try {
                    if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(text);
                        copied = true;
                    }
                } catch (error) {
                    copied = false;
                }
                if (!copied) {
                    const field = document.createElement('textarea');
                    field.value = text;
                    field.style.position = 'fixed';
                    field.style.opacity = '0';
                    document.body.appendChild(field);
                    field.select();
                    try { copied = document.execCommand('copy'); } catch (error) { copied = false; }
                    field.remove();
                    button.focus();
                }
                this.copyStatus[index] = copied ? 'کپی شد' : 'کپی نشد';
                setTimeout(() => { delete this.copyStatus[index]; }, 2000);
            },

            async askQuestion() {
                if (!this.inputQuestion.trim() || this.loading) return;
                let q = this.inputQuestion;
                this.inputQuestion = '';
                await this.dispatchApi({ question: q }, q);
            },

            async askSlug(slug, label) {
                if (this.loading) return;
                await this.dispatchApi({ slug: slug }, label);
            },

            async dispatchApi(payload, displayText) {
                this.loading = true;
                this.followPaused = false;
                if (this.useTableFilters) {
                    const filters = this.$wire.tableFilters || {};
                    payload.filters = {
                        exchange_id: filters.exchange_id?.value || null,
                        status: filters.status?.value || null,
                        uids: filters.uids?.uids || null,
                        date_from: filters.reported_at?.from_date || null,
                        date_to: filters.reported_at?.until_date || null,
                    };
                }
                this.messages.push({ role: 'user', text: displayText });
                this.scrollDown();

                try {
                    let res = await fetch('{{ route('ai.chat') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    });

                    let data = await res.json();
                    let answer = data.answer || 'متأسفانه پاسخی برگردانده نشد.';

                    this.messages.push({ role: 'assistant', text: '' });
                    let targetIndex = this.messages.length - 1;

                    this.typing = true;
                    await this.typeWriter(answer, targetIndex);
                    this.messages[targetIndex].html = data.answer_html || null;

                } catch (e) {
                    this.messages.push({ role: 'assistant', text: 'خطا در ارتباط با سرور یا سرویس هوش مصنوعی.' });
                } finally {
                    this.loading = false;
                    this.typing = false;
                    if (!this.showLatest && !this.followPaused) this.followResponse();
                }
            },

            async typeWriter(text, index) {
                let current = '';
                for (let i = 0; i < text.length; i++) {
                    current += text[i];
                    this.messages[index].text = current;
                    if (i % 4 === 0 && !this.showLatest && !this.followPaused) {
                        this.followResponse();
                    }
                    await new Promise(r => setTimeout(r, 12));
                }
            },

            updateScrollPosition() {
                const box = this.$refs.chatBox;
                const composerHeight = this.$refs.composer.offsetHeight;
                const rect = box.getBoundingClientRect();
                this.showLatest = rect.top < window.innerHeight && rect.bottom > window.innerHeight - composerHeight + 80;
                if (this.followPaused && rect.bottom <= window.innerHeight - composerHeight + 16) this.followPaused = false;

                const topbar = document.querySelector('.fi-topbar');
                const topbarRect = topbar?.getBoundingClientRect();
                const stickyTop = topbarRect && topbarRect.top < 16 && topbarRect.bottom > 0
                    ? topbarRect.bottom
                    : 0;
                this.$el.style.setProperty('--commission-ai-sticky-top', `${stickyTop}px`);
                const sentinelTop = this.$refs.questionsSentinel.getBoundingClientRect().top;
                if (this.questionsManuallyToggled) return;
                if (!this.questionsPinned && sentinelTop < stickyTop - 24) {
                    this.questionsListHeight = this.questionsOpen ? this.$refs.questionsList.offsetHeight : 0;
                    this.questionsPinned = true;
                    this.questionsOpen = false;
                } else if (this.questionsPinned && sentinelTop > stickyTop + 24) {
                    this.questionsPinned = false;
                    this.questionsOpen = true;
                }
            },

            pauseFollowing() {
                this.followPaused = true;
                if (this.followAnimation !== null) cancelAnimationFrame(this.followAnimation);
                this.followAnimation = null;
            },

            followResponse() {
                if (this.followAnimation !== null || this.followPaused) return;
                this.followAnimation = requestAnimationFrame(() => {
                    const follow = () => {
                        if (this.followPaused) { this.followAnimation = null; return; }
                        const bottom = this.$refs.chatBox.getBoundingClientRect().bottom + window.scrollY;
                        const target = Math.min(Math.max(0, bottom - window.innerHeight + this.$refs.composer.offsetHeight + 16), Math.max(0, document.documentElement.scrollHeight - window.innerHeight));
                        const distance = target - window.scrollY;
                        if (distance <= 0.5) { this.followAnimation = null; return; }
                        window.scrollTo(0, window.scrollY + Math.max(1, distance * 0.18));
                        this.followAnimation = requestAnimationFrame(follow);
                    };
                    follow();
                });
            },

            scrollDown(smooth = false) {
                this.$nextTick(() => {
                    if (smooth) this.followPaused = false;
                    if (this.followAnimation !== null) cancelAnimationFrame(this.followAnimation);
                    this.followAnimation = null;
                    const bottom = this.$refs.chatBox.getBoundingClientRect().bottom + window.scrollY;
                    const target = Math.min(Math.max(0, bottom - window.innerHeight + this.$refs.composer.offsetHeight + 16), Math.max(0, document.documentElement.scrollHeight - window.innerHeight));
                    if (this.scrollAnimation) cancelAnimationFrame(this.scrollAnimation);
                    if (!smooth) {
                        if (target > window.scrollY) window.scrollTo(0, target);
                        return;
                    }
                    const start = window.scrollY;
                    const started = performance.now();
                    const animate = (now) => {
                        const progress = Math.min((now - started) / 300, 1);
                        window.scrollTo(0, start + (target - start) * (1 - Math.pow(1 - progress, 3)));
                        if (progress < 1) this.scrollAnimation = requestAnimationFrame(animate);
                        else { this.scrollAnimation = null; this.updateScrollPosition(); }
                    };
                    this.scrollAnimation = requestAnimationFrame(animate);
                });
            }
        }" @scroll.window="updateScrollPosition()" @resize.window="updateScrollPosition()"
           @wheel.window="questionsManuallyToggled = false; if ($event.deltaY < 0) pauseFollowing()" @touchstart.window="pauseFollowing()" @touchmove.window="questionsManuallyToggled = false"
           @keydown.window="if (!['INPUT', 'TEXTAREA'].includes($event.target.tagName)) { if (['ArrowDown', 'ArrowUp', 'PageDown', 'PageUp', 'Home', 'End', ' '].includes($event.key)) questionsManuallyToggled = false; if (['ArrowUp', 'PageUp', 'Home'].includes($event.key)) pauseFollowing() }"
           x-init="$nextTick(() => updateScrollPosition())">

            <!-- Quick and predefined analyses section with custom icons -->
            <div x-ref="questionsSentinel" aria-hidden="true"></div>
            <div class="commission-ai-chat__questions">
                <button type="button" class="commission-ai-chat__questions-header text-xs font-bold flex items-center gap-2" @click="questionsManuallyToggled = true; questionsOpen = !questionsOpen" :aria-expanded="questionsOpen.toString()" aria-controls="commission-ai-questions">
                    <x-filament::icon icon="heroicon-m-bolt" class="w-4 h-4 text-amber-500" />
                    <span>تحلیل‌های سریع و از پیش‌آماده</span>
                    <span x-text="questionsOpen ? '−' : '+'" aria-hidden="true"></span>
                </button>
                <div id="commission-ai-questions" x-ref="questionsList" x-show="questionsOpen" class="commission-ai-chat__questions-list grid grid-cols-1 md:grid-cols-2 gap-2.5">
                    @foreach($predefinedQuestions as $q)
                        <button
                            type="button"
                            @click="askSlug('{{ $q['slug'] }}', '{{ $q['label'] }}')"
                            :disabled="loading"
                            class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-gray-800 border border-primary-100 dark:border-gray-700 hover:border-primary-400 dark:hover:border-primary-600 hover:shadow-md transition text-right group disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <div class="flex items-center gap-2.5">
            <span class="p-2 rounded-lg bg-primary-50 dark:bg-primary-950 text-primary-600 dark:text-primary-400 group-hover:bg-primary-600 group-hover:text-white transition">
                <x-filament::icon icon="{{ $q['icon'] }}" class="w-4 h-4" />
            </span>
                                <span class="text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $q['label'] }}</span>
                            </div>
                            <span class="text-[10px] font-bold px-2.5 py-1 rounded-lg {{ $q['badge_class'] }}">
            {{ $q['badge_text'] }}
        </span>
                        </button>
                    @endforeach

                </div>
            </div>

            <div class="commission-ai-chat__questions-spacer" aria-hidden="true" :style="{ height: questionsPinned && !questionsOpen ? questionsListHeight + 'px' : '0px' }"></div>

            <!-- Chat area with standard message bubbles and scrolling -->
            <div
                x-ref="chatBox"
                class="commission-ai-chat__messages p-4 space-y-4 rounded-2xl"
            >
                <template x-for="(msg, index) in messages" :key="index">
                    <div class="flex items-start gap-3" :class="msg.role === 'user' ? 'flex-row-reverse text-right' : 'flex-row text-right'">

                        <template x-if="msg.role === 'assistant'">
                            <div class="w-10 h-10 rounded-full overflow-hidden shrink-0 shadow-md border-2 border-primary-500/30 bg-white flex items-center justify-center">
                                <img src="{{ asset('images/ai-avatar.webp') }}" alt="AI Robot" class="w-full h-full object-cover">
                            </div>
                        </template>
                        <template x-if="msg.role === 'user'">
                            <div class="w-10 h-10 rounded-full bg-primary-600 text-white flex items-center justify-center shrink-0 shadow-md">
                                <x-filament::icon icon="heroicon-m-user" class="w-5 h-5" />
                            </div>
                        </template>

                        <div class="commission-ai-chat__bubble max-w-[75%] rounded-2xl p-4 text-xs sm:text-sm leading-relaxed shadow-sm"
                             :class="msg.role === 'user'
                                ? 'bg-primary-600 text-white rounded-tl-none font-medium'
                                : 'commission-ai-chat__bubble--assistant text-gray-900 dark:text-gray-100'"
                        >
                            <div class="text-[10px] font-bold mb-1 opacity-75 font-mono" x-text="msg.role === 'user' ? 'شما' : 'هوش مصنوعی رویال‌کلاب'"></div>
                            <template x-if="msg.role === 'assistant' && msg.html">
                                <div class="commission-ai-chat__content commission-ai-chat__content--formatted" x-html="msg.html"></div>
                            </template>
                            <template x-if="msg.role !== 'assistant' || !msg.html">
                                <div class="commission-ai-chat__content" x-text="msg.text"></div>
                            </template>
                            <button type="button" class="commission-ai-chat__copy" @click="copyMessage(index, $event.currentTarget)" :aria-label="copyStatus[index] || 'کپی پیام'" :title="copyStatus[index] || 'کپی پیام'">
                                <x-filament::icon icon="heroicon-o-clipboard-document" class="w-4 h-4" />
                                <span x-show="copyStatus[index]" x-text="copyStatus[index]" aria-live="polite"></span>
                            </button>
                        </div>

                    </div>
                </template>

                <div x-show="loading && !typing" class="flex items-start gap-3 flex-row text-right">
                    <div class="w-10 h-10 rounded-full overflow-hidden shrink-0 shadow-md border-2 border-primary-500/30 bg-white">
                        <img src="{{ asset('images/ai-avatar.webp') }}" alt="AI Robot" class="w-full h-full object-cover">
                    </div>
                    <div class="commission-ai-chat__bubble commission-ai-chat__bubble--assistant flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-primary-500 animate-bounce" style="animation-delay: 0ms;"></span>
                        <span class="w-2 h-2 rounded-full bg-primary-500 animate-bounce" style="animation-delay: 150ms;"></span>
                        <span class="w-2 h-2 rounded-full bg-primary-500 animate-bounce" style="animation-delay: 300ms;"></span>
                        <span class="mr-1">در حال واکشی و تحلیل داده‌ها...</span>
                    </div>
                </div>
            </div>

            <div class="commission-ai-chat__composer" x-ref="composer">
                <button type="button" class="commission-ai-chat__latest" x-show="showLatest" x-cloak @click="scrollDown(true)" aria-label="رفتن به آخرین پیام" title="رفتن به آخرین پیام">
                    <x-filament::icon icon="heroicon-m-arrow-down" class="w-5 h-5" />
                </button>
                <form @submit.prevent="askQuestion()" class="commission-ai-chat__form">
                    <button
                        type="submit"
                        :disabled="loading || !inputQuestion.trim()"
                        class="commission-ai-chat__send bg-primary-600 hover:bg-primary-700 text-white transition disabled:opacity-40 disabled:cursor-not-allowed shadow"
                        title="ارسال"
                        aria-label="ارسال پیام"
                    >
                        <x-filament::icon icon="heroicon-m-paper-airplane" class="w-5 h-5 rtl:-scale-x-100" />
                    </button>
                    <div class="commission-ai-chat__input-wrap">
                        <input
                            type="text"
                            x-model="inputQuestion"
                            placeholder="سؤال خود را از هوش مصنوعی بپرسید..."
                            aria-label="متن پیام"
                            class="commission-ai-chat__input text-xs sm:text-sm placeholder-gray-400"
                        >
                        <div class="commission-ai-chat__filter" @click.outside="filterMenuOpen = false" @keydown.escape.stop="filterMenuOpen = false; $refs.filterTrigger.focus()">
                            <button type="button" x-ref="filterTrigger" class="commission-ai-chat__filter-trigger" @click="filterMenuOpen = !filterMenuOpen" :aria-expanded="filterMenuOpen.toString()" :aria-pressed="useTableFilters.toString()" aria-controls="commission-ai-filter-menu" aria-label="انتخاب فیلتر گزارش">
                                <x-filament::icon icon="heroicon-m-funnel" class="w-4 h-4" />
                                <span class="commission-ai-chat__filter-trigger-label" x-text="useTableFilters ? 'فیلتر جدول' : 'همه داده‌ها'"></span>
                                <x-filament::icon icon="heroicon-m-chevron-down" class="w-4 h-4" />
                            </button>
                            <div id="commission-ai-filter-menu" class="commission-ai-chat__filter-menu" x-show="filterMenuOpen" x-cloak role="radiogroup" aria-label="محدوده داده‌های چت">
                                <button type="button" class="commission-ai-chat__filter-choice" role="radio" :aria-checked="(!useTableFilters).toString()" @click="useTableFilters = false; filterMenuOpen = false; $refs.filterTrigger.focus()">
                                    <span><strong>همه داده‌ها</strong><small>بدون استفاده از فیلترهای جدول</small></span>
                                    <x-filament::icon icon="heroicon-m-check" class="commission-ai-chat__filter-check" x-show="!useTableFilters" />
                                </button>
                                <button type="button" class="commission-ai-chat__filter-choice" role="radio" :aria-checked="useTableFilters.toString()" @click="useTableFilters = true; filterMenuOpen = false; $refs.filterTrigger.focus()">
                                    <span><strong>فیلترهای جدول</strong><small>استفاده از فیلترهای فعال جدول گزارش بالا</small><small>در آمار حساب‌ها فقط حساب‌های دارای گزارش منطبق شمرده می‌شوند.</small></span>
                                    <x-filament::icon icon="heroicon-m-check" class="commission-ai-chat__filter-check" x-show="useTableFilters" />
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-filament-widgets::widget>
