@extends('layouts.app')
@section('content')
    <div class="card">
        <h5 class="card-header">{{ $title }}</h5>
        <div class="card-body">
            <p>
                <a href="{{ $createlink }}" class="btn btn-primary">New Ban</a>
            </p>

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
                @foreach($bans as $ban)
                    @if($ban->wiki_id == NULL)
                        <tr>
                            <td style="vertical-align: middle;">
                                @if ($admin)
                                    <a href="{{ route('admin.bans.update', $ban->id) }}" class="btn btn-primary">{{ $ban->id }}</a>
                                @else
                                    {{ $ban->id }}
                                @endif
                            </td>
                            <td style="vertical-align: middle;">
                                @if($ban->is_protected)
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/cf/Oxygen15.04.1-document-encrypt.svg/20px-Oxygen15.04.1-document-encrypt.svg.png"> <span style='color:red'>
                                    @if($checkuser)
                                        {{ $ban->target }}
                                    @else
                                        {{ __('admin.bans.no-permission') }}
                                    @endif
                                    </span>
                                @else
                                    {{ $ban->target }}
                                @endif
                            </td>
                            @if($ban->expiry > \Carbon\Carbon::now() || $ban->expiry == "1970-01-01 00:00:00")
                                @if($ban->expiry == "1970-01-01 00:00:00")
                                    <td style="vertical-align: middle;">{{__('admin.bans.permanent')}}</td>
                                @else
                                <td style="vertical-align: middle;">{{ $ban->expiry }}</td>
                                @endif
                            @else
                                <td style="vertical-align: middle;">{{ $ban->expiry }} <span style='color:red'>{{__('admin.bans.expired')}}</span></td>
                            @endif
                            <td style="vertical-align: middle;">{{ $ban->reason }}</td>
                            <td style="vertical-align: middle;">All UTRS wikis</td>
                        </tr>
                    @elseif(in_array($ban->wiki()->first()->id,$allowed))
                    <tr>
                        <td style="vertical-align: middle;">
                            @if ($admin)
                                <a href="{{ route('admin.bans.update', $ban->id) }}" class="btn btn-primary">{{ $ban->id }}</a>
                            @else
                                {{ $ban->id }}
                            @endif
                        </td>
                        <td style="vertical-align: middle;">
                        @if($ban->is_protected)
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/cf/Oxygen15.04.1-document-encrypt.svg/20px-Oxygen15.04.1-document-encrypt.svg.png"> <span style='color:red'>
                            @if($checkuser)
                            {{ $ban->target }}
                            @else
                            {{ __('admin.bans.no-permission') }}
                            @endif
                            </span>
                        @else
                            {{ $ban->target }}
                        @endif
                        </td>
                        @if($ban->expiry > \Carbon\Carbon::now())
                            <td style="vertical-align: middle;">{{ $ban->expiry }}</td>
                        @else
                            <td style="vertical-align: middle;">{{ $ban->expiry }} <span style='color:red'>{{__('admin.bans.expired')}}</span></td>
                        @endif
                        <td style="vertical-align: middle;">{{ $ban->reason }}</td>
                        <td style="vertical-align: middle;">{{ $ban->getWikiName() }}</td>

                    </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {{ $bans->links() }}
            </div>
        </div>
    </div>
@endsection
