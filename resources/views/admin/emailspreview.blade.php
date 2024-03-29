@extends('layouts.app')
@section('content')
    <!-- display a table of the available email templates -->
    <div class="card">
        <h5 class="card-header">Email Templates</h5>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Link</th>
                        <th>Template</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($emails as $email)
                        <tr>
                            <!-- add primary button to preview the email template with the text "Preview" and a link to /emailpreview/{name of template} -->
                            <td><a href="{{ route('appeal.emailpreview.view',$email) }}"><button class="btn btn-primary">Preview</button></a></td>
                            <td>{{ $email }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
@endsection