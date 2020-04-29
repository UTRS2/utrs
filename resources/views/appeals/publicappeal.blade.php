@extends('layouts.app')
@section('content')

@if($info['status']==="ACCEPT" || $info['status']==="DECLINE" || $info['status']==="EXPIRE")
	<br />
	<div class="alert alert-danger" role="alert">This appeal is closed. No further changes can be made to it.</div>
@endif
	<br />
	<div class="card">
		<h5 class="card-header">Appeal details</h5>
  		<div class="card-body">
  			<div class="container">
      			<div class="row">
                    <div class="col-12">
                        @if($info['privacyreview']!=0 && $info['status']=="PRIVACY")
                            <div class="alert alert-primary" role="alert">
                                Your appeal is currently under privacy review.
                            </div>
                        @endif
                    </div>
          			<div class="col-5">
        	    		<h4 class="card-title">Appeal for "{{$info['appealfor']}}"</h4>
        	    		<p class="card-text">
        	    			Appeal status: {{$info['status']}}
        	    			<br />Blocking Admin: {{$info['blockingadmin']}}
        	    			<br />Time Submitted: {{$info['submitted']}}
                            @if(!is_null($info['handlingadmin']))
                            <br />Handling Admin: {{$userlist[$info['handlingadmin']]}}
                            @endif
        	    		</p>
            		</div>
                </div>
    		<br>
    		<h4 class="card-title">Appeal Content</h4>
    		<br />
    		<div class="container">
  			<div class="row">
  			<div class="col-6">
	    		<br /><b>Why should you be unblocked?</b>
	    		<p>{{$info['appealtext']}}</p>
    		</div>
    		<div class="col-3">
    			@if($info['status']=="ACCEPT")
    			<center>This appeal was approved.<br />
    			<br /><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/ee/Emblem-unblock-granted.svg/200px-Emblem-unblock-granted.svg.png" class="img-fluid"></center>
    			@elseif($info['status']=="EXPIRE")
    			<center>This appeal expired.<br />
    			<br /><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/34/Emblem-unblock-expired.svg/200px-Emblem-unblock-expired.svg.png" class="img-fluid"></center>
    			@elseif($info['status']=="DECLINE")
    			<center>This appeal was denied.<br />
    			<br /><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/59/Emblem-unblock-denied.svg/200px-Emblem-unblock-denied.svg.png" class="img-fluid"></center>
    			@else
    			<center>This appeal is in progress.<br />
    			<br /><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/ec/Emblem-unblock-request.svg/200px-Emblem-unblock-request.svg.png" class="img-fluid"></center>
    			@endif
    		</div>
    		<div class="col-3">
    			@if($info['privacylevel']==0 && $info['privacyreview']==0)
				<center>This appeal is considered public. Logged in Wikimedians can view this.
    			<br /><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/0d/Oxygen480-actions-irc-voice.svg/200px-Oxygen480-actions-irc-voice.svg.png" class="img-fluid"></center>
    			@elseif($info['privacylevel']==1 && $info['privacyreview']==0)
    			<center>This appeal is considered private. Only logged in administrators have access to this appeal.
    			<br /><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/4e/Oxygen480-actions-irc-unvoice.svg/200px-Oxygen480-actions-irc-unvoice.svg.png" class="img-fluid"></center>
    			@elseif($info['privacylevel']==2 || $info['privacyreview']!=0)
    			<center>This appeal is not availiable for viewing by all administrators at this time. This can occur for multiple reasons and is normal.
    			<br /><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/06/Oversight_logo.png/200px-Oversight_logo.png" class="img-fluid"></center>
    			@endif
    		</div>
    		</div>
    		</div>
    		<br />
    		<b><u>Admin Comments</u></b>
    		<br />
    		<br />
    		<table class="table table-bordered table-dark">
			  <thead>
			    <tr>
			      <th scope="col">Commenting User</th>
			      <th scope="col">Time</th>
			      <th scope="col">Comment</th>
			    </tr>
			  </thead>
			  <tbody>
			    @foreach($comments as $comment)
			    	@if($comment['action']=="comment")
                    <tr class="bg-success">
                    @elseif($comment['action']=="responded")
                    <tr class="bg-primary">
                    @else
                    <tr>
                    @endif
			    	@if(is_null($comment['commentUser']))
                        @if($comment['action']!=="comment")
                            @if($comment['user']==0)
				            <td><i>System</i></td>
                            @else
                            <td><i>{{$userlist[$comment['user']]}}</i></td>
                            @endif
				            <td><i>{{$comment['timestamp']}}</i></td>
                            @if($comment['protected'])
                                <td><i>Access to comment is restricted.</i></td>
                            @else
				                @if($comment['comment']!==NULL)
                                    <td><i>{{$comment['comment']}}</i></td>
                                @else
                                    @if(!is_null($comment['reason']))
                                    <td><i>Action: {{$comment['action']}}, Reason: {{$comment['reason']}}</i></td>
                                    @else
                                    <td><i>Action: {{$comment['action']}}</i></td>
                                    @endif
                                @endif
                            @endif
                        @else
                            <td>{{$userlist[$comment['user']]}}</td>
                            <td>{{$comment['timestamp']}}</td>
                            @if($comment['protected'])
                                <td>Access to comment is restricted.</td>
                            @else
                                @if($comment['comment']!==NULL)
                                    <td>{{$comment['comment']}}</td>
                                @else
                                    <td>{{$comment['reason']}}</td>
                                @endif
                            @endif
                      @endif
				    @else
				      <td>{{$userlist[$comment['commentUser']]}}</td>
				      <td>{{$comment['timestamp']}}</td>
				      @if($comment['protected'])
				        <td><i>Access to comment is restricted.</td>
				        @else
				            @if($comment['comment']!==NULL)
                                <td>{{$comment['comment']}}</td>
                            @else
                                <td>{{$comment['reason']}}</td>
                            @endif
				        @endif
				    @endif
				    </tr>
    			@endforeach
			  </tbody>
			</table>
            <i>Lines that are in blue indicate a response to the user. Lines in green are comments from other administrators.</i>
            <br />
            <br />
            </div>
            </div>
            </div>
        </div>
  		</div>
	</div>
@endsection