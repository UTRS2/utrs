@if(sizeof($errors)>0)
    <div class="alert alert-danger" role="alert">
        The following errors occured:
        <ul>
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
