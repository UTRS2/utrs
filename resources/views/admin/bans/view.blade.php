@extends('layouts.app')

@section('title', 'Ban for ' . $target)
@section('content')
    @can('viewAny', \App\Ban::class)
        <div class="mb-2">
            <a href="{{ route('admin.bans.list') }}" class="btn btn-primary">
                Back to ban list
            </a>
        </div>
    @endcan

    @component('components.errors')
    @endcomponent

    <div class="card mb-4">
        <h5 class="card-header">Ban details</h5>
        <div class="card-body">
            <table class="table">
                <tbody>
                <tr>
                    <th>ID</th>
                    <td>{{ $ban->id }}</td>
                </tr>
                <tr>
                    <th>Target</th>
                    <td>{!! $targetHtml !!}</td>
                </tr>
                <tr>
                    <th>Target oversighted</th>
                    <td>{{ $ban->is_protected ? 'Yes' : 'No' }}</td>
                </tr>
                <tr>
                    <th>Reason</th>
                    <td>{{ $ban->reason }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    @component('components.logs', ['logs' => $ban->logs])
    @endcomponent
@endsection
