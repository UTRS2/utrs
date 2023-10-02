@extends('layouts.app')
@section('css')
  @component('components.appealmap-css')
  @endcomponent
@endsection
@section('content')

<div class="container py-5">
    <div class="row">
  
      <div class="col-md-12 col-lg-12">
        <h5>Appeal Map for {{$appealant}}</h5>
        <br />
        @if($activeBans)
        <div class="alert alert-danger" role="alert">This user has an active ban!
          <br />Ban #{{$activeBans['id']}} - Valid thru: {{$activeBans['expiry']}}
          <br />Reason: {{$activeBans['reason']}}
        </div>
        @endif
        <br />
        <div id="tracking-pre"></div>
        <div id="tracking">
          <div class="tracking-list">
            @foreach($appealmap as $appeal)
            @if($appeal['active'] == "yes")
            <div class="tracking-item">
              <div class="tracking-icon status-intransit">
            @elseif($appeal['active'] == "no")
            <div class="tracking-item tracking-item-pending">
              <div class="tracking-icon status-current">
            @elseif($appeal['active'] == "error")
            <div class="tracking-item tracking-item-error">
              <div class="tracking-icon status-error">
            @endif
                <svg class="svg-inline--fa fa-circle fa-w-16" aria-hidden="true" data-prefix="fas" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg="">
                  <path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"></path>
                </svg>
              </div>
              <div class="tracking-date">
                @component('components.appealmap-icons', ['appeal' => $appeal])
                @endcomponent
              </div>
              <div class="tracking-content">{{$appeal['text']}}<span>{{$appeal['time']}}</span>
                @if($appeal['icon'] == 'sent' || ($appeal['icon'] == "stop" && $isdev))
                @auth
                    <a href="/appeal/{{$appeal['appealid']}}"><button type="submit" class="btn btn-primary">Review this appeal</button></a>
                @endauth
                @guest
                  {{ html()->form('POST', $route)->open() }}  
                  {{ html()->token() }}
                  {{ html()->hidden('appealkey', $appealkey) }}
                  {{ html()->hidden('id', $appeal['appealid']) }}
                  @if($appeal['icon'] !="stop")
                  <button type="submit" class="btn btn-primary">Review this appeal</button>
                  @endif
                  {{ html()->form()->close() }}
                @endguest
                @endif
            </div>
            </div>
            @endforeach
            
          </div>
        </div>
      </div>
    </div>
  </div>
  @endsection