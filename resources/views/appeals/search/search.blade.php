@extends('layouts.app')

@section('title', 'Appeal search')
@section('content')
    <div class="mb-2">
        <a href="{{ route('appeal.list') }}" class="btn btn-primary">
            Back to regular appeal list
        </a>
    </div>

    @component('components.errors')
    @endcomponent

    <form>
        {{ Form::hidden('dosearch', 1) }}{{-- so we know if the form has been opened for the first time or if it's been filled --}}

        <div class="card">
            <div class="card-header">
                <button class="p-0 btn btn-link" type="button" data-toggle="collapse" data-target="#collapse-filters" aria-expanded="{{ !$hasResults }}" aria-controls="collapse-filters">
                    Filters
                </button>
            </div>

            <div class="card-body collapse {{ $hasResults ? '' : 'show' }}" id="collapse-filters">
                <div class="mb-4">
                    <h5>Appeal wiki</h5>
                    @foreach($wikiInputs as $key => $value)
                        <div class="custom-control custom-checkbox">
                            {{ Form::checkbox('wiki_' . $key, '1', $value, ['class' => 'custom-control-input', 'id' => 'wiki_' . $key]) }}
                            {{ Form::label('wiki_' . $key, $key, ['class' => 'custom-control-label']) }}
                        </div>
                    @endforeach
                </div>

                <div class="mb-4">
                    <h5>Appeal status</h5>
                    @foreach($statusInputs as $key => $value)
                        <div class="custom-control custom-checkbox">
                            {{ Form::checkbox('status_' . $key, '1', $value, ['class' => 'custom-control-input', 'id' => 'status_' . $key]) }}
                            {{ Form::label('status_' . $key, $key, ['class' => 'custom-control-label']) }}
                        </div>
                    @endforeach
                </div>

                <div class="mb-4">
                    <h5>Appellant</h5>

                    <div class="form-group mb-2">
                        {{ Form::label('appealfor', 'Appeal for') }}
                        {{ Form::text('appealfor', Request::input('appealfor'), ['class' => 'form-control']) }}
                        <p class="small">
                            MySQL <code>LIKE</code> wildcards are supported.
                        </p>
                    </div>

                    <span>Block type</span>
                    @foreach($blockTypeInputs as $key => $value)
                        <div class="custom-control custom-checkbox">
                            {{ Form::checkbox('blocktype_' . $key, '1', $value, ['class' => 'custom-control-input', 'id' => 'blocktype_' . $key]) }}
                            {{ Form::label('blocktype_' . $key, $blockTypeNames[$key], ['class' => 'custom-control-label']) }}
                        </div>
                    @endforeach
                </div>

                <div>
                    <h5>Administrators involved</h5>

                    <div class="form-group mb-2">
                        {{ Form::label('blockingadmin', 'Blocking administrator') }}
                        {{ Form::text('blockingadmin', Request::input('blockingadmin'), ['class' => 'form-control']) }}
                    </div>

                    <div class="form-group">
                        {{ Form::label('handlingadmin', 'Handling administrator') }}
                        {{ Form::text('handlingadmin', Request::input('handlingadmin'), ['class' => 'form-control']) }}
                    </div>

                    <div class="custom-control custom-checkbox">
                        {{ Form::checkbox('handlingadmin_none', '1', Request::input('handlingadmin_none'), ['class' => 'custom-control-input', 'id' => 'handlingadmin_none']) }}
                        {{ Form::label('handlingadmin_none', 'No handling administrator', ['class' => 'custom-control-label']) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-2 mb-2">
            <button type="submit" class="btn btn-success">Search</button>
            <a href="?" class="btn btn-danger">Clear filters</a>
        </div>
    </form>

    @if($hasResults)
        <div class="card">
            <div class="card-header">
                Results
            </div>

            <div class="card-body">
                @if($results->isEmpty())
                    <div class="alert alert-info">
                        No results found.
                    </div>
                @else
                    @component('components.appeal-table', ['appeals' => $results])
                    @endcomponent
                @endif
            </div>
        </div>
    @endif
@endsection
