@extends('layouts.kiosk')

@section('content')
<div class="min-h-screen bg-[#ffffff] text-gray-900 px-6 py-8 lg:px-20 lg:py-6">

    {{-- BACK BUTTON & HEADER --}}
    <div class="flex items-center gap-6 mb-10">
        <a href="#" class="p-4 bg-gray-50 hover:bg-gray-100 rounded-2xl transition-all border border-gray-100 text-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-black tracking-tight text-gray-800">Absensi Manual</h1>
            <p class="text-sm text-gray-400 font-medium">Pilih nama Anda untuk melakukan absensi</p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('attendances.store') }}" method="POST" id="manualAuthForm">
            @csrf
            <div class="grid grid-cols-1 gap-8">

                {{-- SELECT GURU CARD --}}
                <div class="rounded-[32px] p-8 bg-white border-2 border-gray-50 shadow-sm">
                    <label class="text-xs font-black mb-6 text-gray-400 uppercase tracking-[0.2em] block">
                        Pilih Personel
                    </label>

                    <div class="relative">
                        <select name="guru_id" required ...>
                            <option value="" disabled selected>Cari Nama Anda...</option>
                            @foreach($gurus as $guru)
                            {{-- Gunakan $guru->id karena itu yang akan disimpan di tabel attendance --}}
                            {{-- Gunakan $guru->name karena itu nama kolom di tabel users kamu --}}
                            <option value="{{ $guru->guru_id }}">{{ $guru->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- TOMBOL MASUK --}}
                    <button type="submit" name="status" value="hadir"
                        class="group relative overflow-hidden rounded-[32px] p-8 bg-white border-2 border-gray-50 shadow-sm hover:border-indigo-500 transition-all text-left">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-4 bg-indigo-50 rounded-2xl text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </div>
                            <span class="text-indigo-100 group-hover:text-indigo-200">
                                <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24 opacity-10">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z" />
                                </svg>
                            </span>
                        </div>
                        <h3 class="text-xl font-black text-gray-800">Absen Masuk</h3>
                        <p class="text-sm text-gray-400 font-medium">Mulai jam kerja hari ini</p>
                    </button>

                    {{-- TOMBOL PULANG --}}
                    <button type="submit" name="status" value="pulang"
                        class="group relative overflow-hidden rounded-[32px] p-8 bg-white border-2 border-gray-50 shadow-sm hover:border-orange-500 transition-all text-left">
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