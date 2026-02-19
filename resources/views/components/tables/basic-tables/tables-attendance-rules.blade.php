<div x-data="{
    rules: @js($rules),
    showToast: false,
    toastMessage: '',
    isLoading: false,

    async updateRules() {
        const csrfToken = document.head.querySelector('meta[name=csrf-token]')?.content;
        
        if (!csrfToken) {
            this.showNotification('Error: CSRF Token tidak ditemukan! ❌');
            return;
        }

        this.isLoading = true;

        // Map data rules ke format { id: value }
        const formData = {};
        this.rules.forEach(rule => {
            formData[rule.id] = rule.rule_value;
        });

        try {
            const response = await fetch('{{ route('attendance-rules.update') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    _method: 'PUT', 
                    rules: formData 
                })
            });

            const result = await response.json();

            if (response.ok) {
                this.showNotification('Aturan absensi berhasil diperbarui! ✅');
            } else {
                // Menampilkan pesan error spesifik dari backend jika ada
                const errorMsg = result.errors ? Object.values(result.errors).flat()[0] : result.message;
                this.showNotification('Gagal: ' + (errorMsg || 'Terjadi kesalahan ❌'));
            }
        } catch (err) {
            console.error('Fetch Error:', err);
            this.showNotification('Koneksi bermasalah atau server error ❌');
        } finally {
            this.isLoading = false;
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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Aturan Absensi</h1>
            <p class="text-sm text-gray-500">Kelola batas waktu keterlambatan dan jam pulang guru.</p>
        </div>

        <button @click="updateRules()"
            :disabled="isLoading"
            class="flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white text-sm font-semibold rounded-xl transition shadow-sm shadow-blue-200 dark:shadow-none">
            <svg x-show="!isLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <svg x-show="isLoading" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span x-text="isLoading ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
        </button>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Nama Aturan</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Waktu (Jam)</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Keterangan</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <template x-for="(rule, index) in rules" :key="rule.id">
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900 dark:text-white text-capitalize"
                                        x-text="rule.name.replace('_', ' ')"></span>
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                <div class="relative w-32">
                                    <input type="time" x-model="rule.rule_value"
                                        class="w-full px-3 py-2 text-sm font-semibold border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white transition-all">
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed max-w-xs"
                                    x-text="rule.description"></p>
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