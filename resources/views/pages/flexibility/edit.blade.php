@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Voucher</h1>
                <p class="text-sm text-gray-500">Perbarui data voucher <strong>{{ $item->item_name }}</strong></p>
            </div>
            <a href="{{ route('vouchers.index') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600 dark:text-gray-400">
                &larr; Kembali
            </a>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-white/[0.03]">
            <form action="{{ route('vouchers.update', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    
                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Voucher</label>
                        <input type="text" name="item_name" value="{{ old('item_name', $item->item_name) }}" 
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 dark:bg-gray-900 dark:text-white" required>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe Kompensasi</label>
                        <select name="item_type" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 dark:bg-gray-900 dark:text-white">
                            <option value="LATE_WAVER" {{ $item->item_type == 'LATE_WAVER' ? 'selected' : '' }}>Pemutihan Terlambat</option>
                            <option value="WFH_PASS" {{ $item->item_type == 'WFH_PASS' ? 'selected' : '' }}>Izin WFH</option>
                            <option value="LEAVE_PERMISSION" {{ $item->item_type == 'LEAVE_PERMISSION' ? 'selected' : '' }}>Izin Tanpa Surat</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Power (Menit/Hari)</label>
                        <input type="number" name="value_power" value="{{ old('value_power', $item->value_power) }}" 
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 dark:bg-gray-900 dark:text-white" required>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga (Poin)</label>
                        <input type="number" name="point_cost" value="{{ old('point_cost', $item->point_cost) }}" 
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 dark:bg-gray-900 dark:text-white" required>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Stok (Opsional)</label>
                        <input type="number" name="stock_limit" value="{{ old('stock_limit', $item->stock_limit) }}" 
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi</label>
                        <textarea name="description" rows="3" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 dark:bg-gray-900 dark:text-white" required>{{ old('description', $item->description) }}</textarea>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-8 py-3 text-sm font-semibold text-white hover:bg-indigo-700 shadow-lg transition">
                        Update Voucher
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection