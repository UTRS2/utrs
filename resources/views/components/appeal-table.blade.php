<table class="table table-bordered table-dark">
    <thead>
    <tr>
        <th scope="col">@sortablelink('id','#',[],['style'=>'color:white;'])</th>
        <th scope="col">@sortablelink('appealfor',__('appeals.appeal-for'),[],['style'=>'color:white;'])</th>
        <th scope="col">@sortablelink('status',__('appeals.details-status'),[],['style'=>'color:white;'])</th>
        <th scope="col">@sortablelink('blockingadmin',__('appeals.details-block-admin'),[],['style'=>'color:white;'])</th>
        <th scope="col">@sortablelink('blockreason',__('appeals.details-block-reason'),[],['style'=>'color:white;'])</th>
        <th scope="col">@sortablelink('submitted',__('appeals.details-submitted'),[],['style'=>'color:white;'])</th>
    </tr>
    </thead>
    <tbody>
    @foreach($appeals as $appeal)
        @if($appeal->status === "ADMIN")
            <tr class="bg-primary">
        @elseif($appeal->status === "CHECKUSER")
            <tr class="bg-warning" style="color: #212529!important;">
        @else
            <tr>
        @endif
            <td>
            <a href="{{ route('appeal.view', $appeal) }}" class="btn {{ $appeal->handlingadmin ? 'btn-danger' : 'btn-primary' }}">#{{ $appeal->id }}</a>
            </td>
            <td>{{ $appeal->appealfor }}
            @if($appeal->user_verified == 1)
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/06/Oxygen480-status-security-high.svg/30px-Oxygen480-status-security-high.svg.png">
            @elseif($appeal->user_verified == -1 && $appeal->blocktype != 0)
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/f8/Oxygen480-status-security-low.svg/30px-Oxygen480-status-security-low.svg.png">
            @elseif(!$appeal->blocktype == 0)
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/Oxygen480-status-security-medium.svg/30px-Oxygen480-status-security-medium.svg.png">
            @endif
            </td>
            <td style="vertical-align: middle">
                {{ __('appeals.status.'.$appeal->status) }}<br/>
                @if($appeal->blocktype === 0)
                    {{__('appeals.appeal-types.ip')}}
                @elseif($appeal->blocktype === 1)
                    {{__('appeals.appeal-types.account')}}
                @elseif($appeal->blocktype === 2)
                    {{__('appeals.appeal-types.ip-under')}}
                @else
                    {{__('appeals.appeal-types.unknown')}}: {{ $appeal->blocktype }}
                @endif
                <br /><span class="badge bg-success" style="padding-right: 0.6em; padding-left: 0.6em; border-radius: 10rem;"><b>{{ $appeal->wiki }}</b></span>
            </td>
            <td>{{ $appeal['blockingadmin'] }}</td>
            <td>{!! $appeal->getFormattedBlockReason('style="color: #00ffea!important;"') !!}</td>
            <td>{{ $appeal['submitted'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="d-flex justify-content-center">
    {{ $appeals->links() }}
</div>