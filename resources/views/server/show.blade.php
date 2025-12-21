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
        <form action="{{ route('servers.config.update', $server) }}" method="POST">
            @csrf
            
            <!-- <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Directories (One per line)</label>
                <textarea name="directories[]" class="w-full shadow border rounded p-2 h-32 font-mono text-sm" placeholder="src/app&#10;config/">{{ implode("\n", $server->directories->pluck('path')->toArray()) }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Paths will be recursive copied using rsync/scp.</p>
            </div> -->
            <!-- Hack to handle array input from textarea by splitting in controller or simple JS? 
                 Controller expects array. Let's simpler: use a textarea and split in JS on submit or use multiple inputs.
                 Wait, standard form submit with name="directories[]" only works for multiple inputs. 
                 Let's use a small script to allow adding inputs or just one big textarea and parse in controller? 
                 Controller logic I wrote expects `directories` array.
                 Let's stick to the controller logic: "foreach $request->directories".
                 If I use textarea, I need to split it.
                 Let's update the View to have dynamic inputs or just change Controller to explode newline? 
                 Controller modification is safer/easier than dynamic JS inputs for now.
                 BUT I cannot change controller easily now without rewriting tool.
                 I'll usage AlpineJS to handle the list UI.
            -->

            <!-- Redoing UI for Directories with AlpineJS -->
             <div class="mb-4" x-data="{ paths: {{ $server->directories->pluck('path') }} }">
                <label class="block text-gray-700 text-sm font-bold mb-2">Directories</label>
                <template x-for="(path, index) in paths" :key="index">
                    <div class="flex gap-2 mb-2">
                        <input type="text" name="directories[]" x-model="paths[index]" class="shadow appearance-none border rounded w-full py-1 px-2 text-sm">
                        <button type="button" @click="paths.splice(index, 1)" class="text-red-500 hover:text-red-700">&times;</button>
                    </div>
                </template>
                <button type="button" @click="paths.push('')" class="text-blue-500 text-sm">+ Add Directory</button>
                <!-- If empty, add hidden input to clear? No, controller deletes all first. -->
            </div>

            <div class="mb-4" x-data="{ files: {{ $server->deployFiles->pluck('path') }} }">
                <label class="block text-gray-700 text-sm font-bold mb-2">Files</label>
                <template x-for="(file, index) in files" :key="index">
                    <div class="flex gap-2 mb-2">
                        <input type="text" name="files_list[]" x-model="files[index]" class="shadow appearance-none border rounded w-full py-1 px-2 text-sm">
                        <button type="button" @click="files.splice(index, 1)" class="text-red-500 hover:text-red-700">&times;</button>
                    </div>
                </template>
                <button type="button" @click="files.push('')" class="text-blue-500 text-sm">+ Add File</button>
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
