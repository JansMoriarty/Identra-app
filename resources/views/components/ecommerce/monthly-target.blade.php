@php
    use Carbon\Carbon;
    $currentDate = Carbon::now()->locale('id');
    $daysInMonth = $currentDate->daysInMonth;

    // Logika menghitung kotak kosong: ISO (Senin=1, Minggu=7). 
    // Jika start Senin, maka (dayOfWeekIso - 1)
    $startEmpty = $currentDate->copy()->startOfMonth()->dayOfWeekIso - 1;
@endphp

<div class="group relative overflow-hidden rounded-3xl border border-gray-200 bg-white transition-all duration-300 dark:border-gray-800 dark:bg-gray-900">
    <div class="relative px-6 py-7">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                    Halo, {{ $greeting }}!
                </h3>
                <p class="text-xs font-semibold text-blue-600 uppercase tracking-widest dark:text-blue-400">
                    {{ $currentDate->isoFormat('MMMM YYYY') }}
                </p>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>

        <div class="mb-6">
            <div class="grid grid-cols-7 mb-2 text-center">
                @foreach(['S', 'S', 'R', 'K', 'J', 'S', 'M'] as $day)
                    <span class="text-[10px] font-black text-gray-400 uppercase">{{ $day }}</span>
                @endforeach
            </div>

            <div class="grid grid-cols-7 gap-1 text-center">
                {{-- Render Kotak Kosong --}}
                @for($i = 0; $i < $startEmpty; $i++)
                    <div class="h-8"></div>
                @endfor

                {{-- Render Tanggal --}}
                @for($date = 1; $date <= $daysInMonth; $date++)
                    @php
                        $isToday = ($date == now()->day && $currentDate->isCurrentMonth());
                        // Pastikan $holidayDates dikirim dari Controller, jika tidak default array kosong
                        $isHoliday = in_array($date, $holidayDates ?? []);
                    @endphp

                    <div class="relative flex h-8 items-center justify-center rounded-lg text-xs font-bold transition-all
                        {{ $isToday ? 'bg-blue-600 text-white shadow-md shadow-blue-500/40 scale-110 z-10' : '' }}
                        {{ $isHoliday && !$isToday ? 'text-red-500 bg-red-50 dark:bg-red-500/10' : '' }}
                        {{ !$isToday && !$isHoliday ? 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5' : '' }}">
                        {{ $date }}
                        @if($isHoliday)
                            <span class="absolute bottom-1 h-1 w-1 rounded-full bg-red-500"></span>
                        @endif
                    </div>
                @endfor
            </div>
        </div>

        <div class="space-y-3 rounded-2xl bg-gray-50 p-4 dark:bg-white/[0.03] border border-gray-100 dark:border-gray-800">
            <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Libur Nasional & Cuti</h4>

            @forelse($holidayList ?? [] as $holiday)
                <div class="flex items-start gap-3">
                    <div class="mt-1.5 h-1.5 w-1.5 rounded-full bg-red-500 shrink-0"></div>
                    <p class="text-[11px] font-medium text-gray-600 dark:text-gray-400 leading-tight">
                        <span class="font-bold text-gray-800 dark:text-gray-200">
                            {{ \Carbon\Carbon::parse($holiday['holiday_date'])->isoFormat('D MMMM') }}:
                        </span>
                        {{ $holiday['holiday_name'] }}
                    </p>
                </div>
            @empty
                <p class="text-[11px] text-gray-400 italic text-center">Tidak ada libur nasional bulan ini</p>
            @endforelse
        </div>
    </div>
</div>