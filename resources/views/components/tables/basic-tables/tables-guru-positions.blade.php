<div x-data="{
    // Data diambil dari GuruPositionController
    assignments: @js($assignments),
    search: '',
    itemsPerPage: 10,
    currentPage: 1,
    loading: false,

    // MODAL & TOAST
    showDeleteModal: false,
    selectedId: null,
    toastMessage: '',
    showToast: false,

    init() {
        console.log('Data penugasan dimuat:', this.assignments.length);
    },

    // COMPUTED
    get filteredData() {
        return this.assignments.filter(item => {
            const searchLower = this.search.toLowerCase();

            const namaGuru = (item.guru_nama || '').toLowerCase();
            const namaJabatan = (item.position?.nama_jabatan || '').toLowerCase();

            return namaGuru.includes(searchLower) ||
                   namaJabatan.includes(searchLower);
        });
    },

    get paginatedData() {
        const start = (this.currentPage - 1) * this.itemsPerPage;
        return this.filteredData.slice(start, start + this.itemsPerPage);
    },

    get totalPages() {
        return Math.ceil(this.filteredData.length / this.itemsPerPage) || 1;
    },

    // ACTIONS
    async confirmDelete() {
        try {
            const res = await fetch(`/guru-positions/${this.selectedId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                }
            });

            if (res.ok) {
                this.assignments = this.assignments.filter(a => a.id !== this.selectedId);
                this.showSuccessToast('Riwayat penugasan dihapus ✅');

                if (this.paginatedData.length === 0 && this.currentPage > 1) {
                    this.currentPage--;
                }
            } else {
                throw new Error('Gagal menghapus');
            }
        } catch (err) {
            this.showSuccessToast('Gagal menghapus data ❌');
        } finally {
            this.showDeleteModal = false;
        }
    },

    showSuccessToast(msg) {
        this.toastMessage = msg;
        this.showToast = true;
        setTimeout(() => this.showToast = false, 3000);
    }
}" x-init="init()">

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">

        <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" x-model="search"
                    placeholder="Cari nama guru atau jabatan..."
                    class="pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white w-full sm:w-64">
            </div>

            <a href="{{ route('guru-positions.create') }}"
                class="flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v16m8-8H4" />
                </svg>
                Plot Jabatan Guru
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Guru</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Jabatan</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Masa Jabatan</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <template x-for="(item, index) in paginatedData" :key="item.id">
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">

                            <!-- 🔥 BAGIAN YANG DIUBAH (UUID → NAMA) -->
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white"
                                        x-text="item.guru_nama || 'Guru tidak ditemukan'">
                                    </span>
                                    <span class="text-xs text-gray-500">Nama Guru</span>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-700 dark:text-gray-300 font-medium"
                                    x-text="item.position?.nama_jabatan"></span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="text-xs space-y-1">
                                    <div class="flex items-center gap-1 text-gray-600 dark:text-gray-400">
                                        <span class="font-bold text-blue-500">Mulai:</span>
                                        <span x-text="item.tanggal_mulai"></span>
                                    </div>
                                    <div class="flex items-center gap-1 text-gray-600 dark:text-gray-400">
                                        <span class="font-bold text-red-500">Selesai:</span>
                                        <span x-text="item.tanggal_selesai || '-'"></span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <template x-if="item.is_active">
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-full bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400">Aktif</span>
                                </template>
                                <template x-if="!item.is_active">
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-full bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-400">Selesai</span>
                                </template>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">

                                    <!-- ✅ BUTTON EDIT (SUDAH DIBETULKAN) -->
                                    <a :href="`/guru-positions/${item.id}/edit`"
                                        class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-lg transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    <!-- Button Delete (tetap sama) -->
                                    <button @click="selectedId = item.id; showDeleteModal = true"
                                        class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3m-4 0h12" />
                                        </svg>
                                    </button>

                                </div>
                            </td>

                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <p class="text-sm text-gray-500">
                Menampilkan
                <span class="font-medium" x-text="paginatedData.length"></span>
                dari
                <span class="font-medium" x-text="filteredData.length"></span>
                riwayat
            </p>

            <div class="flex gap-2">
                <button @click="currentPage--" :disabled="currentPage === 1"
                    class="px-4 py-2 text-sm font-medium border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:text-white transition">
                    Prev
                </button>

                <button @click="currentPage++" :disabled="currentPage >= totalPages"
                    class="px-4 py-2 text-sm font-medium border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:text-white transition">
                    Next
                </button>
            </div>
        </div>

    </div>