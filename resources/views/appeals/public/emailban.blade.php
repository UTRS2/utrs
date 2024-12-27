@extends('layouts.app')
@section('content')
    <!-- provide a form, showing the uid (can't be edited), and based on if this is an account ban request, advise the user what type of ban is being implemented -->
    <!-- show inside a card -->
    <div class="card">
        <div class="card-header">
            <h3>{{__('appeals.emailban.header')}}</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('email.ban.submit', [$method,$token]) }}">
                @csrf
                <!-- show a table with the uid and the ban type -->
                <table class="table">
                    <tr>
                        <th>{{__('appeals.emailban.uid')}}</th>
                        <td>{{$token}}</td>
                    </tr>
                    <tr>
                        <th>{{__('appeals.emailban.name')}}</th>
                        @if($isAccountBan)
                        <td><a href="https://meta.wikimedia.org/wiki/Special:CentralAuth/{{$name}}">{{$name}}</a></td>
                        @else
                        <td>{{$name}}</td>
                        @endif
                    </tr>
                    <tr>
                        <th>{{__('appeals.emailban.which')}}</th>
                        <!-- checkbox for appeal and account bans -->
                        <td>
                            <input type="checkbox" id="appeal" name="appeal" value="1">&nbsp;{{__('appeals.emailban.appeal')}}<br />
                            <input type="checkbox" id="account" name="account" value="1">&nbsp;{{__('appeals.emailban.account')}}
                        </td>
                    </tr>
                </table>
                <!-- submit button -->
                <button type="submit" class="btn btn-primary">{{__('generic.submit')}}</button>
            </form>
        </div>
    </div>
@endsection