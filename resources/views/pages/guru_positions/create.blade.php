@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div x-data="plottingForm()" class="py-8 px-4 sm:px-6">
    <div class="max-w-3xl mx-auto">

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div class="flex items-center gap-4">
                <a href="{{ route('guru-positions.index') }}" class="p-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition shadow-sm">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Plotting Jabatan Baru</h2>
                    <p class="text-sm text-gray-500">Hubungkan guru ke struktur jabatan.</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-visible">
            <div class="p-6 md:p-8 space-y-6">

                <div class="relative">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nama Guru / NIP</label>
                    <div class="relative">
                        <input type="text"
                            x-model="searchQuery"
                            @input.debounce.500ms="fetchGurus()"
                            placeholder="Cari guru via API..."
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">

                        <div x-show="isLoading" class="absolute right-3 top-3.5">
                            <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    <div x-show="showDropdown"
                        @click.away="showDropdown = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        class="absolute z-[60] w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl max-h-60 overflow-y-auto">
                        <template x-for="guru in gurus" :key="guru.id">
                            <button @click="selectGuru(guru)" class="w-full text-left px-4 py-3 hover:bg-blue-50 dark:hover:bg-blue-900/20 border-b border-gray-100 dark:border-gray-700 last:border-0 transition">
                                <p class="text-sm font-bold text-gray-900 dark:text-white" x-text="guru.nama"></p>
                                <p class="text-xs text-gray-500" x-text="'NIP: ' + (guru.nip || '-')"></p>
                            </button>
                        </template>
                        <div x-show="gurus.length === 0 && !isLoading" class="p-4 text-center text-sm text-gray-500">Guru tidak ditemukan.</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Pilih Jabatan</label>
                        <select x-model="formData.position_id" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                            <option value="">-- Pilih Jabatan --</option>
                            <template x-for="pos in positions" :key="pos.id">
                                <option :value="pos.id" x-text="pos.nama_jabatan"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Tanggal Mulai</label>
                        <input type="text" x-ref="tglMulai" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 transition cursor-pointer">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Tanggal Selesai (Opsional)
                        </label>

                        <input type="text" x-ref="tglSelesai" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 transition cursor-pointer" placeholder="Biarkan kosong jika aktif">
                    </div>
                    <div class="hidden md:flex items-center h-full pt-6">
                        <div class="text-xs text-blue-700 dark:text-blue-300 bg-blue-50/50 dark:bg-blue-900/10 p-3 rounded-lg border border-dashed border-blue-300 dark:border-blue-700">
                            *Jabatan sebelumnya akan otomatis dinonaktifkan oleh sistem.
                        </div>
                    </div>

                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nomor SK / Keterangan</label>
                    <textarea x-model="formData.keterangan" rows="3" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="Misal: SK.800/012/2024"></textarea>
                </div>
            </div>

            <div class="p-6 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 rounded-b-2xl">
                <div class="flex flex-col-reverse sm:flex-row justify-end items-center gap-3">
                    <a href="{{ route('guru-positions.index') }}"
                        class="w-full sm:w-auto px-6 py-3 text-center text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-xl transition flex items-center justify-center">
                        Batal
                    </a>

                    <button @click="submitForm()"
                        :disabled="!formData.guru_id || !formData.position_id || isSubmitting"
                        class="w-full sm:w-auto min-w-[160px] flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold rounded-xl shadow-md shadow-blue-500/20 transition-all active:scale-95">

                        <svg x-show="isSubmitting" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>

                        <span x-text="isSubmitting ? 'Sedang Menyimpan...' : 'Simpan Penugasan'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function plottingForm() {
        return {
            positions: @js($positions),
            searchQuery: '',
            gurus: [],
            showDropdown: false,
            isLoading: false,
            isSubmitting: false,

            formData: {
                guru_id: '',
                position_id: '',
                tanggal_mulai: '',
                tanggal_selesai: '',
                keterangan: ''
            },

            init() {
                // Setup Flatpickr
                const commonConfig = {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd F Y',
                };

                flatpickr(this.$refs.tglMulai, {
                    ...commonConfig,
                    onChange: (selectedDates, dateStr) => {
                        this.formData.tanggal_mulai = dateStr;
                    }
                });

                flatpickr(this.$refs.tglSelesai, {
                    ...commonConfig,
                    onChange: (selectedDates, dateStr) => {
                        this.formData.tanggal_selesai = dateStr;
                    }
                });
            },

            async fetchGurus() {
                if (this.searchQuery.length < 3) {
                    this.gurus = [];
                    this.showDropdown = false;
                    return;
                }

                this.isLoading = true;

                try {
                    const response = await fetch(
                        `/api/admin/guru?search=${encodeURIComponent(this.searchQuery)}`, {
                            headers: {
                                "Accept": "application/json",
                                "Authorization": "Bearer 5|qsSrv4dC9zt05bCRweZOkK2YafvQHIZl5OTUPH129ea35db4"
                            }
                        }
                    );


                    if (!response.ok) {
                        throw new Error("API error");
                    }

                    const data = await response.json();
                    this.gurus = Array.isArray(data.data) ? data.data : [];
                    this.showDropdown = this.gurus.length > 0;


                } catch (e) {
                    console.error("Fetch error:", e);
                    this.gurus = [];
                    this.showDropdown = false;
                } finally {
                    this.isLoading = false;
                }
            },


            selectGuru(guru) {
                this.formData.guru_id = guru.guru_id;
                this.searchQuery = guru.nama;
                this.showDropdown = false;
            },

            async submitForm() {
                if (this.isSubmitting) return;
                this.isSubmitting = true;

                try {
                    const response = await fetch('{{ route("guru-positions.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.formData)
                    });

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