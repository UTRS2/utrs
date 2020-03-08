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
                        @if($info['privacyreview']!=0)
                            <div class="alert alert-primary" role="alert">
                                You are currently reviewing a ticket that is restricted from public view. You have three options to review this ticket:<br>
                                <br>
                                1) You select "Publicize Appeal" - Only select this if you have reviewed the entire appeal and there is no potentially private data involved.<br>
                                2) You select "Restrict Appeal" - Only select this option if there is private data, but it is not personally identifying.<br>
                                3) You select "Oversight Appeal" - Only select this option if there is personally identifying information in the appeal.
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
                            @if($perms['checkuser'])
                            <h5 class="card-title">CU data</h5>
                            @if($checkuserdone)
                            IP address: {{$cudata['ipaddress']}}<br><br style="line-height: .5em;">
                            Useragent: {{$cudata['useragent']}}<br><br style="line-height: .5em;">
                            Browser Language: {{$cudata['language']}}
                            @else
                            <div class="alert alert-danger" role="alert">
                                You have not submitted a request to view the CheckUser data yet.
                            </div>
                            {{ Form::open(array('url' => 'appeal/checkuser/'.$id)) }}
                            {{Form::token()}}
                            {{Form::label('reason', 'Reason:')}}<br>
                            {{Form::textarea('reason',null,['rows'=>2])}}<br><br>
                            <button type="submit" class="btn btn-success">Submit</button>
                            {{ Form::close() }}
                            @endif
                            @endif
        	    		</p>
            		</div>
        			<div class="col-7">
                        @if($info['privacyreview']!=0)
                            <div class="container">
                                <div class="row">
                                <a href="/appeal/publicize/{{$id}}"><div class="col-4"><button type="button" class="btn btn-danger">Publicize Appeal</button></div></a>
                                <a href="/appeal/privatize/{{$id}}"><div class="col-4"><button type="button" class="btn btn-warning">Restrict Appeal</button></div></a>
                                <a href="/appeal/oversight/{{$id}}"><div class="col-4"><button type="button" class="btn btn-success">Oversight Appeal</button></div></a>
                                </div>
                            </div>
                        @else
                        <div class="container">
                            <div class="row">
                                <div class="col-4"></div>
                                <div class="col-8">
                                <h5 class="card-title">Actions</h5>
                                @if(!$perms['admin'])
                                    <div class="alert alert-danger" role="alert">
                                        You are not an admin, and therefore can't preform any action on this appeal.
                                    </div>
                                @endif
                                    @if(($info['status']==="ACCEPT" || $info['status']==="DECLINE" || $info['status']==="EXPIRE") && !$perms['functionary'])
                                    <div class="alert alert-danger" role="alert">
                                        This appeal is closed and no further action can be taken.
                                    </div>
                                    @elseif(($info['status']==="ACCEPT" || $info['status']==="DECLINE" || $info['status']==="EXPIRE") && $perms['functionary'])
                                    <div style="text-align: center;">
                                    <a href="/appeal/open/{{$id}}"><button type="button" class="btn btn-success">Re-open</button><br><br style="line-height: .5em;"></a>
                                    <a href="/appeal/oversight/{{$id}}"><button type="button" class="btn btn-danger">Oversight appeal</button><br><br style="line-height: .5em;"></a>
                                    </div>
                                    @else
                                        <div style="text-align: center;">
                        				@if($info['handlingadmin']==NULL)
                                        <a href="/appeal/reserve/{{$id}}"><button type="button" class="btn btn-success">Reserve</button><br><br style="line-height: .5em;"></a>
                        				@elseif($info['handlingadmin']!=NULL && $info['handlingadmin'] == Auth::id())
                                        <a href="/appeal/release/{{$id}}"><button type="button" class="btn btn-success">Release</button><br><br style="line-height: .5em;"></a>
                        				@elseif($info['handlingadmin']!=NULL && $info['handlingadmin'] != Auth::id())
                        				<button type="button" class="btn btn-success" disabled>Reserve</button><br><br style="line-height: .5em;">
                                        @endif
                                        @if($perms['dev'])
                                        <a href="/appeal/invalidate/{{$id}}"><button type="button" class="btn btn-danger">Invalidate</button></a> 
                                        @endif
                                        <a href="/appeal/close/{{$id}}/accept"><button type="button" class="btn btn-danger">Accept appeal</button></a><br><br style="line-height: .5em;">
                                        <a href="/appeal/close/{{$id}}/decline"><button type="button" class="btn btn-danger">Decline appeal</button></a><br><br style="line-height: .5em;">
                                        <a href="/appeal/close/{{$id}}/expire"><button type="button" class="btn btn-danger">Mark appeal expired</button></a><br><br style="line-height: .5em;">
                                        @if($info['status']!=="open")
                                        <a href="/appeal/privacy/{{$id}}"><button type="button" class="btn btn-warning">Privacy Team</button></a> <a href="/appeal/checkuserreview/{{$id}}"><button type="button" class="btn btn-warning">CheckUser</button></a> <a href="/appeal/tooladmin/{{$id}}"><button type="button" class="btn btn-warning">Tool admin</button></a><br><br style="line-height: .5em;">
                                        @endif
                                        @if($info['status']!=="open" && !$perms['tooladmin'])
                                        <a href="/appeal/open/{{$id}}"><button type="button" class="btn btn-info">Return to tool users</button></a><br><br style="line-height: .5em;">
                                        @endif
                                    @endif
                                </div>
                                </div>
                            </div>
                        </div>
                        @endif
        		        </div>
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
    			<center>This appeal is oversighted or under privacy review. Only logged in Privacy Team members have access to this appeal.
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
			    	@if($comment['action']!=="comment")
			    	<tr>
			    	@else
			    	<tr class="bg-success">
			    	@endif
			    	@if(is_null($comment['commentUser']))
                        @if($comment['action']!=="comment")
                            @if($comment['user']==0)
				            <td><i>System</i></td>
                            @else
                            <td><i>{{$userlist[$comment['user']]}}</i></td>
                            @endif
				            <td><i>{{$comment['timestamp']}}</i></td>
                            @if($comment['protected'] && !$perms['functionary'])
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
                            @if($comment['protected'] && !$perms['functionary'])
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
				      @if($comment['protected'] && !$perms['functionary'])
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
            <br />
            <b><u>Custom replies</u></b>
            <br />
            <br />
            <table class="table table-bordered table-dark">
              <thead>
                <tr>
                  <th scope="col">Response ID</th>
                  <th scope="col">Response</th>
                </tr>
              </thead>
              <tbody>
                @foreach($replies as $reply)
                <tr>
                    <td>{{$reply['id']}}</td>
                    <td>{{$reply['custom']}}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
			<br />
            @if($perms['admin'])
            <div class="container">
            <div class="row">
            <div class="col-6">
            <h5 class="card-title">Send a templated reply</h5>
            @if($info['handlingadmin']!=NULL && $info['handlingadmin'] == Auth::id())
            <a href="/appeal/template/{{$id}}"><button type="button" class="btn btn-info">Send a reply to the user</button></a>
            @else
            <div class="alert alert-danger" role="alert">
                You are not the handling admin.
            </div>
            @endif
            </div>
            <div class="col-6">
            <h5 class="card-title">Drop a comment</h5>
			{{ Form::open(array('url' => 'appeal/comment/'.$id)) }}
            {{Form::token()}}
            {{Form::label('comment', 'Add a comment to this appeal:')}}<br>
            {{Form::textarea('comment',null,['rows'=>4,'width'=>'-webkit-fill-available'])}}<br><br>
            <button type="submit" class="btn btn-success">Submit</button>
            {{ Form::close() }}
            </div>
            </div>
            </div>
            @endif
        </div>
  		</div>
	</div>
@endsection