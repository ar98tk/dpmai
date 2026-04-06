@extends('admin.layout.admin-layout')

@section('title', 'Create Admin')
@section('page-title', 'Create Admin')

@section('content')
    <div class="card">
        <div class="card-body py-10">
            <form action="{{ route('admin.admins.store') }}" method="POST">
                @csrf
                @include('admin.admins.form')

                <div class="d-flex justify-content-end gap-3">
                    <a href="{{ route('admin.admins.index') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Admin</button>
                </div>
            </form>
        </div>
    </div>
@endsection
