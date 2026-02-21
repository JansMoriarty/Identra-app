@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

<x-common.page-breadcrumb pageTitle="Konfigurasi Geofencing Sekolah" />

<div class="space-y-6" x-data="{ showForm: false }">
    <x-common.component-card title="Area Jangkauan Absensi">
        <div class="relative">
            <div id="map" style="height: 400px;" class="rounded-2xl border-2 border-gray-200 shadow-inner overflow-hidden z-0"></div>
            
            <button onclick="getLocation()" 
                    class="absolute bottom-4 right-4 z-[1000] bg-white p-3 rounded-xl shadow-xl border border-gray-100 hover:bg-blue-50 text-blue-600 transition-all active:scale-90"
                    title="Gunakan Lokasi Saya Saat Ini">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </button>
        </div>

        <div class="flex justify-between items-center mt-3 px-2">
            <p class="text-[11px] text-gray-400 italic font-medium">
                <span class="text-emerald-500 font-bold">●</span> Klik peta atau gunakan icon biru untuk menentukan titik baru
            </p>
            <button onclick="resetMap()" class="text-[11px] font-black uppercase tracking-wider text-gray-400 hover:text-emerald-600 transition-all">
                Reset View
            </button>
        </div>
    </x-common.component-card>

    <x-common.component-card title="Manajemen Data Lokasi">
        <div class="flex justify-between items-center mb-6">
            <button @click="showForm = !showForm" 
                    :class="showForm ? 'bg-rose-50 text-rose-600 border-rose-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'"
                    class="flex items-center gap-2 px-4 py-2 border rounded-xl font-bold text-sm transition-all active:scale-95">
                <span x-text="showForm ? '✕ Batal' : '＋ Tambah Lokasi'"></span>
            </button>
        </div>

        <div x-show="showForm" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform -translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             class="mb-8 p-6 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
            
            <form action="{{ route('locations.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Nama Lokasi</label>
                        <input type="text" name="name" placeholder="E.g. Kampus Utama" 
                               class="w-full bg-white border-2 border-gray-100 rounded-xl px-4 py-3 text-sm focus:border-emerald-500 outline-none transition-all" required>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Radius (Meter)</label>
                        <input type="number" name="radius" id="radius_input" value="50" 
                               class="w-full bg-white border-2 border-gray-100 rounded-xl px-4 py-3 text-sm focus:border-emerald-500 outline-none transition-all" required>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Tindakan</label>
                        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition-all shadow-md">
                            Simpan Lokasi
                        </button>
                    </div>
                </div>

                <input type="hidden" name="latitude" id="lat" required>
                <input type="hidden" name="longitude" id="lng" required>

                <div id="status-koordinat" class="p-3 bg-amber-50 border border-amber-100 rounded-xl hidden">
                    <p class="text-[11px] text-amber-700 flex items-center gap-2 font-medium">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
                        Koordinat terpilih: <span id="display-coords" class="font-bold underline"></span>
                    </p>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto text-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-100 font-bold text-gray-400 uppercase">
                        <th class="py-4 px-2">No</th>
                        <th class="py-4 px-2">Nama Lokasi</th>
                        <th class="py-4 px-2 text-center">Radius</th>
                        <th class="py-4 px-2 text-center text-gray-300">Koordinat</th>
                        <th class="py-4 px-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($locations as $loc)
                        <tr class="group hover:bg-gray-50 cursor-pointer" onclick="focusLocation({{ $loc->latitude }}, {{ $loc->longitude }}, {{ $loc->radius }})">
                            <td class="py-4 px-2 font-bold">{{ $loop->iteration }}</td>
                            <td class="py-4 px-2 font-bold text-gray-800">{{ $loc->name }}</td>
                            <td class="py-4 px-2 text-center">
                                <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-bold">{{ $loc->radius }} m</span>
                            </td>
                            <td class="py-4 px-2 text-center text-xs font-mono text-gray-400">
                                {{ round($loc->latitude, 5) }}, {{ round($loc->longitude, 5) }}
                            </td>
                            <td class="py-4 px-2 text-right">
                                <form action="{{ route('locations.destroy', $loc->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus lokasi ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" @click.stop class="p-2 text-gray-300 hover:text-rose-500 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-gray-400 italic">Belum ada lokasi yang dikonfigurasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.component-card>
</div>

<script>
    // Inisialisasi Peta
    var map = L.map('map').setView([-6.200000, 106.816666], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { 
        attribution: '&copy; OpenStreetMap' 
    }).addTo(map);

    var currentMarker, currentCircle;

    // Render lokasi yang sudah ada
    @foreach($locations as $loc)
        L.marker([{{ $loc->latitude }}, {{ $loc->longitude }}]).addTo(map).bindPopup("<b>{{ $loc->name }}</b>");
        L.circle([{{ $loc->latitude }}, {{ $loc->longitude }}], { 
            radius: {{ $loc->radius }}, 
            color: '#3b82f6', weight: 1, fillOpacity: 0.1 
        }).addTo(map);
    @endforeach

    // Fungsi update marker saat klik atau ambil lokasi
    function updateSelectedLocation(lat, lng) {
        var radius = document.getElementById('radius_input').value;
        
        if (currentMarker) map.removeLayer(currentMarker);
        if (currentCircle) map.removeLayer(currentCircle);

        currentMarker = L.marker([lat, lng]).addTo(map);
        currentCircle = L.circle([lat, lng], { color: '#10b981', radius: radius }).addTo(map);
        
        // Update Input Hidden
        document.getElementById('lat').value = lat;
        document.getElementById('lng').value = lng;
        
        // Update UI Status
        document.getElementById('status-koordinat').classList.remove('hidden');
        document.getElementById('display-coords').innerText = lat.toFixed(6) + ", " + lng.toFixed(6);
    }

    // Event Klik Peta
    map.on('click', function(e) {
        updateSelectedLocation(e.latlng.lat, e.latlng.lng);
    });

    // Fungsi Geolocation (Ambil Lokasi Saat Ini)
    function getLocation() {
        if (navigator.geolocation) {
            // // Tampilkan loading sederhana (opsional)
            // alert("Mencoba mengambil koordinat presisi...");
            
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                map.flyTo([lat, lng], 18);
                updateSelectedLocation(lat, lng);
            }, function(error) {
                alert("Gagal mengambil lokasi: " + error.message);
            }, {
                enableHighAccuracy: true // Menggunakan GPS jika tersedia untuk hasil maksimal
            });
        } else {
            alert("Browser Anda tidak mendukung fitur lokasi.");
        }
    }

    function focusLocation(lat, lng, radius) {
        map.flyTo([lat, lng], 18, { duration: 1.5 });
    }

    function resetMap() {
        map.setView([-6.200000, 106.816666], 13);
    }
</script>
@endsection