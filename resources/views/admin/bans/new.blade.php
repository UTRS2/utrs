@extends('layouts.app')
@section('content')
    @component('components.errors')
    @endcomponent

    <div class="card">
        <h5 class="card-header">Add ban</h5>
        <div class="card-body">
            {{ Form::open(['url' => route('admin.bans.create'), 'method' => 'POST']) }}
            {{ Form::token() }}

            <div class="mb-4">
                {{ Form::label('target', 'Ban target', ['class' => 'form-label']) }}
                {{ Form::text('target', old('target'), ['class' => 'form-control']) }}
                <p class="small">
                    For bans based on on-wiki blocks: exact on-wiki block target. For blocks based on appealing user's
                    IP address: IP address or CIDR range.
                </p>
            </div>

            <div class="mb-4">
                {{ Form::label('reason', 'Ban reason', ['class' => 'form-label']) }}
                {{ Form::text('reason', old('reason'), ['class' => 'form-control']) }}
                <p class="small">
                    This will be shown to the user.
                </p>
            </div>

            <div class="mb-4">
                {{ Form::label('expiry', 'Expiration', ['class' => 'form-label']) }}
                {{ Form::text('expiry', old('expiry'), ['class' => 'form-control']) }}
                <p class="small">
                    Leave empty or as 'indefinite' for a permanent ban.
                </p>
            </div>

            <div class="mb-4">
                {{ Form::label('comment', 'Comment', ['class' => 'form-label']) }}
                {{ Form::text('comment', old('reason'), ['class' => 'form-control']) }}
                <p class="small">
                    This is private and can only be seen by users who can see the ban's details.
                </p>
            </div>

            @if(sizeof($wikis) > 1)
                <div class="mb-4">
                    {{ Form::label('wiki_id', 'Wiki', ['class' => 'form-label']) }}
                    {{ Form::select('wiki_id', $wikis, old('wiki_id'), ['class' => 'form-control']) }}
                </div>
            @else
                {{ Form::hidden('wiki_id', array_keys($wikis)[0]) }}
            @endif

        @can('oversight', [\App\Models\Ban::class, array_keys($wikis)])
                <div class="mb-4">
                    Ban target visibility
                    <div class="form-check">
                        {{ Form::radio('is_protected', 0, old('is_protected', 0) === 0, ['class' => 'form-check-input', 'id' => 'is_protected-0']) }} {{ Form::label('is_protected-0', 'Ban target is visible to all users who can view ban list', ['class' => 'form-check-label']) }}
                    </div>

                    <div class="form-check">
                        {{ Form::radio('is_protected', 1, old('is_protected') === 1, ['class' => 'form-check-input', 'id' => 'is_protected-1']) }} {{ Form::label('is_protected-1', 'Ban target is oversighted', ['class' => 'form-check-label']) }}
                    </div>
                </div>

                <div class="mb-4">
                    {{ Form::label('os_reason', 'Oversight reason', ['class' => 'form-label']) }}
                    {{ Form::text('os_reason', old('os_reason'), ['class' => 'form-control']) }}
                    <p class="small">
                        Reason for restricting the ban target visibility to oversighters only. This can only be seen by
                        functionaries.
                    </p>
                </div>
            @endif

            @error('duplicate')
                <div class="alert alert-warning mb-4">
                    <div class="form-checkbox">
                        {{ Form::checkbox('duplicate', 1, old('duplicate') === 1, ['class' => 'form-check-input', 'id' => 'duplicate']) }} {{ Form::label('duplicate', 'Add duplicate ban', ['class' => 'form-check-label']) }}
                    </div>
                </div>
            @enderror

            <button type="submit" class="btn btn-success">Submit</button>
            {{ Form::close() }}
        </div>
    </div>
@endsection
