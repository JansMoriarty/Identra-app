@extends('layouts.app')

@section('content')

<div x-data="{
    guruId: '{{ $guru_id ?? '' }}',
    name: '',
    email: '',
    nip: '',
    nuptk: '',
    jenis_kelamin: '',
    password: '',
    loading: false,
    initLoading: true,
    error: null,
    success: null,

    async init() {
        if (!this.guruId) {
            this.error = 'UUID guru tidak ditemukan';
            this.initLoading = false;
            return;
        }

        try {
            const res = await fetch(`/api/admin/guru/${this.guruId}`, {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer 1|a1rekHPbsV9hlvoFCruta9c7mmT85Tarcstg8JJv3614fb12'
                }
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal memuat data guru');

            this.name = data.data.name || '';
            this.email = data.data.email || '';
            this.nip = data.data.nip || '';
            this.nuptk = data.data.nuptk || '';
            this.jenis_kelamin = data.data.jenis_kelamin || '';
        } catch (err) {
            this.error = err.message;
        } finally {
            this.initLoading = false;
        }
    },

    async submitForm() {
        this.loading = true;
        this.error = null;
        this.success = null;

        try {
            const res = await fetch(`/api/admin/guru/${this.guruId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Bearer 1|a1rekHPbsV9hlvoFCruta9c7mmT85Tarcstg8JJv3614fb12'
                },
                body: JSON.stringify({
                    name: this.name,
                    email: this.email,
                    nip: this.nip,
                    nuptk: this.nuptk,
                    jenis_kelamin: this.jenis_kelamin,
                    password: this.password
                })
            });

            const result = await res.json();
            if (!res.ok) throw new Error(result.message || 'Gagal mengupdate guru');

            this.success = 'Guru berhasil diupdate 🎉';
            setTimeout(() => window.location.href = '{{ route("guru.index") }}', 1200);
        } catch (err) {
            this.error = err.message;
        } finally {
            this.loading = false;
        }
    }
}" x-init="init()">

    <div class="w-full max-w-3xl mx-auto">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Edit Guru</h2>

            <div x-show="initLoading" class="text-center py-4 text-gray-500">Memuat data guru...</div>
            <div x-show="success" x-text="success" class="mb-4 p-3 rounded-lg bg-green-50 text-green-700 border border-green-200"></div>
            <div x-show="error" x-text="error" class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 border border-red-200"></div>

            <form @submit.prevent="submitForm()" x-show="!initLoading" class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Nama Guru</label>
                    <input type="text" x-model="name" required
                        class="w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800 shadow-theme-xs focus:border-blue-300 focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Email</label>
                    <input type="email" x-model="email" required
                        class="w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800 shadow-theme-xs focus:border-blue-300 focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                <!-- NIP -->
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">NIP</label>
                    <input type="text" x-model="nip"
                        class="w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800 shadow-theme-xs focus:border-blue-300 focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                <!-- NUPTK -->
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">NUPTK</label>
                    <input type="text" x-model="nuptk"
                        class="w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800 shadow-theme-xs focus:border-blue-300 focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                <!-- Jenis Kelamin -->
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Jenis Kelamin</label>
                    <select x-model="jenis_kelamin"
                        class="w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800 shadow-theme-xs focus:border-blue-300 focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">-- Pilih --</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>

                <!-- PASSWORD -->
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Password (Kosongkan jika tidak diubah)</label>
                    <input type="password" x-model="password"
                        placeholder="Masukkan password baru jika ingin diubah"
                        class="w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800 shadow-theme-xs focus:border-blue-300 focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                <div class="col-span-2 flex justify-end gap-3 pt-4">
                    <a href="{{ route('guru.index') }}" class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5">Batal</a>
                    <button type="submit" :disabled="loading" :class="loading ? 'opacity-70 cursor-not-allowed' : ''" class="px-4 py-2.5 rounded-lg bg-blue-500 text-white hover:bg-blue-600">
                        <span x-show="!loading">Update Guru</span>
                        <span x-show="loading">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection