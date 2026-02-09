<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

<div x-data="{
    attendanceData: @js($attendances) || [],
    search: '',
    startDate: '{{ $startDate }}',
    endDate: '{{ $endDate }}',
    
    init() {
        this.$nextTick(() => {
            const config = {
                dateFormat: 'Y-m-d',
                locale: 'id',
                altInput: true,
                altFormat: 'j F Y', // Tampilan: 1 Januari 2024
                allowInput: true
            };

            // Inisialisasi Picker Tanggal Awal
            flatpickr(this.$refs.startPicker, {
                ...config,
                defaultDate: this.startDate,
                onChange: (selectedDates, dateStr) => { this.startDate = dateStr; }
            });

            // Inisialisasi Picker Tanggal Akhir
            flatpickr(this.$refs.endPicker, {
                ...config,
                defaultDate: this.endDate,
                onChange: (selectedDates, dateStr) => { this.endDate = dateStr; }
            });
        });
    },

    // Di dalam x-data
    getAvatarClasses(name) {
        const colors = [
            { bg: 'bg-blue-100 dark:bg-blue-900/30', text: 'text-blue-600 dark:text-blue-400' },
            { bg: 'bg-emerald-100 dark:bg-emerald-900/30', text: 'text-emerald-600 dark:text-emerald-400' },
            { bg: 'bg-rose-100 dark:bg-rose-900/30', text: 'text-rose-600 dark:text-rose-400' },
            { bg: 'bg-amber-100 dark:bg-amber-900/30', text: 'text-amber-600 dark:text-amber-400' },
            { bg: 'bg-purple-100 dark:bg-purple-900/30', text: 'text-purple-600 dark:text-purple-400' },
        ];
        const index = (name ? name.charCodeAt(0) : 0) % colors.length;
        return colors[index];
    },

    normalizeDate(dateStr) {
        if (!dateStr) return '';
        let cleanStr = dateStr.toString().trim();
        if (/^\d{4}-\d{2}-\d{2}$/.test(cleanStr)) return cleanStr;
        const months = { 'january': '01', 'february': '02', 'march': '03', 'april': '04', 'may': '05', 'june': '06', 'july': '07', 'august': '08', 'september': '09', 'october': '10', 'november': '11', 'december': '12', 'januari': '01', 'februari': '02', 'maret': '03', 'april': '04', 'mei': '05', 'juni': '06', 'juli': '07', 'agustus': '08', 'september': '09', 'oktober': '10', 'november': '11', 'desember': '12' };
        try {
            const parts = cleanStr.split(/\s+/);
            if (parts.length < 3) return cleanStr;
            const day = parts[0].padStart(2, '0');
            const month = months[parts[1].toLowerCase()];
            const year = parts[2];
            return (month) ? `${year}-${month}-${day}` : cleanStr;
        } catch (e) { return cleanStr; }
    },

    get filteredData() {
        if (!Array.isArray(this.attendanceData)) return [];
        return this.attendanceData.filter(item => {
            const itemDate = this.normalizeDate(item.tanggal);
            const nama = (item.nama_guru || '').toLowerCase();
            const nip = (item.nip || '').toString();
            const matchDate = (!this.startDate || itemDate >= this.startDate) && (!this.endDate || itemDate <= this.endDate);
            const matchSearch = nama.includes(this.search.toLowerCase()) || nip.includes(this.search);
            return matchDate && matchSearch;
        });
    },

    get groupedData() {
        const groups = {};
        this.filteredData.forEach(item => {
            const tgl = item.tanggal;
            if (!groups[tgl]) groups[tgl] = [];
            groups[tgl].push(item);
        });
        return groups;
    },

    statusColor(status) {
        const s = (status || '').toLowerCase();
        if (s.includes('hadir')) return 'bg-emerald-50 text-emerald-700 border-emerald-200 ring-emerald-600/20';
        if (s.includes('telat')) return 'bg-amber-50 text-amber-700 border-amber-200 ring-amber-600/20';
        return 'bg-rose-50 text-rose-700 border-rose-200 ring-rose-600/20';
    }
}" x-init="init()">

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

        <div class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl transition-all group focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500">
            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 block">Dari Tanggal</label>
            <div class="relative">
                <input x-ref="startPicker" type="text" placeholder="Pilih Tanggal"
                    class="w-full bg-transparent text-sm font-bold text-gray-700 dark:text-gray-200 outline-none cursor-pointer placeholder-gray-300">
                <span class="absolute right-0 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none group-hover:text-blue-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </span>
            </div>
        </div>

        <div class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl transition-all group focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500">
            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 block">Sampai Tanggal</label>
            <div class="relative">
                <input x-ref="endPicker" type="text" placeholder="Pilih Tanggal"
                    class="w-full bg-transparent text-sm font-bold text-gray-700 dark:text-gray-200 outline-none cursor-pointer placeholder-gray-300">
                <span class="absolute right-0 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none group-hover:text-blue-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </span>
            </div>
        </div>

        <div class="md:col-span-2 p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl flex items-center transition-all focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500">
            <div class="relative w-full">
                <input type="text" x-model="search" placeholder="Cari nama guru atau NIP..."
                    class="w-full pl-10 pr-4 py-1 text-sm bg-transparent border-none outline-none text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:ring-0">
                <span class="absolute left-1 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 w-16 text-center">No</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Guru</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 text-center">Jam Masuk</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 text-center">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-gray-500">Metode</th>
                    </tr>
                </thead>

                <template x-for="(items, date) in groupedData" :key="date">
                    <tbody class="border-b border-gray-100 dark:border-gray-700 last:border-0">
                        <tr class="bg-gray-50/80 dark:bg-gray-800/80 sticky top-0 backdrop-blur-sm z-10">
                            <td colspan="5" class="px-6 py-3 border-y border-gray-100 dark:border-gray-700">
                                <div class="flex items-center gap-2.5">
                                    <div class="p-1.5 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="date"></span>
                                    <span class="text-[10px] px-2.5 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full font-medium" x-text="items.length + ' Record'"></span>
                                </div>
                            </td>
                        </tr>

                        <template x-for="(item, index) in items" :key="item.id || index">
                            <tr class="group hover:bg-blue-50/40 dark:hover:bg-blue-900/10 transition-colors duration-150">
                                <td class="px-6 py-4 text-sm text-gray-400 text-center font-mono group-hover:text-blue-500" x-text="index + 1"></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div :class="getAvatarClasses(item.nama_guru).bg + ' ' + getAvatarClasses(item.nama_guru).text"
                                            class="w-10 h-10 flex-shrink-0 rounded-full flex items-center justify-center font-bold text-sm shadow-sm ring-2 ring-white dark:ring-gray-800"
                                            x-text="(item.nama_guru || 'G').charAt(0)">
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-gray-900 dark:text-gray-100 leading-tight mb-0.5" x-text="item.nama_guru"></span>
                                            <span class="text-[11px] text-gray-400 font-mono tracking-wide" x-text="item.nip"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-700">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 font-mono" x-text="item.jam_masuk || '--:--'"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="statusColor(item.status)"
                                        class="inline-flex items-center px-3 py-1 text-[10px] font-bold uppercase rounded-full border shadow-sm ring-1 ring-inset"
                                        x-text="item.status"></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-nowrap" x-text="item.metode"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </template>

                <template x-if="Object.keys(groupedData).length === 0">
                    <tbody>
                        <tr>
                            <td colspan="5" class="px-6 py-24 text-center">
                                <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                    <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-full mb-4">
                                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-gray-900 dark:text-white font-medium mb-1">Tidak ada data ditemukan</h3>
                                    <p class="text-gray-500 text-sm">Coba sesuaikan filter tanggal atau kata kunci pencarian Anda.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </template>
            </table>
        </div>
    </div>
</div>