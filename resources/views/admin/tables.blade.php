@extends('layouts.app')
@section('content')
    <div class="card">
        <h5 class="card-header">{{ $title }}</h5>
        <div class="card-body">
            @if(isset($createlink))
                <p>
                    <a href="{{ $createlink }}" class="btn btn-primary">{{ $createtext }}</a>
                </p>
            @endif

            @if(isset($caption) && strlen($caption) > 0)
                <p>
                    <i>{{ $caption }}</i>
                </p>
            @endif

            <table class="table">
                <thead>
                <tr>
                    @foreach($tableheaders as $tableheader)
                        <th scope="col">{{ $tableheader }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach($rowcontents as $rowcontent)
                    <tr>
                        @foreach($rowcontent as $field)
                            <td style="vertical-align: middle;">{!! $field !!}</td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
