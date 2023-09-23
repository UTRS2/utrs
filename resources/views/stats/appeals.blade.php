@extends('layouts.app')
@section('title', 'Statistics - Appeals')
@section('content')
@foreach($dates as $date)
    {{$date}}<br />
@endforeach
<div id="enwiki_day_div"></div>
<div id="en_div"></div>
<div id="meta_div"></div>
@columnchart('enwiki_weekstat', 'enwiki_day_div')
@barchart('enwiki_appstat', 'en_div')
@barchart('global_appstat', 'meta_div')
@endsection