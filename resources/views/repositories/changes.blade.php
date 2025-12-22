@extends('layout')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-600 mb-4">
        <a href="{{ route('repositories.index') }}" class="hover:text-blue-600">Repositories</a>
        <span>/</span>
        <a href="{{ route('repositories.show', $repoName) }}" class="hover:text-blue-600">{{ $repoName }}</a>
        <span>/</span>
        <span class="text-gray-800 font-medium">Changes</span>
    </div>
    
    <h1 class="text-3xl font-bold text-gray-800 mb-2">📊 File Changes</h1>
    <p class="text-gray-600">Track all modifications, additions, and deletions</p>
</div>

<!-- Filter Tabs -->
<div class="bg-white rounded-lg shadow-md p-4 mb-6 border border-gray-100">
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('repositories.changes', ['repoName' => $repoName, 'status' => 'all']) }}" 
           class="px-4 py-2 rounded-lg font-medium transition-colors {{ $status === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            🔍 All Changes ({{ array_sum($stats) }})
        </a>
        <a href="{{ route('repositories.changes', ['repoName' => $repoName, 'status' => 'new']) }}" 
           class="px-4 py-2 rounded-lg font-medium transition-colors {{ $status === 'new' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            ✨ New ({{ $stats['new'] }})
        </a>
        <a href="{{ route('repositories.changes', ['repoName' => $repoName, 'status' => 'modified']) }}" 
           class="px-4 py-2 rounded-lg font-medium transition-colors {{ $status === 'modified' ? 'bg-yellow-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            📝 Modified ({{ $stats['modified'] }})
        </a>
        <a href="{{ route('repositories.changes', ['repoName' => $repoName, 'status' => 'deleted']) }}" 
           class="px-4 py-2 rounded-lg font-medium transition-colors {{ $status === 'deleted' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            🗑️ Deleted ({{ $stats['deleted'] }})
        </a>
    </div>
</div>

@if($changes->isEmpty())
    <div class="bg-white rounded-lg shadow-md p-12 text-center border border-gray-100">
        <div class="text-6xl mb-4">✅</div>
        <h3 class="text-2xl font-bold text-gray-800 mb-2">No Changes Found</h3>
        <p class="text-gray-600">
            @if($status === 'all')
                All files are up to date with no pending changes.
            @else
                No {{ $status }} files found in this repository.
            @endif
        </p>
    </div>
@else
    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File Path</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SHA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($changes as $change)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-2xl">
                            @if($change->file_type === 'dir')
                                📁
                            @else
                                📄
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-mono text-sm text-gray-800 break-all">{{ $change->file_path }}</div>
                            @if($change->folder_path)
                            <div class="text-xs text-gray-500 mt-1">📂 {{ $change->folder_path }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            @if($change->size)
                                <span class="font-mono">{{ number_format($change->size / 1024, 2) }} KB</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                {{ $change->change_status === 'new' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $change->change_status === 'modified' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $change->change_status === 'deleted' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ ucfirst($change->change_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($change->sha)
                                <code class="text-xs bg-gray-100 px-2 py-1 rounded text-gray-700">{{ substr($change->sha, 0, 7) }}</code>
                            @else
                                <span class="text-gray-400 text-sm">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <div>{{ $change->updated_at->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $change->updated_at->format('H:i:s') }}</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $changes->links() }}
    </div>
@endif

<div class="mt-6 flex justify-center">
    <a href="{{ route('repositories.show', $repoName) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-lg transition-colors">
        ← Back to Overview
    </a>
</div>
@endsection
