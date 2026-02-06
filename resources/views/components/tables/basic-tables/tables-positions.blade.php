<div x-data="{
    // Ambil data langsung dari Controller via Blade
    positions: @js($positions),
    search: '',
    itemsPerPage: 10,
    currentPage: 1,
    loading: false,
    error: null,

    // MODAL & TOAST
    showDeleteModal: false,
    selectedPositionId: null,
    toastMessage: '',
    showToast: false,

    init() {
        console.log('Data jabatan berhasil dimuat:', this.positions.length, 'items');
    },

    // COMPUTED
    get filteredData() {
        return this.positions.filter(p => {
            const searchLower = this.search.toLowerCase();
            return (p.nama_jabatan || '').toLowerCase().includes(searchLower) || 
                   (p.kode_jabatan || '').toLowerCase().includes(searchLower);
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
            const res = await fetch(`/positions/${this.selectedPositionId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                }
            });

            if (res.ok) {
                this.positions = this.positions.filter(p => p.id !== this.selectedPositionId);
                this.showSuccessToast('Jabatan berhasil dihapus ✅');
                
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
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" x-model="search" placeholder="Cari Nama atau Kode..." 
                    class="pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white w-full sm:w-64">
            </div>

            <a href="{{ route('positions.create') }}" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Jabatan
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 dark:text-gray-400">No</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Nama Jabatan</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Kode Jabatan</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <template x-if="loading">
                        <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">Memproses data...</td></tr>
                    </template>

                    <template x-if="!loading && filteredData.length === 0">
                        <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">Data jabatan tidak ditemukan.</td></tr>
                    </template>

                    <template x-for="(item, index) in paginatedData" :key="item.id">
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400" x-text="(currentPage - 1) * itemsPerPage + index + 1"></td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white" x-text="item.nama_jabatan"></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-mono font-medium rounded-md bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400" x-text="item.kode_jabatan"></span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a :href="`/positions/${item.id}/edit`" 
                                        class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-lg transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <button @click="selectedPositionId = item.id; showDeleteModal = true" 
                                        class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3m-4 0h12"/></svg>
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
                Menampilkan <span class="font-medium" x-text="paginatedData.length"></span> dari <span class="font-medium" x-text="filteredData.length"></span> jabatan
            </p>
            <div class="flex gap-2">
                <button @click="currentPage--" :disabled="currentPage === 1" 
                    class="px-4 py-2 text-sm font-medium border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:text-white transition">Prev</button>
                <button @click="currentPage++" :disabled="currentPage >= totalPages" 
                    class="px-4 py-2 text-sm font-medium border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:text-white transition">Next</button>
            </div>
        </div>
    </div>

    <div x-show="showDeleteModal" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak>
        <div @click.away="showDeleteModal = false" class="bg-white dark:bg-gray-900 rounded-2xl max-w-sm w-full p-6 shadow-2xl">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full dark:bg-red-500/10">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-bold text-center text-gray-900 dark:text-white">Hapus Jabatan?</h3>
            <p class="mt-2 text-sm text-center text-gray-500">Data yang dihapus tidak dapat dikembalikan. Lanjutkan?</p>
            <div class="mt-6 flex justify-end gap-3">
                <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Batal</button>
                <button @click="confirmDelete" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition shadow-lg shadow-red-500/30">Ya, Hapus</button>
            </div>
        </div>
    </div>

    <div x-show="showToast" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-10 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed bottom-6 right-6 z-[60]" x-cloak>
        <div class="bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-6 py-3 rounded-xl shadow-2xl flex items-center gap-3 border border-white/10">
            <span class="text-sm font-semibold" x-text="toastMessage"></span>
        </div>
    </div>
</div>