<div id="custom_settings" class="dropdown">
    <button class="dropbtn"><i class="fa fa-gear"></i></button>
    <div class="dropdown-content col-sm-6 col-md-4 col-lg-2 col-xl-2">
        <div class="row panel panel-default">
            <div class="panel-heading">
                <h4>Custom Settings</h4>
            </div>
            <ul>
                <li id="full_view_wrap">
                    <div class="form-group row">
                        {!! Form::checkbox('full_view', '', false, ['id' => 'full_view', 'autocomplete' => 'off']) !!}
                        <div class="btn-group col-sm-12 col-md-12 col-lg-12 col-xs-12" style="position: relative;">
                            <label for="full_view" class="settings_label_box btn btn-default">
                                <span class="glyphicon glyphicon-ok text-danger"></span>
                                <span></span>
                            </label>
                            <label for="full_view" class="settings_label btn btn-default col-sm-9 col-md-9 col-lg-9 col-xs-9">
                                Full View
                            </label>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="form-group row">
                        {!! Form::checkbox('show_yaxis', '', false, ['id' => 'show_yaxis', 'autocomplete' => 'off']) !!}
                        <div class="btn-group col-sm-12 col-md-12 col-lg-12 col-xs-12" style="position: relative;">
                            <label for="show_yaxis" class="settings_label_box btn btn-default">
                                <span class="glyphicon glyphicon-ok text-danger"></span>
                                <span></span>
                            </label>
                            <label for="show_yaxis" class="settings_label btn btn-default col-sm-9 col-md-9 col-lg-9 col-xs-9">
                                Show Y-Axis Label
                            </label>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="form-group row">
                        {!! Form::checkbox('shared_tooltip', '', false, ['id' => 'shared_tooltip', 'autocomplete' => 'off']) !!}
                        <div class="btn-group col-sm-12 col-md-12 col-lg-12 col-xs-12" style="position: relative;">
                            <label for="shared_tooltip" class="settings_label_box btn btn-default">
                                <span class="glyphicon glyphicon-ok text-danger"></span>
                                <span></span>
                            </label>
                            <label for="shared_tooltip" class="settings_label btn btn-default col-sm-9 col-md-9 col-lg-9 col-xs-9">
                                Shared tooltip
                            </label>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="form-group row">
                        {!! Form::checkbox('follow_pointer', '', false, ['id' => 'follow_pointer', 'autocomplete' => 'off']) !!}
                        <div class="btn-group col-sm-12 col-md-12 col-lg-12 col-xs-12" style="position: relative;">
                            <label for="follow_pointer" class="settings_label_box btn btn-default">
                                <span class="glyphicon glyphicon-ok text-danger"></span>
                                <span></span>
                            </label>
                            <label for="follow_pointer" class="settings_label btn btn-default col-sm-9 col-md-9 col-lg-9 col-xs-9">
                                Follow Pointer
                            </label>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="form-group row">
                        {!! Form::checkbox('show_legend', '', true, ['id' => 'show_legend', 'autocomplete' => 'off']) !!}
                        <div class="btn-group col-sm-12 col-md-12 col-lg-12 col-xs-12" style="position: relative;">
                            <label for="show_legend" class="settings_label_box btn btn-default">
                                <span class="glyphicon glyphicon-ok text-danger"></span>
                                <span></span>
                            </label>
                            <label for="show_legend" class="settings_label btn btn-default col-sm-9 col-md-9 col-lg-9 col-xs-9">
                                Show Legends
                            </label>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="form-group row">
                        {!! Form::checkbox('show_export', '', false, ['id' => 'show_export', 'autocomplete' => 'off']) !!}
                        <div class="btn-group col-sm-12 col-md-12 col-lg-12 col-xs-12" style="position: relative;">
                            <label for="show_export" class="settings_label_box btn btn-default">
                                <span class="glyphicon glyphicon-ok text-danger"></span>
                                <span></span>
                            </label>
                            <label for="show_export" class="settings_label btn btn-default col-sm-9 col-md-9 col-lg-9 col-xs-9">
                                Show Export
                            </label>
                        </div>
                    </div>
                </li>
            </ul>
            <div class="panel-footer">
                {!! Form::button('Redraw', ['id' => 'redraw', 'class' => 'btn btn-primary']) !!}
            </div>
        </div>
    </div>
</div>
