@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="From Elements" />
    <div class="space-y-6">
        <x-common.component-card title="Basic Table 3">
            <x-tables.basic-tables.basic-tables-three />
        </x-common.component-card>
    </div>
@endsection
