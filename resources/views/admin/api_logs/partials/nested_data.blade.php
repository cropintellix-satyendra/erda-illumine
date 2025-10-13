@if(is_array($data) || is_object($data))
    @php
        $data = json_decode(json_encode($data), true);
        $level = $level ?? 1;
    @endphp
    @if($level <= 3) {{-- Limit nesting to prevent infinite loops --}}
        @foreach($data as $nestedKey => $nestedValue)
            <div class="nested-row" style="margin-left: {{ $level * 20 }}px; border-left: 2px solid #dee2e6; padding-left: 10px; margin-bottom: 5px;">
                <div class="d-flex">
                    <div class="nested-key" style="min-width: 150px; font-weight: bold;">{{ $nestedKey }}:</div>
                    <div class="nested-value">
                        @if(is_array($nestedValue) || is_object($nestedValue))
                            @if(count($nestedValue) > 0)
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="toggleNested(this)">
                                    <i class="fas fa-plus"></i> Show {{ count($nestedValue) }} items
                                </button>
                                <div class="nested-content" style="display: none;">
                                    @include('admin.api_logs.partials.nested_data', ['data' => $nestedValue, 'level' => $level + 1])
                                </div>
                            @else
                                <span class="text-muted">Empty array</span>
                            @endif
                        @elseif(is_bool($nestedValue))
                            <span class="badge badge-{{ $nestedValue ? 'success' : 'danger' }}">{{ $nestedValue ? 'true' : 'false' }}</span>
                        @elseif(is_numeric($nestedValue))
                            <code>{{ $nestedValue }}</code>
                        @elseif(is_string($nestedValue) && strlen($nestedValue) > 50)
                            <div class="long-text">
                                {{ Str::limit($nestedValue, 50) }}
                                <button type="button" class="btn btn-sm btn-link" onclick="showFullText(this, '{{ addslashes($nestedValue) }}')">Show More</button>
                            </div>
                        @else
                            {{ $nestedValue }}
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="text-muted">Too deeply nested...</div>
    @endif
@else
    @if(is_bool($data))
        <span class="badge badge-{{ $data ? 'success' : 'danger' }}">{{ $data ? 'true' : 'false' }}</span>
    @elseif(is_numeric($data))
        <code>{{ $data }}</code>
    @elseif(is_string($data) && strlen($data) > 50)
        <div class="long-text">
            {{ Str::limit($data, 50) }}
            <button type="button" class="btn btn-sm btn-link" onclick="showFullText(this, '{{ addslashes($data) }}')">Show More</button>
        </div>
    @else
        {{ $data }}
    @endif
@endif
