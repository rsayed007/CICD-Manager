@extends('layout')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">{{ $server->name }}</h1>
        <p class="text-gray-600">{{ $server->username }}@<span class="font-mono">{{ $server->ip_address }}</span>:{{ $server->deploy_path }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">Back</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Configuration -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4 border-b pb-2">Deployment Configuration</h2>
        
        <!-- Repository Selector -->
        @if($repositories->count() > 0)
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Select Repository Source</label>
            <select onchange="window.location.href='?repo='+this.value" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                @foreach($repositories as $repo)
                    <option value="{{ $repo }}" {{ $selectedRepo == $repo ? 'selected' : '' }}>{{ $repo }}</option>
                @endforeach
            </select>
            @if(empty($repositoryTree))
                <p class="text-red-500 text-xs mt-1">Repository empty or structure not found. Sync the repository first.</p>
            @endif
        </div>
        @else
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                <p class="font-bold">No Repositories Found</p>
                <p class="text-sm">Please go to <a href="{{ route('repositories.index') }}" class="underline">Repository Manager</a> to add and sync a repository first.</p>
            </div>
        @endif

        <form action="{{ route('servers.config.update', $server) }}" method="POST">
            @csrf
            
            <div class="mb-4 h-96 overflow-y-auto border rounded p-4 bg-gray-50">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Select Files & Directories</h3>
                
                @if(!empty($repositoryTree))
                    <ul class="text-sm">
                        @foreach($repositoryTree as $name => $node)
                            <x-file-tree-node 
                                :name="$name" 
                                :node="$node" 
                                :selectedDirs="$server->directories->pluck('path')->toArray()"
                                :selectedFiles="$server->deployFiles->pluck('path')->toArray()"
                            />
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-500 text-center italic mt-10">No repository structure available.</p>
                @endif
            </div>

            <div class="mt-6 flex justify-between">
                <button type="button" onclick="simulate()" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded">Simulate</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Save Config</button>
            </div>
        </form>
    </div>

    <!-- Actions & History -->
    <div class="space-y-6">
        <!-- Deployment Action -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4 border-b pb-2">Actions</h2>
            <form action="{{ route('servers.trigger', $server) }}" method="POST" onsubmit="return confirm('Are you sure you want to trigger a deployment to {{ $server->name }}?');">
                @csrf
                <p class="text-gray-600 mb-4">Trigger GitHub Action deployment workflow.</p>
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded flex justify-center items-center gap-2">
                    🚀 Deploy to Production
                </button>
            </form>
        </div>

        <!-- Log History -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4 border-b pb-2">Recent Deployments</h2>
            <div class="overflow-y-auto max-h-64">
                @forelse($server->workflowLogs as $log)
                <div class="border-b py-2 last:border-0">
                    <div class="flex justify-between">
                        <span class="font-bold text-sm">#{{ $log->id }}</span>
                        <span class="text-xs text-gray-500">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm mt-1">Status: <span class="font-mono {{ $log->status == 'success' ? 'text-green-600' : 'text-yellow-600' }}">{{ $log->status }}</span></p>
                    <div class="text-xs text-gray-400 mt-1 truncate">{{ $log->logs }}</div>
                </div>
                @empty
                <p class="text-gray-500 text-sm">No deployment history found.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Simulation Modal -->
<div id="sim-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden z-50 flex items-center justify-center">
    <div class="bg-gray-900 text-green-400 font-mono p-6 rounded shadow-lg w-3/4 max-h-screen overflow-auto border border-gray-700">
        <h3 class="text-white text-lg mb-4 border-b border-gray-700 pb-2">Simulation Output</h3>
        <pre id="sim-output" class="whitespace-pre-wrap text-sm"></pre>
        <button onclick="document.getElementById('sim-modal').classList.add('hidden')" class="mt-4 bg-gray-700 hover:bg-gray-600 text-white py-1 px-4 rounded text-sm">Close</button>
    </div>
</div>

<script>
async function simulate() {
    const btn = event.target;
    const originalText = btn.innerText;
    btn.innerText = 'Loading...';
    
    try {
        const res = await fetch("{{ route('servers.simulate', $server) }}");
        const data = await res.json();
        document.getElementById('sim-output').innerText = data.simulation;
        document.getElementById('sim-modal').classList.remove('hidden');
    } catch(e) {
        alert('Simulation failed');
    } finally {
        btn.innerText = originalText;
    }
}
</script>
@endsection
