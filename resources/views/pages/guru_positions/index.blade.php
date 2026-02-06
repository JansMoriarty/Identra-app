@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Manajemen Posisi" />
    <div class="space-y-6">
        <x-common.component-card title="Daftar Jabatan">
            <x-tables.basic-tables.tables-guru-positions :assignments="$assignments"/>
        </x-common.component-card>
    </div>
@endsection
