@extends('layouts.kiosk')

@section('content')
<style>
    /* Sinkronisasi Mirror agar kotak deteksi pas dengan wajah */
    .camera-container {
        position: relative;
        width: 100%;
        height: 480px;
        background: #1a1a1a;
        overflow: hidden;
        border-radius: 28px;
    }

    #video,
    #overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scaleX(-1);
        /* Efek Cermin */
    }

    #overlay {
        z-index: 20;
        pointer-events: none;
    }
</style>

<div class="min-h-screen transition-colors duration-300 bg-[#f8fafc] text-gray-900 px-6 py-8 lg:px-20 lg:py-6">

    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-semibold tracking-wide text-gray-800">
            Registrasi Wajah: <span class="text-indigo-600">{{ $guru->name }}</span>
        </h1>
        <a href="javascript:history.back()" class="text-sm font-medium text-gray-500 hover:text-gray-700 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        {{-- LEFT SIDE — CAMERA --}}
        <div class="lg:col-span-2">
            <div class="camera-container border border-gray-200">

                {{-- STATUS INDICATOR --}}
                <div id="faceStatus" class="absolute top-5 left-5 z-30 px-4 py-2 rounded-full bg-black/40 backdrop-blur-md border border-white/20 flex items-center gap-2 transition-all opacity-0">
                    <div id="statusDot" class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                    <span id="statusText" class="text-white text-xs font-bold uppercase tracking-wider">No Face</span>
                </div>

                {{-- LOTTIE LOADER --}}
                <div id="lottie-container" class="absolute inset-0 z-50 flex flex-col items-center justify-center bg-gray-50">
                    <script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.8.11/dist/dotlottie-wc.js" type="module"></script>
                    <dotlottie-wc src="https://lottie.host/4719cea0-85a0-409e-bc93-f59e889784da/CHk1BOCF5U.lottie" style="width: 200px; height: 200px" autoplay loop></dotlottie-wc>
                    <p class="text-gray-500 font-medium -mt-5">Memuat Model AI...</p>
                </div>

                <video id="video" autoplay muted playsinline></video>
                <canvas id="overlay"></canvas>
            </div>

            <div class="flex gap-4 mt-8">
                <button id="captureBtn" disabled class="flex items-center gap-3 bg-indigo-600 hover:bg-indigo-700 px-7 py-3.5 rounded-xl font-medium text-white transition shadow-lg shadow-indigo-100 disabled:bg-gray-400">
                    <span id="btnText">Daftarkan Wajah</span>
                </button>
            </div>
        </div>

        {{-- RIGHT SIDE — INFO PANEL --}}
        <div class="flex flex-col gap-6">
            <div class="rounded-[28px] p-6 bg-white border border-gray-200 shadow-sm">
                <h2 class="text-sm font-bold mb-5 text-gray-400 uppercase tracking-widest">Data Guru</h2>
                <div class="flex items-center gap-4 p-4 bg-indigo-50 rounded-2xl border border-indigo-100">
                    <div class="w-12 h-12 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-lg">
                        {{ substr($guru->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="font-bold text-gray-900">{{ $guru->name }}</p>
                        <p class="text-xs text-indigo-600 font-medium">ID: {{ $guru->id }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let isScanning = true;
    const video = document.getElementById('video');
    const canvas = document.getElementById('overlay');
    const captureBtn = document.getElementById('captureBtn');
    const faceStatus = document.getElementById('faceStatus');
    const statusDot = document.getElementById('statusDot');
    const statusText = document.getElementById('statusText');

    function l2Normalize(vector) {
        const sum = vector.reduce((acc, val) => acc + (val * val), 0);
        const norm = Math.sqrt(sum);
        return vector.map(v => v / norm);
    }

    async function init() {
        try {
            const modelPath = "{{ asset('models') }}";
            console.log("Loading models...");

            await faceapi.nets.tinyFaceDetector.loadFromUri(modelPath);
            await faceapi.nets.faceLandmark68Net.loadFromUri(modelPath);
            await faceapi.nets.faceRecognitionNet.loadFromUri(modelPath);

            console.log("Models loaded successfully");
            startVideo();
        } catch (err) {
            console.error("Model Load Error:", err);
            alert("Gagal memuat model AI. Cek folder public/models.");
        }
    }

    async function startVideo() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: 640,
                    height: 480,
                    facingMode: "user"
                }
            });
            video.srcObject = stream;
            video.onloadedmetadata = () => {
                document.getElementById('lottie-container').classList.add('hidden');
                faceStatus.classList.remove('opacity-0');
                startFaceDetectionLoop();
            };
        } catch (err) {
            alert("Gagal akses kamera.");
        }
    }

    function startFaceDetectionLoop() {
        const displaySize = {
            width: video.clientWidth,
            height: video.clientHeight
        };
        faceapi.matchDimensions(canvas, displaySize);

        setInterval(async () => {
            if (!isScanning || !faceapi.nets.tinyFaceDetector.params) return;

            const detection = await faceapi.detectSingleFace(
                video,
                new faceapi.TinyFaceDetectorOptions({
                    inputSize: 224,
                    scoreThreshold: 0.3
                })
            );

            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (detection) {
                statusDot.classList.replace('bg-red-500', 'bg-green-500');
                statusText.innerText = "FACE DETECTED";
                captureBtn.disabled = false;

                const resized = faceapi.resizeResults(detection, displaySize);
                const {
                    x,
                    y,
                    width,
                    height
                } = resized.box;
                drawBox(ctx, x, y, width, height);
            } else {
                statusDot.classList.replace('bg-green-500', 'bg-red-500');
                statusText.innerText = "NO FACE";
                captureBtn.disabled = true;
            }
        }, 100);
    }

    function drawBox(ctx, x, y, width, height) {
        ctx.strokeStyle = '#4f46e5';
        ctx.lineWidth = 3;
        ctx.strokeRect(x, y, width, height);
    }

    captureBtn.addEventListener('click', async () => {
        isScanning = false;
        captureBtn.innerText = "Memproses...";
        captureBtn.disabled = true;

        const fullDetection = await faceapi.detectSingleFace(video,
                new faceapi.TinyFaceDetectorOptions({
                    inputSize: 512
                }))
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!fullDetection) {
            alert("Wajah tidak jelas, silakan coba lagi.");
            isScanning = true;
            captureBtn.innerText = "Daftarkan Wajah";
            captureBtn.disabled = false;
            return;
        }

        const normalized = l2Normalize(Array.from(fullDetection.descriptor));

        // PERBAIKAN FETCH:
        fetch("/api/attendance/store-face", { // Menggunakan absolute path "/"
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json", // WAJIB: Agar Laravel merespon JSON jika error
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    // Ganti dari $guru->id menjadi $guru->guru_id (asumsi variabel $guru punya field guru_id)
                    guru_id: "{{ $guru->guru_id }}",
                    face_descriptor: normalized
                })
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw data; // Lempar error jika status bukan 200-299
                return data;
            })
            .then(data => {
                alert(data.message || "Registrasi Wajah Berhasil!");
                window.location.href = "/admin/guru"; // Pastikan route ini benar
            })
            .catch(err => {
                console.error("Error dari server:", err);
                // Jika server mengirim pesan error, tampilkan
                alert("Gagal: " + (err.message || "Terjadi kesalahan sistem"));
                isScanning = true;
                captureBtn.innerText = "Daftarkan Wajah";
                captureBtn.disabled = false;
            });
    });

    init();
</script>
@endpush