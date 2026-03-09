@extends('layouts.app')
@section('content')
    <div class="card">
        <h5 class="card-header">{{ $title }}</h5>
        <div class="card-body">
            <table>
                <tr>
                    <td>
                        <a href="{{ $createlink }}" class="btn btn-primary">New Ban</a>
                    </td>
                    <td>
                        <form method="GET" action="{{ route('admin.bans.list') }}" class="d-inline mb-3">
                            <input type="hidden" name="hide_expired" value="{{ $hide_expired ? 0 : 1 }}">
                            <button type="submit" class="btn btn-outline-secondary">
                                {{ $hide_expired ? 'Show expired bans' : 'Hide expired bans' }}
                            </button>
                        </form>
                    </td>
                </tr>
            </table>

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
                @include('admin.bans.table', ['bans' => $bans, 'tableheaders' => $tableheaders, 'allowedwikis' => $allowedwikis, 'admin' => $admin, 'checkuser' => $checkuser])
            </div>
        </div>
    </div>
@endsection
