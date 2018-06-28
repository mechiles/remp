@extends('layouts.app')

@section('title', 'Edit newsletter')

@section('content')

    <div class="c-header">
        <h2>Newsletters</h2>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Edit newsletter <small></small></h2>
        </div>

        <div class="card-body card-padding">
            @include('flash::message')
            {!! Form::model($newsletter, ['route' => ['newsletters.update', $newsletter], 'method' => 'PATCH']) !!}
            @include('newsletters._form')
            {!! Form::close() !!}
        </div>

    </div>
@endsection
