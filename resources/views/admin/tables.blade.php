@extends('layouts.app')
@section('content')
<div class="card">
	<h5 class="card-header">{{$title}}</h5>
	<div class="card-body">
		<table class="table table-bordered table-dark">
			<thead>
				<tr>
					@foreach($tableheaders as $tableheader)
					<th scope="col">{{$tableheader}}</th>
					@endforeach
				</tr>
			</thead>
			<tbody>
				@foreach($rowcontents as $rowcontent)
					<tr>
						@foreach($rowcontent as $field)
						<td style="vertical-align: middle;">{!! $field !!}</td>
						@endforeach
					</tr>
				@endforeach
			</tbody>
		</table>
		</div>
	</div>
</div>
@endsection