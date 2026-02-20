@extends('layouts.app')
@section('content')
    <div class="card">
        <h5 class="card-header">{{ __('admin.templates.title') }}</h5>
        <div class="card-body">
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">{{ __('admin.templates.id') }}</th>
                    <th scope="col">{{ __('admin.templates.name') }}</th>
                    <th scope="col">Wiki</th>
                </tr>
                </thead>
                <tbody>
                @foreach($templates as $template)
                    @if($template->active == 1)
                        <tr class="table-success">
                    @else
                        <tr class="table-danger">
                    @endif
                        <td>
                            <a href="{{ route('admin.templates.update', $template->id) }}" class="btn btn-primary">{{ $template->id }}</a>
                        </td>
                        <td style="vertical-align: middle;">{{ $template->name }}</td>
                        <td style="vertical-align: middle;">{{ $template->wiki->name }}</td>

                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {{ $templates->links() }}
            </div>
        </div>
    </div>
@endsection
