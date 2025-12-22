@extends('layout')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Deployment Servers</h1>
    <button onclick="document.getElementById('add-server-modal').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
        + Add Server
    </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($servers as $server)
    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 p-6 {{ !$server->is_active ? 'opacity-75 bg-gray-50' : '' }}">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800"><a href="{{ route('servers.show', $server) }}" class="hover:text-blue-600">{{ $server->name }}</a></h2>
                <p class="text-sm text-gray-500">{{ $server->username }}@</p>
                <p class="text-sm text-gray-500 font-mono bg-gray-100 rounded px-1 inline-block">{{ $server->ip_address }}</p>
            </div>
            @if($server->is_active)
                <div class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded font-bold">Active</div>
            @else
                <div class="bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded font-bold">Inactive</div>
            @endif
        </div>
        <p class="text-gray-600 text-sm mb-4">Deploy Path: <span class="font-mono text-xs">{{ $server->deploy_path }}</span></p>
        
        <div class="text-xs text-gray-400 mb-4">
            @if($server->github_repo)
                <span class="flex items-center gap-1">📦 {{ $server->github_owner }}/{{ $server->github_repo }}</span>
            @endif
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('servers.show', $server) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Manage &rarr;</a>
        </div>
    </div>
    @endforeach
</div>

<!-- Add Server Modal -->
<div id="add-server-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 text-center mb-4">Add New Server</h3>
            <form action="{{ route('servers.store') }}" method="POST" class="text-left">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                        <input type="text" name="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">IP Address</label>
                        <input type="text" name="ip_address" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                        <input type="text" name="username" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Deploy Path</label>
                        <input type="text" name="deploy_path" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">SSH Key Path (Optional)</label>
                    <input type="text" name="ssh_key_path" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="/home/user/.ssh/id_rsa">
                </div>

                <hr class="my-4 border-gray-200">
                <h4 class="text-sm font-bold text-gray-500 uppercase mb-2">GitHub Configuration (Optional)</h4>

                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">GitHub Owner</label>
                        <input type="text" name="github_owner" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="e.g. laravel">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">GitHub Repo</label>
                        <input type="text" name="github_repo" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="e.g. framework">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">GitHub Token</label>
                    <input type="password" name="github_token" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="ghp_...">
                </div>

                <div class="mb-4">
                   <label class="inline-flex items-center">
                        <input type="checkbox" name="is_active" class="form-checkbox h-5 w-5 text-blue-600" checked>
                        <span class="ml-2 text-gray-700">Server is Active</span>
                    </label>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="document.getElementById('add-server-modal').classList.add('hidden')" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancel</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Save Server</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
