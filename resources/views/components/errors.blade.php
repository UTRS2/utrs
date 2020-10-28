@if(sizeof($errors)>0)
    <div class="alert alert-danger" role="alert">
        {{ __('validation.error-msg') }}
        <ul>
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
