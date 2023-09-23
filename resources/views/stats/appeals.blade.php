@extends('layouts.app')
@section('title', 'Statistics - Appeals')
@section('content')
<div id="en_div"></div>
<div id="meta_div"></div>
@barchart('enwiki_appstat', 'en_div')
@barchart('global_appstat', 'meta_div')
@endsection