@include('layouts.welcome')
  
    <div class="flex-center position-ref full-height">
        @if (Route::has('login'))
        <div class="top-right links color-white">
            @auth
            <a href="{{ url('/admin') }}">Admin</a>
            @else
            <a style="color: white" href="{{ route('login') }}">Login</a>

            @if (Route::has('register'))
            <a href="{{ route('register') }}">Register</a>
            @endif
            @endauth
        </div>
        @endif

        <style>
            .welcome-bg {
                position: absolute; inset: 0; z-index: 0;
                background: url('{{ asset('assets/images/birdc.png') }}') center/cover no-repeat fixed;
                opacity: 0.2;
                filter: grayscale(10%) saturate(120%);
                pointer-events: none;
            }
            .welcome-overlay {
                position: absolute; inset: 0; z-index: 1;
                background: radial-gradient(1200px 600px at 50% 0%, rgba(0,0,0,0.2), rgba(0,0,0,0.5));
                pointer-events: none; /* allow clicks through overlay */
            }
            .welcome-content { position: relative; z-index: 2; }
            .card-pro {
                background: rgba(31,36,51,0.92);
                backdrop-filter: blur(6px);
                border: 1px solid rgba(255,255,255,0.08);
                border-radius: 16px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.35);
            }
            .card-pro .card-header {
                background: linear-gradient(180deg, rgba(35,42,59,0.95), rgba(35,42,59,0.75));
                border-bottom-color: rgba(255,255,255,0.08);
                border-top-left-radius: 16px; border-top-right-radius: 16px;
            }
            .brand-title {
                font-weight: 700; letter-spacing: 0.3px;
                font-size: 1.75rem; /* keep heading modest so it doesn't dominate */
                white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            }
        </style>

        <div class="welcome-bg"></div>
        <div class="welcome-overlay"></div>

        <div class="content welcome-content" style="max-width: 880px; margin: 0 auto;">
            <div class="title m-b-md text-center">
                <h1 class="brand-title" style="color:#fff; margin-bottom: 0;">QR Attendance</h1>
                <div class="text-muted" style="font-size: 0.95rem;">Scan your company QR to record time in/out</div>
            </div>

            <div class="card card-pro">
                <div class="card-header">
                    <h5 class="mb-0" style="color:#fff;">QR Attendance Scanner</h5>
                    <small class="text-muted">Point the camera at your QR code to time in/out</small>
                </div>
                <div class="card-body" style="color:#fff;">
                    <div id="qr-reader" style="width: clamp(320px, 90vw, 820px); aspect-ratio: 4/3; margin: 0 auto; border-radius: 12px; overflow: hidden;"></div>
                    <div id="qr-result" class="mt-3" style="min-height:24px"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/minified/html5-qrcode.min.js"></script>
    <script>
        (function(){
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Robust loader: try local then multiple CDNs if Html5Qrcode isn't ready
            function withHtml5Qrcode(callback){
                if (window.Html5Qrcode) return callback();
                const sources = [
                    '{{ asset('js/html5-qrcode.min.js') }}',
                    'https://unpkg.com/html5-qrcode@2.3.8/minified/html5-qrcode.min.js',
                    'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/minified/html5-qrcode.min.js',
                    'https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js'
                ];
                const tryNext = (i) => {
                    if (i >= sources.length) {
                        document.getElementById('qr-result').textContent = 'Failed to load QR library. Check network/CDN access.';
                        return;
                    }
                    const s = document.createElement('script');
                    s.src = sources[i];
                    s.async = true;
                    s.onload = () => callback();
                    s.onerror = () => tryNext(i+1);
                    document.head.appendChild(s);
                };
                tryNext(0);
            }

            function postCheckIn(payload){
                return fetch("{{ route('qr.checkin.public') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify(payload)
                }).then(r => r.json().then(data => ({ ok: r.ok, status: r.status, data }))).catch(err => ({ ok:false, status:0, data:{ message: err?.message || 'Network error' } }));
            }

            function parseScan(text){
                // Accept JSON like {"emp_id": 123} or {"emp_code":"MPD-..."}
                // Or raw emp_code string
                try {
                    const obj = JSON.parse(text);
                    if (obj.emp_id) return { emp_id: parseInt(obj.emp_id, 10) };
                    if (obj.emp_code) return { emp_code: String(obj.emp_code) };
                } catch(_){}
                // fallback: treat as emp_code plain text
                return { emp_code: text.trim() };
            }

            let qrScanner = null;
            let currentCameraId = null;

            async function startScanner(){
                const container = document.getElementById('qr-reader');
                const cw = container ? container.clientWidth : 800;
                // qrbox ~70% of container width, capped
                const qrboxSize = Math.round(Math.min(Math.max(260, cw * 0.72), 520));
                const config = { fps: 15, qrbox: qrboxSize };
                if (!qrScanner) qrScanner = new Html5Qrcode("qr-reader");
                if (!currentCameraId) {
                    const cams = await Html5Qrcode.getCameras();
                    if (!cams.length) throw new Error('No cameras found');
                    const preferred = cams.find(c => /back|rear|environment/i.test(c.label))?.id;
                    currentCameraId = preferred || cams[0].id;
                }
                await qrScanner.start(currentCameraId, config, onScanSuccess, onScanFailure);
            }

            async function stopScanner(){
                if (qrScanner) { try { await qrScanner.stop(); await qrScanner.clear(); } catch(_){} }
            }

            const onScanSuccess = async (decodedText, decodedResult) => {
                await stopScanner();
                document.getElementById('qr-result').textContent = 'Processing...';
                const payload = parseScan(decodedText);
                const res = await postCheckIn(payload);
                if (res.ok) {
                    const emp = res.data && res.data.employee ? res.data.employee : {};
                    const session = res.data && res.data.session ? res.data.session : '';
                    const time = res.data && res.data.time ? res.data.time : '';
                    const name = emp.name || emp.emp_code || ('ID #' + (emp.id||''));
                    document.getElementById('qr-result').innerHTML = `<div class="alert alert-success" role="alert">${session ? (`<strong>${session}</strong> `) : ''}recorded for <strong>${name}</strong> at <strong>${time}</strong>.</div>`;
                    setTimeout(() => { document.getElementById('qr-result').textContent=''; startScanner().catch(()=>{}); }, 10000);
                } else {
                    const msg = res.data && res.data.message ? res.data.message : 'Failed';
                    document.getElementById('qr-result').textContent = msg;
                    const retry = res.data && res.data.retry_after_seconds ? parseInt(res.data.retry_after_seconds, 10) : 0;
                    if (retry) setTimeout(() => startScanner().catch(()=>{}), retry*1000);
                }
            };

            const onScanFailure = (error) => {};

            // Auto-start scanner when library is ready
            withHtml5Qrcode(() => startScanner().catch(e => {
                document.getElementById('qr-result').textContent = e.message || 'Failed to start camera';
            }));
        })();
    </script>

