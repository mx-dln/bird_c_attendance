@extends('layouts.master')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto text-center">
            <h3>QR Attendance Scanner</h3>
            <p>Point the camera at an employee's QR code to record attendance.</p>
            <div id="reader" style="width: 420px;" class="mx-auto"></div>
            <div id="result" class="mt-3"></div>
            <button id="retry-loader" type="button" class="btn btn-sm btn-secondary mt-2">Retry loading scanner</button>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    // Load html5-qrcode from local path then multiple CDNs with fallback
    function withHtml5Qrcode(callback) {
        if (window.Html5Qrcode && window.Html5QrcodeScanner) return callback();
        const sources = [
            // Local path (add file to public/js/html5-qrcode.min.js to use this)
            '{{ asset('js/html5-qrcode.min.js') }}',
            'https://unpkg.com/html5-qrcode@2.3.8/minified/html5-qrcode.min.js',
            'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/minified/html5-qrcode.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js'
        ];
        const tryNext = (i) => {
            if (i >= sources.length) {
                document.getElementById('result').innerHTML = '<div class="alert alert-danger">Failed to load QR scanner library from all CDNs. Please check if your network blocks CDNs. I can switch to a local copy if needed.</div>';
                return;
            }
            const existing = document.querySelector(`script[data-h5q="${i}"]`);
            if (existing) return; // prevent duplicates
            const s = document.createElement('script');
            s.src = sources[i];
            s.async = true;
            s.dataset.h5q = String(i);
            s.onload = () => callback();
            s.onerror = () => tryNext(i + 1);
            document.head.appendChild(s);
        };
        tryNext(0);
    }
</script>
<script>
    const postCheckIn = async (emp_id) => {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const res = await fetch('{{ route('attendance.qr.checkin') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ emp_id })
        });
        return res.json();
    };

    function onScanSuccess(decodedText, decodedResult) {
        try {
            let empId = null;
            try {
                const payload = JSON.parse(decodedText);
                empId = payload.emp_id || null;
            } catch (e) {
                // If not JSON, accept plain numeric ID
                if (/^\d+$/.test(decodedText.trim())) {
                    empId = parseInt(decodedText.trim(), 10);
                }
            }
            if (!empId) throw new Error('Invalid QR payload');
            if (window._html5QrCode) {
                window._html5QrCode.stop().then(() => window._html5QrCode.clear()).catch(()=>{});
            }
            postCheckIn(empId).then((data) => {
                document.getElementById('result').innerHTML = `<div class="alert alert-success">${data.message}</div>`;
            }).catch((e) => {
                document.getElementById('result').innerHTML = `<div class="alert alert-danger">${e.message || 'Failed to check-in'}</div>`;
            });
        } catch (e) {
            document.getElementById('result').innerHTML = `<div class="alert alert-warning">Invalid QR code</div>`;
        }
    }

    function onScanFailure(error) {
        // suppress logs for steady scanning
    }

    // Prefer direct camera start with rear camera on mobile
    async function initScanner() {
        const targetId = 'reader';
        const html5QrCode = new Html5Qrcode(targetId);
        window._html5QrCode = html5QrCode;
        const config = { fps: 10, qrbox: 250 };

        // Check secure context – required on mobile browsers for camera access
        if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
            document.getElementById('result').innerHTML = '<div class="alert alert-warning">Camera requires HTTPS on mobile. Please use https:// or test via localhost.</div>';
        }

        try {
            const cameras = await Html5Qrcode.getCameras();
            if (cameras && cameras.length) {
                // Try environment-facing camera if available
                const constraints = { facingMode: { exact: 'environment' } };
                await html5QrCode.start({ facingMode: constraints.facingMode }, config, onScanSuccess, onScanFailure)
                    .catch(async () => {
                        // Fallback to first camera if exact environment not available
                        const cameraId = cameras.find(c => /back|rear|environment/i.test(c.label))?.id || cameras[0].id;
                        await html5QrCode.start(cameraId, config, onScanSuccess, onScanFailure);
                    });
            } else {
                // Fallback to UI scanner (lets user pick a camera)
                const html5QrcodeScanner = new Html5QrcodeScanner(targetId, config, false);
                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
            }
        } catch (err) {
            let msg = 'Unable to access camera. ';
            if (err && (err.name === 'NotAllowedError' || err.name === 'NotReadableError')) {
                msg += 'Please allow camera permission in your browser.';
            } else if (err && err.name === 'NotFoundError') {
                msg += 'No camera found on this device.';
            } else if (window.isSecureContext === false) {
                msg += 'This page is not in a secure context. Use HTTPS or localhost.';
            } else {
                msg += 'Error: ' + (err.message || String(err));
            }
            document.getElementById('result').innerHTML = `<div class="alert alert-danger">${msg}</div>`;
        }
    }

    // Defer init until library is available
    const startIfReady = () => withHtml5Qrcode(() => { initScanner(); });
    startIfReady();

    // Retry button to reload the library and init again
    document.getElementById('retry-loader').addEventListener('click', () => {
        document.getElementById('result').innerHTML = '<div class="alert alert-info">Retrying to load scanner library...</div>';
        startIfReady();
    });
</script>
@endsection
