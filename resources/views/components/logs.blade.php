@if($logs->isNotEmpty())
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
                        @if($log->user === 0)
                            <td><i>System</i></td>
                        @elseif($log->user === -1)
                            <td><i>Appealing user</i></td>
                        @elseif($log->userObject)
                            <td>
                                @can('view', $log->userObject)
                                    <a href="{{ route('admin.users.view', $log->userObject) }}">{{ $log->userObject->username }}</a>
                                @else
                                    {{ $log->userObject->username }}
                                @endcan
                            </td>
                        @else
                            <td>Unknown actor: {{ $log->user }}</td>
                        @endif

                        <td>{{ $log->timestamp }}</td>

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

                                @if($log->protected === \App\Models\LogEntry::LOG_PROTECTION_FUNCTIONARY)
                                    <br/>
                                    <div class="small">Visibility of this log entry is restricted to functionaries only.</div>
                                @endif
                            </td>
                        @else
                            <td><i>Access to this log entry is restricted.</i></td>
                        @endcan
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
