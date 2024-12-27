@extends('layouts.app')
@section('title', 'Statistics - Appeals')
@section('content')
<div class="dropdown">
    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Change chart
    </button>
    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
        @foreach($chartlinks as $key=>$link)
            <a class="dropdown-item" href="{{ $link }}">{{ $key }}</a>
        @endforeach
    </div>
    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Change time
    </button>
    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
        @foreach($timelinks as $key=>$link)
            <a class="dropdown-item" href="{{ $link }}">{{ $key }}</a>
        @endforeach
    </div>
    <button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Change wiki
    </button>
    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
        @foreach($wikilinks as $key=>$link)
            <a class="dropdown-item" href="{{ $link }}">{{ $key }}</a>
        @endforeach
    </div>
</div>
@if($chart == 'apppd')
    <div id="day_div"></div>
    @columnchart('perday', 'day_div')
@elseif($chart == 'blkadm')
    <div id="blockadmin_div"></div>
    @barchart('admincount', 'blockadmin_div')
@elseif($chart == 'blkreason')
    @if($wiki == 'all')
        <br /><br />
        <div class="alert alert-danger" role="alert">
            Due to varying block reasons and parsing paramaters, this chart is not available for all UTRS wikis. Please select a local wiki.
        </div>
    @else
        <div id="blockreason_div"></div>
        @barchart('blockreason', 'blockreason_div')
    @endif
@elseif($chart == 'appstate')
    <div id="blocktime_div"></div>
    @barchart('appstate', 'blocktime_div')
@elseif($chart == 'hanadm')
    <div id="admhandle_div"></div>
    @barchart('admhandle', 'admhandle_div')
@endif
@endsection