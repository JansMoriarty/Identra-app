<div x-data="{
    // Data Leaderboard dari Controller
    leaderboard: @js($leaderboard), 
    search: '',
    itemsPerPage: 10,
    currentPage: 1,

    // Ambil Top 3 untuk Podium
    get topThree() {
        return this.leaderboard.slice(0, 3);
    },

    // Ambil data untuk tabel (termasuk filter search)
    get filteredData() {
        return this.leaderboard.filter(i => {
            return (i.name || '').toLowerCase().includes(this.search.toLowerCase());
        });
    },

    // Pagination untuk tabel
    get paginatedData() {
        const start = (this.currentPage - 1) * this.itemsPerPage;
        return this.filteredData.slice(start, start + this.itemsPerPage);
    },

    get totalPages() {
        return Math.ceil(this.filteredData.length / this.itemsPerPage) || 1;
    },

    // Fungsi Inisial Nama (Contoh: Pauzan Rizky Alamsyah -> PR)
    getInitials(name) {
        if (!name) return '??';
        const parts = name.split(' ');
        if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
}" class="p-6">

    <div class="flex flex-col items-center justify-center mb-12 mt-4">
        <div class="flex items-end justify-center gap-2 sm:gap-8 w-full max-w-4xl">
            
            <template x-if="topThree[1]">
                <div class="flex flex-col items-center group w-32 sm:w-40">
                    <div class="relative mb-4">
                        <div class="w-16 h-16 sm:w-24 sm:h-24 rounded-full bg-slate-700 flex items-center justify-center text-slate-300 font-bold text-2xl border-4 border-slate-400 shadow-xl" x-text="getInitials(topThree[1].name)"></div>
                        <div class="absolute -bottom-2 -right-2 bg-slate-400 text-slate-900 w-8 h-8 rounded-full flex items-center justify-center text-sm font-black border-2 border-white">2</div>
                    </div>
                    <div class="text-center mb-3">
                        <p class="text-sm font-bold text-gray-800 dark:text-white truncate w-24 sm:w-32" x-text="topThree[1].name"></p>
                        <p class="text-xs font-bold text-indigo-500" x-text="(topThree[1].total_points ?? 0) + ' Pts'"></p>
                    </div>
                    <div class="w-full bg-slate-300 dark:bg-slate-700 h-28 rounded-t-3xl flex items-center justify-center shadow-inner transition-all group-hover:h-32">
                        <span class="text-slate-500 dark:text-slate-400 font-black text-3xl italic">2nd</span>
                    </div>
                </div>
            </template>

            <template x-if="topThree[0]">
                <div class="flex flex-col items-center group w-36 sm:w-48">
                    <div class="mb-2 animate-bounce">
                        <svg class="w-12 h-12 text-amber-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M5 16L3 5L8.5 10L12 4L15.5 10L21 5L19 16H5M19 19C19 19.6 18.6 20 18 20H6C5.4 20 5 19.6 5 19V18H19V19Z" />
                        </svg>
                    </div>
                    <div class="relative mb-4">
                        <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 font-black text-4xl border-4 border-amber-400 shadow-[0_0_25px_rgba(245,158,11,0.4)]" x-text="getInitials(topThree[0].name)"></div>
                        <div class="absolute -bottom-2 -right-2 bg-amber-500 text-white w-10 h-10 rounded-full flex items-center justify-center text-lg font-black border-2 border-white shadow-lg">1</div>
                    </div>
                    <div class="text-center mb-3">
                        <p class="text-lg font-black text-gray-900 dark:text-white truncate w-32 sm:w-40" x-text="topThree[0].name"></p>
                        <p class="text-sm font-black text-amber-600" x-text="(topThree[0].total_points ?? 0) + ' Pts'"></p>
                    </div>
                    <div class="w-full bg-gradient-to-b from-amber-400 to-amber-600 h-44 rounded-t-3xl flex flex-col items-center justify-center shadow-xl transition-all group-hover:h-48">
                        <span class="text-white font-black text-5xl italic drop-shadow-lg">1st</span>
                    </div>
                </div>
            </template>

            <template x-if="topThree[2]">
                <div class="flex flex-col items-center group w-32 sm:w-40">
                    <div class="relative mb-4">
                        <div class="w-16 h-16 sm:w-24 sm:h-24 rounded-full bg-orange-100 flex items-center justify-center text-orange-700 font-bold text-2xl border-4 border-orange-400 shadow-xl" x-text="getInitials(topThree[2].name)"></div>
                        <div class="absolute -bottom-2 -right-2 bg-orange-600 text-white w-8 h-8 rounded-full flex items-center justify-center text-sm font-black border-2 border-white">3</div>
                    </div>
                    <div class="text-center mb-3">
                        <p class="text-sm font-bold text-gray-800 dark:text-white truncate w-24 sm:w-32" x-text="topThree[2].name"></p>
                        <p class="text-xs font-bold text-orange-600" x-text="(topThree[2].total_points ?? 0) + ' Pts'"></p>
                    </div>
                    <div class="w-full bg-orange-200 dark:bg-orange-900/40 h-20 rounded-t-3xl flex items-center justify-center shadow-inner transition-all group-hover:h-24">
                        <span class="text-orange-700 dark:text-orange-400 font-black text-3xl italic">3rd</span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden shadow-sm">
        <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white">Peringkat Guru</h3>
            <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" x-model="search" placeholder="Cari Nama Guru..."
                    class="pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white w-full sm:w-72">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500">Rank</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500">Guru</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 text-right">Total Poin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <template x-for="(user, index) in paginatedData" :key="user.id">
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-4">
                                <template x-if="((currentPage - 1) * itemsPerPage + index + 1) === 1">
                                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-100 text-amber-600 font-bold text-sm">1</span>
                                </template>
                                <template x-if="((currentPage - 1) * itemsPerPage + index + 1) === 2">
                                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-slate-200 text-slate-600 font-bold text-sm">2</span>
                                </template>
                                <template x-if="((currentPage - 1) * itemsPerPage + index + 1) === 3">
                                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-600 font-bold text-sm">3</span>
                                </template>
                                <template x-if="((currentPage - 1) * itemsPerPage + index + 1) > 3">
                                    <span class="text-sm font-bold text-gray-400 ml-3" x-text="'#' + ((currentPage - 1) * itemsPerPage + index + 1)"></span>
                                </template>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-xs font-bold text-gray-500 border border-gray-200 dark:border-gray-700" x-text="getInitials(user.name)"></div>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white" x-text="user.name"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="px-3 py-1 text-sm font-bold text-green-600 bg-green-50 rounded-lg dark:bg-green-500/10 font-mono" x-text="number_format(user.total_points ?? 0) + ' Pts'"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between bg-gray-50/30 dark:bg-transparent">
            <p class="text-sm text-gray-500">Menampilkan <span x-text="paginatedData.length"></span> dari <span x-text="filteredData.length"></span> Guru</p>
            <div class="flex gap-2">
                <button @click="currentPage--" :disabled="currentPage === 1" class="px-4 py-2 text-xs font-bold uppercase tracking-wider border rounded-xl disabled:opacity-30 hover:bg-white transition shadow-sm dark:text-white">Prev</button>
                <button @click="currentPage++" :disabled="currentPage >= totalPages" class="px-4 py-2 text-xs font-bold uppercase tracking-wider border rounded-xl disabled:opacity-30 hover:bg-white transition shadow-sm dark:text-white">Next</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Helper fungsi untuk format angka ribuan
    function number_format(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }
</script>