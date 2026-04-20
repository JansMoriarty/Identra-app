<div x-data="{
    items: @js($items), 
    search: '',
    itemsPerPage: 10,
    currentPage: 1,
    loading: false,

    // MODAL & TOAST
    showDeleteModal: false,
    selectedItemId: null,
    toastMessage: '',
    showToast: false,

    // COMPUTED
    get filteredData() {
        return this.items.filter(i => {
            const searchLower = this.search.toLowerCase();
            return (i.item_name || '').toLowerCase().includes(searchLower) || 
                   (i.item_type || '').toLowerCase().includes(searchLower);
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
        console.log('ID yang akan dihapus:', this.selectedItemId);
        
        if (!this.selectedItemId || this.loading) {
            console.error('Proses batal: ID tidak ada atau sedang loading');
            return;
        }

        this.loading = true;
        try {
            const token = document.querySelector('meta[name=csrf-token]')?.content;

            const res = await fetch(`/vouchers/${this.selectedItemId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            // Cek apakah responnya JSON
            const contentType = res.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const textError = await res.text();
                console.error('Server tidak mengirim JSON. Respon:', textError);
                throw new Error('Server error (Bukan JSON)');
            }

            const data = await res.json();

            if (res.ok) {
                this.items = this.items.filter(i => i.id !== this.selectedItemId);
                this.showSuccessToast('Voucher berhasil dihapus ✅');
                
                if (this.paginatedData.length === 0 && this.currentPage > 1) {
                    this.currentPage--;
                }
            } else {
                throw new Error(data.message || 'Gagal menghapus voucher');
            }
        } catch (err) {
            console.error('Delete error detail:', err);
            this.showSuccessToast(err.message || 'Gagal menghapus voucher ❌');
        } finally {
            this.loading = false;
            this.showDeleteModal = false;
            this.selectedItemId = null;
        }
    },

    showSuccessToast(msg) {
        this.toastMessage = msg;
        this.showToast = true;
        setTimeout(() => this.showToast = false, 3000);
    }
}">

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
        <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </span>
                <input type="text" x-model="search" @input="currentPage = 1" placeholder="Cari Voucher..."
                    class="pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white w-full sm:w-64">
            </div>

            <a href="{{ route('vouchers.create') }}" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Buat Voucher Baru
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500">No</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500">Nama Voucher</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500">Tipe / Power</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500">Harga (Poin)</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <template x-for="(item, index) in paginatedData" :key="item.id">
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-600" x-text="(currentPage - 1) * itemsPerPage + index + 1"></td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white" x-text="item.item_name"></span>
                                    <span class="text-xs text-gray-500" x-text="item.description"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm" x-text="item.item_type"></td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-sm font-bold text-amber-600 bg-amber-50 rounded-full" x-text="item.point_cost + ' Pts'"></span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a :href="`/vouchers/${item.id}/edit`" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </a>
                                    <button type="button" 
                                            @click="selectedItemId = item.id; showDeleteModal = true" 
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3m-4 0h12" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="showDeleteModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         style="display: none;">
        
        <div class="bg-white dark:bg-gray-900 rounded-2xl max-w-sm w-full p-6 shadow-2xl" @click.away="showDeleteModal = false">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Hapus Voucher?</h3>
                <p class="text-sm text-gray-500 mt-2">Data ini akan dihapus permanen dari sistem.</p>
            </div>
            <div class="mt-6 flex gap-3">
                <button @click="showDeleteModal = false" :disabled="loading" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 disabled:opacity-50">
                    Batal
                </button>
                <button @click.prevent="confirmDelete()" :disabled="loading" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium disabled:opacity-50">
                    <span x-show="!loading">Ya, Hapus</span>
                    <span x-show="loading" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Loading...
                    </span>
                </button>
            </div>
        </div>
    </div>

    <div x-show="showToast" 
         x-transition 
         class="fixed bottom-5 right-5 z-[1000] px-6 py-3 rounded-xl bg-gray-900 text-white shadow-2xl"
         style="display: none;">
        <span x-text="toastMessage"></span>
    </div>
</div>