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
            <div class="relative rounded-[28px] overflow-hidden bg-black border border-gray-200 shadow-sm h-[480px]">

                {{-- CLEAN STATUS INDICATOR --}}
                <div id="faceStatus" class="absolute top-4 left-4 z-30 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 flex items-center gap-2 transition-all duration-300">
                    <div id="statusDot" class="w-2 h-2 rounded-full bg-yellow-400 animate-pulse"></div>
                    <span id="statusText" class="text-white text-[10px] font-semibold uppercase tracking-wider">Memuat Sistem...</span>
                </div>

                {{-- LOTTIE LOADER (Hidden by default, shown if camera takes time) --}}
                <div id="lottie-container" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-gray-50/90 backdrop-blur-sm transition-opacity duration-500">
                    <script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.8.11/dist/dotlottie-wc.js" type="module"></script>
                    <dotlottie-wc src="https://lottie.host/4719cea0-85a0-409e-bc93-f59e889784da/CHk1BOCF5U.lottie" style="width: 250px; height: 250px" autoplay loop></dotlottie-wc>
                    <p class="text-sm font-medium text-gray-500 mt-2 animate-pulse">Menyiapkan Kamera & AI...</p>
                </div>

                <video id="video" autoplay muted playsinline class="w-full h-full object-cover scale-x-[-1] opacity-0 transition-opacity duration-700"></video>
                <canvas id="overlay" class="absolute inset-0 z-20 scale-x-[-1]"></canvas>
            </div>

            <div class="flex gap-4 mt-6">
                <button id="retryScan" class="flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 w-full px-5 py-3 rounded-xl font-medium border border-gray-300 text-gray-700 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh Scanner
                </button>
            </div>
        </div>

        {{-- RIGHT SIDE — PANEL --}}
        <div class="flex flex-col gap-6">
            <div class="rounded-[28px] p-6 bg-white border min-h-[250px] border-gray-200 shadow-sm relative overflow-hidden">
                <h2 class="text-xs font-bold mb-5 text-gray-400 uppercase tracking-widest">Scan Result</h2>

                <div id="userResult" class="hidden space-y-6 relative z-10 animate-fade-in-up">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center shrink-0">
                            <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="overflow-hidden">
                            <p id="userName" class="text-lg font-bold text-gray-900 leading-tight truncate">Mencari...</p>
                            <div class="flex items-center gap-2 mt-1.5">
                                <p id="realtimeClock" class="text-[11px] text-gray-500 font-mono bg-gray-100 px-2 py-0.5 rounded-md"></p>
                                <span id="attendanceStatusLabel" class="text-[9px] font-bold uppercase px-2 py-0.5 rounded-full"></span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <form id="formMasuk" class="w-full">
                            @csrf
                            <input type="hidden" name="status" value="hadir">
                            <input type="hidden" name="guru_id" class="guru_id_input">
                            <input type="hidden" name="latitude" class="lat_input">
                            <input type="hidden" name="longitude" class="lng_input">
                            <button type="submit" class="w-full py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition shadow-sm hover:shadow-indigo-200">
                                Masuk
                            </button>
                        </form>

                        <form id="formPulang" class="w-full">
                            @csrf
                            <input type="hidden" name="status" value="pulang">
                            <input type="hidden" name="guru_id" class="guru_id_input">
                            <input type="hidden" name="latitude" class="lat_input">
                            <input type="hidden" name="longitude" class="lng_input">
                            <button type="submit" class="w-full py-2.5 bg-orange-500 text-white rounded-xl text-sm font-bold hover:bg-orange-600 transition shadow-sm hover:shadow-orange-200">
                                Pulang
                            </button>
                        </form>
                    </div>
                </div>

                <div id="placeholderResult" class="absolute inset-0 flex flex-col items-center justify-center text-center opacity-60 bg-white z-0">
                    <svg class="w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <p class="text-xs font-medium text-gray-500">Posisikan wajah di kamera...</p>
                </div>
            </div>

            {{-- MANUAL ACTION --}}
            <div class="rounded-[28px] p-6 bg-white border border-gray-200 shadow-sm">
                <h2 class="text-xs font-bold mb-4 text-gray-400 uppercase tracking-widest">Aksi Manual</h2>
                <a href="{{ route('attendances.manual') }}" class="flex items-center justify-between w-full p-4 bg-gray-50 hover:bg-gray-100 rounded-2xl transition group border border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-emerald-50 rounded-lg text-emerald-500 border border-emerald-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-gray-700">Absen Manual</span>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.4s ease-out forwards;
    }
</style>
@endsection

@push('scripts')
<script>
    let stream = null
    let isScanning = false
    let faceMatcher = null
    let lastIdentifiedId = null
    let isProcessingIdentification = false
    let isModelsLoaded = false
    let isCameraReady = false

    let detectorOptions

    let detectionBuffer = []
    let descriptorBuffer = []

    const REQUIRED_FRAMES = 3

    let attendanceRules = {
        jam_masuk: "12:00:00"
    }
    let userNamesMap = {}

    const video = document.getElementById('video')
    const canvas = document.getElementById('overlay')

    const statusDot = document.getElementById('statusDot')
    const statusText = document.getElementById('statusText')

    const userResult = document.getElementById('userResult')
    const placeholderResult = document.getElementById('placeholderResult')
    const lottieContainer = document.getElementById('lottie-container')

    /* -------------------------
    INIT SYSTEM
    ------------------------- */

    window.addEventListener('load', async () => {

        try {

            await syncAttendanceSettings()

            await loadModelsAndData()

            await startCamera()

            lottieContainer.style.opacity = '0'

            setTimeout(() => {
                lottieContainer.classList.add('hidden')
            }, 500)

            video.classList.remove('opacity-0')

        } catch (e) {

            console.error(e)

        }

    })


    /* -------------------------
    CAMERA
    ------------------------- */

    async function startCamera() {

        try {

            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: "user"
                }
            })

            video.srcObject = stream

            return new Promise(resolve => {

                video.onloadedmetadata = () => {

                    isCameraReady = true

                    checkAndStartScan()

                    resolve()

                }

            })

        } catch (e) {

            console.error("Camera Error", e)

            statusText.innerText = "Kamera Tidak Diizinkan"

            statusDot.className = "w-2 h-2 rounded-full bg-red-600"

        }

    }


    /* -------------------------
    LOAD AI MODELS
    ------------------------- */

    async function loadModelsAndData() {

        const modelPath = "{{ asset('models') }}"

        detectorOptions = new faceapi.TinyFaceDetectorOptions({
            inputSize: 128,
            scoreThreshold: 0.5
        })

        try {

            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(modelPath),
                faceapi.nets.faceLandmark68Net.loadFromUri(modelPath),
                faceapi.nets.faceRecognitionNet.loadFromUri(modelPath)
            ])

            const res = await fetch('/api/face-profiles')

            const profiles = await res.json()

            const labeledDescriptors = profiles.map(profile => {

                userNamesMap[profile.guru_id.toString()] = profile.name

                let data = profile.face_descriptor

                while (typeof data === 'string') {
                    data = JSON.parse(data)
                }

                const desc = new Float32Array(
                    l2Normalize(Object.values(data))
                )

                return new faceapi.LabeledFaceDescriptors(
                    profile.guru_id.toString(),
                    [desc]
                )

            })

            faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.45)

            isModelsLoaded = true

            checkAndStartScan()

        } catch (e) {

            console.error("AI Load Error", e)

            statusText.innerText = "AI Gagal Dimuat"

            statusDot.className = "w-2 h-2 rounded-full bg-red-600"

        }

    }


    /* -------------------------
    START SCAN
    ------------------------- */

    function checkAndStartScan() {

        if (isCameraReady && isModelsLoaded && !isScanning) {

            isScanning = true

            statusText.innerText = "Mencari Wajah"

            statusDot.className = "w-2 h-2 rounded-full bg-blue-400 animate-pulse"

            const displaySize = {

                width: video.videoWidth,

                height: video.videoHeight

            }

            faceapi.matchDimensions(canvas, displaySize)

            detectFrame(displaySize)

        }

    }


    /* -------------------------
    FACE DETECTION LOOP
    ------------------------- */

    async function detectFrame(displaySize) {

        if (!isScanning) return

        const detection = await faceapi
            .detectSingleFace(video, detectorOptions)
            .withFaceLandmarks()
            .withFaceDescriptor()

        const ctx = canvas.getContext('2d')

        ctx.clearRect(0, 0, canvas.width, canvas.height)

        if (detection) {

            updateStatus(true)

            const resized = faceapi.resizeResults(detection, displaySize)

            const box = resized.detection.box

            drawCleanFrame(ctx, box.x, box.y, box.width, box.height)

            detectionBuffer.push(box)

            descriptorBuffer.push(detection.descriptor)

            if (detectionBuffer.length > REQUIRED_FRAMES) {

                detectionBuffer.shift()

                descriptorBuffer.shift()

            }

            if (descriptorBuffer.length === REQUIRED_FRAMES) {

                const avgDescriptor = averageDescriptor(descriptorBuffer)

                const match = faceMatcher.findBestMatch(avgDescriptor)

                if (match.label !== lastIdentifiedId && !isProcessingIdentification) {

                    identifyUser(match.label)

                }

            }

        } else {

            updateStatus(false)

            detectionBuffer = []

            descriptorBuffer = []

            if (lastIdentifiedId !== null && !isProcessingIdentification) {

                lastIdentifiedId = null

                resetPanel()

            }

        }

        requestAnimationFrame(() => detectFrame(displaySize))

    }


    /* -------------------------
    DESCRIPTOR AVERAGING
    ------------------------- */

    function averageDescriptor(descArray) {

        const length = descArray[0].length

        const avg = new Float32Array(length)

        for (let i = 0; i < length; i++) {

            let sum = 0

            for (let j = 0; j < descArray.length; j++) {

                sum += descArray[j][i]

            }

            avg[i] = sum / descArray.length

        }

        return avg

    }


    /* -------------------------
    DRAW FRAME
    ------------------------- */

    function drawCleanFrame(ctx, x, y, w, h) {

        const s = 25
        const p = 15

        ctx.strokeStyle = 'rgba(255,255,255,0.9)'

        ctx.lineWidth = 3

        ctx.lineCap = 'round'

        ctx.beginPath()
        ctx.moveTo(x - p, y - p + s)
        ctx.lineTo(x - p, y - p)
        ctx.lineTo(x - p + s, y - p)
        ctx.stroke()

        ctx.beginPath()
        ctx.moveTo(x + w + p - s, y - p)
        ctx.lineTo(x + w + p, y - p)
        ctx.lineTo(x + w + p, y - p + s)
        ctx.stroke()

        ctx.beginPath()
        ctx.moveTo(x - p, y + h + p - s)
        ctx.lineTo(x - p, y + h + p)
        ctx.lineTo(x - p + s, y + h + p)
        ctx.stroke()

        ctx.beginPath()
        ctx.moveTo(x + w + p - s, y + h + p)
        ctx.lineTo(x + w + p, y + h + p)
        ctx.lineTo(x + w + p, y + h + p - s)
        ctx.stroke()

    }


    /* -------------------------
    STATUS
    ------------------------- */

    function updateStatus(found) {

        if (found) {

            statusDot.className = "w-2 h-2 rounded-full bg-emerald-400 animate-pulse"

            statusText.innerText = "Wajah Terdeteksi"

        } else {

            statusDot.className = "w-2 h-2 rounded-full bg-blue-400 animate-pulse"

            statusText.innerText = "Mencari Wajah"

        }

    }


    /* -------------------------
    UTILS
    ------------------------- */

    const l2Normalize = (v) => {

        const norm = Math.sqrt(v.reduce((acc, val) => acc + (val * val), 0))

        return v.map(val => val / norm)

    }
</script>
@endpush