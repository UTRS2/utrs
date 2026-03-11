@extends('layouts.app')
@section('content')
{{-- add card around search results --}}
<div class="card">
    <h5 class="card-header">Ban list</h5>
        <div class="card-body">
            <p>
                <a href="{{ $createlink }}" class="btn btn-primary">New Ban</a>
                {{-- Back to main ban list --}}
                <a href="{{ route('admin.bans.list') }}" class="btn btn-secondary">Back to Ban List</a>
            </p>

            @if(isset($caption) && strlen($caption) > 0)
                <p>
                    <i>{{ $caption }}</i>
                </p>
            @endif
            <br />
            {{-- Search form --}}
            <form action="{{ route('admin.bans.search.submit') }}" method="POST" class="mb-3">
                @csrf
                {{-- Add title --}}
                <h5 class="card-title">Search Bans</h5>
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="[user, ip, or ban id]" value="{{ old('search', $searchTerm='') }}">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>
            <br />
            <div class="table-responsive">
                @include('admin.bans.table', ['bans' => $bans])
            </div>
        </div>
</div>
@endsection