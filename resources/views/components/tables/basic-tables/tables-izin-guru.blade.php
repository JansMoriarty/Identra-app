<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

<style>
    [x-cloak] { display: none !important; }
</style>

<div x-data="{
    leaveRequests: @js($requests->items()) || [], 
    search: '',
    startDate: '{{ request('start_date') }}',
    endDate: '{{ request('end_date') }}',
    showDetail: false,
    selectedItem: null,

    openDetail(item) {
        this.selectedItem = item;
        this.showDetail = true;
    },
    
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
        if (!status) return '';
        const s = status.toLowerCase();
        if (s === 'disetujui') return 'bg-emerald-50 text-emerald-700 border-emerald-200 ring-emerald-600/20';
        if (s === 'pending') return 'bg-amber-50 text-amber-700 border-amber-200 ring-amber-600/20';
        return 'bg-rose-50 text-rose-700 border-rose-200 ring-rose-600/20';
    }
}" x-init="init()">

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl">
            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 block">Dari Tanggal</label>
            <input x-ref="startPicker" type="text" class="w-full bg-transparent text-sm font-bold text-gray-700 dark:text-gray-200 outline-none cursor-pointer">
        </div>

        <div class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl">
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
                                    <button @click="openDetail(item)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Lihat Detail">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </button>

                                    <template x-if="item.status === 'pending'">
                                        <div class="flex gap-2">
                                            <form :action="'/leave/' + item.id + '/status'" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="disetujui">
                                                <button type="submit" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></button>
                                            </form>
                                            <form :action="'/leave/' + item.id + '/status'" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="ditolak">
                                                <button type="submit" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
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

    <template x-teleport="body">
        <div x-show="showDetail" x-cloak class="fixed inset-0 z-[99999999] flex items-center justify-center p-4">
            
            <div x-show="showDetail" 
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 @click="showDetail = false"
                 class="fixed inset-0 bg-black/60 backdrop-blur-sm"></div>

            <div x-show="showDetail"
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="relative bg-white dark:bg-gray-800 w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
                
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-700/50">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Detail Pengajuan Izin</h3>
                    <button @click="showDetail = false" class="text-gray-400 hover:text-primary"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg></button>
                </div>

                <div class="p-6 space-y-6" x-show="selectedItem">
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-100 dark:border-gray-700">
                        <div :class="getAvatarClasses(selectedItem?.guru?.name).bg + ' ' + getAvatarClasses(selectedItem?.guru?.name).text"
                             class="w-14 h-14 rounded-full flex items-center justify-center font-bold text-xl"
                             x-text="(selectedItem?.guru?.name || 'G').charAt(0)">
                        </div>
                        <div>
                            <p class="font-bold text-gray-900 dark:text-white text-base" x-text="selectedItem?.guru?.name"></p>
                            <p class="text-sm text-gray-500" x-text="'NIP: ' + (selectedItem?.guru?.nip || '-')"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-extrabold uppercase text-gray-400 tracking-widest mb-1">Jenis Izin</label>
                            <p class="font-semibold text-gray-700 dark:text-gray-200 capitalize" x-text="selectedItem?.jenis"></p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-extrabold uppercase text-gray-400 tracking-widest mb-1">Status</label>
                            <span :class="statusStyle(selectedItem?.status || '')" class="inline-flex px-3 py-1 text-[10px] font-bold uppercase rounded-lg border" x-text="selectedItem?.status"></span>
                        </div>
                        <div class="col-span-2 border-t border-gray-100 dark:border-gray-700 pt-4">
                            <label class="block text-[10px] font-extrabold uppercase text-gray-400 tracking-widest mb-1">Periode</label>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-200" x-text="selectedItem?.tanggal_mulai + ' s/d ' + selectedItem?.tanggal_selesai"></p>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-extrabold uppercase text-gray-400 tracking-widest mb-1">Alasan</label>
                            <p class="p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg italic text-sm text-gray-600 dark:text-gray-400" x-text="selectedItem?.keterangan || 'Tidak ada keterangan'"></p>
                        </div>
                    </div>

                    <div x-show="selectedItem?.lampiran_foto">
                        <label class="block text-[10px] font-extrabold uppercase text-gray-400 tracking-widest mb-2">Lampiran</label>
                        <a :href="'/storage/' + selectedItem?.lampiran_foto" target="_blank" class="block rounded-xl overflow-hidden border border-gray-200">
                            <img :src="'/storage/' + selectedItem?.lampiran_foto" class="w-full h-44 object-cover">
                        </a>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <button @click="showDetail = false" class="px-6 py-2 text-sm font-bold text-gray-600 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-all">Tutup</button>
                </div>
            </div>
        </div>
    </template>
</div>