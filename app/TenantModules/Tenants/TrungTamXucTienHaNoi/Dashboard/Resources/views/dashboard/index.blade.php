@extends('backend.index')

@section('title')
DashBoard
@endsection

@section('breadcrumb')
<li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('css')
<style>
    .visit-card {
        border-radius: 12px;
        transition: transform 0.2s;
        height: calc(100% - 20px);
    }
    .visit-card:hover {
        transform: translateY(-5px);
    }
    .text-xs { font-size: 0.75rem; }

    /* AI Upgrade Styles */
    .ai-stat-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .ai-stat-card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }
    .card-health { border-top: 3px solid #28a745; }
    .card-performance { border-top: 3px solid #007bff; }
    .card-questions { border-top: 3px solid #6f42c1; }

    .status-pulse {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 8px;
        position: relative;
    }
    .pulse-success { background-color: #28a745; }
    .pulse-warning { background-color: #ffc107; }
    .pulse-danger { background-color: #dc3545; }

    .status-pulse::after {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }
    .pulse-success::after { box-shadow: 0 0 0 rgba(40, 167, 69, 0.7); }
    .pulse-warning::after { box-shadow: 0 0 0 rgba(255, 193, 7, 0.7); }
    .pulse-danger::after { box-shadow: 0 0 0 rgba(220, 53, 69, 0.7); }

    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
    }

    .latency-bar-container {
        height: 4px;
        background: #f1f1f1;
        border-radius: 2px;
        width: 100%;
        margin-top: 4px;
    }
    .latency-bar-fill {
        height: 100%;
        border-radius: 2px;
        background: #28a745;
        transition: width 0.5s ease;
    }

    .metric-hero {
        font-size: 1.5rem;
        font-weight: 700;
        color: #333;
        line-height: 1.2;
    }
    .metric-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .question-item {
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 8px;
        background: #f8f9fa;
        border-left: 3px solid transparent;
        transition: all 0.2s;
    }
    .question-item:hover {
        background: #f1f3f5;
        border-left-color: #6f42c1;
        transform: translateX(3px);
    }
    .text-purple { color: #6f42c1; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Project General Stats -->
    <div class="row mb-4">
        <div class="col-lg-6 col-12">
            <div class="small-box bg-info border shadow-sm visit-card">
                <div class="inner">
                    <h3>{{ number_format($projectStats['total'] ?? 0) }}</h3>
                    <p class="mb-0 text-bold">Tổng số dự án</p>
                    <a href="javascript:void(0)" class="text-white text-xs mt-2 d-inline-block" data-toggle="modal" data-target="#projectDataDetailsModal" style="text-decoration: underline;">
                        <i class="fas fa-info-circle mr-1"></i>Xem chi tiết thống kê dữ liệu
                    </a>
                </div>
                <div class="icon">
                    <i class="fas fa-project-diagram text-light" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-12">
            <div class="small-box bg-success border shadow-sm visit-card">
                <div class="inner">
                    <h3>{{ number_format($totalInvestment ?? 0) }} <sup style="font-size: 20px">Tỷ VNĐ</sup></h3>
                    <p class="mb-0 text-bold">Tổng mức đầu tư</p>
                    <span class="text-xs text-white mt-2 d-inline-block"><i class="fas fa-coins mr-1"></i>Giá trị ước tính từ hệ thống</span>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave text-light" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- NEW Website Visitor Stats -->
    <div class="card card-outline card-primary shadow-none bg-transparent">
        <div class="card-header border-0 pl-0">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <h3 class="card-title text-bold mb-2 mb-md-0">
                    <i class="fas fa-users mr-2 text-primary"></i>
                    THỐNG KÊ LƯỢT TRUY CẬP QUAY LẠI THEO THÁNG
                </h3>
                <form method="GET" action="{{ url()->current() }}" class="form-inline">
                    <label for="visitor_month" class="text-sm text-muted mr-2 mb-0">Tháng</label>
                    <input type="month" id="visitor_month" name="visitor_month" class="form-control form-control-sm mr-2" value="{{ $siteVisitorStats['month_value'] }}">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-filter mr-1"></i>Lọc
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="row mb-4">
                <div class="col-lg-4 col-12">
                    <div class="small-box bg-white border shadow-sm visit-card">
                        <div class="inner">
                            <h3>{{ number_format($siteVisitorStats['total_visitors']) }}</h3>
                            <p class="text-muted text-sm mb-0">Người truy cập trong tháng {{ $siteVisitorStats['month_label'] }}</p>
                            <span class="text-xs text-primary"><i class="fas fa-user mr-1"></i>Số lượng IP riêng biệt trong tháng</span>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users text-light" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-12">
                    <div class="small-box bg-white border shadow-sm visit-card" style="border-left: 4px solid #17a2b8 !important;">
                        <div class="inner">
                            <h3>{{ number_format($siteVisitorStats['total_hits_in_month']) }}</h3>
                            <p class="text-muted text-sm mb-0">Tổng lượt truy cập trong tháng</p>
                            <a href="javascript:void(0)" class="text-xs text-info mt-2 d-inline-block" data-toggle="modal" data-target="#monthIpsModal" style="text-decoration: underline;">
                                <i class="fas fa-list mr-1"></i>Xem danh sách IP trong tháng
                            </a>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-clock text-light" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-12">
                    <div class="small-box bg-white border shadow-sm visit-card" style="border-left: 4px solid #28a745 !important;">
                        <div class="inner">
                            <h3>{{ number_format($siteVisitorStats['returning_visitors']) }}</h3>
                            <p class="text-muted text-sm mb-0">Người dùng quay trở lại trong tháng</p>
                            <span class="text-xs text-success"><i class="fas fa-undo mr-1"></i>IP truy cập từ 2 ngày trở lên trong tháng</span>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-check text-light" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-header border-0">
                            <h3 class="card-title text-bold text-sm">
                                <i class="fas fa-chart-bar mr-1 text-primary"></i>
                                Biểu đồ truy cập quay lại theo tháng
                            </h3>
                        </div>
                        <div class="card-body">
                            <div style="height: 250px; position: relative;">
                                <canvas id="returning-visitor-monthly-chart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Website Visitor Stats (Old) -->
    <div class="card card-outline card-success shadow-none bg-transparent">
        <div class="card-header border-0 pl-0">
            <h3 class="card-title text-bold">
                <i class="fas fa-eye mr-2 text-success"></i>
                THỐNG KÊ TỈ LỆ TRUY CẬP WEBSITE
            </h3>
        </div>
        <div class="card-body p-0">
            <div class="row mb-4">
                <div class="col-lg-4 col-12">
                    <div class="small-box bg-white border shadow-sm visit-card">
                        <div class="inner">
                            <h3>{{ number_format($visitStats['unique_ips_today']) }}</h3>
                            <p class="text-muted text-sm mb-0">Địa chỉ IP truy cập (Hôm nay)</p>
                            <span class="text-xs text-success"><i class="fas fa-fingerprint mr-1"></i>Mỗi IP chỉ được tính 1 lần/ngày</span>
                        </div>
                        <div class="icon">
                            <i class="fas fa-network-wired text-light" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-12">
                    <div class="small-box bg-white border shadow-sm visit-card" style="border-left: 4px solid #f39c12 !important;">
                        <div class="inner">
                            <h3>{{ number_format($visitStats['bots_today']) }}</h3>
                            <p class="text-muted text-sm mb-0">Lượt Bot/Crawl (Hôm nay)</p>
                            <span class="text-xs text-warning"><i class="fas fa-robot mr-1"></i>Dự đoán dựa trên User-Agent</span>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-secret text-light" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-12">
                    <div class="small-box bg-white border shadow-sm visit-card">
                        <div class="inner">
                            <h3>{{ $visitStats['unique_ips_today'] > 0 ? round(($visitStats['bots_today'] / $visitStats['unique_ips_today']) * 100, 1) : 0 }}%</h3>
                            <p class="text-muted text-sm mb-0">Tỷ lệ Bot trong ngày</p>
                            <div class="progress progress-xxs mt-2">
                                <div class="progress-bar bg-warning" style="width: {{ $visitStats['unique_ips_today'] > 0 ? ($visitStats['bots_today'] / $visitStats['unique_ips_today']) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-percentage text-light" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card card-outline card-success shadow-sm">
                        <div class="card-header border-0">
                            <h3 class="card-title text-bold text-sm">
                                <i class="fas fa-history mr-1 text-success"></i>
                                Biểu đồ truy cập (7 ngày gần nhất)
                            </h3>
                        </div>
                        <div class="card-body">
                            <div style="height: 250px; position: relative;">
                                <canvas id="visitor-activity-chart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="card card-outline card-info shadow-none bg-transparent">
        <div class="card-header border-0 pl-0">
            <h3 class="card-title text-bold">
                <a href="{{ route('backend_chatbot_overview') }}" class="text-dark">
                    <i class="fas fa-robot mr-2 text-info"></i>
                    BÁO CÁO & THỐNG KÊ AI CHATBOT <i class="fas fa-external-link-alt ml-1 text-xs text-muted"></i>
                </a>
            </h3>
        </div>
        <div class="card-body p-0">
            <!-- AI Usage Statistics Overview -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-white border shadow-sm">
                        <div class="inner">
                            <h3 id="ai-total-sessions">0</h3>
                            <p class="text-muted text-sm">Tổng phiên hội thoại</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-comments text-light" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-white border shadow-sm">
                        <div class="inner">
                            <h3 id="ai-total-messages">0</h3>
                            <p class="text-muted text-sm">Tổng số tin nhắn</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-comment-dots text-light" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-white border shadow-sm">
                        <div class="inner">
                            <h3 id="ai-total-tokens">0</h3>
                            <p class="text-muted text-sm">Tổng Tokens sử dụng</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-microchip text-light" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-white border shadow-sm">
                        <div class="inner">
                            <h3 id="ai-total-cost">$0.00</h3>
                            <p class="text-muted text-sm">Chi phí ước tính (USD)</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign text-light" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Extra AI Stats Row -->
            <div class="row mb-4" id="ai-extra-stats-container" style="display: none;">
                <!-- Health & Knowledge -->
                <div class="col-md-4">
                    <div class="card ai-stat-card card-health h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <h3 class="card-title text-sm text-bold text-dark">
                                <a href="{{ route('backend_chatbot_overview') }}" class="text-dark"><i class="fas fa-server mr-1 text-success"></i> Trạng thái Hệ thống</a>
                            </h3>
                        </div>
                        <div class="card-body p-3">
                            <div id="ai-health-status"></div>
                            <div id="ai-knowledge-status" class="mt-3 pt-3 border-top border-light"></div>
                        </div>
                    </div>
                </div>
                <!-- Performance -->
                <div class="col-md-4">
                    <div class="card ai-stat-card card-performance h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <h3 class="card-title text-sm text-bold text-dark">
                                <a href="{{ route('backend_chatbot_overview') }}" class="text-dark"><i class="fas fa-bolt mr-1 text-primary"></i> Hiệu suất & Fallback</a>
                            </h3>
                        </div>
                        <div class="card-body p-3">
                            <div id="ai-performance-stats"></div>
                        </div>
                    </div>
                </div>
                <!-- Top Questions -->
                <div class="col-md-4">
                    <div class="card ai-stat-card card-questions h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <h3 class="card-title text-sm text-bold text-dark">
                                <a href="{{ route('backend_chatbot_overview') }}" class="text-dark"><i class="fas fa-comment-alt mr-1 text-purple"></i> Câu hỏi phổ biến</a>
                            </h3>
                        </div>
                        <div class="card-body p-3" style="max-height: 280px; overflow-y: auto;">
                            <div id="ai-top-questions"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Analytics Charts -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card card-outline card-primary shadow-sm h-100">
                        <div class="card-header border-0">
                            <h3 class="card-title text-bold text-sm">
                                <i class="fas fa-chart-line mr-1 text-primary"></i>
                                Hoạt động AI Bot (7 ngày qua)
                            </h3>
                        </div>
                        <div class="card-body">
                            <div id="ai-daily-chart-container" style="height: 300px; position: relative;">
                                <canvas id="ai-daily-activity-chart"></canvas>
                                <div class="ai-no-data d-none text-center p-5" style="position: absolute; top: 0; width: 100%;">
                                    <i class="fas fa-chart-line fa-3x text-light mb-3"></i>
                                    <p class="text-muted">Chưa có dữ liệu hoạt động trong khoảng thời gian này</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-outline card-success shadow-sm h-100">
                        <div class="card-header border-0">
                            <h3 class="card-title text-bold text-sm">
                                <i class="fas fa-pie-chart mr-1 text-success"></i>
                                Phân bổ ý định (Intents)
                            </h3>
                        </div>
                        <div class="card-body">
                            <div id="ai-intents-chart-container" style="height: 250px; position: relative;">
                                <canvas id="ai-intents-chart"></canvas>
                                <div class="ai-no-data d-none text-center p-4" style="position: absolute; top: 0; width: 100%;">
                                    <i class="fas fa-comment-slash fa-2x text-light mb-3"></i>
                                    <p class="text-muted">Chưa có dữ liệu ý định</p>
                                </div>
                            </div>
                            <div id="ai-intents-legend" class="mt-4" style="max-height: 150px; overflow-y: auto;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <!-- Left col -->
        <section class="col-lg-7 connectedSortable">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-1"></i>
                        Hoạt động hệ thống
                    </h3>
                    <div class="card-tools">
                        <select id="chart-range-selector" class="form-control form-control-sm" style="width: 150px;">
                            <option value="3" {{ $range == 3 ? 'selected' : '' }}>3 tháng gần nhất</option>
                            <option value="6" {{ $range == 6 ? 'selected' : '' }}>6 tháng gần nhất</option>
                            <option value="12" {{ $range == 12 ? 'selected' : '' }}>12 tháng gần nhất</option>
                            <option value="24" {{ $range == 24 ? 'selected' : '' }}>24 tháng gần nhất</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="tab-content p-0">
                        <canvas id="revenue-chart-canvas" height="300" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <!-- Right col -->
        <section class="col-lg-5 connectedSortable">
            <div class="card bg-gradient-info h-100">
                <div class="card-header border-0">
                    <h3 class="card-title">
                        <i class="fas fa-th mr-1"></i>
                        Phân bổ dự án theo lĩnh vực
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-7">
                            <canvas id="project-dist-canvas" height="300" style="height: 300px;"></canvas>
                        </div>
                        <div class="col-md-5">
                            <ul id="project-dist-legend" class="chart-legend clearfix" style="max-height: 280px; overflow-y: auto; list-style: none; padding-left: 0; margin-top: 10px;">
                                <!-- Legend items injected via JS -->
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history mr-1"></i>
                        Nhật ký hoạt động
                    </h3>
                    <div class="card-tools">
                        <form id="activity-log-filter" action="{{ route('backend_dashboard') }}" method="GET" class="form-inline">
                            <input type="hidden" name="range" value="{{ $range }}">
                            <select name="event_log" class="form-control form-control-sm mr-2">
                                <option value="">Tất cả sự kiện</option>
                                <option value="created" {{ request('event_log') == 'created' ? 'selected' : '' }}>Thêm mới</option>
                                <option value="updated" {{ request('event_log') == 'updated' ? 'selected' : '' }}>Cập nhật</option>
                                <option value="deleted" {{ request('event_log') == 'deleted' ? 'selected' : '' }}>Xóa</option>
                            </select>
                            <div class="input-group input-group-sm mr-2" style="width: 200px;">
                                <input type="text" name="search_log" class="form-control" placeholder="Tìm kiếm..." value="{{ request('search_log') }}">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-default">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#exportLogsModal">
                                <i class="fas fa-file-export mr-1"></i> Xuất dữ liệu
                            </button>
                        </form>
                    </div>
                </div>
                <div id="activity-log-container">
                    @include('ttxt-dashboard::dashboard.partials._activity_log_table')
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Project Data Details Modal -->
<div class="modal fade" id="projectDataDetailsModal" tabindex="-1" role="dialog" aria-labelledby="projectDataDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="projectDataDetailsModalLabel"><i class="fas fa-chart-pie mr-2"></i>Chi tiết tiến độ cập nhật dữ liệu dự án</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Loại dữ liệu</th>
                            <th class="text-center">Đã có dữ liệu</th>
                            <th class="text-center">Chưa có (Cần bổ sung)</th>
                            <th class="text-center">Tỷ lệ hoàn thành</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total = $projectStats['total'] ?? 0;
                            $fields = [
                                ['id' => 'general_info', 'label' => 'Thông tin chung', 'count' => $projectStats['has_general_info'] ?? 0],
                                ['id' => 'location', 'label' => 'Tọa độ', 'count' => $projectStats['has_location'] ?? 0],
                                ['id' => 'vrtour', 'label' => 'VR Tour 360', 'count' => $projectStats['has_vrtour'] ?? 0],
                                ['id' => 'legal', 'label' => 'Văn bản pháp quy', 'count' => $projectStats['has_legal'] ?? 0],
                                ['id' => 'investor', 'label' => 'Đã có chủ đầu tư', 'count' => $projectStats['has_investor'] ?? 0],
                            ];
                        @endphp
                        @foreach($fields as $field)
                            @php
                                $hasCount = $field['count'];
                                $missingCount = max(0, $total - $hasCount);
                                $percent = $total > 0 ? round(($hasCount / $total) * 100, 1) : 0;
                                $progressClass = $percent >= 80 ? 'bg-success' : ($percent >= 50 ? 'bg-warning' : 'bg-danger');
                                $hasList = $hasProjects[$field['id']] ?? collect();
                                $missingList = $missingProjects[$field['id']] ?? collect();
                                $showHasList = $hasCount > 0;
                                $showMissingList = $missingCount > 0;
                            @endphp
                            <tr>
                                <td class="font-weight-bold">{{ $field['label'] }}</td>
                                <td class="text-center text-success font-weight-bold">
                                    {{ $hasCount }}
                                    @if($showHasList)
                                        <br>
                                        <button class="btn btn-xs btn-outline-success mt-1" type="button" data-toggle="collapse" data-target="#has-{{ $field['id'] }}" aria-expanded="false" aria-controls="has-{{ $field['id'] }}">
                                            Xem danh sách
                                        </button>
                                    @endif
                                </td>
                                <td class="text-center text-danger font-weight-bold">
                                    {{ $missingCount }}
                                    @if($showMissingList)
                                        <br>
                                        <button class="btn btn-xs btn-outline-danger mt-1" type="button" data-toggle="collapse" data-target="#missing-{{ $field['id'] }}" aria-expanded="false" aria-controls="missing-{{ $field['id'] }}">
                                            Xem danh sách
                                        </button>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <div class="progress progress-sm">
                                        <div class="progress-bar {{ $progressClass }}" role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted d-block text-center mt-1">{{ $percent }}%</small>
                                </td>
                            </tr>
                            @if($showHasList)
                            <tr>
                                <td colspan="4" class="p-0 border-0">
                                    <div class="collapse" id="has-{{ $field['id'] }}">
                                        <div class="p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                                            <ul class="list-unstyled mb-0" style="font-size: 13px;">
                                                @foreach($hasList as $proj)
                                                    <li class="mb-1"><a href="{{ route('backend_project_edit', $proj->id) }}" target="_blank" class="text-dark"><i class="fas fa-caret-right mr-1 text-muted"></i>{{ $proj->name ?? 'Dự án chưa có tên' }} <i class="fas fa-external-link-alt text-muted ml-1" style="font-size: 10px;"></i></a></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @if($showMissingList)
                            <tr>
                                <td colspan="4" class="p-0 border-0">
                                    <div class="collapse" id="missing-{{ $field['id'] }}">
                                        <div class="p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                                            <ul class="list-unstyled mb-0" style="font-size: 13px;">
                                                @foreach($missingList as $proj)
                                                    <li class="mb-1"><a href="{{ route('backend_project_edit', $proj->id) }}" target="_blank" class="text-dark"><i class="fas fa-caret-right mr-1 text-muted"></i>{{ $proj->name ?? 'Dự án chưa có tên' }} <i class="fas fa-external-link-alt text-muted ml-1" style="font-size: 10px;"></i></a></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                <a href="{{ route('backend_project') }}" class="btn btn-primary"><i class="fas fa-edit mr-1"></i>Đi đến Quản lý dự án</a>
            </div>
        </div>
    </div>
</div>

<!-- Export Logs Modal -->
<div class="modal fade" id="exportLogsModal" tabindex="-1" role="dialog" aria-labelledby="exportLogsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="exportLogsModalLabel"><i class="fas fa-file-export mr-2"></i>Xuất dữ liệu nhật ký hoạt động</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('backend_dashboard_export_logs') }}" method="GET">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Chọn khoảng thời gian (theo tháng gần đây):</label>
                        <select name="months" class="form-control">
                            <option value="1">1 tháng gần đây</option>
                            <option value="3" selected>3 tháng gần đây</option>
                            <option value="6">6 tháng gần đây</option>
                            <option value="12">1 năm gần đây</option>
                            <option value="24">2 năm gần đây</option>
                            <option value="0">Toàn bộ dữ liệu</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Chọn định dạng tệp (trước khi nén ZIP):</label>
                        <div class="mt-2">
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="format_excel" name="format" value="excel" class="custom-control-input" checked>
                                <label class="custom-control-label font-weight-normal" for="format_excel">
                                    <i class="fas fa-file-excel text-success mr-1"></i> Excel (.xlsx)
                                </label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="format_csv" name="format" value="csv" class="custom-control-input">
                                <label class="custom-control-label font-weight-normal" for="format_csv">
                                    <i class="fas fa-file-csv text-info mr-1"></i> CSV (.csv)
                                </label>
                            </div>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>Khuyên dùng:</strong> Sử dụng định dạng <b>Excel</b> nếu bạn gặp lỗi hiển thị phông chữ tiếng Việt khi mở bằng CSV.
                        </small>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        <i class="fas fa-lock mr-1"></i>
                        Dữ liệu sẽ được xuất ra file ZIP có mật khẩu bảo vệ. Việc xuất dữ liệu ở đây <strong>không xóa</strong> dữ liệu trong hệ thống.
                    </small>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-download mr-1"></i>Bắt đầu xuất dữ liệu</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Monthly IPs Modal -->
<div class="modal fade" id="monthIpsModal" tabindex="-1" role="dialog" aria-labelledby="monthIpsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="monthIpsModalLabel"><i class="fas fa-list mr-2"></i>Danh sách IP truy cập tháng {{ $siteVisitorStats['month_label'] }}</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th>STT</th>
                                <th>Địa chỉ IP</th>
                                <th class="text-center">Số lượt truy cập</th>
                                <th class="text-center">Số ngày truy cập</th>
                                <th class="text-right">Lần đầu trong tháng</th>
                                <th class="text-right">Gần nhất trong tháng</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($monthIps) && count($monthIps) > 0)
                                @foreach($monthIps as $index => $ipRecord)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="font-weight-bold">{{ $ipRecord->ip_address }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-{{ $ipRecord->hits > 1 ? 'warning' : 'success' }}">
                                                {{ $ipRecord->hits }} lượt
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-{{ $ipRecord->visit_days >= 2 ? 'success' : 'secondary' }}">
                                                {{ $ipRecord->visit_days }} ngày
                                            </span>
                                        </td>
                                        <td class="text-right text-muted">{{ \Carbon\Carbon::parse($ipRecord->first_visit_date)->format('d/m/Y') }}</td>
                                        <td class="text-right text-muted">{{ \Carbon\Carbon::parse($ipRecord->last_visit_date)->format('d/m/Y') }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">Chưa có dữ liệu truy cập trong tháng này.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light justify-content-between">
                <div>
                    <button type="button" class="btn btn-primary btn-sm" onclick="copyIpsToClipboard()">
                        <i class="fas fa-copy mr-1"></i>Copy IP
                    </button>
                    <button type="button" class="btn btn-success btn-sm" onclick="exportTableToCSV('Danh_sach_IP_truy_cap_thang_{{ $siteVisitorStats['month_value'] }}.csv')">
                        <i class="fas fa-file-excel mr-1"></i>Xuất Excel
                    </button>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('backend_assets/vendor/chart/Chart.min.js') }}"></script>
<script>
    function copyIpsToClipboard() {
        var rows = document.querySelectorAll("#monthIpsModal table tbody tr");
        var ips = [];
        for (var i = 0; i < rows.length; i++) {
            var cols = rows[i].querySelectorAll("td");
            if (cols.length >= 2) {
                var ipText = cols[1].innerText.trim();
                if (ipText && ipText !== 'Chưa có dữ liệu truy cập trong tháng này.') {
                    ips.push(ipText);
                }
            }
        }
        if (ips.length === 0) {
            if (typeof toastr !== 'undefined') toastr.info("Không có dữ liệu IP");
            else alert("Không có dữ liệu IP");
            return;
        }
        var text = ips.join('\n');
        navigator.clipboard.writeText(text).then(function() {
            if (typeof toastr !== 'undefined') toastr.success("Đã copy " + ips.length + " IP!");
            else alert("Đã copy " + ips.length + " IP!");
        }, function() {
            alert("Copy thất bại");
        });
    }

    function exportTableToCSV(filename) {
        var csv = [];
        var rows = document.querySelectorAll("#monthIpsModal table tr");
        
        for (var i = 0; i < rows.length; i++) {
            var row = [], cols = rows[i].querySelectorAll("td, th");
            // Bỏ qua cột STT nếu không muốn, nhưng để nguyên cũng ok.
            for (var j = 0; j < cols.length; j++) {
                var text = cols[j].innerText.replace(/"/g, '""').trim();
                row.push('"' + text + '"');
            }
            csv.push(row.join(","));        
        }

        var csvFile = new Blob(["\uFEFF" + csv.join("\n")], {type: "text/csv;charset=utf-8;"});
        var downloadLink = document.createElement("a");
        downloadLink.download = filename;
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }

    $(function() {
        'use strict'

        // Monthly Activity Chart
        var activityChartCanvas = $('#revenue-chart-canvas').get(0).getContext('2d')
        var activityChartData = {
            labels: @json($chartLabels),
            datasets: [{
                    label: 'Dự án mới',
                    backgroundColor: 'rgba(60,141,188,0.9)',
                    borderColor: 'rgba(60,141,188,0.8)',
                    pointRadius: false,
                    pointColor: '#3b8bba',
                    pointStrokeColor: 'rgba(60,141,188,1)',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: 'rgba(60,141,188,1)',
                    data: @json($projectChartData)
                },
                {
                    label: 'Tin tức mới',
                    backgroundColor: 'rgba(210, 214, 222, 1)',
                    borderColor: 'rgba(210, 214, 222, 1)',
                    pointRadius: false,
                    pointColor: 'rgba(210, 214, 222, 1)',
                    pointStrokeColor: '#c1c7d1',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: 'rgba(220,220,220,1)',
                    data: @json($postChartData)
                },
                {
                    label: 'Người dùng mới',
                    backgroundColor: 'rgba(40, 167, 69, 0.9)',
                    borderColor: 'rgba(40, 167, 69, 0.8)',
                    pointRadius: false,
                    pointColor: '#28a745',
                    pointStrokeColor: 'rgba(40, 167, 69, 1)',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: 'rgba(40, 167, 69, 1)',
                    data: @json($guestChartData)
                },
                {
                    label: 'Cẩm nang mới',
                    backgroundColor: 'rgba(255, 193, 7, 0.9)',
                    borderColor: 'rgba(255, 193, 7, 0.8)',
                    pointRadius: false,
                    pointColor: '#ffc107',
                    pointStrokeColor: 'rgba(255, 193, 7, 1)',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: 'rgba(255, 193, 7, 1)',
                    data: @json($investmentGuideChartData)
                }
            ]
        }

        var activityChartOptions = {
            maintainAspectRatio: false,
            responsive: true,
            legend: {
                display: true
            },
            scales: {
                xAxes: [{
                    gridLines: {
                        display: false
                    }
                }],
                yAxes: [{
                    gridLines: {
                        display: false
                    }
                }]
            }
        }

        new Chart(activityChartCanvas, {
            type: 'bar',
            data: activityChartData,
            options: activityChartOptions
        })

        // Project Distribution Pie Chart
        var pieCanvas = $('#project-dist-canvas').get(0).getContext('2d')
        var industryLabels = @json($industryLabels);
        
        // Tạo dải màu tự động tránh trùng lặp
        var generateColors = function(count) {
            var colors = [];
            for (var i = 0; i < count; i++) {
                var hue = (i * 137.508) % 360;
                colors.push('hsl(' + Math.floor(hue) + ', 75%, 55%)');
            }
            return colors;
        };

        var pieData = {
            labels: industryLabels,
            datasets: [{
                data: @json($industryData),
                backgroundColor: generateColors(industryLabels.length)
            }]
        }
        var pieOptions = {
            legend: {
                display: false
            },
            maintainAspectRatio: false,
            responsive: true
        }

        var pieChart = new Chart(pieCanvas, {
            type: 'pie',
            data: pieData,
            options: pieOptions
        })

        // Generate Custom Legend
        var legendContainer = $('#project-dist-legend');
        var colors = pieData.datasets[0].backgroundColor;
        pieData.labels.forEach(function(label, index) {
            var color = colors[index % colors.length];
            var li = $('<li class="nav-item"></li>');
            li.css({
                'display': 'flex',
                'align-items': 'center',
                'color': '#fff',
                'margin-bottom': '8px',
                'cursor': 'pointer',
                'font-size': '13px'
            });

            var colorBox = $('<span class="mr-2"></span>');
            colorBox.css({
                'display': 'inline-block',
                'width': '12px',
                'height': '12px',
                'background-color': color,
                'border': '1px solid rgba(255,255,255,0.2)'
            });

            var labelSpan = $('<span></span>').text(label);
            labelSpan.css({
                'flex': '1',
                'overflow': 'hidden',
                'text-overflow': 'ellipsis',
                'white-space': 'nowrap'
            });

            li.append(colorBox).append(labelSpan);
            li.attr('title', label);

            li.on('click', function() {
                var meta = pieChart.getDatasetMeta(0);
                meta.data[index].hidden = !meta.data[index].hidden;
                pieChart.update();
                $(this).css('opacity', meta.data[index].hidden ? '0.5' : '1');
                $(this).find('span:last-child').css('text-decoration', meta.data[index].hidden ? 'line-through' : 'none');
            });

            legendContainer.append(li);
        });

        // Chart Range Selector Listener
        $('#chart-range-selector').on('change', function() {
            var range = $(this).val();
            var url = new URL(window.location.href);
            url.searchParams.set('range', range);
            window.location.href = url.href;
        });

        // AJAX for Activity Log
        function fetchActivities(url) {
            $.ajax({
                url: url,
                type: 'GET',
                success: function(data) {
                    $('#activity-log-container').html(data);
                },
                error: function() {
                    toastr.error('Không thể tải nhật ký hoạt động.');
                }
            });
        }

        $('#activity-log-filter').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            var url = $(this).attr('action') + '?' + formData;
            fetchActivities(url);
            window.history.pushState({}, '', url);
        });

        $('select[name="event_log"]').on('change', function() {
            $('#activity-log-filter').submit();
        });

        $(document).on('click', '#activity-log-container .pagination a', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            fetchActivities(url);
            window.history.pushState({}, '', url);
        });

        // AI Bot Monitoring logic
        var aiMonitorTimeout;

        // Advanced AI Stats
        var dailyActivityChart;
        var intentsChart;

        function updateAdvancedAiStats() {
            $.ajax({
                url: "{{ route('backend_ai_monitor_advanced_stats') }}",
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        var data = response.data;
                        
                        // 1. Update Overview Stats
                        var totals = data.overview.totals || {};
                        $('#ai-total-sessions').text(totals.active_sessions || 0);
                        $('#ai-total-messages').text(totals.total_messages || 0);
                        $('#ai-total-tokens').text((totals.total_tokens || 0).toLocaleString());
                        $('#ai-total-cost').text('$' + (totals.total_cost_usd || 0).toFixed(4));

                        // 2. Update Daily Activity Chart
                        var dailySeries = data.daily.series || [];
                        var dailyContainer = $('#ai-daily-chart-container');
                        
                        if (dailySeries.length === 0) {
                            dailyContainer.find('canvas').addClass('d-none');
                            dailyContainer.find('.ai-no-data').removeClass('d-none');
                        } else {
                            dailyContainer.find('canvas').removeClass('d-none');
                            dailyContainer.find('.ai-no-data').addClass('d-none');
                            
                            var labels = dailySeries.map(item => item.date);
                            var messages = dailySeries.map(item => item.messages);
                            var sessions = dailySeries.map(item => item.sessions);

                            if (dailyActivityChart) {
                                dailyActivityChart.destroy();
                            }

                            dailyActivityChart = new Chart($('#ai-daily-activity-chart').get(0).getContext('2d'), {
                                type: 'line',
                                data: {
                                    labels: labels,
                                    datasets: [
                                        {
                                            label: 'Tin nhắn',
                                            borderColor: '#007bff',
                                            backgroundColor: 'rgba(0, 123, 255, 0.1)',
                                            data: messages,
                                            fill: true,
                                            tension: 0.4
                                        },
                                        {
                                            label: 'Phiên hội thoại',
                                            borderColor: '#28a745',
                                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                                            data: sessions,
                                            fill: true,
                                            tension: 0.4
                                        }
                                    ]
                                },
                                options: {
                                    maintainAspectRatio: false,
                                    responsive: true,
                                    title: { display: false },
                                    legend: { position: 'top' },
                                    tooltips: { mode: 'index', intersect: false },
                                    hover: { mode: 'nearest', intersect: true },
                                    scales: {
                                        xAxes: [{ gridLines: { display: false }, ticks: { fontSize: 10 } }],
                                        yAxes: [{ gridLines: { color: 'rgba(0,0,0,0.05)' }, ticks: { beginAtZero: true, fontSize: 10 } }]
                                    }
                                }
                            });
                        }

                        // 3. Update Intents Chart
                        var intentsData = data.intents.intents || [];
                        var intentContainer = $('#ai-intents-chart-container');
                        
                        if (intentsData.length === 0) {
                            intentContainer.find('canvas').addClass('d-none');
                            intentContainer.find('.ai-no-data').removeClass('d-none');
                            $('#ai-intents-legend').html('');
                        } else {
                            intentContainer.find('canvas').removeClass('d-none');
                            intentContainer.find('.ai-no-data').addClass('d-none');
                            
                            var intentLabels = intentsData.map(item => item.intent);
                            var intentCounts = intentsData.map(item => item.count);
                            var intentColors = generateColors(intentsData.length);

                            if (intentsChart) {
                                intentsChart.destroy();
                            }

                            intentsChart = new Chart($('#ai-intents-chart').get(0).getContext('2d'), {
                                type: 'doughnut',
                                data: {
                                    labels: intentLabels,
                                    datasets: [{
                                        data: intentCounts,
                                        backgroundColor: intentColors
                                    }]
                                },
                                options: {
                                    maintainAspectRatio: false,
                                    responsive: true,
                                    legend: { display: false },
                                    cutoutPercentage: 70
                                }
                            });

                            // Generate Intent Legend
                            var legendHtml = '<div class="row">';
                            intentsData.forEach((item, index) => {
                                legendHtml += `
                                    <div class="col-6 mb-2">
                                        <div class="d-flex align-items-center">
                                            <div style="width: 12px; height: 12px; background-color: ${intentColors[index]}; margin-right: 8px; border-radius: 2px;"></div>
                                            <div class="text-truncate" title="${item.intent}" style="font-size: 11px;">
                                                <span class="text-bold">${item.percentage.toFixed(1)}%</span> ${item.intent}
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                            legendHtml += '</div>';
                            $('#ai-intents-legend').html(legendHtml);
                        }
                    }
                }
            });
        }

        updateAdvancedAiStats();

        // Extra AI Stats
        function updateExtraAiStats() {
            $.ajax({
                url: "{{ route('backend_ai_monitor_extra_stats') }}",
                type: 'GET',
                success: function(response) {
                    if (response.success && response.data) {
                        $('#ai-extra-stats-container').show();
                        var data = response.data;
                        
                        // Render Health
                        if(data.health) {
                            var statusType = data.health.status === 'healthy' ? 'success' : (data.health.status === 'degraded' ? 'warning' : 'danger');
                            var healthHtml = `
                                <div class="d-flex align-items-center mb-3">
                                    <span class="status-pulse pulse-${statusType}"></span>
                                    <span class="text-bold text-dark text-uppercase" style="font-size: 13px;">${data.health.status}</span>
                                </div>`;
                            
                            if(data.health.services) {
                                healthHtml += '<div class="services-list">';
                                Object.keys(data.health.services).forEach(key => {
                                    var svc = data.health.services[key];
                                    if(svc.status !== 'not_used') {
                                        var isUp = svc.status === 'up' || svc.status === 'ok';
                                        var latency = svc.latency_ms || 0;
                                        // Simple heuristic for bar width: max 500ms
                                        var barWidth = Math.min((latency / 500) * 100, 100);
                                        var barColor = latency > 300 ? '#dc3545' : (latency > 150 ? '#ffc107' : '#28a745');
                                        
                                        healthHtml += `
                                            <div class="mb-2">
                                                <div class="d-flex justify-content-between text-xs mb-1">
                                                    <span class="text-muted">${key}</span>
                                                    <span class="text-bold ${isUp ? 'text-success' : 'text-danger'}">${isUp ? (latency ? latency + 'ms' : 'UP') : 'DOWN'}</span>
                                                </div>
                                                <div class="latency-bar-container">
                                                    <div class="latency-bar-fill" style="width: ${isUp ? (latency ? barWidth : 100) : 0}%; background: ${barColor}"></div>
                                                </div>
                                            </div>`;
                                    }
                                });
                                healthHtml += '</div>';
                            }
                            $('#ai-health-status').html(healthHtml);
                        }

                        // Render Knowledge
                        if(data.knowledge && data.knowledge.qdrant) {
                            $('#ai-knowledge-status').html(`
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="metric-label">Điểm dữ liệu (Points)</div>
                                        <div class="metric-hero" style="font-size: 1.2rem;">${(data.knowledge.qdrant.total_points || 0).toLocaleString()}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="metric-label">Collection</div>
                                        <span class="badge badge-light border text-primary" style="font-size: 10px;">${data.knowledge.qdrant.active_collection || 'N/A'}</span>
                                    </div>
                                </div>
                            `);
                        }

                        // Render Performance & Fallback
                        var perfHtml = '';
                        if(data.latency && data.latency.response_time) {
                            perfHtml += `
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <div class="metric-label">P50 Latency</div>
                                        <div class="metric-hero">${data.latency.response_time.p50_ms}<span class="text-xs text-muted ml-1">ms</span></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="metric-label">P95 Latency</div>
                                        <div class="metric-hero text-primary">${data.latency.response_time.p95_ms}<span class="text-xs text-muted ml-1">ms</span></div>
                                    </div>
                                </div>
                                <div class="mb-3 pt-2 border-top border-light">
                                    <div class="metric-label mb-1">TTFT (Avg)</div>
                                    <div class="text-bold text-dark">${data.latency.ttft ? data.latency.ttft.avg_ms + 'ms' : 'N/A'}</div>
                                </div>`;
                        }
                        
                        if(data.fallback && data.fallback.totals) {
                            var fbRate = data.fallback.totals.fallback_rate_pct || 0;
                            var fbColor = fbRate > 20 ? 'danger' : (fbRate > 10 ? 'warning' : 'success');
                            perfHtml += `
                                <div class="pt-3 border-top border-light">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="metric-label">Tỷ lệ Fallback</div>
                                        <div class="text-bold text-${fbColor}">${fbRate.toFixed(1)}%</div>
                                    </div>
                                    <div class="progress progress-xxs mb-1">
                                        <div class="progress-bar bg-${fbColor}" style="width: ${fbRate}%"></div>
                                    </div>
                                    <div class="text-xs text-muted text-right">
                                        ${data.fallback.totals.fallback_count || 0} tin nhắn không xác định
                                    </div>
                                </div>`;
                        }
                        $('#ai-performance-stats').html(perfHtml);

                        // Render Top Questions
                        if(data.top_questions && data.top_questions.topics) {
                            var tqHtml = '';
                            data.top_questions.topics.slice(0, 6).forEach(topic => {
                                tqHtml += `
                                    <div class="question-item">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <div class="text-bold text-dark" style="font-size: 12px; line-height: 1.2;">${topic.topic_name || topic.intent}</div>
                                            <span class="badge badge-primary ml-2" style="font-size: 10px; opacity: 0.8;">${topic.count}</span>
                                        </div>
                                        ${topic.sample_questions && topic.sample_questions.length > 0 ? 
                                            `<div class="text-muted text-truncate" style="font-size: 11px; font-style: italic;" title="${topic.sample_questions[0]}">
                                                "${topic.sample_questions[0]}"
                                            </div>` : ''}
                                    </div>`;
                            });
                            $('#ai-top-questions').html(tqHtml || '<div class="text-center text-muted p-4">Chưa có dữ liệu câu hỏi</div>');
                        }
                    }
                }
            });
        }
        updateExtraAiStats();

        // Returning Visitor Monthly Chart
        var returningVisitorMonthlyCanvas = $('#returning-visitor-monthly-chart').get(0).getContext('2d');
        new Chart(returningVisitorMonthlyCanvas, {
            type: 'bar',
            data: {
                labels: @json($visitorMonthlyLabels),
                datasets: [
                    {
                        label: 'Unique IP trong tháng',
                        backgroundColor: '#17a2b8',
                        data: @json($visitorMonthlyData)
                    },
                    {
                        label: 'IP quay lại',
                        backgroundColor: '#28a745',
                        data: @json($returningVisitorMonthlyData)
                    }
                ]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    xAxes: [{ gridLines: { display: false } }],
                    yAxes: [{ ticks: { beginAtZero: true }, gridLines: { color: 'rgba(0,0,0,0.05)' } }]
                },
                legend: { position: 'top' }
            }
        });

        // Website Visitor Activity Chart
        var visitorChartCanvas = $('#visitor-activity-chart').get(0).getContext('2d');
        new Chart(visitorChartCanvas, {
            type: 'bar',
            data: {
                labels: @json($visitChartLabels),
                datasets: [
                    {
                        label: 'Địa chỉ IP thực',
                        backgroundColor: '#28a745',
                        data: @json($visitChartData).map((v, i) => v - @json($botChartData)[i])
                    },
                    {
                        label: 'Địa chỉ IP Bot',
                        backgroundColor: '#f39c12',
                        data: @json($botChartData)
                    }
                ]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    xAxes: [{ stacked: true, gridLines: { display: false } }],
                    yAxes: [{ stacked: true, gridLines: { color: 'rgba(0,0,0,0.05)' } }]
                },
                legend: { position: 'top' }
            }
        });
    })
</script>
@endsection
```
