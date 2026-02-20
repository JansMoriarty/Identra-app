@extends('layouts.app')

@section('content')
    {{-- Update breadcrumb agar relevan dengan laporan --}}
    <x-common.page-breadcrumb pageTitle="Rekapitulasi Absensi" />

    <div class="space-y-6">
        {{-- Card Title diubah agar mencerminkan ringkasan performa --}}
        <x-common.component-card title="Ringkasan Kehadiran Seluruh Guru">
            
            {{-- Tambahkan slot atau pembungkus jika diperlukan untuk menjaga stroke layout --}}
            <div class="mt-2">
                <x-tables.basic-tables.tables-report-guru :rekapGuru="$rekapGuru"/>
            </div>
            
        </x-common.component-card>
    </div>
@endsection