@extends('layouts.master')

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
        <div class="mb-2">
            <strong>Daily Time Record</strong> — {{ $employee->name }} (ID: {{ $employee->id }})
        </div>
        <div class="d-flex align-items-center">
            <form method="GET" action="{{ route('reports.dtr', ['employee' => $employee->id]) }}" class="form-inline mr-2">
                <label class="mr-2 mb-0">Month</label>
                <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm mr-2" />
                <button type="submit" class="btn btn-sm btn-primary">Go</button>
            </form>
            <a class="btn btn-sm btn-success mr-2" href="{{ route('reports.dtr.pdf', ['employee' => $employee->id, 'month' => $month]) }}">
                Download PDF (CS Form 48)
            </a>
            <a href="{{ route('reports.employees') }}" class="btn btn-sm btn-secondary">Back</a>
        </div>
    </div>
    <div class="card-body">

        {{-- CS Form 48 Layout --}}
        <div class="row">
            @for ($col = 0; $col < 2; $col++)
            <div class="col-md-6 mb-4">
                <div class="text-center">
                    <div class="small">CS Form 48</div>
                    <h5 class="mb-0">DAILY TIME RECORD</h5>
                </div>

                <div class="mt-2">
                    <div><strong>Name:</strong> {{ $employee->name }}</div>
                    <div>For the month of: {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</div>
                    <div>Office Hours (regular days): _______________________</div>
                    <div>Arrival & Departure: _______________________________</div>
                    <div>Saturdays: _________________________________________</div>
                </div>

                <table class="table table-bordered table-sm mt-2">
                    <thead class="text-center">
                        <tr>
                            <th rowspan="2" style="width: 40px;">Date</th>
                            <th colspan="2">A M</th>
                            <th colspan="2">P M</th>
                            <th rowspan="2" style="width: 60px;">Hours</th>
                            <th rowspan="2" style="width: 60px;">Min.</th>
                        </tr>
                        <tr>
                            <th style="width: 60px;">Arrival</th>
                            <th style="width: 60px;">Departure</th>
                            <th style="width: 60px;">Arrival</th>
                            <th style="width: 60px;">Departure</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($day = 1; $day <= 31; $day++)
                            @php
                                $record = $days[$day-1] ?? null;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $day }}</td>
                                <td class="text-center">{{ $record['in_am'] ?? '' }}</td>
                                <td class="text-center">{{ $record['out_am'] ?? '' }}</td>
                                <td class="text-center">{{ $record['in_pm'] ?? '' }}</td>
                                <td class="text-center">{{ $record['out_pm'] ?? '' }}</td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endfor
                        <tr>
                            <td colspan="7" class="text-right"><strong>Total</strong></td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-2">
                    <p class="small">
                        I certify on my honor that the above is true and correct record of the hours of work performed, record of which was made daily at the time of arrival and departure from the office.
                    </p>
                </div>

                <div class="row text-center mt-4">
                    <div class="col-6">
                        <div style="border-top: 1px solid #000;">(Signature)</div>
                    </div>
                    <div class="col-6">
                        <div style="border-top: 1px solid #000;">(In-charge)</div>
                    </div>
                </div>
            </div>
            @endfor
        </div>
    </div>
</div>
@endsection
