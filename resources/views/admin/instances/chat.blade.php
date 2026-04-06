@extends('admin.layout.admin-layout')

@section('title', 'Lead Chat')
@section('page-title', 'Lead Chat')
@section('page-actions')
    <a href="{{ route('admin.instances.leads', $instance) }}" class="btn btn-light-primary">
        Back to Leads
    </a>
@endsection

@section('content')
    <div class="card mb-7">
        <div class="card-body py-6">
            <div class="d-flex flex-wrap gap-6">
                <div>
                    <div class="text-muted fs-7 mb-1">Name</div>
                    <div class="fw-bold text-gray-900">{{ $lead->name ?: '-' }}</div>
                </div>
                <div>
                    <div class="text-muted fs-7 mb-1">Phone</div>
                    <div class="fw-bold text-gray-900">{{ $lead->phone }}</div>
                </div>
                <div>
                    <div class="text-muted fs-7 mb-1">Intent</div>
                    <div class="fw-bold text-gray-900">{{ $lead->intent ?: '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="fw-bold m-0">Conversation</h3>
            </div>
        </div>
        <div class="card-body">
            @if ($messages->isEmpty())
                <div class="text-center text-muted py-10">No messages yet.</div>
            @else
                <div class="d-flex flex-column gap-5">
                    @foreach ($messages as $message)
                        <div class="d-flex {{ $message->direction === 'outbound' ? 'justify-content-end' : 'justify-content-start' }}">
                            <div class="p-4 rounded mw-700px {{ $message->direction === 'outbound' ? 'bg-light-primary text-gray-900' : 'bg-light text-gray-800' }}">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge {{ $message->direction === 'outbound' ? 'badge-light-primary' : 'badge-light-success' }}">
                                        {{ ucfirst($message->direction) }}
                                    </span>
                                    <span class="text-muted fs-8 ms-4">{{ optional($message->created_at)->format('Y-m-d H:i') }}</span>
                                </div>
                                <div class="fs-6">{!! nl2br(e($message->content)) !!}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
