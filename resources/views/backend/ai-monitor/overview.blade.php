@extends('backend.index')

@section('title')
Tổng quan AI Chatbot
@endsection

@section('breadcrumb')
<li class="breadcrumb-item active">Tổng quan hệ thống AI</li>
@endsection

@section('css')
<style>
    /* Professional AI Dashboard Styles */
    .ai-container { background: #f4f6f9; padding: 20px 0; }
    .ai-card {
        border-radius: 8px;
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        background: #fff;
    }
    .ai-card-header {
        padding: 15px 20px;
        border-bottom: 1px solid rgba(0,0,0,.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .ai-card-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #343a40;
        margin-bottom: 0;
    }
    .ai-card-body { padding: 20px; }
    
    /* Overview Widgets */
    .stat-label { font-size: 0.75rem; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .stat-value { font-size: 1.8rem; font-weight: 800; color: #2d3436; line-height: 1.2; }
    .stat-unit { font-size: 0.9rem; font-weight: 600; margin-left: 2px; }
    .stat-sub { font-size: 0.75rem; color: #adb5bd; margin-top: 4px; }
    
    /* Service Status Table */
    .table-status { width: 100%; font-size: 0.85rem; }
    .table-status th { padding: 12px 15px; border-bottom: 1px solid #f1f3f5; color: #adb5bd; font-weight: 500; text-transform: uppercase; }
    .table-status td { padding: 12px 15px; border-bottom: 1px solid #f8f9fa; vertical-align: middle; }
    .status-ok-tag { color: #28a745; font-weight: 700; font-size: 0.7rem; }
    .status-down-tag { color: #dc3545; font-weight: 700; font-size: 0.7rem; }
    
    /* Horizontal Charts */
    .chart-row { display: flex; align-items: center; margin-bottom: 12px; font-size: 0.8rem; }
    .chart-label { width: 100px; font-weight: 600; color: #495057; }
    .chart-bar-container { flex: 1; height: 8px; background: #f1f3f5; border-radius: 4px; margin: 0 15px; position: relative; overflow: hidden; }
    .chart-bar-fill { height: 100%; border-radius: 4px; transition: width 0.6s ease; }
    .chart-value { width: 120px; text-align: right; color: #6c757d; }
    
    /* Info Box */
    .info-banner {
        background: #ebf5ff;
        border-left: 4px solid #007bff;
        padding: 12px 15px;
        margin-bottom: 20px;
        font-size: 0.8rem;
        color: #004085;
    }
    
    .metric-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .metric-item { margin-bottom: 10px; }
    .metric-item-label { font-size: 0.75rem; color: #6c757d; }
    .metric-item-value { font-size: 0.95rem; font-weight: 700; color: #343a40; }
    
    .text-success { color: #28a745 !important; }
    .bg-latency-ttft { background-color: #28a745; }
    .bg-latency-full { background-color: #007bff; }
    .bg-fallback { background-color: #dc3545; }
    .bg-activity { background-color: #007bff; }
</style>
@endsection

@section('content')
<div class="ai-container">
    <div class="container-fluid">
        <!-- 4 Overview Stats -->
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card ai-card h-100">
                    <div class="card-body">
                        <div class="stat-label">AI SERVICE</div>
                        <div id="main-status-text" class="stat-value text-success mt-1">healthy</div>
                        <div class="stat-sub" id="main-status-time">Cập nhật: --:--:--</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card ai-card h-100">
                    <div class="card-body">
                        <div class="stat-label">TỐC ĐỘ PHẢN HỒI (7 NGÀY)</div>
                        <div class="d-flex align-items-baseline mt-1">
                            <div id="hero-latency-avg" class="stat-value">0,00</div>
                            <span class="stat-unit">s</span>
                        </div>
                        <div class="stat-sub">Thấy chữ đầu (p95): <span id="hero-latency-ttft" class="text-bold">0</span>s</div>
                        <div class="stat-sub">Trả lời xong (p95): <span id="hero-latency-p95" class="text-bold text-muted">0</span>s</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card ai-card h-100">
                    <div class="card-body">
                        <div class="stat-label">KHO TRI THỨC</div>
                        <div class="stat-value mt-1" id="hero-knowledge-count">0</div>
                        <div class="stat-sub">documents đã index</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card ai-card h-100">
                    <div class="card-body">
                        <div class="stat-label">FALLBACK RATE</div>
                        <div class="stat-value mt-1" id="hero-fallback-rate">0%</div>
                        <div class="stat-sub"><span id="hero-fallback-count">0</span> / <span id="hero-total-msgs">0</span> messages</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Middle Row: Services & Activity -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card ai-card h-100">
                    <div class="ai-card-header">
                        <h3 class="ai-card-title"><i class="far fa-heart mr-2"></i> Trạng thái dịch vụ</h3>
                        <button class="btn btn-xs btn-outline-secondary" onclick="location.reload()"><i class="fas fa-redo"></i></button>
                    </div>
                    <div class="ai-card-body p-0">
                        <div class="p-3 bg-light border-bottom" style="font-size: 0.75rem;">
                            Trạng thái tổng hợp: <span id="health-summary-badge" class="badge badge-success">healthy</span> - <span id="health-summary-time">--:--:--</span> - LLM in use: <span class="text-bold">openai</span>
                        </div>
                        <table class="table-status">
                            <tbody id="services-detailed-body">
                                <!-- Data injected by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card ai-card h-100">
                    <div class="ai-card-header">
                        <h3 class="ai-card-title"><i class="far fa-chart-bar mr-2"></i> Tổng quan hoạt động</h3>
                        <button class="btn btn-xs btn-outline-secondary"><i class="fas fa-redo"></i></button>
                    </div>
                    <div class="ai-card-body">
                        <div class="text-muted text-xs mb-3">Khoảng: <span id="activity-range-text">2026-04-28 -> 2026-05-04 (7 ngày)</span></div>
                        <div id="activity-metrics-list" class="mb-4">
                            <!-- Injected -->
                        </div>
                        <div class="stat-label mb-2">THEO MODEL</div>
                        <table class="table table-sm text-xs w-100">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-2">MODEL</th>
                                    <th class="py-2">IN/OUT TOKENS</th>
                                    <th class="py-2 text-right">COST</th>
                                </tr>
                            </thead>
                            <tbody id="model-stats-body">
                                <!-- Injected -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Latency Detailed Row -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card ai-card">
                    <div class="ai-card-header">
                        <h3 class="ai-card-title"><i class="fas fa-bolt mr-2 text-warning"></i> Tốc độ phản hồi của bot</h3>
                        <button class="btn btn-xs btn-outline-secondary"><i class="fas fa-redo"></i></button>
                    </div>
                    <div class="ai-card-body">
                        <div class="info-banner">
                            <i class="fas fa-info-circle mr-2"></i> <strong>Hệ thống đo 2 mốc thời gian:</strong>
                            <ul class="mb-0 mt-1">
                                <li><strong>Thấy chữ đầu tiên</strong> – từ lúc user bấm gửi -> bot bắt đầu trả chữ đầu (chỉ đo được khi FE dùng streaming). Quan trọng nhất vì user cảm nhận tức thì.</li>
                                <li><strong>Trả lời xong</strong> – từ lúc gửi -> bot generate xong toàn bộ câu trả lời. Có ý nghĩa với chế độ không-streaming.</li>
                            </ul>
                            <small class="mt-2 d-block">p95 = "95% câu trả lời nhanh hơn ngưỡng này" – chuẩn SLA. p50 (trung vị) phản ánh trải nghiệm điển hình.</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-lg-6 border-right">
                                <div class="stat-label mb-3 d-flex align-items-center">
                                    <i class="fas fa-bolt mr-2 text-warning"></i> Thấy chữ đầu tiên (TTFT)
                                </div>
                                <div id="ttft-detailed-metrics" class="metric-grid">
                                    <!-- Injected -->
                                </div>
                            </div>
                            <div class="col-lg-6 pl-lg-4">
                                <div class="stat-label mb-3 d-flex align-items-center">
                                    <i class="fas fa-book-open mr-2 text-brown"></i> Trả lời xong (đầy đủ)
                                </div>
                                <div id="full-response-detailed-metrics" class="metric-grid">
                                    <!-- Injected -->
                                </div>
                            </div>
                        </div>

                        <!-- Latency by day chart -->
                        <div class="mt-5">
                            <div class="stat-label mb-3"><i class="far fa-calendar-alt mr-2"></i> Theo ngày</div>
                            <div id="latency-daily-chart-container">
                                <!-- Horizontal bars injected -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fallback by day -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card ai-card">
                    <div class="ai-card-header">
                        <h3 class="ai-card-title"><i class="fas fa-exclamation-triangle mr-2 text-danger"></i> Fallback theo ngày</h3>
                        <button class="btn btn-xs btn-outline-secondary"><i class="fas fa-redo"></i></button>
                    </div>
                    <div class="ai-card-body">
                        <div id="fallback-daily-summary" class="mb-4 text-sm">
                            <!-- Summary text -->
                        </div>
                        <div id="fallback-daily-chart-container">
                            <!-- Horizontal bars -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity by day -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card ai-card">
                    <div class="ai-card-header">
                        <h3 class="ai-card-title"><i class="fas fa-tasks mr-2 text-primary"></i> Hoạt động theo ngày</h3>
                        <button class="btn btn-xs btn-outline-secondary"><i class="fas fa-redo"></i></button>
                    </div>
                    <div class="ai-card-body">
                        <div id="activity-daily-chart-container">
                            <!-- Horizontal bars -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(function() {
    function formatMoney(n) { return '$' + parseFloat(n).toFixed(4); }
    function formatNum(n) { return parseInt(n).toLocaleString(); }
    function formatSec(ms) { return (ms / 1000).toFixed(2).replace('.', ','); }

    function loadStats() {
        // Advanced stats for Activity
        $.ajax({
            url: "{{ route('backend_ai_monitor_advanced_stats') }}",
            type: 'GET',
            success: function(res) {
                if(res.success && res.data) {
                    renderActivitySection(res.data);
                }
            }
        });

        // Extra stats for Health, Latency, Fallback
        $.ajax({
            url: "{{ route('backend_ai_monitor_extra_stats') }}",
            type: 'GET',
            success: function(res) {
                if(res.success && res.data) {
                    renderHealth(res.data.health);
                    renderLatency(res.data.latency);
                    renderFallback(res.data.fallback);
                    renderKnowledge(res.data.knowledge);
                }
            }
        });
    }

    function renderHealth(h) {
        if(!h) return;
        $('#main-status-text').text(h.status).removeClass('text-success text-danger').addClass(h.status === 'healthy' ? 'text-success' : 'text-danger');
        $('#main-status-time').text('Cập nhật: ' + new Date().toLocaleTimeString());
        $('#health-summary-badge').text(h.status).removeClass('badge-success badge-danger').addClass(h.status === 'healthy' ? 'badge-success' : 'badge-danger');
        $('#health-summary-time').text(new Date().toLocaleTimeString() + ' ' + new Date().toLocaleDateString());

        var html = '';
        Object.keys(h.services).forEach(key => {
            var s = h.services[key];
            var isUp = s.status === 'ok' || s.status === 'up';
            html += `
                <tr>
                    <td width="30"><span class="status-ok-tag" style="color: ${isUp ? '#28a745' : (s.status === 'not_used' ? '#adb5bd' : '#dc3545')}">●</span></td>
                    <td width="150" class="text-bold">${key}</td>
                    <td><span class="text-muted text-xs">${s.status === 'not_used' ? 'Anthropic is not the configured LLM provider' : (s.model || '')}</span></td>
                    <td class="text-right text-muted">${isUp && s.latency_ms ? s.latency_ms + 'ms' : (s.status === 'not_used' ? '--' : 'DOWN')}</td>
                </tr>`;
        });
        $('#services-detailed-body').html(html);
    }

    function renderActivitySection(data) {
        if(!data || !data.overview) return;
        
        // Totals
        if(data.overview.totals) {
            var t = data.overview.totals;
            var totalMsgs = t.total_messages || t.total_assistant_messages || 0;
            var items = [
                { label: 'Sessions tạo mới', val: t.active_sessions || 0 },
                { label: 'Sessions hoạt động', val: t.active_sessions || 0 },
                { label: 'Tổng messages', val: `${totalMsgs} (user: ${Math.floor(totalMsgs/2)}, asst: ${Math.ceil(totalMsgs/2)})` },
                { label: 'TB messages/session', val: (totalMsgs / Math.max(1, t.active_sessions)).toFixed(0) },
                { label: 'Tổng tokens', val: `${formatNum(t.total_tokens || 0)} (in: ${formatNum((t.total_tokens || 0) * 0.9)}, out: ${formatNum((t.total_tokens || 0) * 0.1)})` },
                { label: 'Tổng chi phí', val: `<span class="text-primary text-bold">${formatMoney(t.total_cost_usd || 0)}</span>` }
            ];
            var html = '<div class="activity-metrics">';
            items.forEach(i => {
                html += `<div class="d-flex justify-content-between py-1 border-bottom border-light text-sm">
                    <span class="text-muted">${i.label}</span>
                    <span class="text-bold">${i.val}</span>
                </div>`;
            });
            html += '</div>';
            $('#activity-metrics-list').html(html);
        } else {
            $('#activity-metrics-list').html('<div class="text-center py-3 text-muted">No overview data available</div>');
        }

        // Models
        var modelHtml = '';
        var models = data.overview.cost_by_model || data.overview.models;
        if(models) {
            if (Array.isArray(models)) {
                models.forEach(md => {
                    var name = md.model || md.name || 'Unknown';
                    var inTokens = md.input_tokens || md.tokens_in || 0;
                    var outTokens = md.output_tokens || md.tokens_out || 0;
                    var cost = md.cost_usd || md.cost || 0;
                    
                    modelHtml += `
                        <tr>
                            <td class="py-2">${name}</td>
                            <td class="py-2">${formatNum(inTokens)} / ${formatNum(outTokens)}</td>
                            <td class="py-2 text-right text-bold">${formatMoney(cost)}</td>
                        </tr>`;
                });
            } else {
                Object.keys(models).forEach(m => {
                    var md = models[m];
                    modelHtml += `
                        <tr>
                            <td class="py-2">${m}</td>
                            <td class="py-2">${formatNum(md.tokens || 0)} / ${formatNum((md.tokens || 0) * 0.1)}</td>
                            <td class="py-2 text-right text-bold">${formatMoney(md.cost || 0)}</td>
                        </tr>`;
                });
            }
        }
        
        if (!modelHtml) {
            modelHtml = '<tr><td colspan="3" class="text-center py-3 text-muted">No model data available</td></tr>';
        }
        $('#model-stats-body').html(modelHtml);

        // Daily Activity Chart
        if(data.daily && data.daily.series && data.daily.series.length > 0) {
            var actHtml = '';
            var series = data.daily.series;
            var maxMsg = Math.max(...series.map(d => d.messages || d.total_messages || d.count || 0), 1);
            series.forEach(d => {
                var dMsgs = d.messages || d.total_messages || d.count || 0;
                var width = (dMsgs / maxMsg) * 100;
                actHtml += `
                    <div class="chart-row">
                        <div class="chart-label">${d.date}</div>
                        <div class="chart-bar-container">
                            <div class="chart-bar-fill bg-activity" style="width: ${width}%"></div>
                        </div>
                        <div class="chart-value">${dMsgs} msg / ${formatMoney(d.cost || d.total_cost || 0)}</div>
                    </div>`;
            });
            $('#activity-daily-chart-container').html(actHtml);
        } else {
            $('#activity-daily-chart-container').html('<div class="text-center py-3 text-muted">No daily activity data available</div>');
        }
    }

    function renderLatency(lat) {
        if(!lat) return;
        console.log('Latency Data:', lat);
        
        // Handle nested structure if present
        var l = lat.latency || lat;
        var ttft = l.ttft || { p50_ms: 0, p95_ms: 0, avg_ms: 0, max_ms: 0, min_ms: 0 };
        var full = l.response_time || { p50_ms: 0, p95_ms: 0, avg_ms: 0, max_ms: 0, min_ms: 0 };
        
        $('#hero-latency-avg').text(formatSec(full.avg_ms || (full.p95_ms || 0)));
        $('#hero-latency-ttft').text(formatSec(ttft.p95_ms || 0));
        $('#hero-latency-p95').text(formatSec(full.p95_ms || 0));

        var renderMetrics = function(id, m) {
            var p95 = m.p95_ms || m.p95 || 0;
            var p50 = m.p50_ms || m.p50 || 0;
            var avg = m.avg_ms || m.avg || 0;
            var min = m.min_ms || m.min || 0;
            var max = m.max_ms || m.max || 0;
            
            if (p95 > 0 && p95 < 100) p95 *= 1000;
            if (p50 > 0 && p50 < 100) p50 *= 1000;
            if (avg > 0 && avg < 100) avg *= 1000;
            if (min > 0 && min < 100) min *= 1000;
            if (max > 0 && max < 100) max *= 1000;

            $(`#${id}`).html(`
                <div class="metric-item">
                    <div class="metric-item-label">95% nhanh hơn: <span class="text-success text-bold">${formatSec(p95)}s</span></div>
                    <div class="metric-item-label">Trung bình: <span class="text-bold">${formatSec(avg)}s</span></div>
                    <div class="metric-item-label">Nhanh nhất: <span class="text-bold">${formatSec(min)}s</span></div>
                </div>
                <div class="metric-item">
                    <div class="metric-item-label">Trung vị (50%): <span class="text-bold">${formatSec(p50)}s</span></div>
                    <div class="metric-item-label">99% nhanh hơn: <span class="text-bold">${formatSec(max * 0.95)}s</span></div>
                    <div class="metric-item-label">Chậm nhất: <span class="text-bold">${formatSec(max)}s</span></div>
                </div>
            `);
        };
        renderMetrics('ttft-detailed-metrics', ttft);
        renderMetrics('full-response-detailed-metrics', full);

        // Daily Latency Bars - Search deeply for the array
        var days = (lat.latency && lat.latency.by_day) || lat.by_day || lat.latency_by_day || (Array.isArray(lat.days) ? lat.days : []);
        
        if(Array.isArray(days) && days.length > 0) {
            var latHtml = '';
            
            // Pre-calculate max for scaling
            var maxLat = 1;
            days.forEach(d => {
                var dFull_val = d.response_p95_ms || (d.response_time ? d.response_time.p95_ms : 0) || d.p95_ms || 0;
                if (dFull_val > 0 && dFull_val < 100) dFull_val *= 1000;
                if (dFull_val > maxLat) maxLat = dFull_val;
            });

            days.forEach(d => {
                var dTTFT_val = d.ttft_p95_ms || (d.ttft ? d.ttft.p95_ms : 0) || 0;
                var dFull_val = d.response_p95_ms || (d.response_time ? d.response_time.p95_ms : 0) || 0;
                
                if (dTTFT_val > 0 && dTTFT_val < 100) dTTFT_val *= 1000;
                if (dFull_val > 0 && dFull_val < 100) dFull_val *= 1000;

                var dMsgs = d.response_count || d.total || d.messages || d.count || 0;

                var w1 = (dTTFT_val / maxLat) * 100;
                var w2 = (dFull_val / maxLat) * 100;
                
                latHtml += `
                    <div class="chart-row" style="height: auto; margin-bottom: 20px;">
                        <div class="chart-label">${d.date}</div>
                        <div style="flex: 1;">
                            <div class="d-flex align-items-center mb-1">
                                <div class="chart-bar-container" style="margin: 0 10px 0 0;"><div class="chart-bar-fill bg-latency-ttft" style="width: ${w1}%"></div></div>
                                <div class="text-xs text-muted" style="width: 80px;">TTFT (p95): ${formatSec(dTTFT_val)}s</div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="chart-bar-container" style="margin: 0 10px 0 0;"><div class="chart-bar-fill bg-latency-full" style="width: ${w2}%"></div></div>
                                <div class="text-xs text-muted" style="width: 80px;">Full (p95): ${formatSec(dFull_val)}s</div>
                            </div>
                        </div>
                        <div class="chart-value text-xs">${dMsgs} msg</div>
                    </div>`;
            });
            $('#latency-daily-chart-container').html(latHtml);
        } else {
            $('#latency-daily-chart-container').html('<div class="text-center py-3 text-muted">No daily latency data available</div>');
        }
    }

    function renderFallback(fb) {
        if(!fb || !fb.totals) return;
        var t = fb.totals;
        var total = t.total_assistant_messages || t.total_count || t.total_messages || t.total || 0;
        var fallbacks = t.fallback_count || t.count || 0;
        var rate = t.fallback_rate_pct || (total > 0 ? (fallbacks/total)*100 : 0);

        $('#hero-fallback-rate').text(rate.toFixed(2) + '%');
        $('#hero-fallback-count').text(fallbacks);
        $('#hero-total-msgs').text(total);

        $('#fallback-daily-summary').html(`
            <strong>Total messages:</strong> ${total} &nbsp; 
            <strong>Fallback:</strong> ${fallbacks} &nbsp; 
            <strong>Rate:</strong> ${rate.toFixed(2)}%
        `);

        // Correct key is 'by_day' based on API response
        var days = fb.by_day || fb.days || [];
        if(days && days.length > 0) {
            var fbHtml = '';
            days.forEach(d => {
                var dTotal = d.total || d.total_count || d.total_messages || 0;
                var dFallbacks = d.fallback || d.fallback_count || d.count || 0;
                var dRate = d.rate_pct || d.fallback_rate_pct || (dTotal > 0 ? (dFallbacks/dTotal)*100 : 0);
                
                fbHtml += `
                    <div class="chart-row">
                        <div class="chart-label">${d.date}</div>
                        <div class="chart-bar-container">
                            <div class="chart-bar-fill bg-fallback" style="width: ${dRate}%"></div>
                        </div>
                        <div class="chart-value">${dRate.toFixed(1)}% (${dFallbacks}/${dTotal})</div>
                    </div>`;
            });
            $('#fallback-daily-chart-container').html(fbHtml);
        } else {
            $('#fallback-daily-chart-container').html('<div class="text-center py-3 text-muted">No daily fallback data available</div>');
        }
    }

    function renderKnowledge(k) {
        if(k && k.qdrant) {
            $('#hero-knowledge-count').text(k.qdrant.total_points.toLocaleString());
        }
    }

    loadStats();
});
</script>
@endsection
