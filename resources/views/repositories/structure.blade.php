@extends('layout')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-600 mb-4">
        <a href="{{ route('repositories.index') }}" class="hover:text-blue-600">Repositories</a>
        <span>/</span>
        <a href="{{ route('repositories.show', $repoName) }}" class="hover:text-blue-600">{{ $repoName }}</a>
        <span>/</span>
        <span class="text-gray-800 font-medium">Directory Tree</span>
    </div>
    
    <h1 class="text-3xl font-bold text-gray-800 mb-2">🌳 Directory Structure</h1>
    <p class="text-gray-600">Hierarchical view of repository files and folders</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Tree View -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-100">
        <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
            <h3 class="text-lg font-bold text-white">📂 Visual Tree</h3>
        </div>
        <div class="p-6">
            <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                <pre class="text-green-400 font-mono text-sm whitespace-pre">{{ $treeString }}</pre>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4">📊 Statistics</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                    <span class="text-gray-700 font-medium">Total Items</span>
                    <span class="text-2xl font-bold text-blue-600">{{ number_format($files->count()) }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg">
                    <span class="text-gray-700 font-medium">Files</span>
                    <span class="text-2xl font-bold text-purple-600">{{ number_format($files->where('file_type', 'file')->count()) }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                    <span class="text-gray-700 font-medium">Directories</span>
                    <span class="text-2xl font-bold text-green-600">{{ number_format($files->where('file_type', 'dir')->count()) }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4">🎨 Change Status</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                    <span class="text-gray-700 font-medium">✨ New</span>
                    <span class="text-xl font-bold text-green-600">{{ number_format($files->where('change_status', 'new')->count()) }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg">
                    <span class="text-gray-700 font-medium">📝 Modified</span>
                    <span class="text-xl font-bold text-yellow-600">{{ number_format($files->where('change_status', 'modified')->count()) }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                    <span class="text-gray-700 font-medium">🗑️ Deleted</span>
                    <span class="text-xl font-bold text-red-600">{{ number_format($files->where('change_status', 'deleted')->count()) }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-700 font-medium">✅ Unchanged</span>
                    <span class="text-xl font-bold text-gray-600">{{ number_format($files->where('change_status', 'unchanged')->count()) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- File List -->
<div class="mt-6 bg-white rounded-lg shadow-md overflow-hidden border border-gray-100">
    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-bold text-gray-800">📋 All Files & Folders</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Path</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Checked</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($files as $file)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-2xl">
                        @if($file->file_type === 'dir')
                            📁
                        @else
                            📄
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-mono text-sm text-gray-800">{{ $file->file_path }}</div>
                        @if($file->folder_path)
                        <div class="text-xs text-gray-500 mt-1">in {{ $file->folder_path }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        @if($file->size)
                            {{ number_format($file->size / 1024, 2) }} KB
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                            {{ $file->change_status === 'new' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $file->change_status === 'modified' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $file->change_status === 'deleted' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $file->change_status === 'unchanged' ? 'bg-gray-100 text-gray-800' : '' }}">
                            {{ ucfirst($file->change_status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        @if($file->last_checked_at)
                            {{ $file->last_checked_at->diffForHumans() }}
                        @else
                            <span class="text-gray-400">Never</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6 flex justify-center gap-4">
    <a href="{{ route('repositories.show', $repoName) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-lg transition-colors">
        ← Back to Overview
    </a>
    <a href="{{ route('repositories.changes', $repoName) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition-colors">
        View Changes →
    </a>
</div>
@endsection
