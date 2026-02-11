<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

<div x-data="{
    leaveRequests: @js($requests->items()) || [], 
    search: '',
    startDate: '{{ request('start_date') }}',
    endDate: '{{ request('end_date') }}',
    
    init() {
        this.$nextTick(() => {
            const config = {
                dateFormat: 'Y-m-d',
                locale: 'id',
                altInput: true,
                altFormat: 'j F Y',
                allowInput: true
            };

            flatpickr(this.$refs.startPicker, {
                ...config,
                defaultDate: this.startDate,
                onChange: (selectedDates, dateStr) => { this.startDate = dateStr; }
            });

            flatpickr(this.$refs.endPicker, {
                ...config,
                defaultDate: this.endDate,
                onChange: (selectedDates, dateStr) => { this.endDate = dateStr; }
            });
        });
    },

    getAvatarClasses(name) {
        const colors = [
            { bg: 'bg-blue-100 dark:bg-blue-900/30', text: 'text-blue-600 dark:text-blue-400' },
            { bg: 'bg-emerald-100 dark:bg-emerald-900/30', text: 'text-emerald-600 dark:text-emerald-400' },
            { bg: 'bg-rose-100 dark:bg-rose-900/30', text: 'text-rose-600 dark:text-rose-400' },
            { bg: 'bg-amber-100 dark:bg-amber-900/30', text: 'text-amber-600 dark:text-amber-400' },
        ];
        const index = (name ? name.charCodeAt(0) : 0) % colors.length;
        return colors[index];
    },

    get filteredData() {
        return this.leaveRequests.filter(item => {
            const nama = (item.guru?.name || '').toLowerCase();
            const matchSearch = nama.includes(this.search.toLowerCase());
            return matchSearch;
        });
    },

    statusStyle(status) {
        const s = status.toLowerCase();
        if (s === 'disetujui') return 'bg-emerald-50 text-emerald-700 border-emerald-200 ring-emerald-600/20';
        if (s === 'pending') return 'bg-amber-50 text-amber-700 border-amber-200 ring-amber-600/20';
        return 'bg-rose-50 text-rose-700 border-rose-200 ring-rose-600/20';
    }
}" x-init="init()">

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl transition-all group focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500">
            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 block">Dari Tanggal</label>
            <input x-ref="startPicker" type="text" class="w-full bg-transparent text-sm font-bold text-gray-700 dark:text-gray-200 outline-none cursor-pointer">
        </div>

        <div class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl transition-all group focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500">
            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 block">Sampai Tanggal</label>
            <input x-ref="endPicker" type="text" class="w-full bg-transparent text-sm font-bold text-gray-700 dark:text-gray-200 outline-none cursor-pointer">
        </div>

        <div class="md:col-span-2 p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl flex items-center">
            <div class="relative w-full">
                <input type="text" x-model="search" placeholder="Cari nama guru..." class="w-full pl-10 pr-4 py-1 text-sm bg-transparent border-none outline-none text-gray-700 dark:text-gray-200 focus:ring-0">
                <span class="absolute left-1 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </span>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 w-16 text-center">No</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500">Guru</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500">Jenis</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500">Durasi</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 text-center">Status</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, index) in filteredData" :key="item.id">
                        <tr class="group hover:bg-blue-50/40 dark:hover:bg-blue-900/10 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-400 text-center font-mono" x-text="index + 1"></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div :class="getAvatarClasses(item.guru?.name).bg + ' ' + getAvatarClasses(item.guru?.name).text"
                                         class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm"
                                         x-text="(item.guru?.name || 'G').charAt(0)">
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-900 dark:text-gray-100" x-text="item.guru?.name"></span>
                                        <span class="text-[11px] text-gray-400 font-mono" x-text="'NIP: ' + (item.guru?.nip || '-')"></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="capitalize font-medium text-gray-700 dark:text-gray-300" x-text="item.jenis"></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-200" x-text="item.tanggal_mulai"></span>
                                    <span class="text-[10px] text-gray-400" x-text="'sampai ' + item.tanggal_selesai"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span :class="statusStyle(item.status)" class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-bold uppercase rounded-full border shadow-sm ring-1 ring-inset" x-text="item.status"></span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a :href="'/storage/' + item.lampiran_foto" target="_blank" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Lihat Lampiran">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </a>

                                    <template x-if="item.status === 'pending'">
                                        <div class="flex gap-2">
                                            <form :action="'/admin/leave/' + item.id + '/status'" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="disetujui">
                                                <button type="submit" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Setujui">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                </button>
                                            </form>
                                            <form :action="'/admin/leave/' + item.id + '/status'" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="ditolak">
                                                <button type="submit" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Tolak">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-200 dark:border-gray-700">
            {{ $requests->links() }}
        </div>
    </div>
</div>