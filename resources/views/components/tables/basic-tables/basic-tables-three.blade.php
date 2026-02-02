<div x-data="{
    transactions: [],
    itemsPerPage: 10,
    currentPage: 1,
    dropdownOpen: null,
    loading: true,
    error: null,

    init() {
        fetch('/api/admin/guru', {
            headers: {
                'Authorization': 'Bearer 1|9hVR0jhAFdQxs9qRbSAZCj8unBikML2sNylZFvFI969699fe',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(res => {
            if (!res.ok) throw new Error('Failed to fetch API');
            return res.json();
        })
        .then(result => {
            const data = Array.isArray(result?.data) ? result.data : [];
            this.transactions = data.map(guru => ({
                id: guru.id,
                name: guru.name,
                date: guru.created_at,
                email: guru.email,
                category: guru.nip,
                status: guru.jenis_kelamin === 'L' ? 'Pria' : 'Wanita'
            }));
        })
        .catch(err => {
            console.error('Fetch error:', err);
            this.error = 'Failed to load data';
        })
        .finally(() => this.loading = false);
    },

    get totalPages() {
        return Math.ceil(this.transactions.length / this.itemsPerPage);
    },

    get paginatedTransactions() {
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        return this.transactions.slice(start, end);
    },

    get displayedPages() {
        const range = [];
        for (let i = 1; i <= this.totalPages; i++) {
            if (i === 1 || i === this.totalPages || (i >= this.currentPage - 1 && i <= this.currentPage + 1)) {
                range.push(i);
            } else if (range[range.length - 1] !== '...') {
                range.push('...');
            }
        }
        return range;
    },

    prevPage() { if (this.currentPage > 1) this.currentPage--; },
    nextPage() { if (this.currentPage < this.totalPages) this.currentPage++; },
    goToPage(page) { if (typeof page === 'number' && page >= 1 && page <= this.totalPages) this.currentPage = page; },

    getStatusClass(status) {
        const classes = {
            'Pria': 'bg-blue-50 text-blue-600 dark:bg-green-500/15 dark:text-green-500',
            'Wanita': 'bg-red-50 text-red-600 dark:bg-yellow-500/15 dark:text-orange-400',
            'Failed': 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-500',
        };
        return classes[status] || '';
    },

    toggleDropdown(id) { this.dropdownOpen = this.dropdownOpen === id ? null : id; },

    // --- New functions for initials avatar ---
    getInitial(name) {
        if (!name) return '';
        const words = name.split(' ');
        if (words.length === 1) return words[0].charAt(0).toUpperCase();
        return (words[0].charAt(0) + words[1].charAt(0)).toUpperCase();
    },

    getRandomAvatarColors(seed) {
    // daftar warna dasar
    const colors = [
        '59, 130, 246', // biru
        '16, 185, 129', // hijau
        '234, 179, 8',  // kuning
        '239, 68, 68',  // merah
        '139, 92, 246', // ungu
        '249, 115, 22'  // oranye
    ];

    // hash sederhana supaya konsisten per nama
    let hash = 0;
    for (let i = 0; i < seed.length; i++) {
        hash = seed.charCodeAt(i) + ((hash << 5) - hash);
    }
    const index = Math.abs(hash) % colors.length;
    const rgb = colors[index];

    return {
        bg: `rgba(${rgb}, 0.2)`,    // wrapper soft
        text: `rgb(${rgb})`          // text solid
    };
}


}" x-init="init()">
    <div class="rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <!-- Header -->
        <div class="flex flex-col gap-2 px-5 mb-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">

            <!-- Search (kiri) -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <form>
                    <div class="relative">
                        <button type="button" class="absolute -translate-y-1/2 left-4 top-1/2">
                            <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z" />
                            </svg>
                        </button>
                        <input type="text" placeholder="Search..."
                            class="h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-[42px] pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-blue-800 xl:w-[300px]" />
                    </div>
                </form>
            </div>

            <!-- Button Tambah Guru (kanan) -->
            <a href="{{ route('guru.create') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Guru
            </a>

        </div>


        <!-- Table -->
        <div class="overflow-hidden">
            <div class="max-w-full px-5 overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-gray-200 border-y dark:border-gray-700">
                            <th class="px-4 py-3 text-start text-theme-sm text-gray-500 dark:text-gray-400">Name</th>
                            <th class="px-4 py-3 text-start text-theme-sm text-gray-500 dark:text-gray-400">Date</th>
                            <th class="px-4 py-3 text-start text-theme-sm text-gray-500 dark:text-gray-400">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 capitalize">NIP</th>
                            <th class="px-4 py-3 text-start text-theme-sm text-gray-500 dark:text-gray-400">Gender</th>
                            <th class="px-4 py-3 capitalize"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="transaction in paginatedTransactions" :key="transaction.id">
                            <tr>
                                <!-- Avatar + Name -->
                                <td class="py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center font-medium"
                                            :style="`background-color: ${getRandomAvatarColors(transaction.name).bg}; color: ${getRandomAvatarColors(transaction.name).text};`"
                                            x-text="getInitial(transaction.name)">
                                        </div>

                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="transaction.name"></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Date -->
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400" x-text="transaction.date"></div>
                                </td>

                                <!-- Email -->
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400" x-text="transaction.email"></div>
                                </td>

                                <!-- NIP -->
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400" x-text="transaction.category"></div>
                                </td>

                                <!-- Gender -->
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusClass(transaction.status)" x-text="transaction.status"></span>
                                </td>

                                <!-- Actions -->
                                <td class="px-4 py-4 text-sm font-medium text-right whitespace-nowrap">
                                    <div class="flex justify-center relative">
                                        <!-- Dropdown placeholder -->
                                        <button @click="toggleDropdown(transaction.id)" class="text-gray-500 dark:text-gray-400">
                                            ⋮
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination (same as your code) -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-white/[0.05]">
            <div class="flex items-center justify-between">
                <button @click="prevPage" :disabled="currentPage === 1" :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''" class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:px-3.5">
                    Previous
                </button>

                <span class="block text-sm font-medium text-gray-700 dark:text-gray-400 sm:hidden">
                    Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
                </span>

                <ul class="hidden items-center gap-0.5 sm:flex">
                    <template x-for="page in displayedPages" :key="page">
                        <li>
                            <button x-show="page !== '...'" @click="goToPage(page)" :class="currentPage === page ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-blue-500/[0.08] hover:text-blue-500 dark:text-gray-400 dark:hover:text-blue-500'" class="flex h-10 w-10 items-center justify-center rounded-lg text-theme-sm font-medium" x-text="page"></button>
                            <span x-show="page === '...'" class="flex h-10 w-10 items-center justify-center text-gray-500">...</span>
                        </li>
                    </template>
                </ul>

                <button @click="nextPage" :disabled="currentPage === totalPages" :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : ''" class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:px-3.5">
                    Next
                </button>
            </div>
        </div>
    </div>
</div>