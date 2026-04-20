@if ($errors->any())
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div x-data="{
    rules: @js($rules),
    showModal: false,
    editMode: false,
    
    form: {
        id: null,
        rule_name: '',
        target_role: 'guru',
        trigger_event: 'CHECK_IN',
        condition_operator: '<',
        condition_time: '', // Inisialisasi string kosong
        condition_minute: '',
        point_modifier: 0,
        priority: 1,
        is_active: 1
    },

    openAddModal() {
        this.editMode = false;
        this.form = { 
            id: null, rule_name: '', target_role: 'guru', trigger_event: 'CHECK_IN', 
            condition_operator: '<', condition_time: '', condition_minute: '', 
            point_modifier: 0, priority: 1, is_active: 1 
        };
        this.showModal = true;
    },

    openEditModal(rule) {
        this.editMode = true;
        this.form = { ...rule };
        // Pastikan jika di DB null, di Alpine jadi string kosong agar input time tidak error
        if (this.form.condition_time === null) this.form.condition_time = '';
        this.showModal = true;
    },

    getEventColor(event) {
        return event === 'CHECK_IN' ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400' : 'bg-orange-100 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400';
    },

    getModifierColor(val) {
        return val > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
    }
}" class="p-6">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">⚙️ Aturan Poin Otomatis</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Gunakan jam jika aturan bersifat tetap, kosongkan untuk mengikuti jadwal absen.</p>
        </div>
        <button @click="openAddModal()" class="flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition shadow-lg shadow-indigo-500/20">
            Tambah Aturan
        </button>
    </div>

    <div class="bg-white dark:bg-white/[0.03] border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500">Aturan</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 text-center">Trigger</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500">Kondisi</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 text-center">Poin</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <template x-for="rule in rules" :key="rule.id">
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900 dark:text-white" x-text="rule.rule_name"></div>
                                <div class="text-[10px] text-gray-400 uppercase tracking-widest" x-text="rule.target_role"></div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span :class="getEventColor(rule.trigger_event)" class="px-2.5 py-1 rounded-md text-[10px] font-bold tracking-wider" x-text="rule.trigger_event"></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 font-mono">
                                <span x-text="rule.condition_operator" class="font-bold text-indigo-500"></span>
                                <span x-text="rule.condition_time || 'Jadwal Absen'"></span>
                            </td>
                            <td class="px-6 py-4 text-center font-black" :class="getModifierColor(rule.point_modifier)">
                                <span x-text="(rule.point_modifier > 0 ? '+' : '') + rule.point_modifier"></span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button @click="openEditModal(rule)" class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-lg transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" x-cloak x-transition>
        <div class="bg-white dark:bg-gray-900 rounded-3xl w-full max-w-xl p-8 shadow-2xl border border-white/10" @click.away="showModal = false">

            <h3 class="text-xl font-bold mb-6 dark:text-white" x-text="editMode ? '📝 Edit Aturan' : '🚀 Tambah Aturan'"></h3>

            <form :action="editMode ? `/admin/point-rules/${form.id}` : '{{ route('point-rules.store') }}'" method="POST" novalidate>
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Nama Aturan</label>
                        <input type="text" name="rule_name" x-model="form.rule_name" required class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-xl p-3 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Target Role</label>
                        <select name="target_role" x-model="form.target_role" class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-xl p-3 dark:text-white">
                            <option value="guru">Guru</option>
                            <option value="siswa">Siswa</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Trigger Event</label>
                        <select name="trigger_event" x-model="form.trigger_event" required
                            class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-xl p-3 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            <option value="CHECK_IN">Masuk (Check In)</option>
                            <option value="CHECK_OUT">Pulang (Check Out)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Waktu (Boleh Kosong)</label>
                        <input type="time" name="condition_time" x-model="form.condition_time"
                            class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-xl p-3 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Operator</label>
                        <select name="condition_operator" x-model="form.condition_operator" class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-xl p-3 dark:text-white">
                            <option value="<">
                                < Kurang Dari</option>
                            <option value=">">> Lebih Dari</option>
                            <option value="<=">
                                <= Kurang Sama Dengan</option>
                            <option value=">=">>= Lebih Sama Dengan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Poin Modifier</label>
                        <input type="number" name="point_modifier" x-model="form.point_modifier" required class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-xl p-3 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Status</label>
                        <select name="is_active" x-model="form.is_active" class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-xl p-3 dark:text-white">
                            <option :value="1">Aktif</option>
                            <option :value="0">Non-Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="mt-8 flex gap-3">
                    <button type="button" @click="showModal = false" class="flex-1 px-4 py-3 bg-gray-100 dark:bg-gray-800 text-gray-600 rounded-2xl font-bold">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/30">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>