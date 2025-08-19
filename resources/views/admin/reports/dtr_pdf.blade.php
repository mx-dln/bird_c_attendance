<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CS Form 48 - DTR</title>
    <style>
        @page { size: legal portrait; margin: 12mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 11px; color: #000; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mb-4 { margin-bottom: 16px; }
        .mb-2 { margin-bottom: 8px; }
        .mt-2 { margin-top: 8px; }
        .small { font-size: 10px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #000; padding: 4px 6px; }
        .table th { font-weight: bold; text-align: center; }
        .no-border { border: 0 !important; }
        .header { border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 8px; }
        .title { font-size: 14px; font-weight: bold; }
        .subtitle { font-size: 12px; }
        .grid { display: table; width: 100%; }
        .row { display: table-row; }
        .col { display: table-cell; vertical-align: top; }
        .w-50 { width: 50%; }
        .w-33 { width: 33.33%; }
        .signature-line { border-bottom: 1px solid #000; height: 24px; }
        .section-label { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header text-center">
        <div class="small">Civil Service Form No. 48</div>
        <div class="title">DAILY TIME RECORD</div>
        <div class="subtitle">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</div>
    </div>

    <div class="grid mb-4">
        <div class="row">
            <div class="col w-50">
                <div><span class="section-label">Name:</span> <strong>{{ $employee->name }}</strong></div>
                @if(!empty($employee->position))
                <div><span class="section-label">Position:</span> {{ $employee->position }}</div>
                @endif
                <div><span class="section-label">Employee ID:</span> {{ $employee->id }}</div>
            </div>
            <div class="col w-50 text-right">
                <div class="small">Official hours for arrival and departure</div>
                <div class="small">as prescribed</div>
            </div>
        </div>
    </div>

    <table class="table mb-4">
        <thead>
            <tr>
                <th style="width: 80px;">Date</th>
                <th>IN AM</th>
                <th>OUT AM</th>
                <th>IN PM</th>
                <th>OUT PM</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
        @foreach($days as $d)
            <tr>
                <td class="text-center">{{ \Carbon\Carbon::parse($d['date'])->format('m/d') }}</td>
                <td class="text-center">{{ $d['in_am'] ? date('h:i A', strtotime($d['in_am'])) : '' }}</td>
                <td class="text-center">{{ $d['out_am'] ? date('h:i A', strtotime($d['out_am'])) : '' }}</td>
                <td class="text-center">{{ $d['in_pm'] ? date('h:i A', strtotime($d['in_pm'])) : '' }}</td>
                <td class="text-center">{{ $d['out_pm'] ? date('h:i A', strtotime($d['out_pm'])) : '' }}</td>
                <td></td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="mb-4">
        <div class="section-label">I CERTIFY on my honor</div>
        <div class="small">that the above is a true and correct report of the hours of work performed, record of which was made daily at the time of arrival and departure from office.</div>
    </div>

    <div class="grid mb-4">
        <div class="row">
            <div class="col w-50 text-center">
                <div class="signature-line"></div>
                <div class="small">Employee's Signature</div>
            </div>
            <div class="col w-50 text-center">
                <div class="signature-line"></div>
                <div class="small">Date</div>
            </div>
        </div>
    </div>

    <div class="mb-2 section-label">Verified as to the prescribed office hours:</div>

    <div class="grid">
        <div class="row">
            <div class="col w-50 text-center">
                <div class="signature-line"></div>
                <div class="small">Immediate Supervisor</div>
            </div>
            <div class="col w-50 text-center">
                <div class="signature-line"></div>
                <div class="small">Head of Office</div>
            </div>
        </div>
    </div>
</body>
</html>
