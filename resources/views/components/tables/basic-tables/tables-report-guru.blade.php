<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<div class="p-6" x-data="{
    search: '',
    startDate: '{{ request('start', date('Y-m-01')) }}',
    endDate: '{{ request('end', date('Y-m-d')) }}',
    rekapGuru: @js($rekapGuru),

    // State Pagination
    currentPage: 1,
    perPage: 10,

    setupPickers() {
        this.$nextTick(() => {
            const config = {
                dateFormat: 'Y-m-d',
                locale: 'id',
                altInput: true,
                altFormat: 'j F Y',
                onChange: (selectedDates, dateStr, instance) => {
                    const isStart = instance.element === this.$refs.startPicker;
                    const start = isStart ? dateStr : this.startDate;
                    const end = !isStart ? dateStr : this.endDate;
                    window.location.href = `{{ route('reports.index') }}?start=${start}&end=${end}`;
                }
            };
            flatpickr(this.$refs.startPicker, { ...config, defaultDate: this.startDate });
            flatpickr(this.$refs.endPicker, { ...config, defaultDate: this.endDate });
        });
    },

    // Reset ke halaman 1 setiap kali user mengetik pencarian
    updateSearch(val) {
        this.search = val;
        this.currentPage = 1;
    },

    get filteredSummary() {
        if (!this.search) return this.rekapGuru;
        return this.rekapGuru.filter(g => 
            (g.name && g.name.toLowerCase().includes(this.search.toLowerCase())) ||
            (g.nip && g.nip.toString().includes(this.search))
        );
    },

    // Data yang dipotong sesuai halaman saat ini
    get paginatedData() {
        let start = (this.currentPage - 1) * this.perPage;
        let end = start + this.perPage;
        return this.filteredSummary.slice(start, end);
    },

    get totalPages() {
        return Math.ceil(this.filteredSummary.length / this.perPage);
    },

    exportExcel() {
        const url = `/report/all-excel?start=${this.startDate}&end=${this.endDate}`;
        window.location.href = url;
    },

    printAllReport() {
        const url = `/report/all-pdf?start=${this.startDate}&end=${this.endDate}`;
        window.open(url, '_blank');
    },

    getAvatarClasses(name) {
        const colors = [
            { bg: 'bg-blue-50', text: 'text-blue-600', border: 'border-blue-100' },
            { bg: 'bg-emerald-50', text: 'text-emerald-600', border: 'border-emerald-100' },
            { bg: 'bg-rose-50', text: 'text-rose-600', border: 'border-rose-100' },
            { bg: 'bg-amber-50', text: 'text-amber-600', border: 'border-amber-100' }
        ];
        const index = (name ? name.charCodeAt(0) : 0) % colors.length;
        return colors[index];
    }
}" x-init="setupPickers()">

    <div class="flex flex-col lg:flex-row gap-4 items-end mb-8">
        <div class="grid grid-cols-2 gap-2 w-full lg:w-auto">
            <div class="p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl flex-1">
                <label class="text-[10px] font-black text-gray-400 uppercase block mb-1 tracking-widest">Dari</label>
                <input x-ref="startPicker" type="text" class="w-full bg-transparent text-sm font-bold outline-none text-gray-700 dark:text-white cursor-pointer">
            </div>
            <div class="p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl flex-1">
                <label class="text-[10px] font-black text-gray-400 uppercase block mb-1 tracking-widest">Sampai</label>
                <input x-ref="endPicker" type="text" class="w-full bg-transparent text-sm font-bold outline-none text-gray-700 dark:text-white cursor-pointer">
            </div>
        </div>

        <div class="flex-1 w-full">
            <div class="relative group">
                <input type="text"
                    :value="search"
                    @input="updateSearch($event.target.value)"
                    placeholder="Cari nama guru atau NIP..."
                    class="w-full pl-11 pr-4 py-3.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-blue-500 transition-all outline-none text-gray-700 dark:text-white">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap lg:flex-nowrap gap-2 w-full lg:w-auto">
            <button @click="printAllReport()"
                class="flex-1 lg:flex-none px-6 py-3.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl transition-all flex items-center justify-center gap-2 text-sm font-bold whitespace-nowrap border-2 border-transparent">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                PDF
            </button>

            <button @click="exportExcel()"
                class="flex-1 lg:flex-none px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition-all flex items-center justify-center gap-2 text-sm font-bold whitespace-nowrap border-2 border-transparent">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Excel
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">Informasi Guru</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest text-center">Hadir</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-amber-500 tracking-widest text-center">Telat</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-blue-500 tracking-widest text-center">Izin/Sakit</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-rose-500 tracking-widest text-center">Alpha</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest text-center">Skor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <template x-for="guru in paginatedData" :key="guru.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div :class="getAvatarClasses(guru.name).bg + ' ' + getAvatarClasses(guru.name).text + ' ' + getAvatarClasses(guru.name).border"
                                        class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border"
                                        x-text="guru.name ? guru.name.charAt(0) : '?'">
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-900 dark:text-white" x-text="guru.name"></span>
                                        <span class="text-[11px] text-gray-400 font-mono tracking-tighter" x-text="guru.nip || '-'"></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 border border-emerald-100 dark:border-emerald-900/50 bg-emerald-50/50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 rounded-lg font-bold text-xs" x-text="guru.total_hadir"></span>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-amber-500 text-sm" x-text="guru.total_telat"></td>
                            <td class="px-6 py-4 text-center font-bold text-blue-500 text-sm" x-text="parseInt(guru.total_izin || 0) + parseInt(guru.total_sakit || 0)"></td>
                            <td class="px-6 py-4 text-center font-bold text-rose-500 text-sm" x-text="guru.total_alpha"></td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col items-center gap-1.5">
                                    <div class="w-20 bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden border border-gray-200 dark:border-gray-600">
                                        <div class="h-full transition-all duration-700"
                                            :class="guru.persentase > 80 ? 'bg-emerald-500' : (guru.persentase > 50 ? 'bg-amber-500' : 'bg-rose-500')"
                                            :style="`width: ${guru.persentase}%` "></div>
                                    </div>
                                    <span class="text-[10px] font-black text-gray-500" x-text="guru.persentase + '%'"></span>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <template x-if="filteredSummary.length === 0">
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                Tidak ada data guru yang cocok dengan pencarian.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="text-xs text-gray-500 font-medium">
                Menampilkan <span x-text="paginatedData.length" class="font-bold"></span>
                dari <span x-text="filteredSummary.length" class="font-bold"></span> data guru
            </div>

            <div class="flex items-center gap-1" x-show="totalPages > 1">
                <button @click="currentPage > 1 ? currentPage-- : null" :disabled="currentPage === 1"
                    class="p-2 rounded-lg border dark:border-gray-700 disabled:opacity-30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>

                <template x-for="page in totalPages" :key="page">
                    <div>
                        <template x-if="page === 1 || page === totalPages || (page >= currentPage - 1 && page <= currentPage + 1)">
                            <button @click="currentPage = page" x-text="page"
                                :class="currentPage === page ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border dark:border-gray-700'"
                                class="w-9 h-9 text-xs font-bold rounded-lg transition-all">
                            </button>
                        </template>

                        <template x-if="(page === 2 && currentPage > 3) || (page === totalPages - 1 && currentPage < totalPages - 2)">
                            <span class="px-2 text-gray-400">...</span>
                        </template>
                    </div>
                </template>

                <button @click="currentPage < totalPages ? currentPage++ : null" :disabled="currentPage === totalPages"
                    class="p-2 rounded-lg border dark:border-gray-700 disabled:opacity-30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>