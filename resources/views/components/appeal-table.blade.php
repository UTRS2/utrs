<table class="table table-bordered table-dark">
    <thead>
    <tr>
        <th scope="col">ID #</th>
        <th scope="col">Subject</th>
        <th scope="col">Status/Type/Wiki</th>
        <th scope="col">Blocking Admin</th>
        <th scope="col">Block Reason</th>
        <th scope="col">Date</th>
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
                @isset($appeal->handlingadmin)
                    <a href="{{ route('appeal.view', $appeal) }}" class="btn btn-danger">#{{ $appeal->id }}</a>
                @else
                    <a href="{{ route('appeal.view', $appeal) }}" class="btn btn-primary">#{{ $appeal->id }}</a>
                @endisset
                </td>
                <td>{{ $appeal->appealfor }}</td>
                <td style="vertical-align: middle">
                    {{ $appeal->status }}<br/>
                    @if($appeal->blocktype === 0)
                        IP address
                    @elseif($appeal->blocktype === 1)
                        Account
                    @elseif($appeal->blocktype === 2)
                        IP underneath account
                    @else
                        Unknown type: {{ $appeal->blocktype }}
                    @endif
                    on {{ $appeal->wiki }}
                </td>
                <td>{{ $appeal['blockingadmin'] }}</td>
                <td>{!! $appeal->getFormattedBlockReason('style="color: #00ffea!important;"') !!}</td>
                <td>{{ $appeal['submitted'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
