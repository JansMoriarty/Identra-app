<div x-data="{
    classrooms: [],
    search: '',
    itemsPerPage: 10,
    currentPage: 1,
    isLoading: false,
    apiToken: '1|Sg8C2z2Oo2wny9FJy4RHtuK9doSo93yPoWO1JTUN58667636',

    // MODALS STATE
    showCreateModal: false,
    showDeleteModal: false,
    showQRModal: false,
    
    // DATA UNTUK VIEW & FORM
    selectedClass: { name: '', location: '', qr_code: '' },
    newClass: { name: '', location: '' },
    selectedId: null,

    // TOAST
    toastMessage: '',
    showToast: false,

    async init() {
        await this.loadClassrooms();
    },

    async loadClassrooms() {
        this.isLoading = true;
        try {
            const res = await fetch('/api/classrooms', {
                headers: { 
                    'Authorization': `Bearer ${this.apiToken}`, 
                    'Accept': 'application/json' 
                }
            });
            const json = await res.json();
            this.classrooms = json.data;
        } catch (err) {
            this.showNotification('Gagal memuat data ❌');
        } finally {
            this.isLoading = false;
        }
    },

    async saveClassroom() {
        if (!this.newClass.name) return;
        this.isLoading = true;
        try {
            const res = await fetch('/api/classrooms', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.apiToken}`,
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify(this.newClass)
            });

            if (res.ok) {
                this.showNotification('Kelas berhasil dibuat! ✅');
                this.newClass = { name: '', location: '' };
                this.showCreateModal = false;
                await this.loadClassrooms();
            }
        } catch (err) {
            this.showNotification('Gagal menyimpan data ❌');
        } finally {
            this.isLoading = false;
        }
    },

    async confirmDelete() {
        try {
            const res = await fetch(`/api/classrooms/${this.selectedId}`, {
                method: 'DELETE',
                headers: { 
                    'Authorization': `Bearer ${this.apiToken}`, 
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                }
            });

            if (res.ok) {
                this.classrooms = this.classrooms.filter(c => c.id !== this.selectedId);
                this.showNotification('Kelas dihapus 🗑️');
            }
        } catch (err) {
            this.showNotification('Gagal menghapus ❌');
        } finally {
            this.showDeleteModal = false;
            this.selectedId = null;
        }
    },

    printQR() {
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${this.selectedClass.qr_code}`;
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Print QR - ${this.selectedClass.name}</title>
                    <style>
                        body { font-family: sans-serif; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 90vh; text-align: center; }
                        .card { border: 2px solid #eee; padding: 40px; border-radius: 20px; }
                        h1 { font-size: 32px; margin-bottom: 10px; }
                        p { color: #666; font-size: 18px; margin-bottom: 30px; }
                        .code { font-family: monospace; margin-top: 20px; font-weight: bold; background: #f4f4f4; padding: 10px; }
                    </style>
                </head>
                <body>
                    <div class='card'>
                        <h1>${this.selectedClass.name}</h1>
                        <p>${this.selectedClass.location || 'Ruang Kelas'}</p>
                        <img src='${qrUrl}' width='300' height='300' />
                        <div class='code'>${this.selectedClass.qr_code}</div>
                    </div>
                    <script>
                        window.onload = function() { 
                            window.print(); 
                            setTimeout(() => { window.close(); }, 500);
                        }
                    <\/script>
                </body>
            </html>
        `);
    },

    get filteredData() {
        return this.classrooms.filter(item => 
            item.name.toLowerCase().includes(this.search.toLowerCase()) || 
            (item.location || '').toLowerCase().includes(this.search.toLowerCase())
        );
    },

    get paginatedData() {
        const start = (this.currentPage - 1) * this.itemsPerPage;
        return this.filteredData.slice(start, start + this.itemsPerPage);
    },

    get totalPages() {
        return Math.ceil(this.filteredData.length / this.itemsPerPage) || 1;
    },

    showNotification(msg) {
        this.toastMessage = msg;
        this.showToast = true;
        setTimeout(() => this.showToast = false, 3000);
    }
}" class="p-6">

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manajemen Ruang Kelas</h1>
            <p class="text-sm text-gray-500">Kelola ruang belajar dan generate otomatis QR Code.</p>
        </div>

        <button @click="showCreateModal = true"
            class="flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition shadow-sm shadow-blue-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Kelas
        </button>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden shadow-sm">
        <div class="p-5 border-b border-gray-100 dark:border-gray-800">
            <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" x-model="search" placeholder="Cari kelas..." class="pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white w-full sm:w-80 outline-none">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Nama Kelas</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Lokasi</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 dark:text-gray-400">QR Code</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <template x-for="item in paginatedData" :key="item.id">
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <!-- <div class="p-2 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 font-bold text-xs" x-text="item.id"></div> -->
                                    <span class="text-sm font-bold text-gray-900 dark:text-white" x-text="item.name"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500" x-text="item.location || '-'"></td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 rounded-lg text-[10px] font-mono font-bold border border-amber-200" x-text="item.qr_code"></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-x-3">
                                    <button type="button"
                                        @click="selectedClass = item; showQRModal = true; console.log('Buka QR:', item)"
                                        class="group p-2 bg-blue-50 hover:bg-blue-600 rounded-lg transition-all duration-200"
                                        title="Lihat QR">
                                        <svg class="w-5 h-5 text-blue-600 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>

                                    <button type="button"
                                        @click="selectedId = item.id; showDeleteModal = true"
                                        class="group p-2 bg-red-50 hover:bg-red-600 rounded-lg transition-all duration-200"
                                        title="Hapus">
                                        <svg class="w-5 h-5 text-red-600 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <template x-teleport="body">
        <div x-show="showQRModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-md"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">

            <div @click.away="showQRModal = false"
                class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-sm w-full shadow-2xl text-center border border-gray-100 dark:border-gray-700"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">

                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2" x-text="selectedClass?.name"></h3>
                <p class="text-sm text-gray-500 mb-6" x-text="selectedClass?.location || 'Ruang Kelas'"></p>

                <div class="bg-white p-4 rounded-2xl inline-block border-4 border-gray-50 mb-6 shadow-inner">
                    <template x-if="selectedClass?.qr_code">
                        <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${selectedClass.qr_code}`"
                            class="w-48 h-48" alt="QR Code">
                    </template>
                </div>

                <div class="flex flex-col gap-3">
                    <button @click="printQR()" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print QR Code
                    </button>
                    <button @click="showQRModal = false" class="w-full py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold rounded-xl transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div x-show="showCreateModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-cloak>

            <div @click.away="showCreateModal = false"
                class="bg-white dark:bg-gray-800 rounded-3xl p-6 max-w-md w-full shadow-2xl border border-gray-100 dark:border-gray-700"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">

                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Tambah Kelas Baru</h3>
                    <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="saveClassroom()" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Nama Kelas</label>
                        <input type="text" x-model="newClass.name" required placeholder="Contoh: XII RPL 1"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Lokasi (Opsional)</label>
                        <input type="text" x-model="newClass.location" placeholder="Contoh: Gedung A, Lantai 2"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition dark:text-white">
                    </div>

                    <div class="pt-2 flex gap-3">
                        <button type="button" @click="showCreateModal = false"
                            class="flex-1 px-4 py-3 text-sm font-bold text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition dark:bg-gray-700 dark:text-gray-200">
                            Batal
                        </button>
                        <button type="submit" :disabled="isLoading"
                            class="flex-1 px-4 py-3 text-sm font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 disabled:opacity-50 transition shadow-lg shadow-blue-200 dark:shadow-none">
                            <span x-text="isLoading ? 'Menyimpan...' : 'Simpan Kelas'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <template x-if="showDeleteModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 max-w-sm w-full shadow-2xl">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 text-red-600 mb-4">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Hapus Kelas?</h3>
                    <p class="text-sm text-gray-500 mt-2">Data absensi terkait kelas ini mungkin akan terpengaruh.</p>
                </div>
                <div class="mt-6 flex gap-3">
                    <button @click="showDeleteModal = false" class="flex-1 py-2.5 text-sm font-bold bg-gray-100 dark:bg-gray-700 dark:text-white rounded-xl">Batal</button>
                    <button @click="confirmDelete()" class="flex-1 py-2.5 text-sm font-bold bg-red-600 text-white rounded-xl hover:bg-red-700 transition">Hapus</button>
                </div>
            </div>
        </div>
    </template>

    <div x-show="showToast"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-10 left-1/2 -translate-x-1/2 z-50">
        <div class="bg-gray-900 text-white px-6 py-3 rounded-2xl shadow-xl flex items-center gap-3 border border-white/10">
            <span class="text-sm font-medium" x-text="toastMessage"></span>
        </div>
    </div>
</div>