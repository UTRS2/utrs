@extends('layouts.app')
@section('content')
    <div class="card">
        <h5 class="card-header">Supported wikis</h5>
        <div class="card-body">
            <p>
                This listing shows all wikis that UTRS currently supports.
            </p>

            <table class="table">
                <thead>
                <tr>
                    <th scope="col">Internal ID</th>
                    <th scope="col">Database name</th>
                    <th scope="col">Display name</th>
                    <th scope="col">Appeals open</th>
                </tr>
                </thead>
                <tbody>
                @foreach($wikis as $wiki)
                    @php /** @var \App\Models\Wiki $wiki */ @endphp
                    <tr>
                        <td>{{ $wiki->id }}</td>
                        <td>{{ $wiki->database_name }}</td>
                        <td>{{ $wiki->display_name }}</td>
                        <td>{{ $wiki->is_accepting_appeals ? 'Yes' : 'No' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
