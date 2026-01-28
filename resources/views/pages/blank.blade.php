@extends('layouts.kiosk')

@section('content')
<div class="min-h-screen transition-colors duration-300
            bg-gradient-to-b from-slate-100 to-slate-200
            dark:from-[#0b1220] dark:to-[#0e1627]
            text-gray-900 dark:text-white
            px-6 py-8 lg:px-20 lg:py-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-semibold tracking-wide text-gray-800 dark:text-white/90">
            Absensi Face Recognition
        </h1>
    </div>

    {{-- CONTENT --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

        {{-- LEFT SIDE — CAMERA --}}
        <div class="lg:col-span-2">

            <div class="relative rounded-[28px] overflow-hidden
                        bg-white dark:bg-[#0a0f1c]
                        shadow-xl dark:shadow-[0_20px_60px_rgba(0,0,0,0.6)]
                        border border-gray-200 dark:border-white/5
                        transition-colors duration-300">

                <video id="video" autoplay muted playsinline
                    class="w-full h-[480px] object-cover scale-x-[-1]"></video>

                <canvas id="overlay"
                    class="absolute inset-0 scale-x-[-1]"></canvas>

            </div>

            {{-- BUTTONS --}}
            <div class="flex gap-5 mt-8">
                <button id="startScan"
                    class="flex items-center gap-3
                           bg-gradient-to-r from-indigo-500 to-indigo-600
                           hover:opacity-90
                           px-7 py-3.5 rounded-xl font-medium text-white
                           shadow-lg shadow-indigo-600/30 transition">

                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z" />
                    </svg>
                    Start Scan
                </button>

                <button id="retryScan"
                    class="flex items-center gap-3
                           bg-gray-200 hover:bg-gray-300
                           dark:bg-[#121a2b] dark:hover:bg-[#162033]
                           px-7 py-3.5 rounded-xl font-medium
                           border border-gray-300 dark:border-white/10
                           text-gray-800 dark:text-white/90 transition">

                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M4 4v6h6M20 20v-6h-6M20 4a8 8 0 0 0-14 2M4 20a8 8 0 0 0 14-2" />
                    </svg>
                    Retry
                </button>
            </div>

        </div>

        {{-- RIGHT SIDE — RESULT PANEL --}}
        <div class="rounded-[28px] p-7 backdrop-blur-xl
                    bg-white/70 dark:bg-[#111827]/70
                    border border-gray-200 dark:border-white/5
                    shadow-lg dark:shadow-[0_10px_40px_rgba(0,0,0,0.5)]
                    max-h-[480px] flex flex-col
                    transition-colors duration-300">

            <h2 class="text-lg font-semibold mb-6 text-gray-700 dark:text-white/80">
                Recognition Result
            </h2>

            {{-- USER PREVIEW --}}
            <div class="flex items-center gap-5">

                <div id="resultPhoto"
                    class="w-24 h-24 rounded-2xl
                            bg-gray-200 dark:bg-[#1b2435]"></div>

                <div class="flex-1 space-y-3">
                    <div class="h-4 w-40 rounded-full bg-gray-300 dark:bg-[#1b2435]"></div>
                    <div class="h-4 w-28 rounded-full bg-gray-300 dark:bg-[#1b2435]"></div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
    window.addEventListener('load', async () => {
        const video = document.getElementById('video');
        const canvas = document.getElementById('overlay');

        // LOAD MODELS
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
            faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
            faceapi.nets.faceRecognitionNet.loadFromUri('/models'),
        ]);

        console.log("FaceAPI models loaded");

        // START CAMERA
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user'
                },
                audio: false
            });
            video.srcObject = stream;
            video.onloadedmetadata = () => video.play();
        } catch (error) {
            alert('Kamera tidak tersedia atau izin ditolak');
            return;
        }

        video.addEventListener('loadeddata', () => {
            const displaySize = {
                width: video.videoWidth,
                height: video.videoHeight
            };

            canvas.width = displaySize.width;
            canvas.height = displaySize.height;

            const rect = video.getBoundingClientRect();

            canvas.width = rect.width;
            canvas.height = rect.height;

            canvas.style.width = rect.width + "px";
            canvas.style.height = rect.height + "px";


            faceapi.matchDimensions(canvas, displaySize);

            setInterval(async () => {
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

                faceapi.draw.drawDetections(canvas, resizedDetections);
                faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);

            }, 400);
        });

        document.getElementById('retryScan').addEventListener('click', () => {
            location.reload();
        });
    });
</script>

@endpush