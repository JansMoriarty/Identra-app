@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div x-data="editPlottingForm()" class="py-8 px-4 sm:px-6">
    <div class="max-w-3xl mx-auto">

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div class="flex items-center gap-4">
                <a href="{{ route('guru-positions.index') }}"
                    class="p-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition shadow-sm">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                        Edit Plotting Jabatan
                    </h2>
                    <p class="text-sm text-gray-500">Perbarui jabatan guru yang sudah ada.</p>
                </div>
            </div>
        </div>

        <div
            class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-visible">
            <div class="p-6 md:p-8 space-y-6">

                <!-- NAMA GURU (READ ONLY) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Nama Guru
                    </label>
                    <input type="text"
                        value="{{ $guru_nama }}"
                        readonly
                        class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-700 dark:text-gray-300 cursor-not-allowed">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Pilih Jabatan
                        </label>
                        <select x-model="formData.position_id"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                            <option value="">-- Pilih Jabatan --</option>
                            <template x-for="pos in positions" :key="pos.id">
                                <option :value="pos.id" x-text="pos.nama_jabatan"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Tanggal Mulai
                        </label>
                        <input type="text" x-ref="tglMulai"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 transition cursor-pointer">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Tanggal Selesai (Opsional)
                        </label>

                        <input type="text" x-ref="tglSelesai"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 transition cursor-pointer"
                            placeholder="Biarkan kosong jika aktif">
                    </div>

                    <div class="hidden md:flex items-center h-full pt-6">
                        <div
                            class="text-xs text-blue-700 dark:text-blue-300 bg-blue-50/50 dark:bg-blue-900/10 p-3 rounded-lg border border-dashed border-blue-300 dark:border-blue-700">
                            *Jika jabatan diset aktif, jabatan aktif lain akan otomatis dinonaktifkan.
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Status Jabatan
                    </label>
                    <select x-model="formData.is_active"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                        <option :value="true">Aktif</option>
                        <option :value="false">Selesai</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Nomor SK / Keterangan
                    </label>
                    <textarea x-model="formData.keterangan" rows="3"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 transition"
                        placeholder="Misal: SK.800/012/2024"></textarea>
                </div>
            </div>

            <div
                class="p-6 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 rounded-b-2xl">
                <div class="flex flex-col-reverse sm:flex-row justify-end items-center gap-3">
                    <a href="{{ route('guru-positions.index') }}"
                        class="w-full sm:w-auto px-6 py-3 text-center text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-xl transition flex items-center justify-center">
                        Batal
                    </a>

                    <button @click="submitForm()"
                        :disabled="!formData.position_id || isSubmitting"
                        class="w-full sm:w-auto min-w-[160px] flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold rounded-xl shadow-md shadow-blue-500/20 transition-all active:scale-95">

                        <svg x-show="isSubmitting" class="animate-spin h-4 w-4 text-white" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>

                        <span x-text="isSubmitting ? 'Sedang Menyimpan...' : 'Update Penugasan'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editPlottingForm() {
    return {
        positions: @js($positions),

        isSubmitting: false,

        formData: {
            position_id: '{{ $guruPosition->position_id }}',
            tanggal_mulai: '{{ $guruPosition->tanggal_mulai }}',
            tanggal_selesai: '{{ $guruPosition->tanggal_selesai }}',
            is_active: {{ $guruPosition->is_active ? 'true' : 'false' }},
            keterangan: '{{ $guruPosition->keterangan }}'
        },

        init() {
            const commonConfig = {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd F Y',
                allowInput: true,
            };

            flatpickr(this.$refs.tglMulai, {
                ...commonConfig,
                defaultDate: this.formData.tanggal_mulai,
                onChange: (selectedDates, dateStr) => {
                    this.formData.tanggal_mulai = dateStr;
                }
            });

            flatpickr(this.$refs.tglSelesai, {
                ...commonConfig,
                defaultDate: this.formData.tanggal_selesai,
                onChange: (selectedDates, dateStr) => {
                    this.formData.tanggal_selesai = dateStr;
                }
            });
        },

        async submitForm() {
            if (this.isSubmitting) return;
            this.isSubmitting = true;

            try {
                const response = await fetch(
                    '{{ route("guru-positions.update", $guruPosition->id) }}',
                    {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.formData)
                    }
                );

                if (response.ok) {
                    window.location.href = '{{ route("guru-positions.index") }}';
                } else {
                    const errorData = await response.json();
                    alert("Gagal: " + (errorData.message || "Terjadi kesalahan"));
                }
            } catch (e) {
                alert("Kesalahan koneksi ke server.");
            } finally {
                this.isSubmitting = false;
            }
        }
    }
}
</script>
@endsection
