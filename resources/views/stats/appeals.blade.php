@extends('layouts.app')
@section('title', 'Statistics - Appeals')
@section('content')
<div id="enwiki_day_div"></div>
<div id="global_day_div"></div>
<div id="en_div"></div>
<div id="meta_div"></div>
<div id="en_blockadmin_div"></div>
@columnchart('enwiki_daystat', 'enwiki_day_div')
@columnchart('global_daystat', 'global_day_div')
@barchart('enwiki_appstat', 'en_div')
@barchart('global_appstat', 'meta_div')
@columnchart('en_admincount', 'en_blockadmin_div')
@endsection