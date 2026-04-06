@extends('admin.layout.admin-layout')

@section('title', 'Edit Admin')
@section('page-title', 'Edit Admin')

@section('content')
    <div class="card">
        <div class="card-body py-10">
            <form action="{{ route('admin.admins.update', $admin) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.admins.form', ['admin' => $admin])

                <div class="d-flex justify-content-end gap-3">
                    <a href="{{ route('admin.admins.index') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Admin</button>
                </div>
            </form>
        </div>
    </div>
@endsection
