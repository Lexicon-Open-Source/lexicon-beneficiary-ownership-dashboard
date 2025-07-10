<div class="overflow-hidden text-green-400 bg-gray-900 rounded-lg border border-gray-700">
    <!-- Terminal Header -->
    <div class="flex justify-between items-center px-4 py-2 bg-gray-800 border-b border-gray-700">
        <div class="flex items-center space-x-2">
            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
        </div>
        <div class="font-mono text-sm text-gray-400">Job Logs</div>
        <div class="text-sm text-gray-400">
            @if(isset($getState()['error']))
                Error
            @else
                {{ count($getState() ?? []) }} entries
            @endif
        </div>
    </div>

    <!-- Terminal Content -->
    <div class="overflow-y-auto p-4 space-y-1 h-96 font-mono text-sm" id="terminal-logs">
        @if(isset($getState()['error']))
            <!-- Display error message -->
            <div class="flex flex-col py-1 pl-3 border-l-2 border-red-500">
                <div class="flex justify-between items-center mb-1 text-xs text-gray-400">
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium text-red-200 bg-red-900 rounded">
                            ERROR
                        </span>
                        <span>{{ now()->format('Y-m-d H:i:s') }}</span>
                    </div>
                </div>
                <div class="text-red-400 break-words">
                    {{ $getState()['error'] }}
                </div>
            </div>
        @elseif(empty($getState()))
            <div class="italic text-gray-500">No logs available</div>
        @else
            @foreach($getState() ?? [] as $log)
                <div class="flex flex-col border-l-2 @if($log['event_type'] === 'error') border-red-500 @elseif($log['event_type'] === 'warn') border-yellow-500 @else border-green-500 @endif pl-3 py-1">
                    <!-- Log Header -->
                    <div class="flex justify-between items-center mb-1 text-xs text-gray-400">
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                @if($log['event_type'] === 'error') bg-red-900 text-red-200
                                @elseif($log['event_type'] === 'warn') bg-yellow-900 text-yellow-200
                                @else bg-green-900 text-green-200 @endif">
                                {{ strtoupper($log['event_type']) }}
                            </span>
                            <span>{{ \Carbon\Carbon::parse($log['created_at'])->format('Y-m-d H:i:s') }}</span>
                        </div>
                        <span class="font-mono text-xs text-gray-500">{{ substr($log['id'], 0, 8) }}</span>
                    </div>

                    <!-- Log Message -->
                    <div class="text-green-400 break-words">
                        {{ trim($log['message']) }}
                    </div>

                    <!-- Log Details (if exists and decodable) -->
                    @if(!empty($log['details']))
                        @php
                            $details = $log['details'];
                        @endphp

                        @if($details)
                            <div class="mt-1 text-xs text-gray-300">
                                <span class="text-gray-500">Details:</span>
                                <pre class="inline">{{ json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            </div>
                        @endif
                    @endif

                    <!-- Data Source ID -->
                    @if(!empty($log['data_source_id']))
                        <div class="mt-1 text-xs text-gray-500">
                            <span>Source:</span> {{ trim($log['data_source_id']) }}
                        </div>
                    @endif
                </div>
            @endforeach
        @endif
    </div>

    <!-- Terminal Footer -->
    <div class="px-4 py-2 bg-gray-800 border-t border-gray-700">
        <div class="flex justify-between items-center text-xs text-gray-400">
            <div>
                @if(isset($getState()['error']))
                    Service unavailable - Check crawler service status
                @else
                    Use scroll to navigate through logs
                @endif
            </div>
            <div class="flex items-center space-x-2">
                <button
                    onclick="document.getElementById('terminal-logs').scrollTop = 0"
                    class="text-gray-400 transition-colors hover:text-white"
                    title="Scroll to top"
                >
                    ↑ Top
                </button>
                <button
                    onclick="document.getElementById('terminal-logs').scrollTop = document.getElementById('terminal-logs').scrollHeight"
                    class="text-gray-400 transition-colors hover:text-white"
                    title="Scroll to bottom"
                >
                    ↓ Bottom
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-scroll to bottom on load
    document.addEventListener('DOMContentLoaded', function() {
        const terminalLogs = document.getElementById('terminal-logs');
        if (terminalLogs) {
            terminalLogs.scrollTop = terminalLogs.scrollHeight;
        }
    });

    // Listen for refresh events and scroll to bottom
    document.addEventListener('livewire:initialized', function() {
        Livewire.on('refreshed', function() {
            setTimeout(function() {
                const terminalLogs = document.getElementById('terminal-logs');
                if (terminalLogs) {
                    terminalLogs.scrollTop = terminalLogs.scrollHeight;
                }
            }, 100);
        });
    });
</script>
