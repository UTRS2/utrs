@extends('layouts.app')
@section('content')
@if($tooladmin)
<div class="card">
	<h5 class="card-header">Admin tools</h5>
	<div class="card-body">
		<a href="/admin/templates"><button type="button" class="btn btn-primary">Manage Templates</button></a>
	</div>
</div>
@endif
<br />
<div class="card">
	<h5 class="card-header">Current appeals</h5>
	<div class="card-body">
		<table class="table table-bordered table-dark">
			<thead>
				<tr>
					<th scope="col">ID #</th>
					<th scope="col">Subject</th>
					<th scope="col">Block Type</th>
					<th scope="col">Status</th>
					<th scope="col">Blocking Admin</th>
					<th scope="col">Block Reason</th>
					<th scope="col">Date</th>
				</tr>
			</thead>
			<tbody>
				@foreach($appeals as $appeal)
				@if($appeal['status']=="NEW")
					<tr>
				@elseif($appeal['status']=="USER")
					<tr>
				@elseif($appeal['status']=="ADMIN")
					<tr class="table-primary">
				@elseif($appeal['status']=="TOOLADMIN")
					<tr class="table-info">
				@elseif($appeal['status']=="CHECKUSER")
					<tr class="table-warning">
				@elseif($appeal['status']=="PROXY")
					<tr class="table-warning">
				@elseif($appeal['status']=="PRIVACY")
					<tr class="table-danger">
				@endif
						<td><a href="/appeal/{{$appeal['id']}}"><button type="button" class="btn btn-primary">{{$appeal['id']}}</button></a></td>
						<td style="vertical-align: middle;">{{$appeal['appealfor']}}</td>
						@if($appeal['blocktype']==0)
						<td style="vertical-align: middle;">IP address</td>
						@elseif($appeal['blocktype']==1)
						<td style="vertical-align: middle;">Account</td>
						@elseif($appeal['blocktype']==2)
						<td style="vertical-align: middle;">IP underneath account</td>
						@endif
						<td style="vertical-align: middle;">{{$appeal['status']}}</td>
						<td style="vertical-align: middle;">{{$appeal['blockingadmin']}}</td>
						<td style="vertical-align: middle;">{{$appeal['blockreason']}}</td>
						<td style="vertical-align: middle;">{{$appeal['submitted']}}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
		</div>
	</div>

@endsection