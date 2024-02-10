@extends('layouts.app')

@section('title', 'Ban for ' . $target)
@section('content')
    @can('viewAny', \App\Models\Ban::class)
        <div class="mb-2">
            <a href="{{ route('admin.bans.list') }}" class="btn btn-primary">
                {{__('admin.bans.edit.back-to-all')}}
            </a>
        </div>
    @endcan

    @component('components.errors')
    @endcomponent

    <div class="card mb-4">
        <h5 class="card-header">{{__('admin.bans.edit.details')}}</h5>
        <div class="card-body">
            <table class="table">
                <tbody>
                <tr>
                    <th>{{__('admin.bans.id')}}</th>
                    <td>{{ $ban->id }}</td>
                </tr>
                <tr>
                    <th>{{__('admin.bans.target')}}</th>
                    <td>{!! $targetHtml !!}</td>
                </tr>
                <tr>
                    <th>Wiki</th>
                    <td>
                        @if($ban->wiki)
                            {{ $ban->wiki->display_name }} ({{ $ban->wiki->database_name }})
                        @else
                            {{__('admin.bans.allwiki')}}
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>{{__('admin.bans.reason')}}</th>
                    <td>{{ $ban->reason }}</td>
                </tr>
                <tr>
                    <th>{{__('admin.bans.expires')}}</th>
                    <td>{!! $formattedExpiry !!}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    @can('update', $ban)
        {{ html()->form('POST', route('admin.bans.update', $ban))->open() }}
        {{ html()->token() }}
        <div class="card mb-4">
            <h5 class="card-header">{{__('admin.bans.edit.title')}}</h5>
            <div class="card-body">
                <div class="form-group mb-4">
                    {{__('admin.bans.edit.status')}}
                    <div class="custom-control custom-radio">
                        {{ html()->radio('is_active', old('is_active', $ban->is_active) == 0, 0)->class('custom-control-input')->id('is_active-0') }} {{ html()->label(__('admin.bans.effect.no-effect'), 'is_active-0')->class('custom-control-label') }}
                    </div>

                    <div class="custom-control custom-radio">
                        {{ html()->radio('is_active', old('is_active', $ban->is_active) == 1, 1)->class('custom-control-input')->id('is_active-1') }} {{ html()->label(__('admin.bans.effect.active'), 'is_active-1')->class('custom-control-label') }}
                    </div>
                </div>

                @if(sizeof($wikis) > 1)
                    <div class="form-group">
                        {{ html()->label('Wiki', 'wiki_id') }}
                        {{ html()->select('wiki_id', $wikis, old('wiki_id', $ban->wiki_id))->class('form-control') }}
                    </div>
                @else
                    {{ html()->hidden('wiki_id', array_keys($wikis)[0]) }}
                @endif

                <div class="form-group mb-4">
                    {{ html()->label(__('admin.bans.reason'), 'reason') }}
                    {{ html()->text('reason', old('reason', $ban->reason))->class('form-control') }}
                    <p class="small">
                        {{__('admin.bans.hints.show-to-user')}}
                    </p>
                </div>

                <div class="form-group mb-4">
                    {{ html()->label(__('admin.bans.expires'), 'expiry') }}
                    {{ html()->text('expiry', old('expiry', $formOldExpiry))->class('form-control') }}
                    <p class="small">
                        {{__('admin.bans.hints.expires')}}
                    </p>
                </div>

                <div class="form-group mb-4">
                    {{ html()->label(__('admin.bans.reason-change'), 'update_reason') }}
                    {{ html()->input('text', 'update_reason', old('update_reason'))->class('form-control' . ($errors->has('update_reason') ? ' is-invalid' : '')) }}

                    @error('update_reason')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                @can('oversight', $ban)
                    <hr/>
                    <div class="form-group mb-2">
                        {{__('admin.bans.visibility')}}
                        <div class="custom-control custom-radio">
                            {{ html()->radio('is_protected', old('is_protected', $ban->is_protected) == 0, 0)->class('custom-control-input')->id('is_protected-0') }} {{ html()->label(__('admin.bans.visibility.admins'), 'is_protected-0')->class('custom-control-label') }}
                        </div>

                        <div class="custom-control custom-radio">
                            {{ html()->radio('is_protected', old('is_protected', $ban->is_protected) == 1, 1)->class('custom-control-input')->id('is_protected-1') }} {{ html()->label(__('admin.bans.visibility.oversight'), 'is_protected-1')->class('custom-control-label') }}
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        {{ html()->label(__('admin.bans.visibility.change-reason'), 'os_reason') }}
                        {{ html()->text('os_reason', old('os_reason'))->class('form-control') }}
                        <p class="small">
                            {{__('admin.bans.hints.oversight')}}
                        </p>
                    </div>
                @endcan

                <hr/>
                {{ html()->submit(__('generic.submit'))->class('btn btn-primary') }}
            </div>
        </div>
        {{ html()->form()->close() }}
    @endcan

    @component('components.logs', ['logs' => $ban->logs])
    @endcomponent
@endsection
