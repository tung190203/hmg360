@extends('backend.index')

@section('title')
Lịch sử nhận webhook
@endsection

@section('breadcrumb')
<li class="breadcrumb-item active">Lịch sử nhận webhook</li>
@endsection

@section('css')
<style>
    .webhook-card { border-radius: 8px; border: none; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075); }
    .webhook-table th { white-space: nowrap; font-size: 0.78rem; color: #6c757d; text-transform: uppercase; }
    .webhook-table td { vertical-align: middle; font-size: 0.86rem; }
    .webhook-id { max-width: 190px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .webhook-file { max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .payload-preview {
        max-height: 520px;
        overflow: auto;
        background: #111827;
        color: #e5e7eb;
        border-radius: 6px;
        padding: 14px;
        font-size: 0.82rem;
        white-space: pre-wrap;
    }
    .summary-pill { display: inline-flex; align-items: center; gap: 5px; }
</style>
@endsection

@section('content')
<hr class="mt-0">
<section class="content">
    <div class="container-fluid">
        <div class="card webhook-card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('backend_chatbot_webhooks') }}">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group mb-md-0">
                                <label class="small text-muted">Loại event</label>
                                <select class="form-control form-control-sm" name="event_type">
                                    <option value="">Tất cả</option>
                                    @foreach($eventTypes as $eventType)
                                        <option value="{{ $eventType }}" @selected($filters['event_type'] === $eventType)>{{ $eventType }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-md-0">
                                <label class="small text-muted">Trạng thái</label>
                                <select class="form-control form-control-sm" name="status">
                                    <option value="">Tất cả</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-md-0">
                                <label class="small text-muted">Từ ngày</label>
                                <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-md-0">
                                <label class="small text-muted">Đến ngày</label>
                                <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-md-0">
                                <label class="small text-muted">Tìm kiếm</label>
                                <input type="text" name="keyword" value="{{ $filters['keyword'] }}" class="form-control form-control-sm" placeholder="event_id, job_id, doc_id, file">
                            </div>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm btn-block">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card webhook-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title mb-0">
                    <i class="fas fa-inbox mr-2"></i>Webhook đã nhận
                </h3>
                <span class="text-muted small ml-auto">Tổng: {{ number_format($events->total()) }}</span>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover webhook-table mb-0">
                    <thead>
                        <tr>
                            <th>Thời gian</th>
                            <th>Event</th>
                            <th>Trạng thái</th>
                            <th>Event ID</th>
                            <th>Thông tin</th>
                            <th class="text-right">Docs</th>
                            <th class="text-right">Chunks</th>
                            <th class="text-right">Tokens</th>
                            <th class="text-right">Cost</th>
                            <th class="text-right">Payload</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                            @php
                                $payload = $event->payload_json ?: [];
                                $eventType = $event->event_type ?: ($payload['event'] ?? 'unknown');
                                $status = $event->status ?: ($eventType === 'test' ? 'test_ok' : null);
                                $statusClass = match ($status) {
                                    'success', 'completed', 'test_ok' => 'badge-success',
                                    'error', 'failed' => 'badge-danger',
                                    default => 'badge-secondary',
                                };
                                $summary = $payload['message']
                                    ?? $event->source_filename
                                    ?? $payload['title']
                                    ?? $payload['error_message']
                                    ?? '-';
                                $documents = $event->documents_uploaded;
                                if ($documents === null && isset($payload['documents_extracted'])) {
                                    $documents = $payload['documents_extracted'];
                                }
                                $payloadJson = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                $modalId = 'webhook-payload-' . md5($event->event_id);
                            @endphp
                            <tr>
                                <td>
                                    <div>{{ optional($event->received_at)->format('d/m/Y H:i:s') }}</div>
                                    @if(!empty($payload['run_at']))
                                        <div class="text-muted small">run_at: {{ $payload['run_at'] }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $eventType }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $statusClass }}">{{ $status ?: '-' }}</span>
                                </td>
                                <td>
                                    <div class="webhook-id" title="{{ $event->event_id }}">{{ $event->event_id }}</div>
                                    @if($event->job_id)
                                        <div class="text-muted small">job: {{ $event->job_id }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="webhook-file" title="{{ $summary }}">{{ $summary }}</div>
                                    @if($event->doc_id)
                                        <div class="text-muted small">doc: {{ $event->doc_id }}</div>
                                    @endif
                                </td>
                                <td class="text-right">{{ $documents !== null ? number_format($documents) : '-' }}</td>
                                <td class="text-right">{{ $event->chunk_count !== null ? number_format($event->chunk_count) : '-' }}</td>
                                <td class="text-right">{{ $event->embedding_tokens !== null ? number_format($event->embedding_tokens) : '-' }}</td>
                                <td class="text-right">
                                    ${{ number_format((float) $event->cost_usd_total, 6) }}
                                </td>
                                <td class="text-right">
                                    <button type="button" class="btn btn-xs btn-outline-primary" data-toggle="modal" data-target="#{{ $modalId }}">
                                        <i class="fas fa-code"></i> Xem
                                    </button>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    Chưa có webhook nào khớp bộ lọc.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($events->hasPages())
                <div class="card-footer">
                    {{ $events->links() }}
                </div>
            @endif
        </div>

        @foreach($events as $event)
            @php
                $payload = $event->payload_json ?: [];
                $eventType = $event->event_type ?: ($payload['event'] ?? 'unknown');
                $status = $event->status ?: ($eventType === 'test' ? 'test_ok' : null);
                $payloadJson = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $modalId = 'webhook-payload-' . md5($event->event_id);
            @endphp
            <div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Payload webhook: {{ $event->event_id }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <span class="text-muted small d-block">Event</span>
                                    <strong>{{ $eventType }}</strong>
                                </div>
                                <div class="col-md-3">
                                    <span class="text-muted small d-block">Status</span>
                                    <strong>{{ $status ?: '-' }}</strong>
                                </div>
                                <div class="col-md-3">
                                    <span class="text-muted small d-block">Received</span>
                                    <strong>{{ optional($event->received_at)->format('d/m/Y H:i:s') }}</strong>
                                </div>
                                <div class="col-md-3">
                                    <span class="text-muted small d-block">Cost</span>
                                    <strong>${{ number_format((float) $event->cost_usd_total, 6) }}</strong>
                                </div>
                            </div>
                            <pre class="payload-preview mb-0">{{ $payloadJson ?: '{}' }}</pre>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Đóng</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endsection
