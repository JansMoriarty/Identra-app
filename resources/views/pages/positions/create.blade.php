@extends('layouts.app')

@section('content')

<div x-data="{
    formData: {
        nama_jabatan: '',
    },
    loading: false,
    message: '',
    isError: false,

    async submitForm() {
        this.loading = true;
        this.message = '';
        
        try {
            const response = await fetch('/positions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer 1|Gj95onahnRVQ47cazJZJ44iLOwuGZ9yXTrvalqB0e06352dd',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify(this.formData)
            });

            const result = await response.json();

            if (response.ok) {
                this.isError = false;
                this.message = 'Jabatan berhasil ditambahkan! Mengalihkan...';
                setTimeout(() => {
                    window.location.href = '/positions';
                }, 1500);
            } else {
                this.isError = true;
                this.message = result.message || 'Gagal menambahkan data.';
            }
        } catch (err) {
            this.isError = true;
            this.message = 'Terjadi kesalahan sistem.';
        } finally {
            this.loading = false;
        }
    }
}">

    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <a href="/positions" class="text-sm text-blue-600 hover:underline flex items-center gap-2 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Daftar
            </a>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Tambah Jabatan Baru</h2>
        </div>

        <div class="bg-white dark:bg-white/[0.03] border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm">
            <form @submit.prevent="submitForm" class="space-y-5">
                
                <template x-if="message">
                    <div :class="isError ? 'bg-red-50 text-red-600 border-red-200' : 'bg-green-50 text-green-600 border-green-200'" 
                         class="p-4 rounded-lg border text-sm flex items-center gap-3">
                        <span x-text="message"></span>
                    </div>
                </template>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Jabatan</label>
                    <input type="text" x-model="formData.nama_jabatan" required placeholder="Contoh: Kepala Lab, Wali Kelas..."
                        class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white transition">
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="window.location.href='/positions'"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" :disabled="loading"
                        class="px-6 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition disabled:opacity-50 flex items-center gap-2">
                        <svg x-show="loading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="loading ? 'Menyimpan...' : 'Simpan Jabatan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection