<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Logs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .log-entry { transition: all 0.15s ease; }
        .log-entry:hover { transform: translateX(4px); }
        .context-toggle { cursor: pointer; }
        .context-content { display: none; }
        .context-content.show { display: block; }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <h1 class="text-2xl font-bold text-white">Laravel Logs</h1>
                <span class="text-gray-500 text-sm">{{ count($entries) }} entries</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="/logs" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm font-medium transition">
                    Refresh
                </a>
                <a href="/logs/clear" onclick="return confirm('Clear all logs?')" class="px-4 py-2 bg-red-600 hover:bg-red-500 rounded-lg text-sm font-medium transition">
                    Clear Logs
                </a>
            </div>
        </div>

        <!-- Filter buttons -->
        <div class="flex gap-2 mb-4">
            <button onclick="filterLogs('all')" class="filter-btn px-3 py-1.5 bg-gray-700 hover:bg-gray-600 rounded text-sm transition" data-filter="all">All</button>
            <button onclick="filterLogs('ERROR')" class="filter-btn px-3 py-1.5 bg-gray-700 hover:bg-gray-600 rounded text-sm transition" data-filter="ERROR">Errors</button>
            <button onclick="filterLogs('WARNING')" class="filter-btn px-3 py-1.5 bg-gray-700 hover:bg-gray-600 rounded text-sm transition" data-filter="WARNING">Warnings</button>
            <button onclick="filterLogs('INFO')" class="filter-btn px-3 py-1.5 bg-gray-700 hover:bg-gray-600 rounded text-sm transition" data-filter="INFO">Info</button>
            <button onclick="filterLogs('ThirdParty')" class="filter-btn px-3 py-1.5 bg-gray-700 hover:bg-gray-600 rounded text-sm transition" data-filter="ThirdParty">Third Party</button>
        </div>

        <!-- Log entries -->
        <div class="space-y-2" id="log-entries">
            @forelse($entries as $index => $entry)
                <div class="log-entry bg-gray-800 rounded-lg p-4 border-l-4 @if($entry['level'] === 'ERROR') border-red-500 @elseif($entry['level'] === 'WARNING') border-yellow-500 @elseif($entry['level'] === 'INFO') border-green-500 @else border-gray-600 @endif"
                     data-level="{{ $entry['level'] }}"
                     data-tag="{{ $entry['tag'] ?? '' }}">

                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <!-- Header row -->
                            <div class="flex items-center gap-3 mb-2 flex-wrap">
                                <span class="text-gray-500 text-xs font-mono">{{ $entry['timestamp'] }}</span>

                                <span class="px-2 py-0.5 rounded text-xs font-semibold
                                    @if($entry['level'] === 'ERROR') bg-red-500/20 text-red-400
                                    @elseif($entry['level'] === 'WARNING') bg-yellow-500/20 text-yellow-400
                                    @elseif($entry['level'] === 'INFO') bg-green-500/20 text-green-400
                                    @else bg-gray-500/20 text-gray-400 @endif">
                                    {{ $entry['level'] }}
                                </span>

                                @if($entry['tag'])
                                    <span class="px-2 py-0.5 rounded text-xs font-medium
                                        @if(str_contains($entry['tag'], 'Invoice')) bg-blue-500/20 text-blue-400
                                        @elseif(str_contains($entry['tag'], 'Order')) bg-purple-500/20 text-purple-400
                                        @else bg-gray-500/20 text-gray-400 @endif">
                                        {{ $entry['tag'] }}
                                    </span>
                                @endif
                            </div>

                            <!-- Message -->
                            <p class="text-gray-200 text-sm">{{ $entry['message'] }}</p>

                            <!-- Context (collapsible) -->
                            @if($entry['context'])
                                @php
                                    $context = $entry['context'];
                                    // Try to decode raw_content if it's a JSON string
                                    if (isset($context['raw_content']) && is_string($context['raw_content'])) {
                                        $decoded = json_decode($context['raw_content'], true);
                                        if ($decoded !== null) {
                                            $context['raw_content'] = $decoded;
                                        }
                                    }
                                @endphp
                                <div class="mt-3">
                                    <button onclick="toggleContext({{ $index }})" class="context-toggle text-xs text-gray-500 hover:text-gray-300 flex items-center gap-1">
                                        <svg class="w-4 h-4 transform transition" id="chevron-{{ $index }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        Context Data
                                    </button>
                                    <div id="context-{{ $index }}" class="context-content mt-2">
                                        <pre class="bg-gray-900 rounded p-3 text-xs text-gray-300 overflow-x-auto whitespace-pre-wrap break-words">{{ json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p>No log entries found</p>
                </div>
            @endforelse
        </div>
    </div>

    <script>
        function toggleContext(index) {
            const content = document.getElementById('context-' + index);
            const chevron = document.getElementById('chevron-' + index);
            content.classList.toggle('show');
            chevron.classList.toggle('rotate-90');
        }

        function filterLogs(filter) {
            const entries = document.querySelectorAll('.log-entry');
            const buttons = document.querySelectorAll('.filter-btn');

            buttons.forEach(btn => {
                btn.classList.remove('bg-blue-600');
                btn.classList.add('bg-gray-700');
            });
            document.querySelector(`[data-filter="${filter}"]`).classList.remove('bg-gray-700');
            document.querySelector(`[data-filter="${filter}"]`).classList.add('bg-blue-600');

            entries.forEach(entry => {
                const level = entry.dataset.level;
                const tag = entry.dataset.tag;

                if (filter === 'all') {
                    entry.style.display = 'block';
                } else if (filter === 'ThirdParty') {
                    entry.style.display = tag.includes('ThirdParty') ? 'block' : 'none';
                } else {
                    entry.style.display = level === filter ? 'block' : 'none';
                }
            });
        }
    </script>
</body>
</html>
