@extends('layouts.app')
@section('content')
    @component('components.errors')
    @endcomponent

    <div class="card">
        <h5 class="card-header">Add ban</h5>
        <div class="card-body">
            {{ html()->form('POST', route('admin.bans.create'))->open() }}
            {{ html()->token() }}

            <div class="form-group mb-4">
                {{ html()->label('Ban target', 'target') }}
                {{ html()->text('target', old('target'))->class('form-control') }}
                <p class="small">
                    For bans based on on-wiki blocks: exact on-wiki block target. For blocks based on appealing user's
                    IP address: IP address or CIDR range.
                </p>
            </div>

            <div class="form-group mb-4">
                {{ html()->label('Ban reason', 'reason') }}
                {{ html()->text('reason', old('reason'))->class('form-control') }}
                <p class="small">
                    This will be shown to the user.
                </p>
            </div>

            <div class="form-group mb-4">
                {{ html()->label('Expiration', 'expiry') }}
                {{ html()->text('expiry', old('expiry'))->class('form-control') }}
                <p class="small">
                    Leave empty or as 'indefinite' for a permanent ban.
                </p>
            </div>

            <div class="form-group mb-4">
                {{ html()->label('Comment', 'comment') }}
                {{ html()->text('comment', old('reason'))->class('form-control') }}
                <p class="small">
                    This is private and can only be seen by users who can see the ban's details.
                </p>
            </div>

            @if(sizeof($wikis) > 1)
                <div class="form-group">
                    {{ html()->label('Wiki', 'wiki_id') }}
                    {{ html()->select('wiki_id', $wikis, old('wiki_id'))->class('form-control') }}
                </div>
            @else
                {{ html()->hidden('wiki_id', array_keys($wikis)[0]) }}
            @endif

        @can('oversight', [\App\Models\Ban::class, array_keys($wikis)])
                <div class="form-group mb-4">
                    Ban target visibility
                    <div class="custom-control custom-radio">
                        {{ html()->radio('is_protected', old('is_protected', 0) === 0, 0)->class('custom-control-input')->id('is_protected-0') }} {{ html()->label('Ban target is visible to all users who can view ban list', 'is_protected-0')->class('custom-control-label') }}
                    </div>

                    <div class="custom-control custom-radio">
                        {{ html()->radio('is_protected', old('is_protected') === 1, 1)->class('custom-control-input')->id('is_protected-1') }} {{ html()->label('Ban target is oversighted', 'is_protected-1')->class('custom-control-label') }}
                    </div>
                </div>

                <div class="form-group mb-4">
                    {{ html()->label('Oversight reason', 'os_reason') }}
                    {{ html()->text('os_reason', old('os_reason'))->class('form-control') }}
                    <p class="small">
                        Reason for restricting the ban target visibility to oversighters only. This can only be seen by
                        functionaries.
                    </p>
                </div>
            @endif

            @error('duplicate')
                <div class="alert alert-warning form-group mb-4">
                    <div class="custom-control custom-checkbox">
                        {{ html()->checkbox('duplicate', old('duplicate') === 1, 1)->class('custom-control-input')->id('duplicate') }} {{ html()->label('Add duplicate ban', 'duplicate')->class('custom-control-label') }}
                    </div>
                </div>
            @enderror

            <button type="submit" class="btn btn-success">Submit</button>
            {{ html()->form()->close() }}
        </div>
    </div>
@endsection
