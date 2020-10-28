@extends('layouts.app')
@section('content')
    @component('components.errors')
    @endcomponent

    <div class="card">
        <h5 class="card-header">Add ban</h5>
        <div class="card-body">
            {{ Form::open(['url' => route('admin.bans.create'), 'method' => 'POST']) }}
            {{ Form::token() }}

            <div class="form-group mb-4">
                {{ Form::label('target', 'Ban target') }}
                {{ Form::text('target', old('target'), ['class' => 'form-control']) }}
                <p class="small">
                    For bans based on on-wiki blocks: exact on-wiki block target. For blocks based on appealing user's
                    IP address: IP address or CIDR range.
                </p>
            </div>

            <div class="form-group mb-4">
                {{ Form::label('reason', 'Ban reason') }}
                {{ Form::text('reason', old('reason'), ['class' => 'form-control']) }}
                <p class="small">
                    This will be shown to the user.
                </p>
            </div>

            <div class="form-group mb-4">
                {{ Form::label('expiry', 'Expiration') }}
                {{ Form::text('expiry', old('expiry'), ['class' => 'form-control']) }}
                <p class="small">
                    Leave empty or as 'indefinite' for a permanent ban.
                </p>
            </div>

            <div class="form-group mb-4">
                {{ Form::label('comment', 'Comment') }}
                {{ Form::text('comment', old('reason'), ['class' => 'form-control']) }}
                <p class="small">
                    This is private and can only be seen by users who can see the ban's details.
                </p>
            </div>

            @can('oversight', \App\Models\Ban::class)
                <div class="form-group mb-4">
                    Ban target visibility
                    <div class="custom-control custom-radio">
                        {{ Form::radio('is_protected', 0, old('is_protected', 0) === 0, ['class' => 'custom-control-input', 'id' => 'is_protected-0']) }} {{ Form::label('is_protected-0', 'Ban target is visible to all users who can view ban list', ['class' => 'custom-control-label']) }}
                    </div>

                    <div class="custom-control custom-radio">
                        {{ Form::radio('is_protected', 1, old('is_protected') === 1, ['class' => 'custom-control-input', 'id' => 'is_protected-1']) }} {{ Form::label('is_protected-1', 'Ban target is oversighted', ['class' => 'custom-control-label']) }}
                    </div>
                </div>

                <div class="form-group mb-4">
                    {{ Form::label('os_reason', 'Oversight reason') }}
                    {{ Form::text('os_reason', old('os_reason'), ['class' => 'form-control']) }}
                    <p class="small">
                        Reason for restricting the ban target visibility to oversighters only. This can only be seen by
                        functionaries.
                    </p>
                </div>
            @endif

            @error('duplicate')
                <div class="alert alert-warning form-group mb-4">
                    <div class="custom-control custom-checkbox">
                        {{ Form::checkbox('duplicate', 1, old('duplicate') === 1, ['class' => 'custom-control-input', 'id' => 'duplicate']) }} {{ Form::label('duplicate', 'Add duplicate ban', ['class' => 'custom-control-label']) }}
                    </div>
                </div>
            @enderror

            <button type="submit" class="btn btn-success">Submit</button>
            {{ Form::close() }}
        </div>
    </div>
@endsection
