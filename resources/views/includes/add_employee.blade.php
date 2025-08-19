<!-- Add -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>

            </div>

            <h4 class="modal-title"><b>Add Employee</b></h4>
            <div class="modal-body">

                <div class="card-body text-left">

                    <form method="POST" action="{{ route('employees.store') }}">
                        @csrf
                        <div class="form-group">
                            <label for="emp_code">Employee ID (alphanumeric, optional)</label>
                            <input type="text" class="form-control" id="emp_code" name="emp_code"
                                   placeholder="e.g., MPD-0128-272" pattern="^[A-Za-z0-9]+(?:-[A-Za-z0-9]+)*$" />
                            <small class="text-muted">Format like MPD-0128-272. Leave blank if you don't want to set one.</small>
                        </div>

                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" placeholder="Enter Employee Name" id="name"
                                name="name" required />
                        </div>
                        <div class="form-group">
                            <label for="position">Position</label>
                            <input type="text" class="form-control" placeholder="Enter Employee Name" id="position"
                                name="position" required />
                        </div>


                        <div class="form-group">
                            <label for="email" class="col-sm-3 control-label">Email</label>


                            <input type="email" class="form-control" id="email" name="email">

                        </div>
                        <div class="form-group">
                            <label for="schedule" class="col-sm-3 control-label">Schedule</label>


                            <select class="form-control" id="schedule" name="schedule" required>
                                <option value="" selected>- Select -</option>
                                @foreach($schedules as $schedule)
                                    <option value="{{$schedule->slug}}">{{$schedule->slug}} -> AM {{$schedule->time_in_am}} - {{$schedule->time_out_am}}, PM {{$schedule->time_in_pm}} - {{$schedule->time_out_pm}}</option>
                                @endforeach

                            </select>

                        </div>

                        <div class="form-group">
                            <div>
                                <button type="submit" class="btn btn-primary waves-effect waves-light">
                                    Submit
                                </button>
                                <button type="reset" class="btn btn-secondary waves-effect m-l-5" data-dismiss="modal">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>


        </div>

    </div>
</div>
</div>