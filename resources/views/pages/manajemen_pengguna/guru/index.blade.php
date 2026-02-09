@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Manajemen Pengguna" />
    <div class="space-y-6">
        <x-common.component-card title="Daftar Guru">
            <x-tables.basic-tables.tables-guru />
        </x-common.component-card>
    </div>
@endsection
