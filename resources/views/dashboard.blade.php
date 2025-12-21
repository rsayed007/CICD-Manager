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
    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 p-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800"><a href="{{ route('servers.show', $server) }}" class="hover:text-blue-600">{{ $server->name }}</a></h2>
                <p class="text-sm text-gray-500">{{ $server->username }}@</p>
                <p class="text-sm text-gray-500 font-mono bg-gray-100 rounded px-1 inline-block">{{ $server->ip_address }}</p>
            </div>
            <div class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Active</div>
        </div>
        <p class="text-gray-600 text-sm mb-4">Deploy Path: <span class="font-mono text-xs">{{ $server->deploy_path }}</span></p>
        
        <div class="flex justify-end gap-2">
            <a href="{{ route('servers.show', $server) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Manage &rarr;</a>
        </div>
    </div>
    @endforeach
</div>

<!-- Add Server Modal -->
<div id="add-server-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Add New Server</h3>
            <form action="{{ route('servers.store') }}" method="POST" class="mt-4 text-left">
                @csrf
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
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">SSH Key Path (Optional)</label>
                    <input type="text" name="ssh_key_path" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="document.getElementById('add-server-modal').classList.add('hidden')" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancel</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
