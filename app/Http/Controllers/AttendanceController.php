<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\Employee;
use App\Models\Latetime;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\AttendanceEmp;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{   
    //show attendance 
    public function index()
    {  
        return view('admin.attendance')->with(['attendances' => Attendance::all()]);
    }

    //show late times
    public function indexLatetime()
    {
        $latetimes = Latetime::with(['employee.schedules'])
            ->orderByDesc('latetime_date')
            ->get();
        return view('admin.latetime')->with(['latetimes' => $latetimes]);
    }

    

    // public static function lateTime(Employee $employee)
    // {
    //     $current_t = new DateTime(date('H:i:s'));
    //     $start_t = new DateTime($employee->schedules->first()->time_in);
    //     $difference = $start_t->diff($current_t)->format('%H:%I:%S');

    //     $latetime = new Latetime();
    //     $latetime->emp_id = $employee->id;
    //     $latetime->duration = $difference;
    //     $latetime->latetime_date = date('Y-m-d');
    //     $latetime->save();
    // }

    public static function lateTimeDevice($att_dateTime, Employee $employee)
    {
        $attendance_time = new DateTime($att_dateTime);
        $checkin = new DateTime($employee->schedules->first()->time_in_am);
        $difference = $checkin->diff($attendance_time)->format('%H:%I:%S');

        $latetime = new Latetime();
        $latetime->emp_id = $employee->id;
        $latetime->duration = $difference;
        $latetime->latetime_date = date('Y-m-d', strtotime($att_dateTime));
        $latetime->save();
    }

    // Handle QR check-in (public): accepts either emp_id (numeric) or emp_code (alphanumeric)
    public function qrCheckIn(Request $request)
    {
        $request->validate([
            'emp_id' => 'nullable|integer|exists:employees,id',
            'emp_code' => 'nullable|string|exists:employees,emp_code',
        ]);

        if (!$request->filled('emp_id') && !$request->filled('emp_code')) {
            return response()->json(['ok' => false, 'message' => 'Missing employee identifier'], 422);
        }

        // Resolve employee by emp_code first (if provided), else by emp_id
        if ($request->filled('emp_code')) {
            $employee = Employee::where('emp_code', $request->input('emp_code'))->first();
            if (!$employee) {
                return response()->json(['ok' => false, 'message' => 'Employee not found'], 404);
            }
        } else {
            $employee = Employee::find((int) $request->input('emp_id'));
            if (!$employee) {
                return response()->json(['ok' => false, 'message' => 'Employee not found'], 404);
            }
        }

        $empId = (int) $employee->id;
        $today = date('Y-m-d');

        $nowTime = date('H:i:s');
        $isAm = (int)date('H') < 12;

        // Fetch today's sessions
        $todaySessions = Attendance::where('emp_id', $empId)
            ->whereDate('attendance_date', $today)
            ->orderBy('attendance_time')
            ->get();
        $has = function($session) use ($todaySessions){
            return $todaySessions->firstWhere('session', $session) !== null;
        };

        // Decide next session: 0=AM_IN,1=AM_OUT,2=PM_IN,3=PM_OUT
        $nextSession = null;
        if ($isAm) {
            if (!$has(0)) {
                $nextSession = 0; // AM_IN
            } elseif (!$has(1)) {
                $nextSession = 1; // AM_OUT
            } elseif (!$has(2)) {
                $nextSession = 2; // PM_IN (allow early PM in if AM complete)
            } elseif (!$has(3)) {
                $nextSession = 3; // PM_OUT
            }
        } else { // PM
            if (!$has(2)) {
                $nextSession = 2; // PM_IN
            } elseif (!$has(3)) {
                $nextSession = 3; // PM_OUT
            } elseif (!$has(0)) {
                $nextSession = 0; // fallback if AM not recorded
            } elseif (!$has(1)) {
                $nextSession = 1; // fallback if AM_OUT missing
            }
        }

        if ($nextSession === null) {
            return response()->json([
                'ok' => true,
                'message' => 'All sessions recorded for today',
            ]);
        }

        // Enforce 60-second cooldown before OUT after corresponding IN
        if (in_array($nextSession, [1, 3])) { // OUT sessions
            $inSession = $nextSession === 1 ? 0 : 2; // matching IN
            $inRecord = $todaySessions->firstWhere('session', $inSession);
            if ($inRecord) {
                $inTs = strtotime($today . ' ' . $inRecord->attendance_time);
                $nowTs = strtotime($today . ' ' . $nowTime);
                $diff = $nowTs - $inTs;
                if ($diff < 60) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'Please wait at least 1 minute before timing out.',
                        'retry_after_seconds' => 60 - $diff,
                    ], 429);
                }
            }
        }

        $attendance = new Attendance();
        $attendance->emp_id = $empId;
        $attendance->attendance_time = $nowTime;
        $attendance->attendance_date = $today;
        $attendance->session = $nextSession;
        // Maintain legacy type for compatibility: 0 for IN, 1 for OUT
        $attendance->type = in_array($nextSession, [0,2]) ? 0 : 1;
        $attendance->status = 1;
        $attendance->save();

        $labels = ['AM IN','AM OUT','PM IN','PM OUT'];
        $message = $labels[$nextSession] . ' recorded';

        return response()->json([
            'ok' => true,
            'message' => $message,
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name ?? null,
                'emp_code' => $employee->emp_code ?? null,
                'email' => $employee->email ?? null,
            ],
            'session' => $labels[$nextSession],
            'time' => $nowTime,
            'date' => $today,
        ]);
    }
}
