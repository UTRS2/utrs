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
                    <th>Reason</th>
                    <td>{{ $ban->reason }}</td>
                </tr>
                <tr>
                    <th>Expires at</th>
                    <td>{!! $formattedExpiry !!}</td>
                </tr>
                <tr>
                    <th>Ban Operation Mode</th>
                    <td>{{ $ban->ip ? 'IPv4 address' : 'Username or IPv6 address' }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    @can('update', $ban)
        {{ Form::open(['url' => route('admin.bans.update', $ban)]) }}
        {{ Form::token() }}
        <div class="card mb-4">
            <h5 class="card-header">Modify ban options</h5>
            <div class="card-body">
                <div class="form-group mb-4">
                    Active
                    <div class="custom-control custom-radio">
                        {{ Form::radio('is_active', 0, old('is_active', $ban->is_active) == 0, ['class' => 'custom-control-input', 'id' => 'is_active-0']) }} {{ Form::label('is_active-0', 'Ban has no effect', ['class' => 'custom-control-label']) }}
                    </div>

                    <div class="custom-control custom-radio">
                        {{ Form::radio('is_active', 1, old('is_active', $ban->is_active) == 1, ['class' => 'custom-control-input', 'id' => 'is_active-1']) }} {{ Form::label('is_active-1', 'Ban is active', ['class' => 'custom-control-label']) }}
                    </div>
                </div>

                <div class="form-group mb-4">
                    {{ Form::label('reason', 'Ban reason') }}
                    {{ Form::text('reason', old('reason', $ban->reason), ['class' => 'form-control']) }}
                    <p class="small">
                        This will be shown to the user.
                    </p>
                </div>

                <div class="form-group mb-4">
                    {{ Form::label('expiry', 'Expiration') }}
                    {{ Form::text('expiry', old('expiry', $formOldExpiry), ['class' => 'form-control']) }}
                    <p class="small">
                        Leave empty or as 'indefinite' for a permanent ban.
                    </p>
                </div>

                <div class="form-group mb-4">
                    {{ Form::label('update_reason', 'Reason for changes') }}
                    {{ Form::input('text', 'update_reason', old('update_reason'), ['class' => 'form-control' . ($errors->has('update_reason') ? ' is-invalid' : '')]) }}

                    @error('update_reason')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                @can('oversight', $ban)
                    <hr/>
                    <div class="form-group mb-2">
                        Ban target visibility
                        <div class="custom-control custom-radio">
                            {{ Form::radio('is_protected', 0, old('is_protected', $ban->is_protected) == 0, ['class' => 'custom-control-input', 'id' => 'is_protected-0']) }} {{ Form::label('is_protected-0', 'Ban target is visible to all users who can view ban list', ['class' => 'custom-control-label']) }}
                        </div>

                        <div class="custom-control custom-radio">
                            {{ Form::radio('is_protected', 1, old('is_protected', $ban->is_protected) == 1, ['class' => 'custom-control-input', 'id' => 'is_protected-1']) }} {{ Form::label('is_protected-1', 'Ban target is oversighted', ['class' => 'custom-control-label']) }}
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        {{ Form::label('os_reason', 'Visibility change reason') }}
                        {{ Form::text('os_reason', old('os_reason'), ['class' => 'form-control']) }}
                        <p class="small">
                            Reason for restricting the ban target visibility to oversighters only. This can only be seen
                            by functionaries.
                        </p>
                    </div>
                @endcan

                <hr/>
                {{ Form::submit('Save', ['class' => 'btn btn-primary']) }}
            </div>
        </div>
        {{ Form::close() }}
    @endcan

    @component('components.logs', ['logs' => $ban->logs])
    @endcomponent
@endsection
