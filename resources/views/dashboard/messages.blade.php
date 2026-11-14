<x-layouts.dashboard title="Messages | Next Keepers">
    @if (count($threads) === 0)
        <div class="mx-auto max-w-2xl rounded-2xl border border-forest/10 bg-white p-10 text-center shadow-sm">
            <h1 class="font-display text-2xl font-bold text-forest-deep">Messages &amp; Inquiries</h1>
            <p class="mt-3 text-sm text-forest/70">You have no conversations yet. When you send an inquiry about a business, the thread will appear here.</p>
            <a href="{{ route('discover') }}" class="mt-6 inline-flex items-center gap-2 rounded-full bg-forest-deep px-6 py-2.5 text-xs font-bold text-ivory transition hover:bg-forest">Discover businesses &rarr;</a>
        </div>
    @else
    <div class="space-y-6 max-w-7xl mx-auto pb-10" x-data="{
        activeTab: 'all',
        searchQuery: '',
        activeThreadId: 1,
        messageInput: '',
        threads: {{ json_encode($threads) }},
        init() {
            // Find active thread
        },
        getActiveThread() {
            return this.threads.find(t => t.id === this.activeThreadId) || this.threads[0];
        },
        selectThread(id) {
            this.activeThreadId = id;
            var t = this.threads.find(th => th.id === id);
            if (t && t.unread) {
                t.unread = false;
                fetch('/messages/' + id + '/read', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}'
                    }
                });
            }
        },
        sendMessage() {
            if (!this.messageInput.trim()) return;
            var t = this.getActiveThread();
            if (!t) return;

            var now = new Date();
            var hours = now.getHours();
            var mins = now.getMinutes();
            var ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            mins = mins < 10 ? '0' + mins : mins;
            var timeStr = 'Today ' + hours + ':' + mins + ' ' + ampm;

            t.messages.push({
                sender: 'me',
                name: {{ \Illuminate\Support\Js::from($userName ?? 'You') }},
                time: timeStr,
                text: this.messageInput.trim()
            });

            var textToSend = this.messageInput.trim();
            t.last_message = textToSend;
            t.time_ago = 'Just now';
            this.messageInput = '';

            fetch('/messages/' + t.id + '/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}'
                },
                body: JSON.stringify({ text: textToSend })
            });

            this.$nextTick(() => {
                var el = document.getElementById('chat-messages-container');
                if (el) el.scrollTop = el.scrollHeight;
            });
        },
        sendQuickReply(text) {
            this.messageInput = text;
            this.sendMessage();
        },
        filterMatch(thread) {
            if (this.searchQuery.trim()) {
                var q = this.searchQuery.toLowerCase();
                var match = thread.contact_name.toLowerCase().includes(q) ||
                            thread.business_name.toLowerCase().includes(q) ||
                            thread.last_message.toLowerCase().includes(q);
                if (!match) return false;
            }
            if (this.activeTab === 'all') return true;
            if (this.activeTab === 'unread') return thread.unread;
            if (this.activeTab === 'opportunities') return thread.category === 'opportunities';
            if (this.activeTab === 'brokers') return thread.category === 'brokers';
            return true;
        }
    }">
        {{-- Hero Header Banner with Mountain Panorama & KPI Row --}}
        <section class="relative overflow-hidden rounded-3xl border border-forest/10 bg-gradient-to-r from-forest-deep/95 via-forest/80 to-forest-deep/90 text-ivory shadow-md min-h-[9.5rem] p-6 sm:p-8 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6">
            {{-- Background Mountain Panorama --}}
            <img
                src="{{ asset('images/dashboard/mountain-clean.jpg') }}"
                alt="Snow-capped mountains"
                class="absolute inset-0 size-full object-cover object-center opacity-30 mix-blend-luminosity -z-10"
            >
            <div class="absolute inset-0 bg-gradient-to-r from-forest-deep/90 via-forest-deep/70 to-forest-deep/85 -z-10"></div>

            {{-- Title & Subtitle --}}
            <div class="relative z-10">
                <div class="flex items-center gap-3">
                    <span class="grid size-9 place-items-center rounded-xl bg-gold/20 text-gold shadow-sm">
                        <svg class="size-5 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    <h1 class="font-display text-3xl font-medium tracking-tight text-ivory sm:text-4xl">
                        Messages &amp; Inquiries
                    </h1>
                </div>
                <p class="mt-2 text-sm text-ivory/85 max-w-xl">
                    Direct, confidential communication with verified business owners and intermediary brokers.
                </p>
            </div>

            {{-- 4 KPI Metric Cards in Header --}}
            <div class="relative z-10 grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="flex items-center gap-3 rounded-2xl border border-ivory/20 bg-white/95 backdrop-blur-md p-3.5 text-ink shadow-lg min-w-[130px]">
                    <div class="grid size-10 place-items-center rounded-xl bg-forest/10 text-forest shrink-0">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <div>
                        <div class="font-display text-2xl font-bold tracking-tight text-forest-deep leading-none">{{ count($threads) }}</div>
                        <div class="text-[11px] font-medium text-forest-deep/60 mt-0.5">Active Threads</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 rounded-2xl border border-ivory/20 bg-white/95 backdrop-blur-md p-3.5 text-ink shadow-lg min-w-[130px]">
                    <div class="grid size-10 place-items-center rounded-xl bg-gold/15 text-gold shrink-0">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    <div>
                        <div class="font-display text-2xl font-bold tracking-tight text-forest-deep leading-none">{{ collect($threads)->where('unread', true)->count() }}</div>
                        <div class="text-[11px] font-medium text-forest-deep/60 mt-0.5">Unread Messages</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 rounded-2xl border border-ivory/20 bg-white/95 backdrop-blur-md p-3.5 text-ink shadow-lg min-w-[130px]">
                    <div class="grid size-10 place-items-center rounded-xl bg-emerald/15 text-emerald shrink-0">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <div class="font-display text-2xl font-bold tracking-tight text-forest-deep leading-none">{{ collect($threads)->where('nda_signed', true)->count() }}</div>
                        <div class="text-[11px] font-medium text-forest-deep/60 mt-0.5">NDA Signed</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 rounded-2xl border border-ivory/20 bg-white/95 backdrop-blur-md p-3.5 text-ink shadow-lg min-w-[130px]">
                    <div class="grid size-10 place-items-center rounded-xl bg-teal-500/15 text-teal-700 shrink-0">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <div class="font-display text-2xl font-bold tracking-tight text-forest-deep leading-none">{{ collect($threads)->where('call_scheduled', true)->count() }}</div>
                        <div class="text-[11px] font-medium text-forest-deep/60 mt-0.5">Call Scheduled</div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Split Messaging Workspace Container --}}
        <div class="rounded-3xl border border-forest/10 bg-white shadow-sm overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[640px]">
            {{-- Left Panel: Conversations List (5 cols on lg) --}}
            <div class="lg:col-span-5 border-r border-forest/10 flex flex-col bg-[#fdfcf9]">
                {{-- Search Box & Filter Tabs --}}
                <div class="p-4 border-b border-forest/10 space-y-3 bg-white">
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 grid w-9 place-items-center text-forest/40">
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                        </span>
                        <input
                            type="text"
                            x-model="searchQuery"
                            placeholder="Search messages or listings..."
                            class="w-full rounded-xl border border-forest/15 bg-white py-2 pl-9 pr-3 text-xs font-medium text-ink placeholder:text-forest/40 shadow-sm focus:border-forest focus:outline-none"
                        >
                    </div>

                    {{-- Category Filter Pills --}}
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs font-semibold">
                        <button
                            type="button"
                            @click="activeTab = 'all'"
                            :class="activeTab === 'all' ? 'bg-forest-deep text-ivory shadow-sm' : 'bg-forest/5 text-forest/75 hover:bg-forest/10'"
                            class="rounded-full px-3 py-1 transition shrink-0"
                        >
                            All ({{ count($threads) }})
                        </button>
                        <button
                            type="button"
                            @click="activeTab = 'unread'"
                            :class="activeTab === 'unread' ? 'bg-forest-deep text-ivory shadow-sm' : 'bg-forest/5 text-forest/75 hover:bg-forest/10'"
                            class="rounded-full px-3 py-1 transition shrink-0"
                        >
                            Unread ({{ collect($threads)->where('unread', true)->count() }})
                        </button>
                        <button
                            type="button"
                            @click="activeTab = 'opportunities'"
                            :class="activeTab === 'opportunities' ? 'bg-forest-deep text-ivory shadow-sm' : 'bg-forest/5 text-forest/75 hover:bg-forest/10'"
                            class="rounded-full px-3 py-1 transition shrink-0"
                        >
                            Owners
                        </button>
                        <button
                            type="button"
                            @click="activeTab = 'brokers'"
                            :class="activeTab === 'brokers' ? 'bg-forest-deep text-ivory shadow-sm' : 'bg-forest/5 text-forest/75 hover:bg-forest/10'"
                            class="rounded-full px-3 py-1 transition shrink-0"
                        >
                            Brokers
                        </button>
                    </div>
                </div>

                {{-- Threads List Items --}}
                <div class="flex-1 overflow-y-auto divide-y divide-forest/5">
                    <template x-for="t in threads" :key="t.id">
                        <div
                            x-show="filterMatch(t)"
                            @click="selectThread(t.id)"
                            :class="activeThreadId === t.id ? 'bg-forest/10 border-l-4 border-l-gold' : 'hover:bg-forest/5 border-l-4 border-l-transparent'"
                            class="p-4 transition cursor-pointer flex items-start gap-3 relative"
                        >
                            {{-- Avatar with Online Dot --}}
                            <div class="relative shrink-0">
                                <div class="size-10 rounded-full bg-forest-deep text-gold font-display font-bold text-sm grid place-items-center shadow-inner">
                                    <span x-text="t.contact_avatar"></span>
                                </div>
                                <span
                                    x-show="t.online"
                                    class="absolute bottom-0 right-0 size-2.5 rounded-full bg-emerald-500 ring-2 ring-white"
                                    title="Online Now"
                                ></span>
                            </div>

                            {{-- Thread Summary Info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-bold text-forest-deep truncate" x-text="t.contact_name"></h4>
                                    <span class="text-[10px] text-forest/50 shrink-0" x-text="t.time_ago"></span>
                                </div>

                                {{-- Opportunity Tag Badge --}}
                                <div class="mt-0.5 flex items-center gap-1.5">
                                    <span class="inline-flex items-center gap-1 rounded bg-forest/5 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-800 border border-emerald-200/60 truncate max-w-[190px]">
                                        <span>🏢</span>
                                        <span x-text="t.business_name"></span>
                                    </span>
                                    <span class="text-[10px] font-bold text-forest-deep/60" x-text="'(' + t.business_fit + ' score)'"></span>
                                </div>

                                {{-- Snippet --}}
                                <p class="mt-1 text-xs text-sage line-clamp-1" :class="t.unread ? 'font-semibold text-forest-deep' : ''" x-text="t.last_message"></p>
                            </div>

                            {{-- Unread Badge Dot --}}
                            <div x-show="t.unread" class="size-2 rounded-full bg-gold shrink-0 mt-1.5"></div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Right Panel: Active Chat Thread Stream (7 cols on lg) --}}
            <div class="lg:col-span-7 flex flex-col bg-white">
                {{-- Active Thread Header --}}
                <div class="p-4 sm:p-5 border-b border-forest/10 flex flex-wrap items-center justify-between gap-3 bg-[#faf9f5]">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="size-11 rounded-full bg-forest-deep text-gold font-display font-bold text-base grid place-items-center shadow shrink-0">
                            <span x-text="getActiveThread().contact_avatar"></span>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-display text-base font-bold text-forest-deep truncate" x-text="getActiveThread().contact_name"></h3>
                                <span class="rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 px-2 py-0.5 text-[10px] font-bold shrink-0" x-text="getActiveThread().contact_role"></span>
                            </div>
                            <div class="flex items-center gap-1.5 text-xs text-forest/70 mt-0.5 truncate">
                                <span>Opportunity:</span>
                                <span class="font-semibold text-forest-deep truncate" x-text="getActiveThread().business_name"></span>
                                <span class="text-forest/30">&bull;</span>
                                <span class="text-emerald font-semibold" x-text="getActiveThread().business_asking"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center gap-2">
                        <a
                            :href="'/business/' + (getActiveThread().business_id || 1)"
                            class="inline-flex items-center gap-1 rounded-xl border border-forest/20 bg-white px-3 py-1.5 text-xs font-semibold text-forest shadow-sm hover:bg-forest/5 transition"
                            title="View Business Opportunity"
                        >
                            <span>View Deal</span>
                            <span>&rarr;</span>
                        </a>
                        <button
                            type="button"
                            @click="sendQuickReply('Let us schedule a 15-minute introductory video call for this week.')"
                            class="inline-flex items-center gap-1 rounded-xl bg-forest-deep px-3 py-1.5 text-xs font-semibold text-ivory hover:bg-forest transition shadow-sm"
                        >
                            <span>📅 Schedule Call</span>
                        </button>
                    </div>
                </div>

                {{-- Deal Highlights Ribbon --}}
                <div class="px-5 py-2.5 bg-forest/5 border-b border-forest/10 flex items-center justify-between text-xs text-forest-deep">
                    <div class="flex items-center gap-4">
                        <span><strong>Revenue:</strong> <span x-text="getActiveThread().business_revenue"></span></span>
                        <span><strong>SDE:</strong> <span x-text="getActiveThread().business_sde"></span></span>
                        <span><strong>Fit Score:</strong> <span class="font-bold text-emerald" x-text="getActiveThread().business_fit + '/100'"></span></span>
                    </div>
                    <div class="flex items-center gap-1.5 text-emerald text-[11px] font-semibold">
                        <span>🛡️</span>
                        <span>NDA Executed</span>
                    </div>
                </div>

                {{-- Chat Messages Stream Container --}}
                <div id="chat-messages-container" class="flex-1 p-5 space-y-4 overflow-y-auto max-h-[460px] bg-[#fbfaf8]">
                    <div class="text-center my-2">
                        <span class="rounded-full bg-forest/10 px-3 py-1 text-[11px] font-semibold text-forest/60">Discussion Timeline</span>
                    </div>

                    <template x-for="(msg, idx) in getActiveThread().messages" :key="idx">
                        <div>
                            {{-- Incoming Bubble (Them) --}}
                            <div x-show="msg.sender === 'them'" class="flex items-start gap-3 max-w-xl">
                                <div class="size-8 rounded-full bg-forest-deep text-gold font-display font-bold text-xs grid place-items-center shrink-0 mt-0.5">
                                    <span x-text="getActiveThread().contact_avatar"></span>
                                </div>
                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-forest-deep" x-text="msg.name"></span>
                                        <span class="text-[10px] text-forest/40" x-text="msg.time"></span>
                                    </div>
                                    <div class="rounded-2xl rounded-tl-sm border border-forest/10 bg-white p-3.5 text-xs text-forest-deep leading-relaxed shadow-sm">
                                        <p x-text="msg.text"></p>

                                        {{-- Attachments if any --}}
                                        <template x-if="msg.attachments">
                                            <div class="mt-3 space-y-2 pt-2 border-t border-forest/10">
                                                <template x-for="att in msg.attachments" :key="att.name">
                                                    <div class="flex items-center justify-between rounded-xl border border-forest/15 bg-forest/5 p-2.5">
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <span class="text-base">📄</span>
                                                            <div class="min-w-0">
                                                                <div class="font-bold text-xs text-forest-deep truncate" x-text="att.name"></div>
                                                                <div class="text-[10px] text-forest/50" x-text="att.size"></div>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="text-xs font-semibold text-emerald hover:underline shrink-0 ml-2">
                                                            Download &darr;
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Outgoing Bubble (Me) --}}
                            <div x-show="msg.sender === 'me'" class="flex items-start justify-end gap-3 max-w-xl ml-auto">
                                <div class="space-y-1.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <span class="text-[10px] text-forest/40" x-text="msg.time"></span>
                                        <span class="text-xs font-bold text-forest-deep">You</span>
                                    </div>
                                    <div class="rounded-2xl rounded-tr-sm bg-forest-deep p-3.5 text-xs text-ivory leading-relaxed shadow-sm text-left">
                                        <p x-text="msg.text"></p>
                                    </div>
                                </div>
                                <div class="size-8 rounded-full bg-emerald text-ivory font-display font-bold text-xs grid place-items-center shrink-0 mt-0.5 border border-gold/40">
                                    YT
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Quick Suggested Prompts --}}
                <div class="px-5 py-2 border-t border-forest/10 bg-[#faf9f5] flex items-center gap-2 overflow-x-auto text-[11px]">
                    <span class="font-bold text-forest/50 shrink-0">Quick Reply:</span>
                    <button
                        type="button"
                        @click="sendQuickReply('Could you confirm the full equipment inventory and whether replacement reserves were capitalized?')"
                        class="rounded-full border border-forest/20 bg-white px-2.5 py-1 text-forest/80 hover:bg-forest hover:text-ivory transition shrink-0"
                    >
                        Inquire about equipment &amp; reserves
                    </button>
                    <button
                        type="button"
                        @click="sendQuickReply('We have reviewed the CIM and would like to proceed with a 15-minute management call.')"
                        class="rounded-full border border-forest/20 bg-white px-2.5 py-1 text-forest/80 hover:bg-forest hover:text-ivory transition shrink-0"
                    >
                        Schedule management call
                    </button>
                    <button
                        type="button"
                        @click="sendQuickReply('What is the current owner transition timeline and willingness to provide consulting post-sale?')"
                        class="rounded-full border border-forest/20 bg-white px-2.5 py-1 text-forest/80 hover:bg-forest hover:text-ivory transition shrink-0"
                    >
                        Owner transition timeline
                    </button>
                </div>

                {{-- Chat Input Bar --}}
                <div class="p-4 border-t border-forest/10 bg-white">
                    <form @submit.prevent="sendMessage()" class="flex items-center gap-2.5">
                        <button
                            type="button"
                            class="grid size-10 place-items-center rounded-xl border border-forest/15 text-forest/60 hover:text-forest hover:bg-forest/5 transition shrink-0"
                            title="Attach File"
                        >
                            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        </button>

                        <input
                            type="text"
                            x-model="messageInput"
                            placeholder="Type a confidential message..."
                            class="flex-1 rounded-xl border border-forest/15 bg-[#fbfaf8] py-2.5 px-4 text-xs font-medium text-ink placeholder:text-forest/40 focus:border-forest focus:bg-white focus:outline-none"
                        >

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-forest-deep px-5 py-2.5 text-xs font-bold text-ivory hover:bg-forest shadow-md transition cursor-pointer shrink-0"
                        >
                            <span>Send</span>
                            <span>&rarr;</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</x-layouts.dashboard>
