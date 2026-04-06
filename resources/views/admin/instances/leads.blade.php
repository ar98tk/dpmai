@extends('admin.layout.admin-layout')

@section('title', 'Instance Leads')
@section('page-title', 'Leads')
@section('page-actions')
    <a href="{{ route('admin.businesses.show', $instance->business_id) }}" class="btn btn-light-primary">
        Back to Business
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="fw-bold m-0">Leads for {{ $instance->name }}</h3>
            </div>
        </div>
        <div class="card-body py-4">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Last Interaction</th>
                        <th>Messages Count</th>
                        <th>Intent</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                    @forelse ($leads as $lead)
                        <tr>
                            <td>{{ $lead->name ?: '-' }}</td>
                            <td>{{ $lead->phone }}</td>
                            <td>{{ optional($lead->last_interaction_at)->format('Y-m-d H:i') ?: '-' }}</td>
                            <td>{{ (int) ($lead->display_messages_count ?? 0) }}</td>
                            <td>{{ $lead->intent ?: '-' }}</td>
                            <td class="text-end">
                                <a
                                    href="{{ route('admin.instances.leads.chat', ['instance' => $instance, 'lead' => $lead]) }}"
                                    class="btn btn-sm btn-light-primary me-2"
                                >
                                    <i class="ki-outline ki-message-text-2 fs-6 me-1"></i>View Chat
                                </a>
                                <form
                                    method="POST"
                                    action="{{ route('admin.instances.leads.destroy', ['instance' => $instance, 'lead' => $lead]) }}"
                                    class="d-inline"
                                    onsubmit="return confirm('Delete this lead?');"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light-danger">
                                        <i class="ki-outline ki-trash fs-6 me-1"></i>Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-10">No leads found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
