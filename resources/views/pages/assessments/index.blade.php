@extends('layouts.app')

@section('content')
<div x-data="{
    showRateModal: false,
    teachers: @js($teachers),
    categories: @js($categories),
    ratedIds: @js($ratedTeacherIds),
    selectedGuru: { id: null, name: '', avatar: {} },
    activePeriod: @js($activePeriod),
    searchQuery: '',
    
    // Pagination State
    currentPage: 1,
    itemsPerPage: 10,

    get progress() {
        return Math.round((this.ratedIds.length / this.teachers.length) * 100) || 0;
    },

    get filteredTeachers() {
        let filtered = this.teachers;
        if (this.searchQuery.trim() !== '') {
            filtered = this.teachers.filter(guru => {
                return guru.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                       (guru.email && guru.email.toLowerCase().includes(this.searchQuery.toLowerCase()));
            });
        }
        // Reset ke halaman 1 saat mencari
        return filtered;
    },

    get paginatedTeachers() {
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        return this.filteredTeachers.slice(start, end);
    },

    get totalPages() {
        return Math.ceil(this.filteredTeachers.length / this.itemsPerPage) || 1;
    },

    get pages() {
    let pages = [];
    let startPage, endPage;
    let total = this.totalPages;
    let current = this.currentPage;
    let delta = 2; // Jumlah angka di kiri/kanan halaman aktif

    if (total <= 7) {
        // Jika total halaman sedikit, tampilkan semua
        for (let i = 1; i <= total; i++) pages.push(i);
    } else {
        // Logic sliding window dengan dots
        if (current <= 4) {
            pages = [1, 2, 3, 4, 5, '...', total];
        } else if (current >= total - 3) {
            pages = [1, '...', total - 4, total - 3, total - 2, total - 1, total];
        } else {
            pages = [1, '...', current - 1, current, current + 1, '...', total];
        }
    }
    return pages;
},

    // Fungsi untuk generate inisial dan warna random yang konsisten berdasarkan nama
    getAvatar(name) {
        if (!name) return { initials: 'U', bg: 'bg-gray-100', text: 'text-gray-600' };
        
        const parts = name.trim().split(' ').filter(Boolean);
        let initials = parts[0] ? parts[0][0] : 'U';
        if (parts.length > 1) initials += parts[1][0];
        initials = initials.toUpperCase();

        const colors = [
            { bg: 'bg-blue-100 dark:bg-blue-900/30', text: 'text-blue-600 dark:text-blue-400' },
            { bg: 'bg-green-100 dark:bg-green-900/30', text: 'text-green-600 dark:text-green-400' },
            { bg: 'bg-amber-100 dark:bg-amber-900/30', text: 'text-amber-600 dark:text-amber-400' },
            { bg: 'bg-purple-100 dark:bg-purple-900/30', text: 'text-purple-600 dark:text-purple-400' },
            { bg: 'bg-rose-100 dark:bg-rose-900/30', text: 'text-rose-600 dark:text-rose-400' },
            { bg: 'bg-indigo-100 dark:bg-indigo-900/30', text: 'text-indigo-600 dark:text-indigo-400' },
            { bg: 'bg-teal-100 dark:bg-teal-900/30', text: 'text-teal-600 dark:text-teal-400' }
        ];

        let hash = 0;
        for (let i = 0; i < name.length; i++) {
            hash = name.charCodeAt(i) + ((hash << 5) - hash);
        }
        const index = Math.abs(hash) % colors.length;

        return { initials, ...colors[index] };
    },

    openRating(guru) {
        if (this.ratedIds.includes(guru.id)) {
            Swal.fire({
                icon: 'info',
                title: 'Sudah Dinilai',
                text: 'Guru ini sudah memiliki nilai untuk periode aktif.',
                confirmButtonColor: '#3C50E0' 
            });
            return;
        }

        this.selectedGuru = { 
            id: guru.id, 
            name: guru.name, 
            avatar: this.getAvatar(guru.name)
        };
        this.showRateModal = true;
    }
}">
    <x-common.page-breadcrumb pageTitle="Penilaian Guru: {{ $activePeriod->name ?? 'Periode Tidak Aktif' }}" />

    <div class="mb-8 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-32 h-32 rounded-full bg-[#3C50E0]/5 blur-2xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 relative z-10">
            <div class="flex-1 w-full">
                <div class="flex items-center gap-3 mb-3">
                    <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-[#3C50E0]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white leading-none">Progress Penilaian</h3>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="w-full bg-gray-100 rounded-full h-3 dark:bg-gray-800 flex-1 relative">
                        <div class="bg-[#3C50E0] h-3 rounded-full transition-all duration-700 ease-out relative" :style="'width: ' + progress + '%'">
                            <div class="absolute right-0 top-1/2 -translate-y-1/2 w-4 h-4 bg-white rounded-full border-[3px] border-[#3C50E0] shadow-[0_0_10px_rgba(60,80,224,0.6)]" x-show="progress > 0"></div>
                        </div>
                    </div>
                    <span class="text-2xl font-black text-[#3C50E0] min-w-[3.5rem] text-right" x-text="progress + '%'"></span>
                </div>

                <p class="text-sm text-gray-500 mt-2">
                    <template x-if="progress === 100">
                        <span class="text-green-600 font-medium flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Luar biasa! Semua guru telah dinilai.
                        </span>
                    </template>
                    <template x-if="progress < 100">
                        <span>Anda telah menilai <strong class="text-gray-800 dark:text-gray-200" x-text="ratedIds.length"></strong> dari <strong class="text-gray-800 dark:text-gray-200" x-text="teachers.length"></strong> guru.</span>
                    </template>
                </p>
            </div>

            <div class="w-full md:w-80">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cari Guru</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input type="text"
                        x-model="searchQuery"
                        @input="currentPage = 1"
                        placeholder="Ketik nama atau email..."
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 py-3 pl-11 pr-4 text-sm text-gray-900 focus:border-[#3C50E0] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#3C50E0] dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-900 transition-colors">

                    <button x-show="searchQuery.length > 0" @click="searchQuery = ''; currentPage = 1" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="filteredTeachers.length === 0" class="text-center py-16 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800" x-cloak>
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.24 12.24a6 6 0 00-8.49-8.49L5 10.5V19h8.5z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 8L2 22"></path>
            </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Guru Tidak Ditemukan</h3>
        <p class="text-gray-500 mt-1">Coba gunakan kata kunci pencarian yang lain.</p>
        <button @click="searchQuery = ''; currentPage = 1" class="mt-4 px-4 py-2 text-sm font-medium text-[#3C50E0] bg-blue-50 dark:bg-blue-900/20 rounded-md hover:bg-blue-100 transition-colors">Reset Pencarian</button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" x-show="filteredTeachers.length > 0">
        <template x-for="guru in paginatedTeachers" :key="guru.id">
            <div @click="openRating(guru)"
                :class="ratedIds.includes(guru.id) ? 'opacity-70 bg-gray-50 dark:bg-gray-800/50' : 'hover:border-[#3C50E0] hover:shadow-md cursor-pointer'"
                class="group relative bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6 transition-all">

                <div class="absolute top-4 right-4">
                    <template x-if="ratedIds.includes(guru.id)">
                        <span class="inline-flex rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-green-600 ring-1 ring-inset ring-green-600/20">Selesai</span>
                    </template>
                    <template x-if="!ratedIds.includes(guru.id)">
                        <span class="inline-flex rounded-full bg-yellow-50 px-2.5 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">Belum</span>
                    </template>
                </div>

                <div class="flex flex-col items-center text-center mt-2">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center text-xl font-bold mb-4 group-hover:scale-105 transition-transform"
                        :class="getAvatar(guru.name).bg + ' ' + getAvatar(guru.name).text"
                        x-text="getAvatar(guru.name).initials">
                    </div>

                    <h4 class="font-semibold text-gray-900 dark:text-white text-lg" x-text="guru.name"></h4>
                    <p class="text-gray-500 text-sm mb-6" x-text="guru.email"></p>

                    <button class="w-full py-2.5 rounded-md text-sm font-medium transition-colors"
                        :class="ratedIds.includes(guru.id) ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-[#3C50E0]/10 text-[#3C50E0] group-hover:bg-[#3C50E0] group-hover:text-white dark:bg-gray-800 dark:text-gray-300'">
                        <span x-text="ratedIds.includes(guru.id) ? 'Sudah Dinilai' : 'Beri Nilai'"></span>
                    </button>
                </div>
            </div>
        </template>
    </div>

    <div x-show="totalPages > 1" class="mt-8 flex items-center justify-between border-t border-gray-200 dark:border-gray-800 pt-6">
        <div class="flex flex-1 justify-between sm:hidden">
            <button @click="currentPage > 1 ? currentPage-- : null"
                :disabled="currentPage === 1"
                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50">
                Previous
            </button>
            <button @click="currentPage < totalPages ? currentPage++ : null"
                :disabled="currentPage === totalPages"
                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50">
                Next
            </button>
        </div>
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700 dark:text-gray-400">
                    Menampilkan <span class="font-medium" x-text="((currentPage - 1) * itemsPerPage) + 1"></span> sampai <span class="font-medium" x-text="Math.min(currentPage * itemsPerPage, filteredTeachers.length)"></span> dari <span class="font-medium" x-text="filteredTeachers.length"></span> guru
                </p>
            </div>
            <div>
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm bg-white dark:bg-gray-900" aria-label="Pagination">
                    <button @click="currentPage > 1 ? currentPage-- : null"
                        :disabled="currentPage === 1"
                        class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <template x-for="page in pages" :key="Math.random()">
                        <div class="inline-flex">
                            <template x-if="page !== '...'">
                                <button @click="currentPage = page"
                                    :class="currentPage === page 
                            ? 'z-10 bg-[#3C50E0] text-white ring-1 ring-inset ring-[#3C50E0]' 
                            : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:text-white dark:hover:bg-gray-800'"
                                    class="relative inline-flex items-center px-4 py-2 text-sm font-semibold focus:z-20 transition-all min-w-[40px] justify-center"
                                    x-text="page">
                                </button>
                            </template>

                            <template x-if="page === '...'">
                                <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-400 ring-1 ring-inset ring-gray-300">...</span>
                            </template>
                        </div>
                    </template>

                    <button @click="currentPage < totalPages ? currentPage++ : null"
                        :disabled="currentPage === totalPages"
                        class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </nav>
            </div>
        </div>
    </div>

    <div x-show="showRateModal"
        class="fixed inset-0 z-[99999] overflow-y-auto"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showRateModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white dark:bg-gray-900 rounded-xl w-full max-w-2xl shadow-2xl overflow-hidden flex flex-col"
                @click.stop
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0">

                <div class="px-7 py-5 border-b border-gray-200 dark:border-gray-800 flex items-center gap-4 sticky top-0 bg-white dark:bg-gray-900 z-10">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-lg font-bold"
                        :class="selectedGuru.avatar.bg + ' ' + selectedGuru.avatar.text"
                        x-text="selectedGuru.avatar.initials">
                    </div>
                    <div>
                        <h3 class="text-title-sm font-bold text-gray-900 dark:text-white leading-tight" x-text="selectedGuru.name"></h3>
                        <p class="text-sm text-gray-500">Input penilaian objektif 1-5</p>
                    </div>
                    <button @click="showRateModal = false" class="ml-auto text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-7 overflow-y-auto max-h-[65vh] bg-gray-50/50 dark:bg-gray-900">
                    <form action="{{ route('admin.assessments.store') }}" method="POST" id="formPenilaian">
                        @csrf
                        <input type="hidden" name="teacher_id" :value="selectedGuru.id">
                        <input type="hidden" name="assessment_period_id" :value="activePeriod ? activePeriod.id : ''">

                        <div class="space-y-5">
                            <template x-for="(cat, index) in categories" :key="cat.id">
                                <div class="p-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                                    <div class="mb-5 flex justify-between items-start">
                                        <div>
                                            <h5 class="text-base font-semibold text-gray-900 dark:text-white" x-text="cat.name"></h5>
                                            <p class="text-sm text-gray-500 mt-1" x-text="cat.description"></p>
                                        </div>
                                        <span class="text-xs font-bold uppercase tracking-wider text-gray-400">BOBOT: <span x-text="cat.weight + '%'"></span></span>
                                    </div>

                                    <div class="flex flex-wrap gap-3">
                                        <template x-for="score in [1, 2, 3, 4, 5]">
                                            <label class="cursor-pointer group/score">
                                                <input type="radio" :name="'scores['+cat.id+']'" :value="score" class="sr-only peer" required>
                                                <div class="w-12 h-12 flex items-center justify-center rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-base font-semibold text-gray-600 dark:text-gray-300 peer-checked:bg-[#3C50E0] peer-checked:text-white peer-checked:border-[#3C50E0] hover:border-[#3C50E0] transition-all">
                                                    <span x-text="score"></span>
                                                </div>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <div class="p-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                                <label class="block text-base font-semibold text-gray-900 dark:text-white mb-3">General Feedback</label>
                                <textarea name="general_feedback" rows="3"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-transparent px-4 py-3 text-sm text-gray-900 dark:text-white focus:border-[#3C50E0] focus:ring-[#3C50E0] focus:outline-none transition-colors"
                                    placeholder="Tuliskan catatan untuk guru ini..."></textarea>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="px-7 py-5 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 flex justify-end gap-3 sticky bottom-0 z-10">
                    <button @click="showRateModal = false" type="button" class="px-6 py-2.5 rounded-md font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        Batal
                    </button>
                    <button type="submit" form="formPenilaian" class="px-8 py-2.5 bg-[#3C50E0] text-white rounded-md font-medium hover:bg-blue-700 transition-colors shadow-sm">
                        Simpan & Publish
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal Simpan!',
            text: "{{ session('error') }}",
            confirmButtonColor: '#ef4444',
        });
        @endif

        @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            timer: 2500,
            showConfirmButton: false,
            background: '#f8fafc'
        });
        @endif
    });
</script>
@endsection