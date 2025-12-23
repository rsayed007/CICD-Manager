@extends('layout')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-600 mb-4">
        <a href="{{ route('repositories.index') }}" class="hover:text-blue-600">Repositories</a>
        <span>/</span>
        <a href="{{ route('repositories.show', $repoName) }}" class="hover:text-blue-600">{{ $repoName }}</a>
        <span>/</span>
        <span class="text-gray-800 font-medium">Sync Logs</span>
    </div>
    
    <h1 class="text-3xl font-bold text-gray-800 mb-2">📜 Sync History</h1>
    <p class="text-gray-600">Complete log of all repository synchronization operations</p>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
        <div class="text-3xl mb-2">📊</div>
        <div class="text-3xl font-bold mb-1">{{ number_format($stats['total']) }}</div>
        <div class="text-blue-100 text-sm font-medium">Total Syncs</div>
    </div>
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
        <div class="text-3xl mb-2">✅</div>
        <div class="text-3xl font-bold mb-1">{{ number_format($stats['completed']) }}</div>
        <div class="text-green-100 text-sm font-medium">Completed</div>
    </div>
    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
        <div class="text-3xl mb-2">❌</div>
        <div class="text-3xl font-bold mb-1">{{ number_format($stats['failed']) }}</div>
        <div class="text-red-100 text-sm font-medium">Failed</div>
    </div>
</div>

<!-- Filter Tabs -->
<div class="bg-white rounded-lg shadow-md p-4 mb-6 border border-gray-100">
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('repositories.logs', ['repoName' => $repoName, 'status' => 'all']) }}" 
           class="px-4 py-2 rounded-lg font-medium transition-colors {{ $status === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            🔍 All Logs
        </a>
        <a href="{{ route('repositories.logs', ['repoName' => $repoName, 'status' => 'completed']) }}" 
           class="px-4 py-2 rounded-lg font-medium transition-colors {{ $status === 'completed' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            ✅ Completed
        </a>
        <a href="{{ route('repositories.logs', ['repoName' => $repoName, 'status' => 'failed']) }}" 
           class="px-4 py-2 rounded-lg font-medium transition-colors {{ $status === 'failed' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            ❌ Failed
        </a>
        <a href="{{ route('repositories.logs', ['repoName' => $repoName, 'status' => 'running']) }}" 
           class="px-4 py-2 rounded-lg font-medium transition-colors {{ $status === 'running' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            🔄 Running
        </a>
        <a href="{{ route('repositories.logs', ['repoName' => $repoName, 'status' => 'pending']) }}" 
           class="px-4 py-2 rounded-lg font-medium transition-colors {{ $status === 'pending' ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            ⏳ Pending
        </a>
    </div>
</div>

@if($logs->isEmpty())
    <div class="bg-white rounded-lg shadow-md p-12 text-center border border-gray-100">
        <div class="text-6xl mb-4">📭</div>
        <h3 class="text-2xl font-bold text-gray-800 mb-2">No Sync Logs Found</h3>
        <p class="text-gray-600">No synchronization logs match your current filters.</p>
    </div>
@else
    <div class="space-y-4">
        @foreach($logs as $log)
        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-100">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="text-3xl">
                            @if($log->status === 'completed')
                                ✅
                            @elseif($log->status === 'failed')
                                ❌
                            @elseif($log->status === 'running')
                                🔄
                            @else
                                ⏳
                            @endif
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-bold text-gray-800">Sync #{{ $log->id }}</h3>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    {{ $log->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $log->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $log->status === 'running' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $log->status === 'pending' ? 'bg-gray-100 text-gray-800' : '' }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-600 mt-1">
                                Started: {{ $log->started_at ? $log->started_at->format('M d, Y H:i:s') : 'Not started' }}
                                @if($log->completed_at)
                                    • Completed: {{ $log->completed_at->format('M d, Y H:i:s') }}
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($log->runtime_seconds !== null)
                    <div class="text-right">
                        <div class="text-2xl font-bold text-gray-800">{{ $log->runtime_seconds }}s</div>
                        <div class="text-xs text-gray-600">Runtime</div>
                    </div>
                    @endif
                </div>

                @if($log->status === 'completed')
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-4">
                    <div class="bg-blue-50 rounded-lg p-3">
                        <div class="text-xl font-bold text-blue-600">{{ number_format($log->files_scanned) }}</div>
                        <div class="text-xs text-blue-800 font-medium">Files Scanned</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-3">
                        <div class="text-xl font-bold text-green-600">{{ number_format($log->new_files) }}</div>
                        <div class="text-xs text-green-800 font-medium">New Files</div>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-3">
                        <div class="text-xl font-bold text-yellow-600">{{ number_format($log->modified_files) }}</div>
                        <div class="text-xs text-yellow-800 font-medium">Modified</div>
                    </div>
                    <div class="bg-red-50 rounded-lg p-3">
                        <div class="text-xl font-bold text-red-600">{{ number_format($log->deleted_files) }}</div>
                        <div class="text-xs text-red-800 font-medium">Deleted</div>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-3">
                        <div class="text-xl font-bold text-purple-600">{{ number_format($log->total_changes) }}</div>
                        <div class="text-xs text-purple-800 font-medium">Total Changes</div>
                    </div>
                </div>
                @endif

                @if($log->error_message)
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start gap-2">
                        <div class="text-xl">⚠️</div>
                        <div class="flex-1">
                            <div class="text-sm font-semibold text-red-800 mb-1">Error Message:</div>
                            <div class="text-sm text-red-700 font-mono">{{ $log->error_message }}</div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <div class="text-xs text-gray-500">
                        Created {{ $log->created_at->diffForHumans() }}
                    </div>
                    <div class="text-xs text-gray-500 font-mono">
                        {{ $log->repo_url }}
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $logs->appends(['status' => $status])->links() }}
    </div>
@endif

<div class="mt-6 flex justify-center">
    <a href="{{ route('repositories.show', $repoName) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-lg transition-colors">
        ← Back to Overview
    </a>
</div>
@endsection
