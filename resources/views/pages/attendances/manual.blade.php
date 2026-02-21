@extends('layouts.kiosk')

@section('content')
<div class="min-h-screen bg-white text-gray-900 px-6 py-8 lg:px-20 lg:py-6"
    x-data="{ 
        searchQuery: '', 
        showDropdown: false, 
        selectedGuru: null,
        gurus: {{ json_encode($gurus) }},
        get filteredGurus() {
            if (this.searchQuery === '') return [];
            return this.gurus.filter(g => g.name.toLowerCase().includes(this.searchQuery.toLowerCase()));
        },
        selectGuru(guru) {
            this.selectedGuru = guru;
            this.searchQuery = guru.name;
            this.showDropdown = false;
        }
     }">

    {{-- BACK BUTTON & HEADER --}}
    <div class="flex items-center gap-6 mb-10">
        <a href="#" class="p-4 bg-gray-50 hover:bg-gray-100 rounded-2xl transition-all border border-gray-200 text-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-black tracking-tight text-gray-800">Absensi Manual</h1>
            <p class="text-sm text-gray-400 font-medium">Cari nama Anda untuk melakukan absensi</p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('attendances.store') }}" method="POST" id="manualAuthForm">
            @csrf
            <input type="hidden" name="latitude" id="lat_input">
            <input type="hidden" name="longitude" id="lng_input">

            {{-- Hidden Input untuk menyimpan guru_id yang dipilih --}}
            <input type="hidden" name="guru_id" :value="selectedGuru ? selectedGuru.guru_id : ''" required>

            <div class="grid grid-cols-1 gap-8">

                {{-- SEARCH GURU CARD --}}
                <div class="rounded-[32px] p-8 bg-white border-2 border-gray-100">
                    <label class="text-xs font-black mb-4 text-gray-400 uppercase tracking-[0.2em] block">
                        Nama Personel
                    </label>

                    <div class="relative">
                        <input type="text"
                            x-model="searchQuery"
                            @focus="showDropdown = true"
                            @input="showDropdown = true"
                            placeholder="Ketik nama Anda..."
                            class="w-full px-6 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:ring-0 focus:border-indigo-500 outline-none transition-all font-bold text-gray-800">

                        {{-- Dropdown Result --}}
                        <div x-show="showDropdown && filteredGurus.length > 0"
                            @click.away="showDropdown = false"
                            x-transition
                            class="absolute z-50 w-full mt-2 bg-white border-2 border-gray-100 rounded-2xl max-h-60 overflow-y-auto overflow-hidden">

                            <template x-for="guru in filteredGurus" :key="guru.guru_id">
                                <button type="button"
                                    @click="selectGuru(guru)"
                                    class="w-full text-left px-6 py-4 hover:bg-indigo-50 border-b border-gray-50 last:border-0 transition-all">
                                    <p class="font-bold text-gray-800" x-text="guru.name"></p>
                                    <p class="text-xs text-gray-400 uppercase tracking-widest">Guru / Staff</p>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- ACTION BUTTONS --}}
                {{-- Saya hapus x-show agar selalu tampil --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- TOMBOL MASUK --}}
                    <button type="submit" name="status" value="hadir"
                        {{-- Kita tambahkan logika disabled jika belum pilih guru agar form tidak error --}}
                        :disabled="!selectedGuru"
                        :class="!selectedGuru ? 'opacity-50 cursor-not-allowed' : ''"
                        class="group rounded-[32px] p-8 bg-white border-2 border-gray-100 hover:border-indigo-500 transition-all text-left">

                        <div class="flex items-center justify-between mb-4">
                            <div class="p-4 bg-indigo-50 rounded-2xl text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-black text-gray-800">Absen Masuk</h3>
                        {{-- Teks dinamis: jika sudah pilih muncul nama, jika belum muncul instruksi --}}
                        <p class="text-sm text-gray-400 font-medium">
                            <template x-if="selectedGuru">
                                <span>Mulai jam kerja sebagai <span class="text-indigo-600" x-text="selectedGuru.name"></span></span>
                            </template>
                            <template x-if="!selectedGuru">
                                <span>Pilih nama Anda terlebih dahulu</span>
                            </template>
                        </p>
                    </button>

                    {{-- TOMBOL PULANG --}}
                    <button type="submit" name="status" value="pulang"
                        :disabled="!selectedGuru"
                        :class="!selectedGuru ? 'opacity-50 cursor-not-allowed' : ''"
                        class="group rounded-[32px] p-8 bg-white border-2 border-gray-100 hover:border-orange-500 transition-all text-left">

                        <div class="flex items-center justify-between mb-4">
                            <div class="p-4 bg-orange-50 rounded-2xl text-orange-600 group-hover:bg-orange-600 group-hover:text-white transition-all">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-black text-gray-800">Absen Pulang</h3>
                        <p class="text-sm text-gray-400 font-medium">Selesaikan jam kerja hari ini</p>
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
{{-- Tambahkan script SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- LOGIC AMBIL LOKASI OTOMATIS ---
        function getKioskLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    document.getElementById('lat_input').value = position.coords.latitude;
                    document.getElementById('lng_input').value = position.coords.longitude;
                    console.log("Location fixed: " + position.coords.latitude + ", " + position.coords.longitude);
                }, function(error) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'GPS Tidak Aktif',
                        text: 'Aktifkan lokasi pada browser/perangkat Anda agar bisa melakukan absensi.',
                        confirmButtonColor: '#6366f1',
                    });
                }, {
                    enableHighAccuracy: true
                });
            }
        }

        // Panggil fungsi saat halaman dimuat
        getKioskLocation();

        // --- MODAL SUKSES ---
        @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            customClass: {
                popup: 'rounded-[32px]'
            }
        });
        @endif

        // --- MODAL GAGAL (Termasuk Gagal Geofencing) ---
        @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: "{{ session('error') }}",
            showConfirmButton: true,
            confirmButtonColor: '#6366f1',
            confirmButtonText: 'Tutup',
            customClass: {
                popup: 'rounded-[32px]',
                confirmButton: 'rounded-xl px-6 py-3 font-bold'
            }
        });
        @endif
    });
</script>
@endpush