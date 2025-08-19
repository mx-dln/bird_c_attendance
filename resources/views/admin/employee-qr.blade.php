@extends('layouts.master')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto text-center">
            <h3>Employee QR Code</h3>
            <p><strong>Name:</strong> {{ $employee->name }}</p>
            <p><strong>Email:</strong> {{ $employee->email }}</p>
            <div id="qrcode" class="d-flex justify-content-center my-3"></div>
            <p>Scan this QR code at the scanner to check in.</p>
            <a href="{{ route('attendance.qr.scan') }}" class="btn btn-primary">Open Scanner</a>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    (function(){
        const payload = JSON.stringify({ emp_id: {{ (int) $employee->id }} });
        new QRCode(document.getElementById('qrcode'), {
            text: payload,
            width: 256,
            height: 256,
            colorDark : '#000000',
            colorLight : '#ffffff',
            correctLevel : QRCode.CorrectLevel.H
        });
    })();
</script>
@endsection
