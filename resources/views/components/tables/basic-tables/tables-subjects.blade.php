<div x-data="{
    subjects: [],
    showToast: false,
    toastMessage: '',
    isLoading: false,
    showForm: false,
    apiToken: '1|a1rekHPbsV9hlvoFCruta9c7mmT85Tarcstg8JJv3614fb12', // Token hardcoded kamu
    newSubject: { name: '', code: '' },

    async init() {
        await this.loadSubjects();
    },

    async loadSubjects() {
        try {
            const res = await fetch('/api/subjects', {
                headers: { 
                    'Authorization': `Bearer ${this.apiToken}`,
                    'Accept': 'application/json' 
                }
            });
            const json = await res.json();
            this.subjects = json.data;
        } catch (err) {
            this.showNotification('Gagal memuat data mapel ❌');
        }
    },

    async saveSubject() {
        if (!this.newSubject.name || !this.newSubject.code) {
            this.showNotification('Isi semua field! ⚠️');
            return;
        }

        this.isLoading = true;
        try {
            const response = await fetch('/api/subjects', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.apiToken}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.newSubject)
            });

            if (response.ok) {
                this.showNotification('Mata pelajaran berhasil ditambahkan! ✅');
                this.newSubject = { name: '', code: '' };
                this.showForm = false;
                await this.loadSubjects();
            } else {
                const result = await response.json();
                this.showNotification('Gagal: ' + (result.message || 'Terjadi kesalahan ❌'));
            }
        } catch (err) {
            this.showNotification('Koneksi bermasalah ❌');
        } finally {
            this.isLoading = false;
        }
    },

    async deleteSubject(id) {
        if (!confirm('Hapus mata pelajaran ini?')) return;
        
        try {
            const res = await fetch(`/api/subjects/${id}`, {
                method: 'DELETE',
                headers: { 
                    'Authorization': `Bearer ${this.apiToken}`,
                    'Accept': 'application/json' 
                }
            });
            if (res.ok) {
                this.showNotification('Mata pelajaran dihapus! 🗑️');
                await this.loadSubjects();
            }
        } catch (err) {
            this.showNotification('Gagal menghapus ❌');
        }
    },

    showNotification(msg) {
        this.toastMessage = msg;
        this.showToast = true;
        setTimeout(() => this.showToast = false, 3000);
    }
}" class="p-6">

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Daftar Mata Pelajaran</h1>
            <p class="text-sm text-gray-500">Kelola list mata pelajaran yang akan digunakan dalam jadwal mengajar.</p>
        </div>

        <button @click="showForm = !showForm"
            class="flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition shadow-sm shadow-blue-200 dark:shadow-none">
            <svg x-show="!showForm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <svg x-show="showForm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span x-text="showForm ? 'Batal' : 'Tambah Mapel'"></span>
        </button>
    </div>

    <div x-show="showForm" x-transition 
        class="mb-6 p-6 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div class="flex flex-col gap-2">
                <label class="text-xs font-bold uppercase text-gray-500">Nama Mata Pelajaran</label>
                <input type="text" x-model="newSubject.name" placeholder="Masukan nama mapel..."
                    class="w-full px-4 py-2 text-sm border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white outline-none transition-all">
            </div>
            <div class="flex flex-col gap-2">
                <label class="text-xs font-bold uppercase text-gray-500">Kode Mapel</label>
                <input type="text" x-model="newSubject.code" placeholder="Contoh: MTK-01"
                    class="w-full px-4 py-2 text-sm border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white outline-none transition-all">
            </div>
            <button @click="saveSubject()" :disabled="isLoading"
                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl transition-all disabled:opacity-50">
                <span x-text="isLoading ? 'Sedang Menyimpan...' : 'Simpan Mapel'"></span>
            </button>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 dark:text-gray-400">ID</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Nama Mapel</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Kode</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <template x-for="(subject, index) in subjects" :key="subject.id">
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-5 text-sm text-gray-500" x-text="subject.id"></td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900 dark:text-white" x-text="subject.name"></span>
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-bold uppercase" x-text="subject.code"></span>
                            </td>

                            <td class="px-6 py-5 text-center">
                                <button @click="deleteSubject(subject.id)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="showToast"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed bottom-10 left-1/2 -translate-x-1/2 z-50">
        <div class="bg-gray-900 text-white px-6 py-3 rounded-2xl shadow-xl flex items-center gap-3 border border-white/10">
            <span class="text-sm font-medium" x-text="toastMessage"></span>
        </div>
    </div>
</div>