<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Http\Requests\ScheduleEmp;

class ScheduleController extends Controller
{
   
    public function index()
    {
     
        return view('admin.schedule')->with('schedules', Schedule::all());

    }


    public function store(ScheduleEmp $request)
    {
        $request->validated();

        $schedule = new schedule;
        $schedule->slug = $request->slug;
        $schedule->time_in_am = $request->time_in_am;
        $schedule->time_out_am = $request->time_out_am;
        $schedule->time_in_pm = $request->time_in_pm;
        $schedule->time_out_pm = $request->time_out_pm;
        $schedule->save();




        flash()->success('Success','Schedule has been created successfully !');
        return redirect()->route('schedule.index');

    }

    public function update(ScheduleEmp $request, Schedule $schedule)
    {
        $request['time_in_am'] = str_split($request->time_in_am, 5)[0];
        $request['time_out_am'] = str_split($request->time_out_am, 5)[0];
        $request['time_in_pm'] = str_split($request->time_in_pm, 5)[0];
        $request['time_out_pm'] = str_split($request->time_out_pm, 5)[0];

        $request->validated();

        $schedule->slug = $request->slug;
        $schedule->time_in_am = $request->time_in_am;
        $schedule->time_out_am = $request->time_out_am;
        $schedule->time_in_pm = $request->time_in_pm;
        $schedule->time_out_pm = $request->time_out_pm;
        $schedule->save();
        flash()->success('Success','Schedule has been Updated successfully !');
        return redirect()->route('schedule.index');


    }

  
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        flash()->success('Success','Schedule has been deleted successfully !');
        return redirect()->route('schedule.index');
    }
}
