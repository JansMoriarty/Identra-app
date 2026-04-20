@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Buat Voucher Baru</h1>
                <p class="text-sm text-gray-500">Tambahkan item kelonggaran ke dalam marketplace.</p>
            </div>
            <a href="{{ route('vouchers.index') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600 dark:text-gray-400">
                &larr; Kembali
            </a>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-white/[0.03]">
            <form action="{{ route('vouchers.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    
                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Voucher</label>
                        <input type="text" name="item_name" value="{{ old('item_name') }}" placeholder="Contoh: Token Bebas Terlambat 15 Menit" 
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe Kompensasi</label>
                        <select name="item_type" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:border-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            <option value="LATE_WAVER">Pemutihan Terlambat</option>
                            <option value="WFH_PASS">Izin WFH</option>
                            <option value="LEAVE_PERMISSION">Izin Tanpa Surat</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Power (Menit/Hari)</label>
                        <input type="number" name="value_power" value="{{ old('value_power') }}" placeholder="30" 
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:border-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga (Poin)</label>
                        <input type="number" name="point_cost" value="{{ old('point_cost') }}" placeholder="50" 
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:border-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Stok (Opsional)</label>
                        <input type="number" name="stock_limit" value="{{ old('stock_limit') }}" 
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:border-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi</label>
                        <textarea name="description" rows="3" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:border-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-8 py-3 text-sm font-semibold text-white hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition">
                        Simpan Voucher
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection