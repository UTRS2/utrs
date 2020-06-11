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
                Ban type
                <div class="custom-control custom-radio">
                    {{ Form::radio('ip', 0, old('ip') === 0, ['class' => 'custom-control-input', 'id' => 'ip-0']) }} {{ Form::label('ip-0', 'Based on wiki block target', ['class' => 'custom-control-label']) }}
                </div>

                <div class="custom-control custom-radio">
                    {{ Form::radio('ip', 1, old('ip') === 1, ['class' => 'custom-control-input', 'id' => 'ip-1']) }} {{ Form::label('ip-1', 'Based on appealing user\'s IP address', ['class' => 'custom-control-label']) }}
                </div>
            </div>

            <div class="form-group">
                {{ Form::label('target', 'Ban target') }}
                {{ Form::text('target', old('target'), ['class' => 'form-control']) }}
                <p class="small">
                    For bans based on on-wiki blocks: exact on-wiki block target. For blocks based on appealing user's IP address: IP address or CIDR range.
                </p>
            </div>

            <div class="form-group">
                {{ Form::label('reason', 'Ban reason') }}
                {{ Form::text('reason', old('reason'), ['class' => 'form-control']) }}
                <p class="small">
                    This will be shown to the user.
                </p>
            </div>

            @can('oversight', \App\Ban::class)
                <div class="form-group mb-4">
                    Ban target visibility
                    <div class="custom-control custom-radio">
                        {{ Form::radio('is_protected', 0, old('is_protected', 0) === 0, ['class' => 'custom-control-input', 'id' => 'is_protected-0']) }} {{ Form::label('is_protected-0', 'Ban target is visible to all users who can view ban list', ['class' => 'custom-control-label']) }}
                    </div>

                    <div class="custom-control custom-radio">
                        {{ Form::radio('is_protected', 1, old('is_protected') === 1, ['class' => 'custom-control-input', 'id' => 'is_protected-1']) }} {{ Form::label('is_protected-1', 'Ban target is oversighted', ['class' => 'custom-control-label']) }}
                    </div>
                </div>
            @endif

            <button type="submit" class="btn btn-success">Submit</button>
            {{ Form::close() }}
        </div>
    </div>
@endsection
