@extends('layouts.app')
@section('title', 'Statistics - Appeals')
@section('content')
<div id="chart_div"></div>
@barchart('appstat', 'chart_div')
@endsection