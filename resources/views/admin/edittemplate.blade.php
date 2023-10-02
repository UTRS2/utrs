@extends('layouts.app')
@section('content')
    @component('components.errors')
    @endcomponent

    <div class="card">
        <div class="card-header">
            Edit template
        </div>
        <div class="card-body">
            {{ html()->form('POST', route('admin.templates.update', $template))->open() }}
            {{ html()->token() }}

            <div class="form-group">
                {{ html()->label('Template name', 'name') }}
                {{ html()->text('name', old('name', $template->name))->class('form-control') }}
            </div>

            <div class="form-group">
                {{ html()->label('What should the template say?', 'template') }}
                {{ html()->textarea('template', old('template', $template->template))->class('form-control h-25')->rows('15') }}
            </div>

            <div class="form-group">
                {{ html()->label('Default status after replying:', "default_status-" . $template->id) }}
                {{ html()->select('default_status', \App\Models\Appeal::REPLY_STATUS_CHANGE_OPTIONS, old('default_status', $template->default_status))->class('form-control')->id("default_status-" . $template->id) }}
            </div>

            @if($wikis->count() > 1)
                <div class="form-group">
                    {{ html()->label('Wiki', 'wiki_id') }}
                    {{ html()->select('wiki_id', $wikis, old('wiki_id', $template->wiki_id))->class('form-control') }}
                </div>
            @else
                {{ html()->hidden('wiki_id', $template->wiki_id) }}
            @endif

            <button type="submit" class="btn btn-success">Submit</button>
            {{ html()->form()->close() }}
        </div>
    </div>
@endsection
