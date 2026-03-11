<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<div class="p-6" x-data="{
    search: '',
    selectedDate: '{{ request('date', date('Y-m-d')) }}',
    rekapSesi: @js($rekapSesi),

    setupPicker() {
        this.$nextTick(() => {
            flatpickr(this.$refs.datePicker, {
                dateFormat: 'Y-m-d',
                locale: 'id',
                altInput: true,
                altFormat: 'j F Y',
                defaultDate: this.selectedDate,
                onChange: (selectedDates, dateStr) => {
                    window.location.href = `{{ route('report-class.index') }}?date=${dateStr}`;
                }
            });
        });
    },

    get filteredSummary() {
        if (!this.search) return this.rekapSesi;
        return this.rekapSesi.filter(s => 
            (s.nama_guru && s.nama_guru.toLowerCase().includes(this.search.toLowerCase())) ||
            (s.nama_mapel && s.nama_mapel.toLowerCase().includes(this.search.toLowerCase())) ||
            (s.nama_kelas && s.nama_kelas.toLowerCase().includes(this.search.toLowerCase()))
        );
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
}" x-init="setupPicker()">

    <div class="flex flex-col lg:flex-row gap-4 items-end mb-8">
        <div class="w-full lg:w-72">
            <div class="p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                <label class="text-[10px] font-black text-gray-400 uppercase block mb-1 tracking-widest">Pilih Tanggal Laporan</label>
                <input x-ref="datePicker" type="text" class="w-full bg-transparent text-sm font-bold outline-none text-gray-700 dark:text-white cursor-pointer">
            </div>
        </div>

        <div class="flex-1 w-full">
            <div class="relative group">
                <input type="text" x-model="search" placeholder="Cari nama guru, mapel, atau kelas..."
                    class="w-full pl-11 pr-4 py-3.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-blue-500 transition-all outline-none text-gray-700 dark:text-white">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="flex gap-2 w-full lg:w-auto">
            <button class="flex-1 lg:flex-none px-6 py-3.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl transition-all flex items-center justify-center gap-2 text-sm font-bold border-2 border-transparent shadow-lg shadow-rose-500/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                PDF
            </button>
            <button class="flex-1 lg:flex-none px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition-all flex items-center justify-center gap-2 text-sm font-bold border-2 border-transparent shadow-lg shadow-emerald-500/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Excel
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">Waktu & Sesi</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">Guru & Mata Pelajaran</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest text-center">Kelas</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest text-center">Status Kehadiran</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">Keterangan / Jurnal</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <template x-for="sesi in filteredSummary" :key="sesi.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white" x-text="sesi.jam_mulai + ' - ' + sesi.jam_selesai"></span>
                                    <span class="text-[10px] text-blue-600 dark:text-blue-400 font-black uppercase tracking-tighter" x-text="sesi.hari"></span>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div :class="getAvatarClasses(sesi.nama_guru).bg + ' ' + getAvatarClasses(sesi.nama_guru).text + ' ' + getAvatarClasses(sesi.nama_guru).border"
                                        class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm border shadow-sm"
                                        x-text="sesi.nama_guru ? sesi.nama_guru.charAt(0) : '?'">
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-900 dark:text-white" x-text="sesi.nama_guru"></span>
                                        <span class="text-[11px] text-gray-500 font-medium" x-text="sesi.nama_mapel"></span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="inline-block px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-[11px] font-bold" x-text="sesi.nama_kelas"></span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <template x-if="sesi.status === 'hadir' && !sesi.is_telat">
                                    <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-lg font-black text-[10px] uppercase tracking-wide border border-emerald-100 dark:border-emerald-900/50">Tepat Waktu</span>
                                </template>
                                
                                <template x-if="sesi.status === 'hadir' && sesi.is_telat">
                                    <div class="flex flex-col items-center">
                                        <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 rounded-lg font-black text-[10px] uppercase tracking-wide border border-amber-100 dark:border-amber-900/50">Terlambat</span>
                                        <span class="text-[9px] text-amber-500 mt-1 font-bold" x-text="sesi.menit_telat + ' Menit'"></span>
                                    </div>
                                </template>

                                <template x-if="sesi.status === 'kosong'">
                                    <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded-lg font-black text-[10px] uppercase tracking-wide border border-rose-100 dark:border-rose-900/50">Tanpa Keterangan</span>
                                </template>
                            </td>

                            <td class="px-6 py-4">
                                <p class="text-xs text-gray-600 dark:text-gray-400 italic line-clamp-1 max-w-[180px]" x-text="sesi.materi || 'Belum ada catatan jurnal...'"></p>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <button class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 text-blue-600 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>