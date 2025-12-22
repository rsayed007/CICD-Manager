@extends('layout')

@section('content')
<div x-data="{ showSyncModal: false }">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">📦 Repository Monitoring</h1>
            <p class="text-gray-600 mt-1">Monitor Git repositories and track changes automatically</p>
        </div>
        <button @click="showSyncModal = true" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-3 px-6 rounded-lg shadow-lg transition-all duration-200 transform hover:scale-105">
            <span class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Sync New Repository
            </span>
        </button>
    </div>

    @if($repositories->isEmpty())
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <div class="text-6xl mb-4">📦</div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">No Repositories Yet</h3>
            <p class="text-gray-600 mb-6">Start monitoring your Git repositories by syncing your first one!</p>
            <button @click="showSyncModal = true" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg">
                Sync Your First Repository
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($repositories as $repo)
            <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <h2 class="text-xl font-bold text-gray-800 mb-1">
                                <a href="{{ route('repositories.show', $repo->repo_name) }}" class="hover:text-blue-600 transition-colors">
                                    {{ $repo->repo_name }}
                                </a>
                            </h2>
                            <p class="text-xs text-gray-500 font-mono truncate">{{ $repo->repo_url }}</p>
                        </div>
                        @php
                            $syncLog = $syncLogs->get($repo->repo_name);
                            $statusColor = $syncLog && $syncLog->last_status === 'completed' ? 'green' : ($syncLog && $syncLog->last_status === 'failed' ? 'red' : 'gray');
                        @endphp
                        <span class="bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800 text-xs px-2 py-1 rounded-full font-semibold">
                            {{ $syncLog ? ucfirst($syncLog->last_status) : 'Never' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="bg-blue-50 rounded-lg p-3">
                            <div class="text-2xl font-bold text-blue-600">{{ number_format($repo->total_files) }}</div>
                            <div class="text-xs text-blue-800 font-medium">Total Files</div>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-3">
                            <div class="text-2xl font-bold text-purple-600">{{ $repo->new_files + $repo->modified_files + $repo->deleted_files }}</div>
                            <div class="text-xs text-purple-800 font-medium">Changes</div>
                        </div>
                    </div>

                    @if($repo->new_files > 0 || $repo->modified_files > 0 || $repo->deleted_files > 0)
                    <div class="flex gap-2 mb-4 text-xs">
                        @if($repo->new_files > 0)
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded font-semibold">
                            +{{ $repo->new_files }} new
                        </span>
                        @endif
                        @if($repo->modified_files > 0)
                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded font-semibold">
                            ~{{ $repo->modified_files }} modified
                        </span>
                        @endif
                        @if($repo->deleted_files > 0)
                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded font-semibold">
                            -{{ $repo->deleted_files }} deleted
                        </span>
                        @endif
                    </div>
                    @endif

                    <div class="text-xs text-gray-500 mb-4">
                        Last updated: {{ $repo->last_updated->diffForHumans() }}
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('repositories.show', $repo->repo_name) }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-4 rounded-lg font-medium transition-colors">
                            View Details
                        </a>
                        <a href="{{ route('repositories.structure', $repo->repo_name) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-lg font-medium transition-colors">
                            🌳 Tree
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif

    <!-- Sync Repository Modal -->
    <div x-show="showSyncModal" 
         x-cloak
         @click.away="showSyncModal = false"
         class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50 flex items-center justify-center"
         style="display: none;">
        <div @click.stop class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-lg">
                <h3 class="text-xl font-bold text-white">🔄 Sync Repository</h3>
                <p class="text-blue-100 text-sm mt-1">Connect to a Git repository and start monitoring</p>
            </div>
            
            <form action="{{ route('repositories.sync') }}" method="POST" class="p-6">
                @csrf
                
                <div class="mb-5">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Repository Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="repo_name" 
                           value="{{ old('repo_name') }}"
                           placeholder="e.g., my-project"
                           class="shadow-sm border border-gray-300 rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                           required>
                    <p class="text-xs text-gray-500 mt-1">A friendly name to identify this repository</p>
                </div>

                <div class="mb-5">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Git API URL <span class="text-red-500">*</span>
                    </label>
                    <input type="url" 
                           name="repo_url" 
                           value="{{ old('repo_url') }}"
                           placeholder="https://api.github.com/repos/owner/repo"
                           class="shadow-sm border border-gray-300 rounded-lg w-full py-3 px-4 text-gray-700 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                           required>
                    <p class="text-xs text-gray-500 mt-1">
                        Examples:<br>
                        • GitHub: https://api.github.com/repos/owner/repo<br>
                        • GitLab: https://gitlab.com/api/v4/projects/12345<br>
                        • Bitbucket: https://api.bitbucket.org/2.0/repositories/workspace/repo
                    </p>
                </div>

                <div class="mb-5">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Branch
                    </label>
                    <input type="text" 
                           name="branch" 
                           value="{{ old('branch', 'main') }}"
                           placeholder="main"
                           class="shadow-sm border border-gray-300 rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Default: main</p>
                </div>

                <div class="mb-5">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        API Token (Optional)
                    </label>
                    <input type="password" 
                           name="token" 
                           value="{{ old('token') }}"
                           placeholder="ghp_xxxxxxxxxxxxx"
                           class="shadow-sm border border-gray-300 rounded-lg w-full py-3 px-4 text-gray-700 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Required for private repositories. Get tokens from GitHub/GitLab/Bitbucket settings.</p>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                    <button type="button" 
                            @click="showSyncModal = false"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-2 px-6 rounded-lg shadow-lg transition-all">
                        🚀 Start Sync
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
