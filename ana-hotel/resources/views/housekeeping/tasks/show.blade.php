@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold">Task Details</h2>
                    <a href="{{ route('housekeeping-tasks.index') }}" class="text-blue-600 hover:text-blue-800">&larr; Back to Tasks</a>
                </div>

                @if (session('success'))
                    <div class="mb-4 rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Room</dt>
                        <dd class="mt-1 text-lg text-gray-900">{{ $task->room->room_number ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Assigned To</dt>
                        <dd class="mt-1 text-lg text-gray-900">{{ $task->assignedTo->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Assigned By</dt>
                        <dd class="mt-1 text-lg text-gray-900">{{ $task->assignedBy->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Task Type</dt>
                        <dd class="mt-1 text-lg text-gray-900">{{ ucfirst(str_replace('_',' ', $task->task_type)) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Priority</dt>
                        <dd class="mt-1 text-lg text-gray-900">{{ ucfirst($task->priority) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1 text-lg text-gray-900">{{ ucfirst(str_replace('_',' ', $task->status)) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                        <dd class="mt-1 text-lg text-gray-900">{{ optional($task->due_date)->format('Y-m-d H:i') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Completed At</dt>
                        <dd class="mt-1 text-lg text-gray-900">{{ optional($task->completed_at)->format('Y-m-d H:i') ?? '—' }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                        <dd class="mt-1 text-gray-900">{{ $task->description ?? '—' }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Notes</dt>
                        <dd class="mt-1 text-gray-900">{{ $task->notes ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
