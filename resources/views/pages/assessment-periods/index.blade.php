@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Manajemen Periode Penilaian" />
    
    <div class="space-y-6">
        <x-common.component-card title="Daftar Periode">
            {{-- Kita panggil komponen tabel period --}}
            <x-tables.basic-tables.tables-assessment-periods :periods="$periods"/>
        </x-common.component-card>
    </div>
@endsection