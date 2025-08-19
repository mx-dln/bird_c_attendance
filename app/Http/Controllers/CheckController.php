<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Leave;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class CheckController extends Controller
{
    public function index()
    {
        // Redirect Checks page to QR Scanner view
        return view('admin.qr-scan');
    }

    public function CheckStore(Request $request)
    {
        if (isset($request->attd)) {
            foreach ($request->attd as $keys => $values) {
                foreach ($values as $key => $value) {
                    if ($employee = Employee::whereId(request('emp_id'))->first()) {
                        if (
                            !Attendance::whereAttendance_date($keys)
                                ->whereEmp_id($key)
                                ->whereType(0)
                                ->first()
                        ) {
                            $data = new Attendance();
                            
                            $data->emp_id = $key;
                            $emp_req = Employee::whereId($data->emp_id)->first();
                            // Use scheduled AM time for default attendance_time
                            $data->attendance_time = date('H:i:s', strtotime($emp_req->schedules->first()->time_in_am));
                            $data->attendance_date = $keys;
                            
                            // $emps = date('H:i:s', strtotime($employee->schedules->first()->time_in_am));
                            // if (!($emps >= $data->attendance_time)) {
                            //     $data->status = 0;
                           
                            // }
                            $data->save();
                        }
                    }
                }
            }
        }
        if (isset($request->leave)) {
            foreach ($request->leave as $keys => $values) {
                foreach ($values as $key => $value) {
                    if ($employee = Employee::whereId(request('emp_id'))->first()) {
                        if (
                            !Leave::whereLeave_date($keys)
                                ->whereEmp_id($key)
                                ->whereType(1)
                                ->first()
                        ) {
                            $data = new Leave();
                            $data->emp_id = $key;
                            $emp_req = Employee::whereId($data->emp_id)->first();
                            // Use scheduled PM time for default leave_time
                            $data->leave_time = $emp_req->schedules->first()->time_out_pm;
                            $data->leave_date = $keys;
                            // if ($employee->schedules->first()->time_out <= $data->leave_time) {
                            //     $data->status = 1;
                                
                            // }
                            // 
                            $data->save();
                        }
                    }
                }
            }
        }
        flash()->success('Success', 'You have successfully submited the attendance !');
        return back();
    }
    public function sheetReport()
    {

    return view('admin.sheet-report')->with(['employees' => Employee::all()]);
    }

    public function monthlyDtr(Request $request, Employee $employee)
    {
        $monthParam = $request->input('month'); // format: YYYY-MM
        $month = $monthParam ? Carbon::parse($monthParam . '-01') : Carbon::now()->startOfMonth();
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $days = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $ymd = $date->format('Y-m-d');
            // Prefer session-aware records
            $dayAtts = Attendance::where('emp_id', $employee->id)
                ->whereDate('attendance_date', $ymd)
                ->orderBy('attendance_time', 'asc')
                ->get();

            $getBySession = function ($s) use ($dayAtts) {
                return optional($dayAtts->firstWhere('session', $s))->attendance_time;
            };

            $inAm = $getBySession(0);
            $outAm = $getBySession(1);
            $inPm = $getBySession(2);
            $outPm = $getBySession(3);

            // Legacy fallback when session is null: map type 0=>IN (AM/PM by time), type 1=>OUT (AM/PM by time)
            if (!$inAm || !$outPm || !$outAm || !$inPm) {
                $legacyIns = $dayAtts->where('session', null)->where('type', 0)->values();
                $legacyOuts = $dayAtts->where('session', null)->where('type', 1)->values();
                foreach ($legacyIns as $rec) {
                    $hour = (int)date('H', strtotime($rec->attendance_time));
                    if ($hour < 12 && !$inAm) $inAm = $rec->attendance_time;
                    if ($hour >= 12 && !$inPm) $inPm = $rec->attendance_time;
                }
                foreach ($legacyOuts as $rec) {
                    $hour = (int)date('H', strtotime($rec->attendance_time));
                    if ($hour < 12 && !$outAm) $outAm = $rec->attendance_time;
                    if ($hour >= 12 && !$outPm) $outPm = $rec->attendance_time;
                }
                // Also check Leave as potential PM OUT
                if (!$outPm) {
                    $leave = Leave::where('emp_id', $employee->id)
                        ->whereDate('leave_date', $ymd)
                        ->orderBy('leave_time', 'desc')
                        ->first();
                    if ($leave) $outPm = $leave->leave_time;
                }
            }

            $days[] = [
                'date' => $ymd,
                'in_am' => $inAm,
                'out_am' => $outAm,
                'in_pm' => $inPm,
                'out_pm' => $outPm,
            ];
        }

        return view('admin.reports.dtr', [
            'employee' => $employee,
            'month' => $start->format('Y-m'),
            'days' => $days,
        ]);
    }

    public function monthlyDtrPdf(Request $request, Employee $employee)
    {
        $monthParam = $request->input('month'); // format: YYYY-MM
        $month = $monthParam ? Carbon::parse($monthParam . '-01') : Carbon::now()->startOfMonth();
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $days = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $ymd = $date->format('Y-m-d');
            $dayAtts = Attendance::where('emp_id', $employee->id)
                ->whereDate('attendance_date', $ymd)
                ->orderBy('attendance_time', 'asc')
                ->get();

            $getBySession = function ($s) use ($dayAtts) {
                return optional($dayAtts->firstWhere('session', $s))->attendance_time;
            };

            $inAm = $getBySession(0);
            $outAm = $getBySession(1);
            $inPm = $getBySession(2);
            $outPm = $getBySession(3);

            if (!$inAm || !$outPm || !$outAm || !$inPm) {
                $legacyIns = $dayAtts->where('session', null)->where('type', 0)->values();
                $legacyOuts = $dayAtts->where('session', null)->where('type', 1)->values();
                foreach ($legacyIns as $rec) {
                    $hour = (int)date('H', strtotime($rec->attendance_time));
                    if ($hour < 12 && !$inAm) $inAm = $rec->attendance_time;
                    if ($hour >= 12 && !$inPm) $inPm = $rec->attendance_time;
                }
                foreach ($legacyOuts as $rec) {
                    $hour = (int)date('H', strtotime($rec->attendance_time));
                    if ($hour < 12 && !$outAm) $outAm = $rec->attendance_time;
                    if ($hour >= 12 && !$outPm) $outPm = $rec->attendance_time;
                }
                if (!$outPm) {
                    $leave = Leave::where('emp_id', $employee->id)
                        ->whereDate('leave_date', $ymd)
                        ->orderBy('leave_time', 'desc')
                        ->first();
                    if ($leave) $outPm = $leave->leave_time;
                }
            }

            $days[] = [
                'date' => $ymd,
                'in_am' => $inAm,
                'out_am' => $outAm,
                'in_pm' => $inPm,
                'out_pm' => $outPm,
            ];
        }

        $pdf = Pdf::loadView('admin.reports.dtr_pdf', [
            'employee' => $employee,
            'month' => $start->format('Y-m'),
            'days' => $days,
        ])->setPaper('legal', 'portrait');

        $filename = 'DTR_CS_Form_48_' . $employee->id . '_' . $start->format('Y_m') . '.pdf';
        return $pdf->download($filename);
    }
}
