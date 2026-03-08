<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div x-data="scheduleManager" class="p-8 bg-gray-50/30 min-h-screen">

    <div x-show="currentPage === 'list'" x-transition.opacity>
        <div class="max-w-6xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Jadwal Pelajaran</h1>
                    <p class="text-sm text-gray-500 mt-1">Kelola seluruh jadwal mengajar guru dalam satu tempat.</p>
                </div>
                <button @click="openCreatePage()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-semibold shadow-sm transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Jadwal
                </button>
            </div>

            <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden">
                <div class="hidden md:flex items-center px-6 py-3 bg-gray-100/50 border-b border-gray-100">
                    <div class="w-1/3 min-w-[300px] text-[11px] font-bold text-gray-400 uppercase tracking-wider">Guru & Identitas</div>
                    <div class="w-1/4 text-center text-[11px] font-bold text-gray-400 uppercase tracking-wider">Waktu & Hari</div>
                    <div class="w-1/3 text-right text-[11px] font-bold text-gray-400 uppercase tracking-wider pr-10">Mata Pelajaran & Kelas</div>
                </div>

                <template x-for="(group, teacherName) in groupedSchedules" :key="teacherName">
                    <div class="border-b border-gray-100 last:border-none">
                        <div class="flex items-center gap-3 px-6 py-4 bg-gray-50/50">
                            <div class="p-2 bg-white border border-gray-100 shadow-sm rounded-lg text-gray-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h2 class="text-[15px] font-bold text-gray-800" x-text="teacherName"></h2>
                            <span class="ml-2 px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full" x-text="group.data.length + ' Sesi'"></span>
                        </div>

                        <div class="w-full h-px bg-gray-100"></div>

                        <div>
                            <template x-for="(item, index) in group.data" :key="item.id">
                                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50 hover:bg-gray-50/50 transition-colors bg-white last:border-none">

                                    <div class="flex items-center gap-6 w-1/3 min-w-[300px]">
                                        <div class="text-[14px] font-medium text-gray-400 w-4 text-center" x-text="index + 1"></div>
                                        <div class="flex items-center gap-4">
                                            <div :class="getAvatarStyle(teacherName)"
                                                class="w-10 h-10 rounded-full flex items-center justify-center text-[15px] font-bold">
                                                <span x-text="teacherName.substring(0,1).toUpperCase()"></span>
                                            </div>
                                            <div>
                                                <div class="text-[15px] font-bold text-gray-900" x-text="teacherName"></div>
                                                <div class="text-[13px] text-gray-400 mt-0.5" x-text="group.nip || '-'"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-col items-center justify-start w-1/4 gap-1">
                                        <div class="flex items-center gap-3 text-[14px] text-gray-700 font-bold tracking-wide">
                                            <span x-text="formatTime(item.start_time)"></span>
                                            <span class="text-gray-300 text-xs font-normal">--</span>
                                            <span x-text="formatTime(item.end_time)"></span>
                                        </div>

                                        <div class="px-2 py-0.5 bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase rounded tracking-widest border border-indigo-100/50"
                                            x-text="item.day">
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-end gap-3 w-1/3">
                                        <div class="px-4 py-1 border border-yellow-200 text-yellow-600 text-[11px] font-bold uppercase rounded-full bg-yellow-50/50 whitespace-nowrap"
                                            x-text="item.subject?.name || 'MATA PELAJARAN'"></div>

                                        <div class="px-3 py-1.5 bg-gray-100 text-gray-500 text-[11px] font-bold uppercase rounded-md whitespace-nowrap"
                                            x-text="item.classroom?.name || 'KELAS'"></div>

                                        <button @click="deleteSchedule(item.id)" class="ml-3 text-gray-300 hover:text-red-500 transition-colors" title="Hapus Jadwal">
                                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <div x-show="currentPage === 'create'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" style="display: none;">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center gap-4 mb-8">
                <button @click="currentPage = 'list'" class="w-10 h-10 flex items-center justify-center rounded-full bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <h1 class="text-2xl font-bold text-gray-900">Tambah Jadwal Massal</h1>
            </div>

            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-500 uppercase ml-1">Pilih Guru</label>
                        <select x-model="bulkData.user_id" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-700 focus:ring-2 focus:ring-indigo-500 transition cursor-pointer outline-none">
                            <option value="">-- Pilih Guru --</option>
                            <template x-for="g in gurus" :key="g.id">
                                <option :value="g.id" x-text="g.nama"></option>
                            </template>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-500 uppercase ml-1">Hari</label>
                        <select x-model="bulkData.day" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-700 focus:ring-2 focus:ring-indigo-500 transition cursor-pointer outline-none">
                            <option value="">-- Pilih Hari --</option>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                            <option value="Minggu">Minggu</option>
                        </select>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden p-6">
                    <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                        <h3 class="text-[15px] font-bold text-gray-700 underline decoration-indigo-200 underline-offset-4">Detail Jam Pelajaran</h3>
                        <button @click="addRow()" class="text-xs font-bold text-white bg-emerald-500 hover:bg-emerald-600 px-4 py-2 rounded-lg transition shadow-sm flex items-center gap-1">
                            <span>+</span> Tambah Jam
                        </button>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(row, index) in bulkData.items" :key="index">
                            <div class="flex flex-wrap md:flex-nowrap gap-4 items-center p-4 rounded-xl border border-gray-100 bg-gray-50/30 transition-all hover:bg-gray-50">
                                <div class="flex-1 min-w-[140px]">
                                    <label class="text-[10px] font-bold text-gray-500 uppercase mb-1.5 block">Kelas</label>
                                    <select x-model="row.classroom_id" class="w-full bg-white border border-gray-200 rounded-lg p-2.5 text-sm text-gray-600 focus:ring-2 focus:ring-indigo-500 outline-none">
                                        <option value="">-- Kelas --</option>
                                        <template x-for="c in classrooms" :key="c.id">
                                            <option :value="c.id" x-text="c.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="flex-[1.5] min-w-[180px]">
                                    <label class="text-[10px] font-bold text-gray-500 uppercase mb-1.5 block">Mata Pelajaran</label>
                                    <select x-model="row.subject_id" class="w-full bg-white border border-gray-200 rounded-lg p-2.5 text-sm text-gray-600 focus:ring-2 focus:ring-indigo-500 outline-none">
                                        <option value="">-- Mapel --</option>
                                        <template x-for="s in subjects" :key="s.id">
                                            <option :value="s.id" x-text="s.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="w-24">
                                    <label class="text-[10px] font-bold text-gray-500 uppercase mb-1.5 block">Mulai</label>
                                    <input type="text" :id="'start-'+index" placeholder="--:--" class="timepicker w-full bg-white border border-gray-200 rounded-lg p-2.5 text-sm text-center text-gray-700 focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer" readonly>
                                </div>
                                <div class="w-24">
                                    <label class="text-[10px] font-bold text-gray-500 uppercase mb-1.5 block">Selesai</label>
                                    <input type="text" :id="'end-'+index" placeholder="--:--" class="timepicker w-full bg-white border border-gray-200 rounded-lg p-2.5 text-sm text-center text-gray-700 focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer" readonly>
                                </div>
                                <div class="w-10 flex justify-center pt-5">
                                    <button @click="removeRow(index)" class="p-2 text-red-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition" title="Hapus Baris">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button @click="currentPage = 'list'" class="flex-1 py-3.5 bg-gray-100 text-gray-600 rounded-xl font-bold hover:bg-gray-200 transition">Batal</button>
                    <button @click="saveBulkSchedules()" :disabled="isSaving" class="flex-[2] py-3.5 bg-indigo-600 text-white rounded-xl font-bold shadow-md shadow-indigo-200 hover:bg-indigo-700 disabled:opacity-50 transition flex items-center justify-center gap-3">
                        <template x-if="isSaving">
                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Semua Jadwal'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('scheduleManager', () => ({
            currentPage: 'list',
            schedules: [],
            gurus: [],
            classrooms: [],
            subjects: [],
            isLoading: false,
            isSaving: false,
            apiToken: '1|Sg8C2z2Oo2wny9FJy4RHtuK9doSo93yPoWO1JTUN58667636',

            bulkData: {
                user_id: '',
                day: '',
                items: [{
                    classroom_id: '',
                    subject_id: '',
                    start_time: '',
                    end_time: ''
                }]
            },

            get authHeaders() {
                return {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.apiToken}`
                };
            },

            get groupedSchedules() {
                const groups = {};
                this.schedules.forEach(item => {
                    const name = item.guru?.name || item.user?.name || item.user?.nama || 'Guru Tidak Diketahui';
                    const nip = item.guru?.nip || item.user?.nip || '-';

                    if (!groups[name]) {
                        groups[name] = {
                            name,
                            nip,
                            data: []
                        };
                    }
                    groups[name].data.push(item);
                });
                return groups;
            },

            // Menggunakan kombinasi text/background color serasi untuk wrapper opacity 
            getAvatarStyle(name) {
                const styles = [
                    'text-blue-600 bg-blue-100',
                    'text-purple-600 bg-purple-100',
                    'text-emerald-600 bg-emerald-100',
                    'text-pink-600 bg-pink-100',
                    'text-amber-600 bg-amber-100',
                    'text-indigo-600 bg-indigo-100',
                    'text-rose-600 bg-rose-100'
                ];
                let hash = 0;
                for (let i = 0; i < name.length; i++) {
                    hash = name.charCodeAt(i) + ((hash << 5) - hash);
                }
                return styles[Math.abs(hash) % styles.length];
            },

            async init() {
                this.isLoading = true;
                try {
                    await Promise.all([
                        this.loadSchedules(),
                        this.loadGurus(),
                        this.loadClassrooms(),
                        this.loadSubjects()
                    ]);
                } catch (e) {
                    console.error("Gagal memuat data awal");
                }
                this.isLoading = false;
            },

            openCreatePage() {
                this.currentPage = 'create';
                this.bulkData = {
                    user_id: '',
                    day: '',
                    items: [{
                        classroom_id: '',
                        subject_id: '',
                        start_time: '',
                        end_time: ''
                    }]
                };
                this.$nextTick(() => this.initTimePickers(0));
            },

            addRow() {
                this.bulkData.items.push({
                    classroom_id: '',
                    subject_id: '',
                    start_time: '',
                    end_time: ''
                });
                const idx = this.bulkData.items.length - 1;
                this.$nextTick(() => this.initTimePickers(idx));
            },

            removeRow(index) {
                if (this.bulkData.items.length > 1) {
                    this.bulkData.items.splice(index, 1);
                }
            },

            initTimePickers(index) {
                const config = {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    time_24hr: true,
                    disableMobile: "true"
                };

                flatpickr(`#start-${index}`, {
                    ...config,
                    onChange: (sd, ds) => {
                        this.bulkData.items[index].start_time = ds;
                    }
                });
                flatpickr(`#end-${index}`, {
                    ...config,
                    onChange: (sd, ds) => {
                        this.bulkData.items[index].end_time = ds;
                    }
                });
            },

            async saveBulkSchedules() {
                if (!this.bulkData.user_id || !this.bulkData.day) return alert('Pilih Guru dan Hari');

                this.isSaving = true;
                let errorCount = 0;

                for (const item of this.bulkData.items) {
                    if (!item.classroom_id || !item.start_time) continue;

                    const payload = {
                        user_id: this.bulkData.user_id,
                        day: this.bulkData.day,
                        classroom_id: item.classroom_id,
                        subject_id: item.subject_id,
                        start_time: item.start_time + ':00',
                        end_time: item.end_time + ':00'
                    };

                    try {
                        const res = await fetch('/api/schedules', {
                            method: 'POST',
                            headers: this.authHeaders,
                            body: JSON.stringify(payload)
                        });
                        if (!res.ok) errorCount++;
                    } catch (e) {
                        errorCount++;
                    }
                }

                this.isSaving = false;
                if (errorCount > 0) alert(`${errorCount} data gagal disimpan. Periksa autentikasi.`);
                else {
                    alert('Semua jadwal berhasil disimpan!');
                    this.currentPage = 'list';
                    this.loadSchedules();
                }
            },

            async loadSchedules() {
                const res = await fetch('/api/schedules', {
                    headers: this.authHeaders
                });
                const json = await res.json();
                this.schedules = json.data || [];
            },
            async loadGurus() {
                const res = await fetch('/api/admin/guru', {
                    headers: this.authHeaders
                });
                const json = await res.json();
                this.gurus = json.data || [];
            },
            async loadClassrooms() {
                const res = await fetch('/api/classrooms', {
                    headers: this.authHeaders
                });
                const json = await res.json();
                this.classrooms = json.data || [];
            },
            async loadSubjects() {
                const res = await fetch('/api/subjects', {
                    headers: this.authHeaders
                });
                const json = await res.json();
                this.subjects = json.data || [];
            },
            async deleteSchedule(id) {
                if (confirm('Hapus jadwal ini?')) {
                    await fetch(`/api/schedules/${id}`, {
                        method: 'DELETE',
                        headers: this.authHeaders
                    });
                    this.loadSchedules();
                }
            },
            formatTime(t) {
                return t ? t.substring(0, 5) : '--:--';
            }
        }));
    });
</script>