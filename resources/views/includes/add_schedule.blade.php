<!-- Add -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
              
            </div>
            <h4 class="modal-title"><b>Add Schedule</b></h4>
            <div class="modal-body text-left">
                <form class="form-horizontal" method="POST" action="{{ route('schedule.store') }}">
                    @csrf
                    <div class="form-group">
                        <label for="name" class="col-sm-3 control-label">Name</label>

                        
                            <div class="bootstrap-timepicker">
                                <input type="text" class="form-control timepicker" id="name" name="slug">
                            </div>
                        
                    </div>
                    <div class="form-group">
                        <label for="time_in_am" class="col-sm-3 control-label">Time In AM</label>

                        
                            <div class="bootstrap-timepicker">
                                <input type="time" class="form-control timepicker" id="time_in_am" name="time_in_am" required>
                            </div>
                        
                    </div>
                    <div class="form-group">
                        <label for="time_out_am" class="col-sm-3 control-label">Time Out AM</label>

                        
                            <div class="bootstrap-timepicker">
                                <input type="time" class="form-control timepicker" id="time_out_am" name="time_out_am" required>
                            </div>
                        
                    </div>
                    <div class="form-group">
                        <label for="time_in_pm" class="col-sm-3 control-label">Time In PM</label>

                        
                            <div class="bootstrap-timepicker">
                                <input type="time" class="form-control timepicker" id="time_in_pm" name="time_in_pm" required>
                            </div>
                        
                    </div>
                    <div class="form-group">
                        <label for="time_out_pm" class="col-sm-3 control-label">Time Out PM</label>

                        
                            <div class="bootstrap-timepicker">
                                <input type="time" class="form-control timepicker" id="time_out_pm" name="time_out_pm" required>
                            </div>
                        
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                <button type="submit" class="btn btn-primary btn-flat"><i class="fa fa-save"></i> Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

