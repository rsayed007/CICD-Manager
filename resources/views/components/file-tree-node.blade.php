@props(['name', 'node', 'path' => '', 'selectedDirs' => [], 'selectedFiles' => []])

@php
    $fullPath = $path ? $path . '/' . $name : $name;
    $type = $node['type'] ?? 'dir';
    $isDir = $type === 'dir';
    // Check if this path is in the selected arrays
    $isChecked = $isDir 
        ? in_array($fullPath, $selectedDirs) 
        : in_array($fullPath, $selectedFiles);
    
    // For directories, we might want to check if any child is selected to auto-expand? 
    // Keeping it simple for now: recursive rendering.
@endphp

<li class="my-1" x-data="{ open: false }">
    <div class="flex items-center gap-2 group">
        @if($isDir)
            <span @click="open = !open" class="cursor-pointer text-gray-500 hover:text-gray-700 w-4 font-mono">
                <span x-show="!open">▶</span>
                <span x-show="open">▼</span>
            </span>
        @else
            <span class="w-4"></span>
        @endif

        <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-100 px-2 py-1 rounded w-full">
            <input type="checkbox" 
                   name="{{ $isDir ? 'directories[]' : 'files_list[]' }}" 
                   value="{{ $fullPath }}" 
                   class="rounded text-blue-600 focus:ring-blue-500"
                   {{ $isChecked ? 'checked' : '' }}
            >
            
            <span class="text-xs">{{ $isDir ? '📁' : '📄' }}</span>
            <span class="text-sm text-gray-700 {{ $isChecked ? 'font-semibold text-blue-700' : '' }}">
                {{ $name }}
            </span>
            
            @if(isset($node['change_status']) && $node['change_status'] !== 'unchanged')
                <span class="text-xs px-1 rounded {{ $node['change_status'] === 'new' ? 'bg-green-100 text-green-800' : ($node['change_status'] === 'modified' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    {{ substr($node['change_status'], 0, 1) }}
                </span>
            @endif
        </label>
    </div>

    @if($isDir)
        <ul x-show="open" class="pl-6 border-l ml-2 border-gray-200">
            @foreach($node as $childName => $childNode)
                @if(is_array($childNode))
                    <x-file-tree-node 
                        :name="$childName" 
                        :node="$childNode" 
                        :path="$fullPath" 
                        :selectedDirs="$selectedDirs"
                        :selectedFiles="$selectedFiles"
                    />
                @endif
            @endforeach
        </ul>
    @endif
</li>
