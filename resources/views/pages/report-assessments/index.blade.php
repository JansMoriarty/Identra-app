@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Dashboard Analisis Penilaian" />

<div class="mt-4 flex flex-col gap-4 md:mt-6 md:gap-6 2xl:mt-7.5 2xl:gap-7.5 max-w-full overflow-hidden">

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4 md:gap-6">
        <div class="rounded-[10px] border border-stroke bg-white p-6 shadow-sm dark:border-strokedark dark:bg-boxdark">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-[#F0F4FD] text-[#3C50E0] mb-5">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-[13px] font-medium text-gray-500">Total Guru/Staff</span>
                <div class="flex items-center gap-2 mt-1">
                    <h4 class="text-2xl font-bold text-black dark:text-white">{{ $stats['total_guru'] }}</h4>
                    <span class="text-[11px] font-medium text-[#3C50E0] bg-[#F0F4FD] px-2 py-0.5 rounded-full">Staff</span>
                </div>
            </div>
        </div>
        <div class="rounded-[10px] border border-stroke bg-white p-6 shadow-sm dark:border-strokedark dark:bg-boxdark">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-[#EBFDFA] text-[#10B981] mb-5">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-[13px] font-medium text-gray-500">Rata-rata Skor</span>
                <div class="flex items-center gap-2 mt-1">
                    <h4 class="text-2xl font-bold text-black dark:text-white">{{ number_format($stats['rata_rata_sekolah'], 1) }}</h4>
                    <span class="text-[11px] font-medium text-[#10B981] bg-[#EBFDFA] px-2 py-0.5 rounded-full">/ 5.0</span>
                </div>
            </div>
        </div>
        <div class="rounded-[10px] border border-stroke bg-white p-6 shadow-sm dark:border-strokedark dark:bg-boxdark">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-[#F3E8FF] text-[#8B5CF6] mb-5">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-[13px] font-medium text-gray-500">Total Penilaian</span>
                <div class="flex items-center gap-2 mt-1">
                    <h4 class="text-2xl font-bold text-black dark:text-white">{{ $stats['total_penilaian'] }}</h4>
                    <span class="text-[11px] font-medium text-[#8B5CF6] bg-[#F3E8FF] px-2 py-0.5 rounded-full">Sesi</span>
                </div>
            </div>
        </div>
        <div class="rounded-[10px] border border-stroke bg-white p-6 shadow-sm dark:border-strokedark dark:bg-boxdark">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-[#FFF4ED] text-[#F97316] mb-5">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-[13px] font-medium text-gray-500">Status Sistem</span>
                <div class="flex items-center gap-2 mt-1">
                    <h4 class="text-2xl font-bold text-black dark:text-white">Aktif</h4>
                    <span class="text-[11px] font-medium text-[#F97316] bg-[#FFF4ED] px-2 py-0.5 rounded-full">Live</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart Section: FIX LEBAR DISINI --}}
    <div class="rounded-[10px] border border-stroke bg-white px-5 pt-7 pb-5 shadow-sm dark:border-strokedark dark:bg-boxdark sm:px-7.5 w-full max-w-full overflow-hidden">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-6">
            <div>
                <h3 class="text-xl font-bold text-black dark:text-white">Statistics</h3>
                <p class="text-sm font-medium text-gray-500 mt-1">Target you've set for each month</p>
            </div>
        </div>

        <div class="h-[350px] w-full relative">
            <canvas id="mainChart" class="max-w-full"></canvas>
        </div>
    </div>

    {{-- Table Section --}}
    <div class="rounded-[10px] border border-stroke bg-white p-4 md:p-6 shadow-sm dark:border-strokedark dark:bg-boxdark w-full overflow-hidden">
        <div class="mb-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h4 class="text-lg font-bold text-black dark:text-white">Manajemen Laporan Guru</h4>
                <p class="text-sm text-gray-500 mt-1">Daftar laporan aktif {{ date('d F Y') }}</p>
            </div>
            <form action="{{ route('report-assessments.index') }}" method="GET" class="relative w-full md:w-auto">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </span>
                <input type="text" name="search" placeholder="Cari..." value="{{ request('search') }}"
                    class="w-full md:w-[250px] rounded-lg border border-stroke bg-gray-50 py-2 pl-10 pr-4 text-sm font-medium outline-none focus:border-[#3C50E0] dark:border-form-strokedark dark:bg-form-input">
            </form>
        </div>

        <div class="w-full overflow-x-auto">
            <table class="w-full table-auto border-collapse text-left min-w-[600px]">
                <thead>
                    <tr class="border-b border-stroke dark:border-strokedark bg-gray-50/50 dark:bg-meta-4/50">
                        <th class="py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Profil Guru</th>
                        <th class="py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Identitas Resmi</th>
                        <th class="py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Status</th>
                        <th class="py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stroke dark:divide-strokedark">
                    @forelse($teachers as $teacher)
                    <tr class="hover:bg-gray-50 dark:hover:bg-meta-4/20 transition-colors">
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F0F4FD] text-[#3C50E0] font-bold text-sm">
                                    {{ substr($teacher->name, 0, 1) }}
                                </div>
                                <div class="max-w-[150px] md:max-w-[200px]">
                                    <h5 class="font-medium text-black dark:text-white truncate">{{ $teacher->name }}</h5>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-black dark:text-white">{{ $teacher->nip ?? '-' }}</span>
                                <span class="text-xs text-gray-500 truncate max-w-[150px]">{{ $teacher->email }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <span class="inline-flex rounded-full bg-[#EBFDFA] px-3 py-1 text-xs font-medium text-[#10B981]">Verified</span>
                        </td>
                        <td class="py-4 px-4 text-right">
                            <a href="{{ route('report-assessments.show', $teacher->id) }}" 
                               class="inline-flex items-center justify-center rounded-md border border-[#3C50E0] text-[#3C50E0] py-1.5 px-4 text-xs font-medium hover:bg-[#3C50E0] hover:text-white transition-all whitespace-nowrap">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-12 text-gray-400 text-sm">Guru tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 pt-4 border-t border-stroke dark:border-strokedark">
            {{ $teachers->links() }}
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('mainChart').getContext('2d');
        
        // Buat Gradient Biru (Opsional, agar visual lebih premium)
        const gradient = ctx.createLinearGradient(0, 0, 0, 350);
        gradient.addColorStop(0, '#3C50E0');
        gradient.addColorStop(1, 'rgba(60, 80, 224, 0.4)');

        new Chart(ctx, {
            type: 'bar', // Diubah dari 'line' ke 'bar'
            data: {
                labels: @js($chartData['labels']),
                datasets: [{
                    label: 'Skor Rata-rata',
                    data: @js($chartData['data']),
                    backgroundColor: gradient,
                    hoverBackgroundColor: '#3C50E0',
                    borderRadius: 6, // Membuat ujung bar agak bulat
                    barThickness: 40, // Mengatur ketebalan batang bar
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1C2434',
                        titleFont: { size: 14 },
                        bodyFont: { size: 13 },
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return ' Skor: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        max: 5,
                        ticks: {
                            stepSize: 1,
                            color: '#64748B',
                            font: { weight: '500' }
                        },
                        grid: {
                            color: '#F1F5F9',
                            drawBorder: false
                        }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: {
                            color: '#64748B',
                            font: { weight: '500' }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection