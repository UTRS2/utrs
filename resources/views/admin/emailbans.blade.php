@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-header">
            Email bans
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <!-- add a section where when the ban/unban button is clicked, the form displays and asks for a reason -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Linked appeals</th>
                            <th>Appeal banned?</th>
                            <th>Account banned?</th>
                            <th>Last used</th>
                            <th>Last email sent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($emailBans as $emailBan)
                            <tr>
                                <td>{{ $emailBan->email }}</td>
                                <td>
                                    @if($emailBan->linkedappeals() != NULL || sizeOf($emailBan->linkedappeals()) > 0)
                                        @foreach($emailBan->linkedappeals()->get() as $appeal)
                                            <a href="{{ route('appeal.view', $appeal) }}">Appeal #{{ $appeal->id }}</a><br />
                                        @endforeach
                                    @else
                                        No linked appeals
                                    @endif
                                </td>
                                <td>
                                    {{ $emailBan->appealbanned ? 'Yes' : 'No' }}
                                    &nbsp;&nbsp;<a href="{{ route('admin.emailban.appealreason', $emailBan->id) }}" class="{{ $emailBan->appealbanned ? 'btn btn-success' : 'btn btn-danger' }}">{{ $emailBan->appealbanned ? 'Unban' : 'Ban' }}</a>
                                </td>
                                <td>
                                    {{ $emailBan->accountbanned ? 'Yes' : 'No' }}
                                    &nbsp;&nbsp;<a href="{{ route('admin.emailban.accountreason', $emailBan->id) }}" class="{{ $emailBan->accountbanned ? 'btn btn-success' : 'btn btn-danger' }}">{{ $emailBan->accountbanned ? 'Unban' : 'Ban' }}</a>
                                </td>
                                <td>{{ $emailBan->lastused }}</td>
                                <td>{{ $emailBan->lastemail }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-center">
                    {{ $emailBans->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection