<table class="table table-bordered table-dark">
    <thead>
    <tr>
        <th scope="col">{{__('appeals.appeal-number')}}</th>
        <th scope="col">{{__('appeals.appeal-for')}}</th>
        <th scope="col">{{__('appeals.details-status')}}</th>
        <th scope="col">{{__('appeals.details-block-admin')}}</th>
        <th scope="col">{{__('appeals.details-block-reason')}}</th>
        <th scope="col">{{__('appeals.details-submitted')}}</th>
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
            <td>{{ $appeal->appealfor }}</td>
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
