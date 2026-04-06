@extends('admin.layout.admin-layout')

@section('title', 'Admins')
@section('page-title', 'Admins')
@section('page-actions')
    <a href="{{ route('admin.admins.create') }}" class="btn btn-primary">
        <i class="ki-outline ki-plus fs-2"></i>Create Admin
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body py-4">
            <div class="table-responsive">
                <table id="admins_table" class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                    @foreach ($admins as $admin)
                        <tr>
                            <td>{{ $admin->name }}</td>
                            <td>{{ $admin->email }}</td>
                            <td>
                                @if ($admin->is_active)
                                    <span class="badge badge-light-success fw-bold px-4 py-3">Active</span>
                                @else
                                    <span class="badge badge-light-danger fw-bold px-4 py-3">Inactive</span>
                                @endif
                            </td>
                            <td>{{ optional($admin->created_at)->format('Y-m-d H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.admins.edit', $admin) }}" class="btn btn-sm btn-light-primary me-2">
                                    <i class="ki-outline ki-notepad-edit fs-6 me-1"></i>Edit
                                </a>

                                <form action="{{ route('admin.admins.destroy', $admin) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light-danger" onclick="return confirm('Delete this admin?')">
                                        <i class="ki-outline ki-trash fs-6 me-1"></i>Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#admins_table').DataTable({
                order: [[3, 'desc']],
                pageLength: 25,
                dom: "<'row mb-5'<'col-12 d-flex justify-content-end align-items-center gap-3'Bf>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                language: {
                    search: '',
                    searchPlaceholder: 'Search ...'
                },
                buttons: [
                    {
                        extend: 'csvHtml5',
                        title: 'admins'
                    },
                    {
                        extend: 'excelHtml5',
                        title: 'admins'
                    },
                    {
                        extend: 'pdfHtml5',
                        title: 'admins'
                    }
                ]
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        #admins_table_filter label {
            margin-bottom: 0;
        }

        #admins_table_filter input {
            width: 180px;
            min-width: 180px;
            height: 38px;
            border-radius: 8px;
            border: 1px solid #d8e2f8;
            padding-inline: 10px;
        }

        #admins_table_wrapper .dt-buttons .btn {
            margin-right: 0.45rem;
            height: 38px;
            display: inline-flex;
            align-items: center;
            padding-top: 0;
            padding-bottom: 0;
        }
    </style>
@endpush
