@extends('layouts.app')

@section('title', 'Verify appeal')
@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                Verify appeal
            </div>

            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('appeal.verifyownership.submit', $appeal) }}" method="POST">
                    @csrf

                    <input type="hidden" name="verify_token" value="{{ $appeal->verify_token }}">

                    <div class="form-group">
                        <label for="secret_key">Secret key</label>
                        <input type="text" class="form-control" id="secret_key" name="secret_key">
                        <small class="form-text text-muted">
                            You should have received this when you created your appeal.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
@endsection
