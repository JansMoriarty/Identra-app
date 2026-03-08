@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Kehadiran Guru" />
<div class="space-y-6">
    <x-common.component-card title="Absensi Guru">
        <x-tables.basic-tables.tables-schedules />
    </x-common.component-card>
</div>
@endsection