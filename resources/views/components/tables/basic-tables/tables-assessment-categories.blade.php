@props(['categories'])

<div x-data="{
    // Data Kategori Penilaian
    categories: @js($categories),
    search: '',
    itemsPerPage: 10,
    currentPage: 1,

    // MODAL STATE
    showDeleteModal: false,
    showFormModal: false,
    isEdit: false,

    // FORM DATA
    formData: {
        id: null,
        name: '',
        description: '',
        weight: ''
    },

    // TOAST
    toastMessage: '',
    showToast: false,

    // COMPUTED
    get filteredData() {
        return this.categories.filter(item => {
            const searchLower = this.search.toLowerCase();
            return (item.name || '').toLowerCase().includes(searchLower) || 
                   (item.description || '').toLowerCase().includes(searchLower);
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
    openCreateModal() {
        this.isEdit = false;
        this.formData = { id: null, name: '', description: '', weight: '' };
        this.showFormModal = true;
    },

    openEditModal(item) {
        this.isEdit = true;
        this.formData = { ...item };
        this.showFormModal = true;
    },

    async confirmDelete() {
        try {
            const res = await fetch(`/assessment-categories/${this.formData.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                }
            });

            if (res.ok) {
                this.categories = this.categories.filter(c => c.id !== this.formData.id);
                this.showSuccessToast('Kategori berhasil dihapus ✅');
            }
        } catch (err) {
            this.showSuccessToast('Gagal menghapus kategori ❌');
        } finally {
            this.showDeleteModal = false;
        }
    },

    showSuccessToast(msg) {
        this.toastMessage = msg;
        this.showToast = true;
        setTimeout(() => this.showToast = false, 3000);
    }
}" @open-modal-tambah.window="openCreateModal()">

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
        <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </span>
                <input type="text" x-model="search" placeholder="Cari kategori..." class="pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white w-full sm:w-64">
            </div>

            <button @click="openCreateModal()" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Tambah Kategori
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500">Nama Kategori</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500">Deskripsi</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 text-center">Bobot</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <template x-for="item in paginatedData" :key="item.id">
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white" x-text="item.name"></td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400" x-text="item.description || '-'"></td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-50 text-blue-600 dark:bg-blue-500/10" x-text="item.weight + '%'"></span>
                            </td>
                            <td class="px-6 py-4 text-right flex justify-end gap-2">
                                <button @click="openEditModal(item)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </button>
                                <button @click="formData.id = item.id; showDeleteModal = true" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3m-4 0h12" /></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <p class="text-sm text-gray-500">Menampilkan <span x-text="paginatedData.length"></span> dari <span x-text="filteredData.length"></span> kategori</p>
            <div class="flex gap-2">
                <button @click="currentPage--" :disabled="currentPage === 1" class="px-4 py-2 text-sm border rounded-lg disabled:opacity-50">Prev</button>
                <button @click="currentPage++" :disabled="currentPage >= totalPages" class="px-4 py-2 text-sm border rounded-lg disabled:opacity-50">Next</button>
            </div>
        </div>
    </div>

    <div x-show="showFormModal" class="fixed inset-0 z-999 flex items-center justify-center p-4 bg-black/50" x-cloak x-transition>
        <div class="bg-white dark:bg-gray-900 rounded-2xl w-full max-w-md p-6 shadow-xl" @click.away="showFormModal = false">
            <h3 class="text-xl font-bold mb-4 dark:text-white" x-text="isEdit ? 'Edit Kategori' : 'Tambah Kategori'"></h3>
            <form :action="isEdit ? `/assessment-categories/${formData.id}` : '/assessment-categories'" method="POST">
                @csrf
                <template x-if="isEdit">
                    @method('PUT')
                </template>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama Kategori</label>
                        <input type="text" name="name" x-model="formData.name" required class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Bobot (%)</label>
                        <input type="number" name="weight" x-model="formData.weight" required class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Deskripsi</label>
                        <textarea name="description" x-model="formData.description" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showFormModal = false" class="px-4 py-2 text-gray-500">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showDeleteModal" class="fixed inset-0 z-999 flex items-center justify-center p-4 bg-black/50" x-cloak x-transition>
        <div class="bg-white dark:bg-gray-900 rounded-2xl w-full max-w-sm p-6 text-center">
            <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-lg font-bold mb-2">Hapus Kategori?</h3>
            <p class="text-gray-500 text-sm mb-6">Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex gap-3">
                <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 border rounded-lg">Batal</button>
                <button @click="confirmDelete()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg">Hapus</button>
            </div>
        </div>
    </div>

    <div x-show="showToast" x-transition class="fixed bottom-5 right-5 z-9999 bg-gray-900 text-white px-6 py-3 rounded-xl shadow-2xl" x-text="toastMessage" x-cloak></div>
</div>