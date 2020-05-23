@extends('layouts.app')

@section('title', 'Templates')
@section('scripts')
    function toggleShow(id) {
    document.getElementById("template"+id).style.display="block";
    document.getElementById("template"+id+"hidden").style.display="none";
    }
    function toggleHide(id) {
    document.getElementById("template"+id).style.display="none";
    document.getElementById("template"+id+"hidden").style.display="block";
    }
@endsection
@section('content')
    <div class="col-1"></div>
    <div class="col-10">
        <div class="alert alert-info" role="alert">
            On this screen, you will see a list of templates to choose from in responding to a user. To view a template,
            click "View template".
        </div>
        <br/>
        <a type="button" class="btn btn-danger" href="/appeal/{{ $appeal->id }}">Return to appeal</a>
        <a href="{{ route('appeal.customresponse', $appeal) }}" class="btn btn-info">Use a custom reply</a><br/><br/>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Preview</th>
                <th>Send</th>
            </tr>
            </thead>
            <tbody>
            @foreach($templates as $template)
                <tr>
                    <td>{{ $template->id }}</td>
                    <td>{{ $template['name'] }}</td>
                    <td>
                        <div id="template{{ $template->id }}" style="display: none">
                            Hello {{ $appeal['appealfor'] }}, <br/><br/>{{ $template['template'] }}
                            <br/><br/>{{ $userlist[Auth::id()] }}<br/>English Wikipedia Administrator
                        </div>
                        <div id="template{{ $template->id }}hidden">Template hidden</div>
                    </td>
                    <td><a href="/appeal/template/{{ $appeal->id }}/{{ $template['id'] }}">
                            <button type="button" class="btn btn-danger">Use template</button>
                        </a>
                        <br/><br style="line-height: .5em;">
                        <button id="template{{ $template['id'] }}show" type="button"
                                onClick="toggleShow({{ $template['id'] }})" class="btn btn-success">View template
                        </button>
                        <br/><br style="line-height: .5em;">
                        <button id="template{{ $template['id'] }}hide" type="button"
                                onClick="toggleHide({{ $template['id'] }})" class="btn btn-warning">Hide template
                        </button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>
    <div class="col-1"></div>

@endsection
