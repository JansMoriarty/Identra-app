@extends('layouts.kiosk')

@section('content')
<div class="min-h-screen transition-colors duration-300
            bg-[#ffffff]
            text-gray-900 dark:text-gray-800
            px-6 py-8 lg:px-20 lg:py-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-semibold tracking-wide text-gray-800 dark:text-gray-900">
            Absensi Face Recognition
        </h1>
    </div>

    {{-- CONTENT --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

        {{-- LEFT SIDE — CAMERA --}}
        <div class="lg:col-span-2">
            <div class="relative rounded-[28px] overflow-hidden
                dark:bg-gray-50
                border border-gray-200 dark:border-gray-300
                transition-colors duration-300 h-[480px]">

                <div id="lottie-container" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-gray-50">
                    <script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.8.11/dist/dotlottie-wc.js" type="module"></script>
                    <dotlottie-wc
                        src="https://lottie.host/4719cea0-85a0-409e-bc93-f59e889784da/CHk1BOCF5U.lottie"
                        style="width: 300px; height: 300px"
                        autoplay
                        loop>
                    </dotlottie-wc>
                    <!-- <p class="text-gray-400 font-medium -mt-10">Klik "Start Scan" untuk memulai</p> -->
                </div>

                <video id="video" autoplay muted playsinline
                    class="w-full h-full object-cover scale-x-[-1]"></video>

                <canvas id="overlay"
                    class="absolute inset-0 scale-x-[-1] z-20"></canvas>
            </div>

            {{-- SCAN CONTROLS --}}
            <div class="flex gap-4 mt-8">
                <button id="startScan"
                    class="flex items-center gap-3 bg-gradient-to-r from-indigo-500 to-indigo-600 hover:opacity-90 px-7 py-3.5 rounded-xl font-medium text-white transition">
                    <span id="btnIcon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z" />
                        </svg>
                    </span>
                    <span id="btnText">Start Scan</span>
                </button>

                <button id="retryScan"
                    class="flex items-center gap-3 bg-gray-100 hover:bg-gray-200 px-7 py-3.5 rounded-xl font-medium border border-gray-300 text-gray-800 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M4 4v6h6M20 20v-6h-6M20 4a8 8 0 0 0-14 2M4 20a8 8 0 0 0 14-2" />
                    </svg>
                    Retry
                </button>
            </div>
        </div>

        {{-- RIGHT SIDE — INTERACTION PANEL --}}
        <div class="flex flex-col gap-6">

            {{-- RECOGNITION RESULT CARD --}}
            <div class="rounded-[28px] p-6 bg-white border border-gray-200">
                <h2 class="text-sm font-bold mb-5 text-gray-400 uppercase tracking-widest">
                    Scan Result
                </h2>

                {{-- USER FOUND --}}
                <div id="userResult" class="hidden space-y-6">
                    <div class="flex items-center gap-4">
                        <div id="resultPhoto" class="w-16 h-16 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center">
                            <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <p id="userName" class="text-lg font-bold text-gray-900 leading-tight">Mencari...</p>
                            <p class="text-xs text-green-500 font-medium">Wajah Dikenali</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <form action="{{ route('attendances.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="hadir">
                            <input type="hidden" name="guru_id" class="guru_id_input">
                            <button type="submit" class="w-full py-3 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition">Masuk</button>
                        </form>
                        <form action="#" method="POST">
                            @csrf
                            <input type="hidden" name="guru_id" class="guru_id_input">
                            <button type="submit" class="w-full py-3 bg-orange-500 text-white rounded-xl text-sm font-bold hover:bg-orange-600 transition">Pulang</button>
                        </form>
                    </div>
                </div>

                {{-- IDLE STATE --}}
                <div id="placeholderResult" class="py-8 flex flex-col items-center justify-center text-center opacity-60">
                    <div class="w-12 h-12 mb-3 rounded-full border-2 border-dashed border-gray-300 flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M12 4v1m0 11v1m5-10v1m-10 1v1m10 5v1m-10 1v1" />
                        </svg>
                    </div>
                    <p class="text-xs font-medium text-gray-500">Menunggu scan wajah...</p>
                </div>
            </div>

            {{-- MANUAL OVERRIDE CARD --}}
            <div class="rounded-[28px] p-6 bg-white border border-gray-200">
                <h2 class="text-sm font-bold mb-4 text-gray-400 uppercase tracking-widest">
                    Manual Action
                </h2>
                <div class="space-y-3">
                    <a href="{{ route('attendances.manual')}}" class="flex items-center justify-between w-full p-4 bg-gray-50 hover:bg-gray-100 rounded-2xl transition group border border-gray-200 hover:border-green-500/30">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-green-500/10 rounded-lg text-green-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-gray-700">Absen Manual</span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 opacity-0 group-hover:opacity-100 transition-all transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 5l7 7-7 7" />
                        </svg>
                    </a>

                    <a href="#" class="flex items-center justify-between w-full p-4 bg-gray-50 hover:bg-gray-100 rounded-2xl transition group border border-gray-200 hover:border-orange-500/30">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-orange-500/10 rounded-lg text-orange-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-gray-700">Checkout Manual</span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 opacity-0 group-hover:opacity-100 transition-all transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let stream = null;
    let scanInterval = null;
    let isScanning = false;

    // Element Selector
    const video = document.getElementById('video');
    const canvas = document.getElementById('overlay');
    const startBtn = document.getElementById('startScan');
    const btnText = document.getElementById('btnText');
    const btnIcon = document.getElementById('btnIcon');
    const lottieContainer = document.getElementById('lottie-container');

    // 1. LOAD MODELS ON PAGE LOAD
    window.addEventListener('load', async () => {
        try {
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
                faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
                faceapi.nets.faceRecognitionNet.loadFromUri('/models'),
            ]);
            console.log("FaceAPI models loaded");
        } catch (err) {
            console.error("Error loading models:", err);
        }
    });

    // 2. TOGGLE SCAN FUNCTION
    startBtn.addEventListener('click', async () => {
        if (!isScanning) {
            await startCamera();
        } else {
            stopCamera();
        }
    });

    // 3. START CAMERA
    async function startCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                },
                audio: false
            });
            
            video.srcObject = stream;

            // Tunggu video benar-benar termuat sebelum menyembunyikan lottie
            video.onloadedmetadata = () => {
                video.play();
                lottieContainer.classList.add('hidden'); // Sembunyikan Placeholder Lottie
                startDetection();
            };

            isScanning = true;
            btnText.innerText = "End Scan";
            btnIcon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M6 6h12v12H6z"/></svg>`;
            
            // Ubah Warna Button ke Merah
            startBtn.classList.remove('from-indigo-500', 'to-indigo-600');
            startBtn.classList.add('from-red-500', 'to-red-600');

        } catch (error) {
            console.error(error);
            alert('Kamera tidak tersedia atau izin ditolak');
        }
    }

    // 4. STOP CAMERA
    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            video.srcObject = null;
        }
        
        clearInterval(scanInterval);

        // Bersihkan Canvas
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Tampilkan Kembali Lottie
        lottieContainer.classList.remove('hidden');

        isScanning = false;
        btnText.innerText = "Start Scan";
        btnIcon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>`;
        
        // Kembalikan Warna Button ke Indigo
        startBtn.classList.remove('from-red-500', 'to-red-600');
        startBtn.classList.add('from-indigo-500', 'to-indigo-600');
    }

    // 5. DETECTION LOGIC
    function startDetection() {
        const displaySize = {
            width: video.clientWidth,
            height: video.clientHeight
        };
        faceapi.matchDimensions(canvas, displaySize);

        scanInterval = setInterval(async () => {
            if (!isScanning || video.paused || video.ended) return;

            const detections = await faceapi
                .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions({
                    inputSize: 320,
                    scoreThreshold: 0.5
                }))
                .withFaceLandmarks()
                .withFaceDescriptors();

            const resizedDetections = faceapi.resizeResults(detections, displaySize);
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Gambar bounding box dan landmarks
            faceapi.draw.drawDetections(canvas, resizedDetections);
            faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);

            // Di sini Anda bisa menambahkan fetch ke backend untuk membandingkan descriptor wajah
        }, 400);
    }

    // 6. RETRY BUTTON
    document.getElementById('retryScan').addEventListener('click', () => {
        location.reload();
    });
</script>
@endpush