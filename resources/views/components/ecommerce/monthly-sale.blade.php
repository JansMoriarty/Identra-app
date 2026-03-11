<div x-data="{ 
    leaves: @js($leaves),
    getAvatarClasses(name) {
        const colors = [
            { bg: 'bg-blue-100 dark:bg-blue-900/30', text: 'text-blue-600 dark:text-blue-400' },
            { bg: 'bg-emerald-100 dark:bg-emerald-900/30', text: 'text-emerald-600 dark:text-emerald-400' },
            { bg: 'bg-rose-100 dark:bg-rose-900/30', text: 'text-rose-600 dark:text-rose-400' },
        ];
        const index = (name ? name.charCodeAt(0) : 0) % colors.length;
        return colors[index];
    }
}" class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
    
    <div class="flex items-center justify-between mb-5">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Izin Guru Hari Ini</h3>
            <p class="text-xs text-gray-500 mt-1">Daftar pengajuan aktif tanggal {{ now()->translatedFormat('d F Y') }}</p>
        </div>
        <a href="{{ route('leave.index') }}" class="text-xs font-medium text-blue-600 hover:underline">Lihat Semua</a>
    </div>

    <div class="space-y-4">
        <template x-for="item in leaves" :key="item.id">
            <div class="flex items-center justify-between p-3 rounded-xl border border-gray-50 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/20">
                <div class="flex items-center gap-3">
                    <div :class="getAvatarClasses(item.guru?.name).bg + ' ' + getAvatarClasses(item.guru?.name).text"
                         class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs"
                         x-text="(item.guru?.name || 'G').charAt(0)">
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-800 dark:text-white/90" x-text="item.guru?.name"></p>
                        <p class="text-[10px] text-gray-500 uppercase font-medium" x-text="item.jenis"></p>
                    </div>
                </div>
                <div class="text-right">
                    <span :class="item.status === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-emerald-50 text-emerald-700 border-emerald-100'" 
                          class="px-2 py-0.5 text-[9px] font-bold uppercase rounded-md border" 
                          x-text="item.status"></span>
                </div>
            </div>
        </template>

        <template x-if="leaves.length === 0">
            <div class="flex flex-col items-center justify-center py-8 text-center">
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-full mb-2">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-sm text-gray-500">Tidak ada guru yang izin hari ini.</p>
            </div>
        </template>
    </div>
</div>