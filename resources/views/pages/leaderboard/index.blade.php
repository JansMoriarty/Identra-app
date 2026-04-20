@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Pengajuan Izin" />
    
    <div class="space-y-6">
        <x-common.component-card title="Daftar Izin Guru">
            <x-tables.basic-tables.tables-leaderboard :leaderboard="$leaderboard" />
        </x-common.component-card>
    </div>
@endsection