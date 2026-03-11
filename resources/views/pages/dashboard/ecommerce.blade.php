@extends('layouts.app')

@section('content')
<div class="grid grid-cols-12 gap-4 md:gap-6">
  <div class="col-span-12 space-y-6 xl:col-span-7">
    <x-ecommerce.ecommerce-metrics
      :totalGuru="$totalGuru"
      :totalGuruHadir="$totalGuruHadir"
      :persentaseHadir="$persentaseHadir"
      :totalRuangan="$totalRuangan"
      :totalJadwalHariIni="$totalJadwalHariIni" />

    @php
    $todayLeavesData = \App\Models\LeaveRequest::whereDate('tanggal_mulai', '<=', now()->toDateString())
      ->whereDate('tanggal_selesai', '>=', now()->toDateString())
      ->with('guru')
      ->get();
      @endphp

      <x-ecommerce.monthly-sale :leaves="$todayLeaves" />
  </div>
  <div class="col-span-12 xl:col-span-5">
    <x-ecommerce.monthly-target
      :greeting="$greeting"
      :holidayDates="$holidayDates"
      :holidayList="$holidayList" />
  </div>

  <div class="col-span-12">
    <x-ecommerce.statistics-chart />
  </div>
</div>
@endsection