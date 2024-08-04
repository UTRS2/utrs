@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-header">
            Reason for email ban
        </div>
        <div class="card-body">
            @if($type == 'appealban')
                <form action="{{ route('admin.emailban.appealban', $emailBan->id) }}" method="post">
            @else
                <form action="{{ route('admin.emailban.accountban', $emailBan->id) }}" method="post">
            @endif
            @csrf
            <div class="form-group row">
                <label for="reason" class="col-md-4 col-form-label text-md-right">Reason</label>
                <div class="col-md-6">
                    <input id="reason" type="text" class="form-control @error('reason') is-invalid @enderror" name="reason" value="{{ old('reason') }}" required autocomplete="reason" autofocus>
                    @error('reason')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-primary">
                        Submit
                    </button>
                </div>
            </div>
            </form>
        </div>
    </div>
@endsection