@extends('layout')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-600 mb-4">
        <a href="{{ route('repositories.index') }}" class="hover:text-blue-600">Repositories</a>
        <span>/</span>
        <span class="text-gray-800 font-medium">{{ $repoName }}</span>
    </div>
    
    <div class="flex justify-between items-start">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $repoName }}</h1>
            <p class="text-sm text-gray-600 font-mono">{{ $repository->repo_url }}</p>
        </div>
        <div class="flex gap-2">
            <button onclick="openSyncModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center gap-2">
                🔄 Resync
            </button>
            <button onclick="location.reload()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors">
                Reload Page
            </button>
        </div>
    </div>
</div>

<!-- Sync Confirmation Modal -->
<div id="syncModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Sync Repository</h3>
            <form action="{{ route('repositories.sync') }}" method="POST">
                @csrf
                <input type="hidden" name="repo_name" value="{{ $repoName }}">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Repository URL</label>
                    <input type="text" name="repo_url" value="{{ $repository->repo_url }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Branch</label>
                    <input type="text" name="branch" value="main" placeholder="main" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">API Token</label>
                    <input type="password" name="token" placeholder="GitHub/GitLab Token" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <p class="text-xs text-gray-500 mt-1">Leave empty to use default system credentials.</p>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="closeSyncModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Sync Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex justify-between items-start mb-2">
            <div class="text-4xl">📁</div>
            <div class="bg-white bg-opacity-20 rounded-full p-2">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                </svg>
            </div>
        </div>
        <div class="text-3xl font-bold mb-1">{{ number_format($stats['total_files']) }}</div>
        <div class="text-blue-100 text-sm font-medium">Total Items</div>
        <div class="mt-2 text-xs text-blue-100">
            {{ number_format($stats['files']) }} files, {{ number_format($stats['directories']) }} dirs
        </div>
    </div>

    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex justify-between items-start mb-2">
            <div class="text-4xl">✨</div>
            <div class="bg-white bg-opacity-20 rounded-full p-2">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
        <div class="text-3xl font-bold mb-1">{{ number_format($stats['new_files']) }}</div>
        <div class="text-green-100 text-sm font-medium">New Files</div>
        <a href="{{ route('repositories.changes', ['repoName' => $repoName, 'status' => 'new']) }}" class="mt-2 text-xs text-green-100 hover:text-white inline-block">
            View all →
        </a>
    </div>

    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex justify-between items-start mb-2">
            <div class="text-4xl">📝</div>
            <div class="bg-white bg-opacity-20 rounded-full p-2">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                </svg>
            </div>
        </div>
        <div class="text-3xl font-bold mb-1">{{ number_format($stats['modified_files']) }}</div>
        <div class="text-yellow-100 text-sm font-medium">Modified Files</div>
        <a href="{{ route('repositories.changes', ['repoName' => $repoName, 'status' => 'modified']) }}" class="mt-2 text-xs text-yellow-100 hover:text-white inline-block">
            View all →
        </a>
    </div>

    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex justify-between items-start mb-2">
            <div class="text-4xl">🗑️</div>
            <div class="bg-white bg-opacity-20 rounded-full p-2">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
        <div class="text-3xl font-bold mb-1">{{ number_format($stats['deleted_files']) }}</div>
        <div class="text-red-100 text-sm font-medium">Deleted Files</div>
        <a href="{{ route('repositories.changes', ['repoName' => $repoName, 'status' => 'deleted']) }}" class="mt-2 text-xs text-red-100 hover:text-white inline-block">
            View all →
        </a>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <a href="{{ route('repositories.structure', $repoName) }}" class="bg-white hover:bg-gray-50 rounded-lg shadow-md p-6 transition-all hover:shadow-lg border border-gray-100">
        <div class="text-3xl mb-3">🌳</div>
        <h3 class="font-bold text-gray-800 mb-1">Directory Tree</h3>
        <p class="text-sm text-gray-600">View hierarchical structure</p>
    </a>

    <a href="{{ route('repositories.changes', $repoName) }}" class="bg-white hover:bg-gray-50 rounded-lg shadow-md p-6 transition-all hover:shadow-lg border border-gray-100">
        <div class="text-3xl mb-3">📊</div>
        <h3 class="font-bold text-gray-800 mb-1">All Changes</h3>
        <p class="text-sm text-gray-600">View detailed change log</p>
    </a>

    <a href="{{ route('repositories.notifications', $repoName) }}" class="bg-white hover:bg-gray-50 rounded-lg shadow-md p-6 transition-all hover:shadow-lg border border-gray-100 relative">
        <div class="text-3xl mb-3">🔔</div>
        <h3 class="font-bold text-gray-800 mb-1">Notifications</h3>
        <p class="text-sm text-gray-600">Manage alerts</p>
        @if($unsentNotifications > 0)
        <span class="absolute top-4 right-4 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">
            {{ $unsentNotifications }}
        </span>
        @endif
    </a>

    <a href="{{ route('repositories.logs', $repoName) }}" class="bg-white hover:bg-gray-50 rounded-lg shadow-md p-6 transition-all hover:shadow-lg border border-gray-100">
        <div class="text-3xl mb-3">📜</div>
        <h3 class="font-bold text-gray-800 mb-1">Sync Logs</h3>
        <p class="text-sm text-gray-600">View sync history</p>
    </a>
</div>

<!-- Latest Sync Info -->
@if($latestSync)
<div class="bg-white rounded-lg shadow-md p-6 mb-8 border border-gray-100">
    <h3 class="text-lg font-bold text-gray-800 mb-4">📋 Latest Sync</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <div class="text-sm text-gray-600 mb-1">Status</div>
            <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold
                {{ $latestSync->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                {{ $latestSync->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                {{ $latestSync->status === 'running' ? 'bg-blue-100 text-blue-800' : '' }}
                {{ $latestSync->status === 'pending' ? 'bg-gray-100 text-gray-800' : '' }}">
                {{ ucfirst($latestSync->status) }}
            </span>
        </div>
        <div>
            <div class="text-sm text-gray-600 mb-1">Runtime</div>
            <div class="font-semibold text-gray-800">{{ $latestSync->runtime_seconds ?? 0 }}s</div>
        </div>
        <div>
            <div class="text-sm text-gray-600 mb-1">Files Scanned</div>
            <div class="font-semibold text-gray-800">{{ number_format($latestSync->files_scanned) }}</div>
        </div>
        <div>
            <div class="text-sm text-gray-600 mb-1">Synced At</div>
            <div class="font-semibold text-gray-800">{{ $latestSync->created_at->diffForHumans() }}</div>
        </div>
    </div>
    @if($latestSync->error_message)
    <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-3">
        <div class="text-sm font-semibold text-red-800 mb-1">Error:</div>
        <div class="text-sm text-red-700">{{ $latestSync->error_message }}</div>
    </div>
    @endif
</div>
@endif

<!-- Recent Changes -->
@if($recentChanges->isNotEmpty())
<div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-100">
    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-bold text-gray-800">🔥 Recent Changes</h3>
    </div>
    <div class="divide-y divide-gray-200">
        @foreach($recentChanges as $change)
        <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 flex-1">
                    <div class="text-2xl">
                        @if($change->file_type === 'dir')
                            📁
                        @else
                            📄
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="font-mono text-sm text-gray-800">{{ $change->file_path }}</div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $change->updated_at->diffForHumans() }}
                            @if($change->size)
                                • {{ number_format($change->size / 1024, 2) }} KB
                            @endif
                        </div>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold
                    {{ $change->change_status === 'new' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $change->change_status === 'modified' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $change->change_status === 'deleted' ? 'bg-red-100 text-red-800' : '' }}">
                    {{ ucfirst($change->change_status) }}
                </span>
            </div>
        </div>
        @endforeach
    </div>
    <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
        <a href="{{ route('repositories.changes', $repoName) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
            View all changes →
        </a>
    </div>
</div>
@endif

<script>
    function openSyncModal() {
        document.getElementById('syncModal').classList.remove('hidden');
    }

    function closeSyncModal() {
        document.getElementById('syncModal').classList.add('hidden');
    }

    // Close modal if clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('syncModal');
        if (event.target == modal) {
            closeSyncModal();
        }
    }
</script>

@endsection
