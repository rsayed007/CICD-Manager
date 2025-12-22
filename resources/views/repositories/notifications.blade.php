@extends('layout')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-600 mb-4">
        <a href="{{ route('repositories.index') }}" class="hover:text-blue-600">Repositories</a>
        <span>/</span>
        <a href="{{ route('repositories.show', $repoName) }}" class="hover:text-blue-600">{{ $repoName }}</a>
        <span>/</span>
        <span class="text-gray-800 font-medium">Notifications</span>
    </div>
    
    <h1 class="text-3xl font-bold text-gray-800 mb-2">🔔 Notifications</h1>
    <p class="text-gray-600">Manage change alerts and notification channels</p>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
        <div class="text-3xl mb-2">📬</div>
        <div class="text-3xl font-bold mb-1">{{ number_format($stats['total']) }}</div>
        <div class="text-blue-100 text-sm font-medium">Total Notifications</div>
    </div>
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
        <div class="text-3xl mb-2">✅</div>
        <div class="text-3xl font-bold mb-1">{{ number_format($stats['sent']) }}</div>
        <div class="text-green-100 text-sm font-medium">Sent</div>
    </div>
    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
        <div class="text-3xl mb-2">⏳</div>
        <div class="text-3xl font-bold mb-1">{{ number_format($stats['unsent']) }}</div>
        <div class="text-orange-100 text-sm font-medium">Pending</div>
    </div>
</div>

<!-- Filter Tabs -->
<div class="bg-white rounded-lg shadow-md p-4 mb-6 border border-gray-100">
    <div class="mb-3">
        <h3 class="text-sm font-semibold text-gray-700 mb-2">Filter by Type:</h3>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('repositories.notifications', ['repoName' => $repoName, 'type' => 'all', 'sent' => $sent]) }}" 
               class="px-4 py-2 rounded-lg font-medium transition-colors {{ $type === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                🔍 All Types
            </a>
            <a href="{{ route('repositories.notifications', ['repoName' => $repoName, 'type' => 'email', 'sent' => $sent]) }}" 
               class="px-4 py-2 rounded-lg font-medium transition-colors {{ $type === 'email' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                ✉️ Email
            </a>
            <a href="{{ route('repositories.notifications', ['repoName' => $repoName, 'type' => 'ui_alert', 'sent' => $sent]) }}" 
               class="px-4 py-2 rounded-lg font-medium transition-colors {{ $type === 'ui_alert' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                🔔 UI Alert
            </a>
            <a href="{{ route('repositories.notifications', ['repoName' => $repoName, 'type' => 'message_log', 'sent' => $sent]) }}" 
               class="px-4 py-2 rounded-lg font-medium transition-colors {{ $type === 'message_log' ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                📝 Message Log
            </a>
        </div>
    </div>
    <div>
        <h3 class="text-sm font-semibold text-gray-700 mb-2">Filter by Status:</h3>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('repositories.notifications', ['repoName' => $repoName, 'type' => $type, 'sent' => 'all']) }}" 
               class="px-4 py-2 rounded-lg font-medium transition-colors {{ $sent === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                All Status
            </a>
            <a href="{{ route('repositories.notifications', ['repoName' => $repoName, 'type' => $type, 'sent' => 'sent']) }}" 
               class="px-4 py-2 rounded-lg font-medium transition-colors {{ $sent === 'sent' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                ✅ Sent
            </a>
            <a href="{{ route('repositories.notifications', ['repoName' => $repoName, 'type' => $type, 'sent' => 'unsent']) }}" 
               class="px-4 py-2 rounded-lg font-medium transition-colors {{ $sent === 'unsent' ? 'bg-orange-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                ⏳ Pending
            </a>
        </div>
    </div>
</div>

@if($notifications->isEmpty())
    <div class="bg-white rounded-lg shadow-md p-12 text-center border border-gray-100">
        <div class="text-6xl mb-4">🔕</div>
        <h3 class="text-2xl font-bold text-gray-800 mb-2">No Notifications Found</h3>
        <p class="text-gray-600">No notifications match your current filters.</p>
    </div>
@else
    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-100">
        <div class="divide-y divide-gray-200">
            @foreach($notifications as $notification)
            <div class="p-6 hover:bg-gray-50 transition-colors">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="text-2xl">
                                @if($notification->notification_type === 'email')
                                    ✉️
                                @elseif($notification->notification_type === 'ui_alert')
                                    🔔
                                @else
                                    📝
                                @endif
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        {{ $notification->notification_type === 'email' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ $notification->notification_type === 'ui_alert' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                        {{ $notification->notification_type === 'message_log' ? 'bg-teal-100 text-teal-800' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $notification->notification_type)) }}
                                    </span>
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        {{ $notification->change_type === 'new' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $notification->change_type === 'modified' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $notification->change_type === 'deleted' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ ucfirst($notification->change_type) }}
                                    </span>
                                    @if($notification->sent)
                                        <span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800">
                                            ✓ Sent
                                        </span>
                                    @else
                                        <span class="px-2 py-1 rounded text-xs font-semibold bg-orange-100 text-orange-800">
                                            ⏳ Pending
                                        </span>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-600 mt-1">
                                    {{ $notification->created_at->format('M d, Y H:i:s') }}
                                    @if($notification->sent_at)
                                        • Sent {{ $notification->sent_at->diffForHumans() }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="ml-11">
                            <p class="text-gray-800 font-medium mb-1">{{ $notification->message }}</p>
                            <code class="text-xs bg-gray-100 px-2 py-1 rounded text-gray-700 font-mono">{{ $notification->file_path }}</code>
                            @if($notification->metadata)
                            <div class="mt-2 text-xs text-gray-600">
                                @if(isset($notification->metadata['size']))
                                    Size: {{ number_format($notification->metadata['size'] / 1024, 2) }} KB
                                @endif
                                @if(isset($notification->metadata['sha']))
                                    • SHA: <code class="bg-gray-100 px-1 rounded">{{ substr($notification->metadata['sha'], 0, 7) }}</code>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $notifications->appends(['type' => $type, 'sent' => $sent])->links() }}
    </div>
@endif

<div class="mt-6 flex justify-center">
    <a href="{{ route('repositories.show', $repoName) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-lg transition-colors">
        ← Back to Overview
    </a>
</div>
@endsection
