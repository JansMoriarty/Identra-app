<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<div x-data="{
    attendanceData: @js($attendances) || [],
    showModal: false,
    search: '',
    startDate: '{{ $startDate }}',
    endDate: '{{ $endDate }}',
    
    // Form Modal Data
    selectedGuru: '',
    selectedGuruName: '',
    searchGuru: '',
    isGuruDropdownOpen: false,
    reportStart: '{{ date('Y-m-01') }}',
    reportEnd: '{{ date('Y-m-d') }}',
    guruList: @js(\App\Models\User::where('role', 'guru')->get(['guru_id', 'name'])),

    setupPickers() {
        this.$nextTick(() => {
            const config = {
                dateFormat: 'Y-m-d',
                locale: 'id',
                altInput: true,
                altFormat: 'j F Y',
                allowInput: true
            };

            // Picker Halaman Utama (Filter Monitoring)
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

            // Picker Di Dalam Modal (Cetak Rekap)
            flatpickr(this.$refs.modalStartPicker, {
                ...config,
                defaultDate: this.reportStart,
                onChange: (selectedDates, dateStr) => { this.reportStart = dateStr; }
            });

            flatpickr(this.$refs.modalEndPicker, {
                ...config,
                defaultDate: this.reportEnd,
                onChange: (selectedDates, dateStr) => { this.reportEnd = dateStr; }
            });
        });
    },

    // Logika Search Guru di Modal
    get filteredGuruList() {
        if (this.searchGuru === '') return this.guruList;
        return this.guruList.filter(g => 
            g.name.toLowerCase().includes(this.searchGuru.toLowerCase())
        );
    },

    selectGuru(guru) {
        this.selectedGuru = guru.guru_id;
        this.selectedGuruName = guru.name;
        this.searchGuru = guru.name; // Set text input jadi nama guru
        this.isGuruDropdownOpen = false;
    },

    printPersonalReport() {
        if(!this.selectedGuru) return alert('Pilih guru terlebih dahulu');
        const url = `/report/personal/${this.selectedGuru}?start=${this.reportStart}&end=${this.reportEnd}`;
        window.open(url, '_blank');
        this.showModal = false;
    },

    getAvatarClasses(name) {
        const colors = [
            { bg: 'bg-blue-100', text: 'text-blue-600' },
            { bg: 'bg-emerald-100', text: 'text-emerald-600' },
            { bg: 'bg-rose-100', text: 'text-rose-600' },
            { bg: 'bg-amber-100', text: 'text-amber-600' }
        ];
        const index = (name ? name.charCodeAt(0) : 0) % colors.length;
        return colors[index];
    },

    get filteredData() {
        return (this.attendanceData || []).filter(item => {
            const itemDate = item.tanggal_raw || item.tanggal;
            const nama = (item.nama_guru || '').toLowerCase();
            const nip = (item.nip || '').toString();
            
            const matchDate = (!this.startDate || itemDate >= this.startDate) && 
                             (!this.endDate || itemDate <= this.endDate);
            const matchSearch = nama.includes(this.search.toLowerCase()) || nip.includes(this.search);

            return matchDate && matchSearch;
        });
    },

    get groupedData() {
        const groups = {};
        const sorted = [...this.filteredData].sort((a, b) => b.tanggal.localeCompare(a.tanggal));
        sorted.forEach(item => {
            if (!groups[item.tanggal]) groups[item.tanggal] = [];
            groups[item.tanggal].push(item);
        });
        return groups;
    },

    statusColor(status) {
        const s = (status || '').toLowerCase();
        if (s.includes('hadir')) return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        if (s.includes('telat')) return 'bg-amber-50 text-amber-700 border-amber-200';
        return 'bg-rose-50 text-rose-700 border-rose-200';
    }
}" x-init="setupPickers()">

    <div class="flex flex-col lg:flex-row gap-4 items-end mb-6">
        <div class="grid grid-cols-2 gap-2 w-full lg:w-auto">
            <div class="p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl flex-1">
                <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Dari</label>
                <input x-ref="startPicker" type="text" class="w-full bg-transparent text-sm font-bold outline-none text-gray-700 dark:text-white">
            </div>
            <div class="p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl flex-1">
                <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Sampai</label>
                <input x-ref="endPicker" type="text" class="w-full bg-transparent text-sm font-bold outline-none text-gray-700 dark:text-white">
            </div>
        </div>

        <div class="flex-1 w-full">
            <div class="relative group">
                <input type="text" x-model="search" placeholder="Cari nama guru atau NIP..."
                    class="w-full pl-11 pr-4 py-3.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-gray-700 dark:text-white">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <button @click="showModal = true" class="w-full lg:w-auto px-6 py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center gap-2 text-sm font-bold whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 00-2-2h2m3 2h10M9 3h6m-6 4h6m-6 10h6" />
            </svg>
            Cetak Rekap
        </button>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 w-20 text-center">No</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Guru</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 text-center">Jam Masuk</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 text-center">Jam Pulang</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 text-center">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-gray-500">Metode</th>
                    </tr>
                </thead>

                <template x-for="(items, date) in groupedData" :key="date">
                    <tbody>
                        <tr class="bg-gray-50/80 dark:bg-gray-800/80 backdrop-blur-sm">
                            <td colspan="6" class="px-6 py-3 border-y border-gray-100 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <div class="p-1.5 bg-gray-100 dark:bg-gray-700 rounded-lg">
                                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>

                                    <span class="text-sm font-bold text-gray-700 dark:text-gray-200" x-text="date"></span>

                                    <span class="text-[10px] px-2.5 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-800/50 rounded-full font-semibold"
                                        x-text="items.length + ' Record'">
                                    </span>
                                </div>
                            </td>
                        </tr>

                        <template x-for="(item, index) in items" :key="item.id || index">
                            <tr class="group hover:bg-blue-50/40 dark:hover:bg-blue-900/10 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-400 text-center font-mono" x-text="index + 1"></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div :class="getAvatarClasses(item.nama_guru).bg + ' ' + getAvatarClasses(item.nama_guru).text"
                                            class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs shadow-sm"
                                            x-text="(item.nama_guru || 'G').charAt(0)">
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-gray-900 dark:text-white" x-text="item.nama_guru"></span>
                                            <span class="text-[11px] text-gray-400" x-text="item.nip"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-mono text-gray-600 dark:text-gray-300" x-text="item.jam_masuk || '--:--'"></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-mono text-gray-600 dark:text-gray-300" x-text="item.jam_pulang || '--:--'"></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="statusColor(item.status)"
                                        class="inline-flex items-center px-3 py-1 text-[10px] font-bold uppercase rounded-full border"
                                        x-text="item.status"></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-[10px] font-medium text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded" x-text="item.metode"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </template>
            </table>
        </div>
    </div>

    <div x-show="showModal"
        class="fixed inset-0 z-[99999] overflow-y-auto"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 text-center">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showModal = false"></div>

            <div class="inline-block w-full max-w-md p-8 my-8 overflow-visible text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-2xl rounded-3xl z-10">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Cetak Rekap Guru</h3>

                <div class="space-y-5">
                    <div class="relative">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest block mb-2">Pilih Guru</label>

                        <div class="relative" @click.away="isGuruDropdownOpen = false">
                            <input type="text"
                                placeholder="Cari & pilih nama guru..."
                                class="w-full bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 rounded-xl text-sm p-3.5 pr-10 focus:ring-2 focus:ring-blue-500 outline-none dark:text-white"
                                x-model="searchGuru"
                                @focus="isGuruDropdownOpen = true"
                                @input="isGuruDropdownOpen = true">

                            <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg class="w-5 h-5" :class="isGuruDropdownOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        <div x-show="isGuruDropdownOpen"
                            class="absolute z-[100] w-full mt-2 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl shadow-xl max-h-48 overflow-y-auto"
                            x-transition>
                            <template x-for="guru in filteredGuruList" :key="guru.guru_id">
                                <div @click="selectGuru(guru)"
                                    class="px-4 py-3 text-sm cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/30 dark:text-gray-200 border-b border-gray-50 dark:border-gray-700 last:border-0"
                                    x-text="guru.name">
                                </div>
                            </template>
                            <div x-show="filteredGuruList.length === 0" class="px-4 py-3 text-sm text-gray-500 italic">
                                Guru tidak ditemukan...
                            </div>
                        </div>

                        <div x-show="selectedGuruName && !isGuruDropdownOpen" class="mt-2 flex items-center gap-2">
                            <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2 py-1 rounded-md">
                                Terpilih: <span x-text="selectedGuruName"></span>
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest block mb-2">Dari Tanggal</label>
                            <div class="relative">
                                <input x-ref="modalStartPicker" type="text"
                                    class="w-full bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 rounded-xl text-sm p-3.5 outline-none dark:text-white">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest block mb-2">Sampai Tanggal</label>
                            <div class="relative">
                                <input x-ref="modalEndPicker" type="text"
                                    class="w-full bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 rounded-xl text-sm p-3.5 outline-none dark:text-white">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-10 flex gap-3">
                    <button @click="showModal = false"
                        class="flex-1 px-4 py-3.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">
                        Batal
                    </button>
                    <button @click="printPersonalReport()"
                        :disabled="!selectedGuru"
                        class="flex-1 px-4 py-3.5 bg-blue-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-blue-500/30 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                        Cetak PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>