@extends('layouts.app')
@section('scripts')
            $(document).ready(function() {
                $('a[data-toggle="modal"]').click(function() {
                    var target = $(this).attr('data-target');
                    $(target).modal('show');
                });
                $('.close').click(function() {
                    $(this).closest('.modal').modal('hide');
                });
            });
@endsection
@section('content')
    <div class="container" style="max-width: none!important">
        <div class="row">
            <div class="col-md-12">
                @if(count($logs) > 0)
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Log ID</th>
                                <th>Log User</th>
                                <th>Related item</th>
                                <th>Action</th>
                                <th style="width: 30%">Reason</th>
                                <th>Log Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                @if($log->protected)
                                    <tr class="table-danger">
                                @else
                                <tr>
                                @endif
                                    <!-- clickable button with the log id that brings up a modal with the ip and ua -->
                                    <td><a href="#" data-toggle="modal" data-target="#log{{ $log->id }}"><button class="btn btn-primary">{{ $log->id }}</button></a></td>
                                    
                                    @if($log->user_id === 0)
                                        <td>System</td>
                                    @elseif ($log->user_id === -1)
                                        <td>Appealing user</td>
                                    @else
                                        <td>{{ $users->where('id',$log->user_id)->first()['username'] }}</td>
                                    @endif
                                    <td>
                                        @if($log->model_type === "App\Models\Appeal")
                                            <a href="{{ route('appeal.view', $log->model_id) }}">Appeal #{{ $log->model_id }}</a>
                                        @elseif($log->model_type === "App\Models\User")
                                            <a href="{{ route('admin.users.view', $log->model_id) }}">User</a>
                                        @elseif($log->model_type === "App\Models\Ban")
                                            <a href="{{ route('admin.bans.view', $log->model_id) }}">Ban #{{ $log->model_id }}</a>
                                        @else
                                            {{ $log->model_type }}: {{ $log->model_id }}
                                        @endif
                                    </td>
                                    <td>{{ $log->action }}</td>
                                    <td>{{ $log->reason }}</td>
                                    <td>{{ $log->timestamp }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-center">
                        {{ $logs->links() }}
                    </div>
                    @foreach($logs as $log)
                        <div class="modal fade bd-example-modal-lg" id="log{{ $log->id }}" tabindex="-1" role="dialog" aria-labelledby="log{{ $log->id }}Label" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header" style="display: flex; justify-content: space-between;">
                                        <h5 class="modal-title" id="log{{ $log->id }}Label">Log ID: {{ $log->id }}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <!-- vertical table with the ip and ua -->
                                        <table class="table table-bordered">
                                            <tr>
                                                <th>IP</th>
                                                <td>{{ $log->ip }}</td>
                                            </tr>
                                            <tr>
                                                <th>User Agent</th>
                                                <td>{{ $log->ua }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p>No logs found</p>
                @endif
            </div>
        </div>
    </div>
@endsection