<div class="card">
    <h5 class="card-header">Logs</h5>
    <div class="card-body">
        <table class="table">
            <thead>
            <tr>
                <th scope="col">Acting user</th>
                <th scope="col">Time</th>
                <th scope="col">Details</th>
            </tr>
            </thead>
            <tbody>
            @foreach($logs as $log)
                <tr class="{{ $log->action === 'comment' ? 'bg-success' : '' }}">
                    @if($log->user == 0)
                        <td><i>System</i></td>
                    @else
                        @can('view', $log->userObject)
                            <td><i><a href="{{ route('admin.users.view', $log->userObject) }}">{{ $log->userObject->username }}</a></i></td>
                        @else
                            <td><i>{{ $log->userObject->username }}</i></td>
                        @endcan
                    @endif
                    <td><i>{{ $log->timestamp }}</i></td>
                    @can('view', $log)
                        <td>
                            @if($log->comment!==null)
                                <i>{{ $log->comment }}</i>
                            @else
                                @if(!is_null($log->reason))
                                    <i>Action: {{ $log->action }},
                                        Reason: {{ $log->reason }}</i>
                                @else
                                    <i>Action: {{ $log->action }}</i>
                                @endif
                            @endif

                            @if($log->protected == \App\Log::LOG_PROTECTION_FUNCTIONARY)
                                <br/>
                                <div class="small">Visibility of this comment is restricted to functionaries only.</div>
                            @endif
                        </td>
                    @else
                        <td><i>Access to comment is restricted.</i></td>
                    @endcan
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
