<div class="w-full min-w-0 overflow-x-auto">
    <table class="w-full table-auto border-collapse text-left">
        <thead>
            <tr class="border-b border-stroke dark:border-strokedark bg-gray-50/50 dark:bg-meta-4/50">
                <th class="py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/3">
                    Profil Guru
                </th>
                <th class="py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/3">
                    Identitas Resmi
                </th>
                <th class="py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">
                    Status
                </th>
                <th class="py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">
                    Aksi
                </th>
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
                        <div class="overflow-hidden">
                            <h5 class="font-medium text-black dark:text-white truncate">
                                {{ $teacher->name }}
                            </h5>
                        </div>
                    </div>
                </td>

                <td class="py-4 px-4">
                    <div class="flex flex-col">
                        <span class="text-sm font-medium text-black dark:text-white">
                            {{ $teacher->nip ?? '-' }}
                        </span>
                        <span class="text-xs text-gray-500 truncate max-w-[180px]">
                            {{ $teacher->email }}
                        </span>
                    </div>
                </td>

                <td class="py-4 px-4 text-center">
                    <span class="inline-flex rounded-full bg-[#EBFDFA] px-3 py-1 text-xs font-medium text-[#10B981]">
                        Verified
                    </span>
                </td>

                <td class="py-4 px-4 text-right">
                    <a href="{{ route('report-assessments.show', $teacher->id) }}" 
                       class="inline-flex items-center justify-center rounded-md border border-[#3C50E0] text-[#3C50E0] py-1.5 px-4 text-xs font-medium hover:bg-[#3C50E0] hover:text-white transition-all">
                        Detail
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center py-12">
                    <div class="flex flex-col items-center justify-center text-gray-400">
                        <svg class="w-12 h-12 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="font-medium text-sm">Guru tidak ditemukan.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>