@extends('layouts.app')

@section('content')
@php
    // Daftar kombinasi warna yang estetik (Tailwind classes)
    $colors = [
        ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'border' => 'border-blue-200'],
        ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200'],
        ['bg' => 'bg-violet-100', 'text' => 'text-violet-600', 'border' => 'border-violet-200'],
        ['bg' => 'bg-amber-100', 'text' => 'text-amber-600', 'border' => 'border-amber-200'],
        ['bg' => 'bg-rose-100', 'text' => 'text-rose-600', 'border' => 'border-rose-200'],
        ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-600', 'border' => 'border-cyan-200'],
    ];
    
    // Pilih warna berdasarkan ID atau Nama agar konsisten bagi guru tersebut
    $colorIndex = $teacher->id % count($colors);
    $selectedColor = $colors[$colorIndex];
@endphp

<x-common.page-breadcrumb pageTitle="Rapor Kompetensi Guru" />

<div class="mt-4 grid grid-cols-12 gap-4 md:mt-6 md:gap-6 2xl:mt-7.5 2xl:gap-7.5">

    {{-- 1. Card Profil & Predikat --}}
    <div class="col-span-12 xl:col-span-4 flex flex-col gap-4">
        <div class="rounded-sm border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="flex flex-col items-center">
                {{-- Avatar dengan warna dinamis --}}
                <div class="h-24 w-24 rounded-full {{ $selectedColor['bg'] }} flex items-center justify-center {{ $selectedColor['text'] }} text-3xl font-bold mb-4 border-4 {{ $selectedColor['border'] }}">
                    {{ strtoupper(substr($teacher->name, 0, 1)) }}
                </div>
                
                <h3 class="text-2xl font-bold text-black dark:text-white">{{ $teacher->name }}</h3>
                <p class="text-sm font-medium text-gray-500">NIP: {{ $teacher->nip ?? '-' }}</p>

                <div class="mt-6 w-full border-t border-stroke pt-5 dark:border-strokedark">
                    <div class="flex flex-col items-center">
                        <span class="text-sm text-gray-400 uppercase tracking-widest font-bold">Skor Keseluruhan</span>
                        <h1 class="text-6xl font-black text-primary mt-2">{{ number_format($averageScore, 1) }}</h1>
                        <div class="mt-3 inline-flex items-center rounded-full bg-success/10 px-4 py-1.5 text-sm font-bold text-success">
                            {{ $predicate }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- ... (bagian info kontak tetap sama) ... --}}
        <div class="rounded-sm border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
            <h4 class="mb-4 text-sm font-bold uppercase text-black dark:text-white">Informasi Kontak</h4>
            <div class="flex flex-col gap-3">
                <div class="flex items-center gap-3">
                    <span class="text-gray-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></span>
                    <span class="text-sm font-medium">{{ $teacher->email }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-gray-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></span>
                    <span class="text-sm font-medium text-gray-400 font-mono tracking-tighter italic">Bergabung: {{ $teacher->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Radar Chart --}}
    <div class="col-span-12 xl:col-span-8">
        {{-- ... (tetap sama seperti sebelumnya) ... --}}
        <div class="rounded-sm border border-stroke bg-white p-7.5 shadow-default dark:border-strokedark dark:bg-boxdark h-full">
            <div class="mb-4 justify-between gap-4 sm:flex">
                <div>
                    <h4 class="text-xl font-bold text-black dark:text-white">Analisis Kompetensi</h4>
                    <p class="text-sm font-medium">Visualisasi perbandingan tiap kategori penilaian</p>
                </div>
            </div>
            <div class="mb-2">
                <div id="chartRadar" class="mx-auto flex justify-center h-[350px]">
                    <canvas id="radarChart"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 border-t border-stroke pt-6 dark:border-strokedark">
                @foreach($categories as $index => $cat)
                <div class="text-center">
                    <p class="text-xs font-bold text-gray-400 uppercase truncate">{{ $cat->name }}</p>
                    <p class="text-lg font-bold text-black dark:text-white">{{ number_format($radarScores[$index] ?? 0, 1) }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 3. History Penilaian --}}
    <div class="col-span-12">
        {{-- ... (tetap sama, pastikan pakai $loop->iteration) ... --}}
        <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="border-b border-stroke py-4 px-6.5 dark:border-strokedark">
                <h3 class="font-bold text-black dark:text-white">Riwayat Penilaian & Feedback</h3>
            </div>
            <div class="p-6">
                <div class="flex flex-col gap-7">
                    @forelse($feedbacks as $item)
                    <div class="relative flex gap-4 before:absolute before:left-[19px] before:top-8 before:h-full before:w-[2px] before:bg-stroke last:before:hidden dark:before:bg-strokedark">
                        <div class="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary text-white font-bold shadow-10">
                            {{ $loop->iteration }}
                        </div>
                        <div class="flex w-full flex-col">
                            <div class="flex items-center justify-between">
                                <h5 class="text-lg font-bold text-black dark:text-white">{{ $item->period->name ?? 'Periode N/A' }}</h5>
                                <span class="text-sm font-medium text-gray-400">{{ $item->created_at->format('d M, Y') }}</span>
                            </div>
                            <div class="mt-2 rounded-lg bg-gray-2 p-4 dark:bg-meta-4">
                                <p class="text-sm font-medium text-black dark:text-white leading-relaxed">
                                    "{{ $item->general_feedback ?? 'Tidak ada feedback khusus.' }}"
                                </p>
                                <div class="mt-3 flex items-center justify-between">
                                    <span class="text-xs font-bold text-primary uppercase">Skor: {{ number_format($item->final_score, 2) }}</span>
                                    <span class="text-xs text-gray-400 italic font-medium">Oleh: {{ $item->evaluator->name ?? 'Sistem' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-center py-10 text-gray-400 font-medium">Belum ada riwayat penilaian.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('radarChart').getContext('2d');
        new Chart(ctx, {
            type: 'radar',
            data: {
                labels: @js($categories->pluck('name')),
                datasets: [{
                    label: 'Skor Kompetensi',
                    data: @js($radarScores),
                    fill: true,
                    backgroundColor: 'rgba(60, 80, 224, 0.2)',
                    borderColor: '#3C50E0',
                    pointBackgroundColor: '#3C50E0',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#3C50E0',
                    borderWidth: 3
                }]
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: { display: true, color: '#E2E8F0' },
                        suggestedMin: 0,
                        suggestedMax: 5,
                        ticks: { stepSize: 1, display: false },
                        grid: { color: '#E2E8F0' },
                        pointLabels: {
                            font: { size: 12, weight: 'bold' },
                            color: '#64748B'
                        }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>
@endsection