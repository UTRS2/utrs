@extends('layouts.app')
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
                                @if($cu)
                                <th>IP</th>
                                <th style="width: 30%; word-wrap:break-word">UA</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                @if($log->protected)
                                    <tr class="table-danger">
                                @else
                                <tr>
                                @endif
                                    <td>{{ $log->id }}</td>
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
                                    @if($cu)
                                    <td>{{ $log->ip }}</td>
                                    <td>{{ $log->ua }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-center">
                        {{ $logs->links() }}
                    </div>
                @else
                    <p>No logs found</p>
                @endif
            </div>
        </div>
    </div>
@endsection