@extends('admin.layout.admin-layout')

@php
    $intentTags = collect(old('intents', $aiSetting->intents ?? []))
        ->filter(fn ($tag) => is_string($tag) && trim($tag) !== '')
        ->map(fn ($tag) => trim($tag))
        ->unique()
        ->values();
@endphp

@section('title', 'AI Settings')
@section('page-title', 'AI Settings')
@section('page-actions')
    <a href="{{ route('admin.businesses.show', $instance->business_id) }}" class="btn btn-light-primary">Back to Business</a>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.instances.update', $instance) }}">
        @csrf
        @method('PUT')

        <div class="card mb-7">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="fw-bold m-0">Personality</h3>
                </div>
            </div>
            <div class="card-body">
                <label class="form-label required">System Prompt</label>
                <textarea name="system_prompt" rows="7" class="form-control form-control-solid" required>{{ old('system_prompt', $aiSetting->system_prompt) }}</textarea>
                <div class="form-text mt-2">Main behavior and tone used in every reply.</div>
                <div class="text-muted fs-8 mt-1">Example: "You are a polite spa assistant. Keep replies short and helpful."</div>
            </div>
        </div>

        <div class="card mb-7">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="fw-bold m-0">Rules</h3>
                </div>
            </div>
            <div class="card-body">
                <label class="form-label">Rules</label>
                <textarea name="rules" rows="5" class="form-control form-control-solid">{{ old('rules', $aiSetting->rules) }}</textarea>
                <div class="form-text mt-2">Hard rules that the assistant should always follow.</div>
                <div class="text-muted fs-8 mt-1">Example: "Always ask for preferred time before confirming booking."</div>
            </div>
        </div>

        <div class="card mb-7">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="fw-bold m-0">Restrictions</h3>
                </div>
            </div>
            <div class="card-body">
                <label class="form-label">Restrictions</label>
                <textarea name="restrictions" rows="5" class="form-control form-control-solid">{{ old('restrictions', $aiSetting->restrictions) }}</textarea>
                <div class="form-text mt-2">Topics or behaviors that the assistant must avoid.</div>
                <div class="text-muted fs-8 mt-1">Example: "Do not provide medical advice. Do not mention internal systems."</div>
            </div>
        </div>

        <div class="card mb-7">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="fw-bold m-0">Intents</h3>
                </div>
            </div>
            <div class="card-body">
                <label class="form-label">Intent Tags</label>
                <div class="form-text mb-3">Add short tags (1-2 words). AI will classify the lead based on the whole conversation.</div>

                <div class="border border-gray-300 rounded p-3 bg-light" id="intent_tags_box">
                    <div class="d-flex flex-wrap gap-2 mb-3" id="intent_tags_list"></div>
                    <input
                        id="intent_tag_input"
                        type="text"
                        class="form-control form-control-solid"
                        placeholder="Type intent and press Enter (example: booking, price inquiry, complaint)"
                    />
                    <div id="intent_hidden_inputs"></div>
                </div>

                <div class="text-muted fs-8 mt-2">Example tags: booking, price inquiry, complaint, info request</div>
            </div>
        </div>

        <div class="card mb-7">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="fw-bold m-0">Configuration</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-6">
                    <div class="col-md-4">
                        <label class="form-label required">Model</label>
                        <input type="text" name="model" value="{{ old('model', $aiSetting->model) }}" class="form-control form-control-solid" required />
                        <div class="text-muted fs-8 mt-1">Example: gpt-4o-mini</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Temperature</label>
                        <input type="number" step="0.1" min="0" max="2" name="temperature" value="{{ old('temperature', $aiSetting->temperature) }}" class="form-control form-control-solid" required />
                        <div class="text-muted fs-8 mt-1">Example: 0.7 for balanced responses</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Context Limit</label>
                        <input type="number" min="1" name="context_limit" value="{{ old('context_limit', $aiSetting->context_limit) }}" class="form-control form-control-solid" required />
                        <div class="text-muted fs-8 mt-1">Example: 10 = last 10 messages used in context</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mb-8">
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tags = @json($intentTags->all());
            var tagsList = document.getElementById('intent_tags_list');
            var hiddenInputs = document.getElementById('intent_hidden_inputs');
            var input = document.getElementById('intent_tag_input');

            function cleanTag(value) {
                return (value || '').trim().replace(/\s+/g, ' ');
            }

            function removeTag(tag) {
                tags = tags.filter(function (item) {
                    return item !== tag;
                });
                render();
            }

            function addTag(raw) {
                var tag = cleanTag(raw);
                if (!tag || tags.indexOf(tag) !== -1) {
                    return;
                }

                tags.push(tag);
                render();
            }

            function render() {
                tagsList.innerHTML = '';
                hiddenInputs.innerHTML = '';

                tags.forEach(function (tag) {
                    var badge = document.createElement('span');
                    badge.className = 'badge badge-light-primary fw-semibold d-inline-flex align-items-center gap-2 px-3 py-2';
                    badge.innerHTML = '<span>' + tag + '</span>';

                    var removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn btn-icon btn-sm btn-light-danger';
                    removeBtn.innerHTML = '<i class="ki-outline ki-cross fs-7"></i>';
                    removeBtn.addEventListener('click', function () {
                        removeTag(tag);
                    });

                    badge.appendChild(removeBtn);
                    tagsList.appendChild(badge);

                    var hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'intents[]';
                    hidden.value = tag;
                    hiddenInputs.appendChild(hidden);
                });
            }

            input.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ',') {
                    event.preventDefault();
                    addTag(input.value);
                    input.value = '';
                }
            });

            input.addEventListener('blur', function () {
                if (cleanTag(input.value) !== '') {
                    addTag(input.value);
                    input.value = '';
                }
            });

            render();
        });
    </script>
@endpush
