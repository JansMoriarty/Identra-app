@extends('layouts.kiosk')

@section('content')
<div class="min-h-screen transition-colors duration-300 bg-[#ffffff] text-gray-900 dark:text-gray-800 px-6 py-8 lg:px-20 lg:py-6">

    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-semibold tracking-wide text-gray-800 dark:text-gray-900">
            Absensi Face Recognition
        </h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        {{-- LEFT SIDE — CAMERA --}}
        <div class="lg:col-span-2">
            <div class="relative rounded-[28px] overflow-hidden dark:bg-gray-50 border border-gray-200 dark:border-gray-300 h-[480px]">

                {{-- STATUS INDICATOR (Kiri Atas) --}}
                <div id="faceStatus" class="absolute top-5 left-5 z-30 px-4 py-2 rounded-full bg-black/40 backdrop-blur-md border border-white/20 flex items-center gap-2 transition-all opacity-0">
                    <div id="statusDot" class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                    <span id="statusText" class="text-white text-xs font-bold uppercase tracking-wider">No Face</span>
                </div>

                <div id="lottie-container" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-gray-50">
                    <script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.8.11/dist/dotlottie-wc.js" type="module"></script>
                    <dotlottie-wc src="https://lottie.host/4719cea0-85a0-409e-bc93-f59e889784da/CHk1BOCF5U.lottie" style="width: 300px; height: 300px" autoplay loop></dotlottie-wc>
                </div>

                <video id="video" autoplay muted playsinline class="w-full h-full object-cover scale-x-[-1]"></video>
                {{-- Canvas dibiarkan normal, kita mirror via JS --}}
                <canvas id="overlay" class="absolute inset-0 z-20 scale-x-[-1]"></canvas>
            </div>

            <div class="flex gap-4 mt-8">
                <button id="startScan" class="flex items-center gap-3 bg-indigo-600 hover:bg-indigo-700 px-7 py-3.5 rounded-xl font-medium text-white transition">
                    <span id="btnIcon"><svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z" />
                        </svg></span>
                    <span id="btnText">Start Scan</span>
                </button>
                <button id="retryScan" class="flex items-center gap-3 bg-gray-100 px-7 py-3.5 rounded-xl font-medium border border-gray-300 text-gray-800">
                    Retry
                </button>
            </div>
        </div>

        {{-- RIGHT SIDE — PANEL --}}
        <div class="flex flex-col gap-6">
            <div class="rounded-[28px] p-6 bg-white border min-h-[250px] border-gray-200 shadow-sm">
                <h2 class="text-sm font-bold mb-5 text-gray-400 uppercase tracking-widest">Scan Result</h2>

                <div id="userResult" class="hidden space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center">
                            <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <p id="userName" class="text-lg font-bold text-gray-900 leading-tight">Mencari...</p>
                            <div class="flex items-center gap-2 mt-1">
                                <p id="realtimeClock" class="text-xs text-gray-500 font-mono bg-gray-100 px-2 py-0.5 rounded"></p>
                                <span id="attendanceStatusLabel" class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full"></span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <form id="formMasuk">
                            @csrf
                            <input type="hidden" name="status" value="hadir">
                            <input type="hidden" name="guru_id" class="guru_id_input">
                            <input type="hidden" name="latitude" class="lat_input">
                            <input type="hidden" name="longitude" class="lng_input">

                            <button type="submit" class="w-full py-3 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition">
                                Masuk
                            </button>
                        </form>

                        <form id="formPulang">
                            @csrf
                            <input type="hidden" name="status" value="pulang">
                            <input type="hidden" name="guru_id" class="guru_id_input">
                            <input type="hidden" name="latitude" class="lat_input">
                            <input type="hidden" name="longitude" class="lng_input">

                            <button type="submit" class="w-full py-3 bg-orange-500 text-white rounded-xl text-sm font-bold hover:bg-orange-600 transition">
                                Pulang
                            </button>
                        </form>
                    </div>
                </div>

                <div id="placeholderResult" class="py-8 flex flex-col items-center justify-center text-center opacity-60">
                    <p class="text-xs font-medium text-gray-500">Menunggu scan wajah...</p>
                </div>
            </div>

            {{-- MANUAL ACTION --}}
            <div class="rounded-[28px] p-6 bg-white border border-gray-200 shadow-sm">
                <h2 class="text-sm font-bold mb-4 text-gray-400 uppercase tracking-widest">Manual Action</h2>
                <a href="{{ route('attendances.manual') }}" class="flex items-center justify-between w-full p-4 bg-gray-50 hover:bg-gray-100 rounded-2xl transition group border border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-500/10 rounded-lg text-green-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-gray-700">Absen Manual</span>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // --- KONFIGURASI & STATE ---
    let stream = null;
    let scanInterval = null;
    let isScanning = false;
    let faceMatcher = null;
    let lastIdentifiedId = null;
    let isProcessingIdentification = false; // Flag agar tidak spam API
    let isModelsLoaded = false; // Flag untuk status loading model & data
    // --- KONFIGURASI & STATE ---
    let attendanceRules = {
        jam_masuk: "12:00:00" // Nilai default sementara
    };

    // Fungsi untuk mengambil jam masuk dari database
    async function syncAttendanceSettings() {
        try {
            const res = await fetch('{{ route('attendance-settings') }}');
            if (res.ok) {
                const data = await res.json();
                attendanceRules.jam_masuk = data.jam_masuk;
                console.log("Aturan sinkron: Masuk pukul " + attendanceRules.jam_masuk);
            }
        } catch (e) {
            console.error("Gagal sinkronisasi aturan:", e);
        }
    }

    // Panggil fungsi saat halaman dimuat
    window.addEventListener('load', async () => {
        await syncAttendanceSettings(); // Ambil jam terbaru dari DB
        await loadModelsAndData();
    });
    let userNamesMap = {};

    const video = document.getElementById('video');
    const canvas = document.getElementById('overlay');
    const faceStatus = document.getElementById('faceStatus');
    const statusDot = document.getElementById('statusDot');
    const statusText = document.getElementById('statusText');
    const userResult = document.getElementById('userResult');
    const placeholderResult = document.getElementById('placeholderResult');

    // --- UTILITIES ---
    const l2Normalize = (v) => {
        const norm = Math.sqrt(v.reduce((acc, val) => acc + (val * val), 0));
        return v.map(val => val / norm);
    };

    function prepareAttendance(detectedGuruId) {
        // 1. Isi ID Guru ke semua form
        document.querySelectorAll('.guru_id_input').forEach(el => el.value = detectedGuruId);

        // 2. Ambil Lokasi Perangkat
        if (navigator.geolocation) {
            // Gunakan getCurrentPosition atau watchPosition
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    // Gunakan querySelectorAll karena ada dua form (Masuk & Pulang)
                    document.querySelectorAll('.lat_input').forEach(el => el.value = position.coords.latitude);
                    document.querySelectorAll('.lng_input').forEach(el => el.value = position.coords.longitude);

                    console.log("📍 Lokasi Terkunci:", position.coords.latitude, position.coords.longitude);
                },
                (error) => {
                    alert("Gagal mendapatkan lokasi. Pastikan izin GPS aktif di browser!");
                }, {
                    enableHighAccuracy: true
                } // Tambahkan ini agar lebih akurat untuk geofencing
            );
        }
    }

    function updateRealtimeStatus() {
        const now = new Date();
        const h = now.getHours();
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        const currentTime = `${String(h).padStart(2, '0')}:${m}:${s}`;

        const clockElem = document.getElementById('realtimeClock');
        const labelElem = document.getElementById('attendanceStatusLabel');

        if (clockElem) clockElem.innerText = currentTime;

        if (labelElem) {
            // Bandingkan waktu sekarang dengan aturan dari database
            // String comparison "08:10:00" > "08:00:00" bekerja dengan baik di JS
            if (currentTime > attendanceRules.jam_masuk) {
                labelElem.innerText = "Terlambat";
                labelElem.className = "text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-red-100 text-red-600 border border-red-200";
            } else {
                labelElem.innerText = "Tepat Waktu";
                labelElem.className = "text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-green-100 text-green-600 border border-green-200";
            }
        }
    }

    const handleAttendanceSubmit = async (e) => {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        if (!data.guru_id) {
            Swal.fire({
                icon: 'warning',
                title: 'Wajah belum teridentifikasi',
                text: 'Silakan posisikan wajah Anda dengan benar.',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        Swal.fire({
            title: 'Memproses...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            const response = await fetch("/api/attendance/scan-face", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json' // WAJIB agar Laravel membalas JSON jika terjadi error
                },
                body: JSON.stringify(data)
            });

            // Cek apakah responnya beneran JSON
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                // Jika yang balik malah HTML (DOCTYPE), kita tangkap di sini
                throw new TypeError("Server tidak mengirim JSON. Masalah kemungkinan di Route/Controller.");
            }

            const result = await response.json();

            if (response.ok) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: result.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Absen',
                    text: result.message || 'Terjadi kesalahan.',
                    confirmButtonColor: '#ef4444'
                });
            }
        } catch (error) {
            console.error("Error Detail:", error);
            Swal.fire({
                icon: 'error',
                title: 'Sistem Error',
                text: error.message === "Server tidak mengirim JSON. Masalah kemungkinan di Route/Controller." ?
                    "Server mengirim respon HTML. Pastikan Anda sudah login dan Route benar." : "Gagal terhubung ke server.",
            });
        }
    };

    // Pasang event listener ke kedua form
    document.getElementById('formMasuk').addEventListener('submit', handleAttendanceSubmit);
    document.getElementById('formPulang').addEventListener('submit', handleAttendanceSubmit);

    setInterval(updateRealtimeStatus, 1000);

    // --- CORE LOGIC ---
    async function loadModelsAndData() {
        // Set tampilan awal saat mulai loading
        statusDot.className = "w-2 h-2 rounded-full bg-yellow-500 animate-pulse";
        statusText.innerText = "Syncing...";
        faceStatus.classList.remove('opacity-0'); // Munculkan bar status

        const modelPath = "{{ asset('models') }}";
        try {
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(modelPath),
                faceapi.nets.faceLandmark68Net.loadFromUri(modelPath),
                faceapi.nets.faceRecognitionNet.loadFromUri(modelPath)
            ]);

            const res = await fetch('/api/face-profiles');
            const profiles = await res.json();

            const labeledDescriptors = profiles.map(profile => {
                // Simpan nama ke dalam Map berdasarkan guru_id
                userNamesMap[profile.guru_id.toString()] = profile.name;

                let data = profile.face_descriptor;
                while (typeof data === 'string') data = JSON.parse(data);
                const desc = new Float32Array(l2Normalize(Object.values(data)));
                return new faceapi.LabeledFaceDescriptors(profile.guru_id.toString(), [desc]);
            }).filter(d => d !== null);

            if (labeledDescriptors.length > 0) {
                faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.5);
                isModelsLoaded = true; // Tandai sudah siap

                // Setelah siap, ubah ke status awal (No Face)
                statusDot.className = "w-2 h-2 rounded-full bg-red-500 animate-pulse";
                statusText.innerText = "No Face";
                console.log("Matcher Ready");
            }
        } catch (e) {
            statusText.innerText = "Sync Error";
            statusDot.className = "w-2 h-2 rounded-full bg-red-700";
            console.error("Data Load Error:", e);
        }
    }

    async function sendAttendanceRequest(detectedGuruId, type = 'hadir') {
        try {
            const response = await fetch("/api/attendance/scan-face", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    // 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                alert(result.message); // Tampilkan sukses (Ganti dengan Toast/Modal agar lebih cantik)
                location.reload(); // Refresh untuk reset scanner
            } else {
                alert(result.message); // Tampilkan pesan error (Misal: Belum waktunya pulang)
            }
        } catch (error) {
            console.error("Error sending attendance:", error);
        }
    }

    async function startDetection() {
        // 1. Pastikan video sudah siap
        if (video.readyState !== 4) {
            video.addEventListener('loadeddata', startDetection);
            return;
        }

        const displaySize = {
            width: video.offsetWidth,
            height: video.offsetHeight
        };

        faceapi.matchDimensions(canvas, displaySize);

        scanInterval = setInterval(async () => {
            if (!isScanning || !faceMatcher) return;

            const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({
                    inputSize: 224,
                    scoreThreshold: 0.5
                }))
                .withFaceLandmarks()
                .withFaceDescriptor();

            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (detection) {
                updateStatus(true);
                const resized = faceapi.resizeResults(detection, displaySize);
                const box = resized.detection ? resized.detection.box : resized.box;

                if (box) {
                    drawSimpleFrame(ctx, box.x, box.y, box.width, box.height);
                }

                // --- BAGIAN ANALISA JARAK ---
                const descriptor = new Float32Array(l2Normalize(Array.from(detection.descriptor)));
                const match = faceMatcher.findBestMatch(descriptor);

                // Munculkan di console untuk debug
                // Jika Distance > 0.4, face-api otomatis melabeli 'unknown' karena settingan Matcher kamu
                console.log(`Hasil Scan: ${match.label} | Jarak Kemiripan: ${match.distance.toFixed(3)}`);

                if (match.label !== lastIdentifiedId && !isProcessingIdentification) {
                    identifyUser(match.label); // Izinkan label 'unknown' masuk ke sini
                }
            } else {
                updateStatus(false);
            }
        }, 100);
    }

    async function identifyUser(guruId) {
        if (isProcessingIdentification) return;

        isProcessingIdentification = true;
        lastIdentifiedId = guruId; // Simpan ID terakhir (termasuk 'unknown') agar tidak spam

        const userNameElem = document.getElementById('userName');

        // --- LOGIKA WAJAH TIDAK DIKENAL ---
        if (guruId === 'unknown') {
            userNameElem.innerText = "Wajah Tidak Dikenal";
            userNameElem.classList.add('text-red-600'); // Beri warna merah

            // Kosongkan ID agar form tidak bisa dikirim
            document.querySelectorAll('.guru_id_input').forEach(input => input.value = "");

            placeholderResult.classList.add('hidden');
            userResult.classList.remove('hidden');

            isProcessingIdentification = false;
            return; // Berhenti di sini, jangan lanjut fetch API
        }

        // --- LOGIKA WAJAH TERDAFTAR ---
        userNameElem.innerText = "Mencari...";
        userNameElem.classList.remove('text-red-600');

        try {
            const res = await fetch(`/api/guru-detail/${guruId}`);
            if (res.ok) {
                const result = await res.json();
                const guruData = result.data || result;
                const finalName = guruData.name || (guruData.user ? guruData.user.name : "Nama Tidak Ditemukan");

                userNameElem.innerText = finalName;

                document.querySelectorAll('.guru_id_input').forEach(input => {
                    input.value = guruId;
                });

                prepareAttendance(guruId);

                placeholderResult.classList.add('hidden');
                userResult.classList.remove('hidden');
            }
        } catch (e) {
            console.error("Error Fetch:", e);
            userNameElem.innerText = "Error Memuat Data";
        } finally {
            isProcessingIdentification = false;
        }
    }

    // --- UI HELPERS ---
    function updateStatus(found) {
        if (!isModelsLoaded) return; // Jangan ubah status jika masih loading model

        if (found) {
            statusDot.className = "w-2 h-2 rounded-full animate-pulse bg-green-500";
            statusText.innerText = "Face Detected";
        } else {
            statusDot.className = "w-2 h-2 rounded-full animate-pulse bg-red-500";
            statusText.innerText = "No Face";
        }
    }

    function drawSimpleFrame(ctx, x, y, w, h) {
        const s = 40; // Panjang sudut
        const p = 10; // Padding agar kotak tidak terlalu mepet wajah

        ctx.strokeStyle = '#00D1FF';
        ctx.lineWidth = 6;
        ctx.lineCap = 'round';

        // Shadow untuk efek Glow agar terlihat di background gelap/terang
        ctx.shadowBlur = 15;
        ctx.shadowColor = '#00D1FF';

        // Gambar 4 sudut saja agar estetik seperti di registrasi
        // Top Left
        ctx.beginPath();
        ctx.moveTo(x - p, y - p + s);
        ctx.lineTo(x - p, y - p);
        ctx.lineTo(x - p + s, y - p);
        ctx.stroke();

        // Top Right
        ctx.beginPath();
        ctx.moveTo(x + w + p - s, y - p);
        ctx.lineTo(x + w + p, y - p);
        ctx.lineTo(x + w + p, y - p + s);
        ctx.stroke();

        // Bottom Left
        ctx.beginPath();
        ctx.moveTo(x - p, y + h + p - s);
        ctx.lineTo(x - p, y + h + p);
        ctx.lineTo(x - p + s, y + h + p);
        ctx.stroke();

        // Bottom Right
        ctx.beginPath();
        ctx.moveTo(x + w + p - s, y + h + p);
        ctx.lineTo(x + w + p, y + h + p);
        ctx.lineTo(x + w + p, y + h + p - s);
        ctx.stroke();

        ctx.shadowBlur = 0; // Reset shadow
    }

    // --- EVENT LISTENERS ---
    document.getElementById('startScan').addEventListener('click', async () => {
        if (!isModelsLoaded) {
            alert("Sistem sedang sinkronisasi data, mohon tunggu sebentar...");
            return;
        }
        if (!isScanning) {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: 640,
                        height: 480
                    }
                });
                video.srcObject = stream;
                isScanning = true;
                document.getElementById('btnText').innerText = "Stop Scan";
                document.getElementById('lottie-container').classList.add('hidden');
                faceStatus.classList.remove('opacity-0');
                startDetection();
            } catch (e) {
                alert("Kamera Error");
            }
        } else {
            stopCamera();
        }
    });

    function stopCamera() {
        if (stream) stream.getTracks().forEach(t => t.stop());
        clearInterval(scanInterval);
        isScanning = false;
        lastIdentifiedId = null;
        document.getElementById('btnText').innerText = "Start Scan";
        video.srcObject = null;
        faceStatus.classList.add('opacity-0');
        placeholderResult.classList.remove('hidden');
        userResult.classList.add('hidden');
        canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
    }

    window.addEventListener('load', loadModelsAndData);
    document.getElementById('retryScan').addEventListener('click', () => location.reload());
</script>
@endpush