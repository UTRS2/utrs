@extends('layouts.app')
@section('content')
    <div class="card">
        <h5 class="card-header">{{ $title }}</h5>
        <div class="card-body">
            @if(isset($createlink))
                <p>
                    <a href="{{ $createlink }}" class="btn btn-primary">{{ $createtext }}</a>
                </p>
            @endif

            @if(isset($caption) && strlen($caption) > 0)
                <p>
                    <i>{{ $caption }}</i>
                </p>
            @endif

            <table class="table">
                <thead>
                <tr>
                    @foreach($tableheaders as $tableheader)
                        <th scope="col">{{ $tableheader }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>
                        @if ($admin)
                            <a href="{{ route('admin.users.update', $user->id) }}" class="btn btn-primary">{{ $user->id }}</a>
                        @else
                            {{ $user->id }}
                        @endif
                        </td>
                        <td style="vertical-align: middle;">{{ $user->username }}</td>
                        <td style="vertical-align: middle;">{{ $user->email ?? 'None'}}</td>
                        <td style="vertical-align: middle;">{{ $user->last_permission_check_at }}</td>
                        <td style="vertical-align: middle;">{{ $user->mediawiki_id }}</td>

                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {{ $users->links() }}
            </div>
        </div>
    </div>
@endsection
