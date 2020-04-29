@extends('layouts.app')
@section('content')
<div class="alert alert-danger" role="alert">
    <b>IMPORTANT MESSAGE</b><br />
    UTRS is in the process of moving over to UTRS 2.0 of the software. We needed to do this because several users were unable to file proper appeals due to IPv6 IP addresses not being accepted by our severs. Therefore, we made the decision to move over to a rudimentary beta software instead to allow everyone to appeal properly.<br /><br />

	Please note:
	<ul>
	<li>In doing this, please understand that there will be bugs and issues. We will try our best to keep up with those issues. You can get assistance at [[WT:UTRS|the UTRS talkpage]] (preferably) or by placing "{{tl|UTRS help me}}" on your talkpage.</li>
	<li>New features are not being considered at this time. Though your idea may have already been thought of and be in development.</li>
	<li>Administrators will need to create a new login to use UTRS 2.0. The only thing that needs to match is your Wikipedia username. You should receive a confirmation email to verify your account within 5 minutes. At this time, there is no plans for reintegrating OAuth for login (for multiple reasons).</li>
	<li>Temporary tool administrator status can be requested on [[WT:UTRS]], and will be granted liberally at this time to help create templates from the [https://utrs.wmflabs.org/tempMgmt.php old version]. All bans, user management, and other tool administration functions are only available via the database or automated scripts already running on the server at this time.</li>
	<li>More information will be available in the days to come about the features of UTRS.</li>
	</ul>

	We appreciate your patience in advance,<br />
    UTRS Developement Team
</div>
@if($tooladmin)
<div class="card">
	<h5 class="card-header">Admin tools</h5>
	<div class="card-body">
		<div class="alert alert-danger" role="alert">
			Managing templates is the only functional option at this time.
		</div>
		<a href="/admin/templates"><button type="button" class="btn btn-primary">Manage Templates</button></a>
		<a href="/admin/bans"><button type="button" class="btn btn-primary">Manage Bans</button></a>
		<a href="/admin/users"><button type="button" class="btn btn-primary">Manage Users</button></a>
		<a href="/admin/sitenotices"><button type="button" class="btn btn-primary">Manage Sitenotices</button></a>
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