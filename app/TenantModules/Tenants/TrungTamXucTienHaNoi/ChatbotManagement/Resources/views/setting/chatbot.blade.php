@extends('backend.index')

@section('title')
    @switch($tab)
        @case('basic') Cài đặt cơ bản @break
        @case('sync') Đồng bộ trí thức @break
        @case('prompts') Kịch bản (Prompts) @break
        @case('blacklist') Rào chắn (Blacklist) @break
        @case('sessions') Lịch sử & Insight @break
        @case('knowledge') Tài liệu nội bộ @break
        @case('usage') Token & chi phí @break
        @default Cấu hình Chatbot
    @endswitch
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">AI Chatbot</a></li>
    <li class="breadcrumb-item active">
        @switch($tab)
            @case('basic') Cài đặt cơ bản @break
            @case('sync') Đồng bộ trí thức @break
            @case('prompts') Kịch bản (Prompts) @break
            @case('blacklist') Rào chắn (Blacklist) @break
            @case('sessions') Lịch sử & Insight @break
            @case('knowledge') Tài liệu nội bộ @break
            @case('usage') Token & chi phí @break
        @endswitch
    </li>
@endsection

@section('content')
    <script src="{{ asset('js/ckfinder/ckfinder.js') }}"></script>
    <script src="{{ asset('js/ckeditor/ckeditor.js') }}"></script>
    <script src="{{ asset('backend_assets/js/globals.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script>CKFinder.config({ connectorPath: '/ckfinder/connector' });</script>

    <section class="content premium-chatbot-ui">
        <div class="container-fluid">
            <div class="row">
                <!-- CONTENT AREA FULL WIDTH -->
                <div class="col-md-12">
                    <div class="tab-content" id="chatbot-settings-tabContent">
                        
                        {{-- PHẦN CƠ BẢN --}}
                        @if($tab == 'basic')
                        <div class="tab-pane fade show active" id="tab-basic" role="tabpanel">
                            <div class="card premium-card">
                                <div class="card-header glass-header">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <h4 class="font-weight-bold mb-0 text-dark">Giao diện & Cấu hình Hiển thị</h4>
                                        <button type="submit" form="formDataGrid" class="btn btn-primary rounded-md px-4 shadow-sm btn-hover-lift"><i class="fas fa-save mr-1"></i> Lưu Cài đặt</button>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <form action="{{ route('tenant.trung_tam_xuc_tien_ha_noi.setting.save') }}" method="post" enctype="multipart/form-data" class="form-horizontal" id="formDataGrid">
                                        @csrf
                                        <ul class="nav nav-tabs modern-tabs mb-4" id="settingTabs" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" id="vi-tab" data-toggle="tab" href="#tab-vi" role="tab">Tiếng Việt</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="en-tab" data-toggle="tab" href="#tab-en" role="tab">Tiếng Anh</a>
                                            </li>
                                        </ul>

                                        <div class="tab-content" id="settingTabsContent">
                                            <div class="tab-pane fade show active" id="tab-vi" role="tabpanel">
                                                <x-forms.input name="settings[chatbot_name][vi]" value="{{ $settings['chatbot_name']['vi'] ?? '' }}" label="Tên Trợ lý (VI)" />
                                                <x-forms.input name="settings[chatbot_tooltip][vi]" value="{{ $settings['chatbot_tooltip']['vi'] ?? '' }}" label="Nội dung Tooltip (VI)" />
                                                <x-forms.textarea name="settings[chatbot_welcome_message][vi]" :required="true" value="{{ $settings['chatbot_welcome_message']['vi'] ?? '' }}" label="Tin nhắn chào mừng (VI)" />
                                            </div>
                                            <div class="tab-pane fade" id="tab-en" role="tabpanel">
                                                <x-forms.input name="settings[chatbot_name][en]" value="{{ $settings['chatbot_name']['en'] ?? '' }}" label="Tên Trợ lý (EN)" />
                                                <x-forms.input name="settings[chatbot_tooltip][en]" value="{{ $settings['chatbot_tooltip']['en'] ?? '' }}" label="Nội dung Tooltip (EN)" />
                                                <x-forms.textarea name="settings[chatbot_welcome_message][en]" :required="true" value="{{ $settings['chatbot_welcome_message']['en'] ?? '' }}" label="Tin nhắn chào mừng (EN)" />
                                            </div>
                                        </div>

                                        <hr class="my-4 border-light">
                                        <h5 class="font-weight-bold text-dark mb-4"><i class="fas fa-paint-brush mr-2 text-primary"></i>Tùy biến Nhận diện</h5>
                                        
                                        <x-forms.upload name="settings[chatbot_avatar]" value="{{ $settings['chatbot_avatar'] ?? '' }}" label="Ảnh đại diện (Avatar)" type="image" />
                                        
                                        <div class="form-group row align-items-center mt-4">
                                            <label class="col-sm-3 col-form-label font-weight-bold text-dark">Màu sắc chủ đạo</label>
                                            <div class="col-sm-9 d-flex align-items-center">
                                                <div class="color-picker-wrapper shadow-sm">
                                                    <input type="color" name="settings[chatbot_primary_color]" value="{{ $settings['chatbot_primary_color'] ?? '#1a6fc4' }}" id="chatbot_primary_color" class="custom-color-input">
                                                </div>
                                                <input type="text" id="color-hex-preview" class="form-control ml-3 font-weight-bold" style="width: 120px; background: #f8f9fa; border-radius: 8px;" value="{{ $settings['chatbot_primary_color'] ?? '#1a6fc4' }}" readonly>
                                                <small class="text-muted ml-3 d-none d-md-block">Được dùng cho bong bóng chat, nút bấm và header</small>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- PHẦN SYNC --}}
                        @if($tab == 'sync')
                        <div class="tab-pane fade show active" id="tab-sync" role="tabpanel">
                            <div class="card premium-card">
                                <div class="card-header glass-header">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <h4 class="font-weight-bold text-dark mb-0"><i class="fas fa-sync-alt mr-2 text-primary"></i>Cấu hình lịch cập nhật dữ liệu</h4>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="loadSyncSettings()"><i class="fas fa-sync-alt"></i> Tải lại</button>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <div id="sync-status-container" class="text-center py-5">
                                        <div class="spinner-grow text-primary" role="status"></div>
                                        <p class="text-muted mt-2">Đang kết nối đến AI Core...</p>
                                    </div>
                                    
                                    <div id="sync-content-container" style="display: none;">
                                        <div class="mb-4">
                                            <p class="text-muted text-sm">Hệ thống có 2 chế độ cập nhật dữ liệu từ web TTXT vào AI:</p>
                                            <ul class="text-sm text-muted">
                                                <li><strong>Cập nhật mới (delta)</strong> — chạy thường xuyên, chỉ kéo dự án/tài liệu vừa thay đổi.</li>
                                                <li><strong>Tổng hợp lại (full rebuild)</strong> — chạy 1 lần/ngày vào ban đêm, kéo lại toàn bộ để đồng bộ tuyệt đối (kể cả các bản ghi đã bị xoá).</li>
                                            </ul>
                                        </div>

                                        <!-- STATUS TABLE -->
                                        <div class="table-responsive mb-4">
                                            <table class="table table-borderless table-sm status-info-table">
                                                <tr>
                                                    <td width="250" class="text-muted">Trạng thái tự động</td>
                                                    <td><span class="badge badge-soft-success" id="sync-status-badge">Đang bật</span></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Bộ dữ liệu đang phục vụ</td>
                                                    <td><span class="font-weight-bold text-dark" id="sync-active-collection">-</span> <small class="badge badge-light border ml-1">slot a</small></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Bộ dữ liệu dự phòng</td>
                                                    <td><span class="text-muted" id="sync-standby-collection">-</span></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Tần suất cập nhật mới (delta)</td>
                                                    <td>Mỗi <strong id="info-delta-interval">1</strong> giờ</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Giờ tổng hợp lại hàng ngày</td>
                                                    <td><strong id="info-full-time">03:00</strong> mỗi ngày</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Lần cập nhật mới gần nhất</td>
                                                    <td id="info-last-delta">10:24:57 4/5/2026</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Lần tổng hợp lại gần nhất</td>
                                                    <td id="info-last-full">10:06:38 4/5/2026</td>
                                                </tr>
                                            </table>
                                        </div>

                                        <!-- CONFIG FORM -->
                                        <div class="config-panel bg-light p-4 rounded-lg mb-4 border-0 position-relative">
                                            <h6 class="font-weight-bold text-dark mb-3">Cấu hình lịch tự động</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="text-sm font-weight-bold text-muted">Tần suất cập nhật mới (giờ)</label>
                                                        <input type="number" class="form-control rounded-md" id="sync_delta_interval" value="1" min="1" max="24">
                                                        <small class="text-muted">Cứ mỗi N giờ sẽ kéo dữ liệu mới (1-24h)</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="text-sm font-weight-bold text-muted">Giờ tổng hợp lại hàng ngày (0-23)</label>
                                                        <input type="number" class="form-control rounded-md" id="sync_full_hour" value="3" min="0" max="23">
                                                        <small class="text-muted">Mỗi ngày lúc HH:00 sẽ tổng hợp lại toàn bộ. Khuyến nghị ban đêm.</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="custom-control custom-switch mb-3">
                                                <input type="checkbox" class="custom-control-input" id="sync_auto_enabled" checked>
                                                <label class="custom-control-label font-weight-bold text-primary" for="sync_auto_enabled">Tự động cập nhật theo lịch</label>
                                            </div>
                                            <button class="btn btn-primary rounded-md px-4 shadow-sm" id="btn-save-sync-config">
                                                <i class="fas fa-save mr-2"></i>Lưu cấu hình
                                            </button>
                                            <div class="mt-2 text-xs text-warning">
                                                <i class="fas fa-exclamation-triangle mr-1"></i> Khi lưu, lịch tự động sẽ <strong>khởi động lại ngay lập tức</strong> với cấu hình mới.
                                            </div>
                                        </div>
                                        
                                        <!-- MANUAL ACTIONS -->
                                        <div class="manual-panel">
                                            <h6 class="font-weight-bold text-dark mb-3">Chạy thủ công</h6>
                                            <div class="d-flex flex-wrap gap-2">
                                                <button class="btn btn-primary rounded-md px-3 btn-hover-lift mr-2 mb-2" id="btn-trigger-sync-delta">
                                                    <i class="fas fa-play mr-2 text-xs"></i>Cập nhật dữ liệu mới ngay
                                                </button>
                                                <button class="btn btn-info rounded-md px-3 btn-hover-lift mr-2 mb-2" id="btn-trigger-sync-full">
                                                    <i class="fas fa-sync-alt mr-2 text-xs"></i>Tổng hợp lại toàn bộ ngay
                                                </button>
                                                <button class="btn btn-outline-dark rounded-md px-3 btn-hover-lift mb-2" id="btn-swap-slots">
                                                    <i class="fas fa-exchange-alt mr-2 text-xs"></i>Đổi bộ dữ liệu phục vụ
                                                </button>
                                            </div>
                                            <p class="text-xs text-muted font-italic mt-2">
                                                <i class="fas fa-lightbulb mr-1 text-warning"></i> Các nút này chỉ dùng khi <strong>cần ép cập nhật</strong> ngoài lịch tự động (vd: vừa thêm dự án mới và muốn AI biết ngay).
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- PHẦN PROMPTS --}}
                        @if($tab == 'prompts')
                        <div class="tab-pane fade show active" id="tab-prompts" role="tabpanel">
                            <div class="card premium-card">
                                <div class="card-header glass-header">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <h4 class="font-weight-bold mb-0 text-dark"><i class="fas fa-code mr-2 text-primary"></i>Kịch bản hội thoại</h4>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div id="prompts-container" class="text-center py-5">
                                        <div class="spinner-border text-info" role="status"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- PHẦN BLACKLIST --}}
                        @if($tab == 'blacklist')
                        <div class="tab-pane fade show active" id="tab-blacklist" role="tabpanel">
                            <div class="card premium-card">
                                <div class="card-header glass-header">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <h4 class="font-weight-bold text-dark mb-0">Hệ thống Rào chắn Nội dung</h4>
                                        <button class="btn btn-primary rounded-md px-3 shadow-sm btn-hover-lift" data-toggle="modal" data-target="#modalAddKeyword">
                                            <i class="fas fa-plus mr-1"></i> Thêm từ khóa chặn
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <ul class="nav nav-tabs modern-tabs mb-4" id="blacklistTabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="bl-keywords-tab" data-toggle="tab" href="#bl-keywords" role="tab"><i class="fas fa-list-ul mr-1"></i> Bộ lọc Từ khóa</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="bl-refusals-tab" data-toggle="tab" href="#bl-refusals" role="tab"><i class="fas fa-reply mr-1"></i> Cấu hình Từ chối</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="bl-logs-tab" data-toggle="tab" href="#bl-logs" role="tab"><i class="fas fa-history mr-1"></i> Lịch sử Bắt chặn</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content" id="blacklistTabsContent">
                                        <div class="tab-pane fade show active" id="bl-keywords" role="tabpanel">
                                            <div id="blacklist-container" class="text-center py-5">
                                                <div class="spinner-grow text-primary" role="status"></div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="bl-refusals" role="tabpanel">
                                            <div id="refusals-container" class="text-center py-5">
                                                <div class="spinner-grow text-primary" role="status"></div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="bl-logs" role="tabpanel">
                                            <div id="blacklist-logs-container" class="text-center py-5">
                                                <div class="spinner-grow text-primary" role="status"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- PHẦN KNOWLEDGE --}}
                        @if($tab == 'knowledge')
                        <div class="tab-pane fade show active" id="tab-knowledge" role="tabpanel">
                            <div class="card premium-card">
                                <div class="card-header glass-header">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <h4 class="font-weight-bold text-dark mb-0"><i class="fas fa-folder-open mr-2 text-primary"></i>Tài liệu nội bộ đưa thẳng vào AI</h4>
                                        <button class="btn btn-sm btn-outline-secondary" id="btn-refresh-knowledge"><i class="fas fa-sync-alt"></i> Tải lại</button>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <div class="config-panel bg-light p-4 rounded-lg border mb-4">
                                        <h6 class="font-weight-bold text-dark mb-3">Thêm tài liệu</h6>
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <div class="form-group">
                                                    <label class="text-sm font-weight-bold text-muted">Tiêu đề/citation</label>
                                                    <input type="text" class="form-control rounded-md" id="knowledge-title" placeholder="VD: Cẩm nang đầu tư FDI 2026">
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="form-group">
                                                    <label class="text-sm font-weight-bold text-muted">File đã lưu trong CKFinder</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="knowledge-file-url" readonly placeholder="/storage/.../files/tai-lieu.pdf">
                                                        <div class="input-group-append">
                                                            <button class="btn btn-outline-primary" id="btn-knowledge-browse" type="button"><i class="fas fa-folder-open mr-1"></i> Chọn</button>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">Chọn file hoặc nhập text bên dưới. Không dùng cả hai.</small>
                                                </div>
                                            </div>
                                            <div class="col-lg-2 col-md-6">
                                                <div class="form-group">
                                                    <label class="text-sm font-weight-bold text-muted">Ngôn ngữ</label>
                                                    <select class="form-control" id="knowledge-language">
                                                        <option value="auto">Tự nhận diện</option>
                                                        <option value="vi">Tiếng Việt</option>
                                                        <option value="en">Tiếng Anh</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-2 col-md-6">
                                                <div class="form-group">
                                                    <label class="text-sm font-weight-bold text-muted">Tóm tắt</label>
                                                    <select class="form-control" id="knowledge-summary-mode">
                                                        <option value="none">Không tóm tắt</option>
                                                        <option value="auto">Tự động</option>
                                                        <option value="short">Ngắn</option>
                                                        <option value="normal">Bình thường</option>
                                                        <option value="detailed">Chi tiết</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="text-sm font-weight-bold text-muted">Hoặc paste text trực tiếp</label>
                                            <textarea class="form-control" id="knowledge-text" rows="5" placeholder="Nội dung tài liệu nội bộ..."></textarea>
                                        </div>
                                        <button class="btn btn-primary rounded-md px-4" id="btn-create-knowledge"><i class="fas fa-upload mr-2"></i>Đưa vào AI</button>
                                        <span class="text-xs text-muted ml-2">API trả job_id ngay, worker xử lý nền. Bảng jobs sẽ tự cập nhật.</span>
                                    </div>

                                    <ul class="nav nav-tabs modern-tabs mb-3" role="tablist">
                                        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#knowledge-jobs-pane">Jobs</a></li>
                                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#knowledge-docs-pane">Đã indexed</a></li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane fade show active" id="knowledge-jobs-pane">
                                            <div id="knowledge-jobs-container" class="text-center py-5"><div class="spinner-grow text-primary"></div></div>
                                        </div>
                                        <div class="tab-pane fade" id="knowledge-docs-pane">
                                            <div id="knowledge-docs-container" class="text-center py-5"><div class="spinner-grow text-primary"></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- PHẦN USAGE --}}
                        @if($tab == 'usage')
                        <div class="tab-pane fade show active" id="tab-usage" role="tabpanel">
                            <div class="card premium-card">
                                <div class="card-header glass-header">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <h4 class="font-weight-bold text-dark mb-0"><i class="fas fa-coins mr-2 text-primary"></i>Token & chi phí AI</h4>
                                        <div>
                                            <button type="submit" form="usageAlertSettingsForm" class="btn btn-sm btn-primary mr-2"><i class="fas fa-save mr-1"></i>Lưu alert</button>
                                            <button class="btn btn-sm btn-outline-secondary" id="btn-refresh-usage"><i class="fas fa-sync-alt"></i> Tải lại</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <form action="{{ route('tenant.trung_tam_xuc_tien_ha_noi.setting.save') }}" method="post" id="usageAlertSettingsForm" class="mb-4">
                                        @csrf
                                        <div class="border rounded-lg p-3 bg-light">
                                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                                <div>
                                                    <h6 class="font-weight-bold mb-1"><i class="fas fa-bell mr-2 text-warning"></i>Cảnh báo vượt ngưỡng cost</h6>
                                                    <div class="text-muted small">Hệ thống kiểm tra mỗi giờ, gửi mail khi cost 24h vượt ngưỡng. Mỗi ngày gửi tối đa 1 lần cho cảnh báo cost.</div>
                                                </div>
                                                <div class="custom-control custom-switch mt-2 mt-md-0">
                                                    <input type="hidden" name="settings[ai_usage_alert_enabled]" value="0">
                                                    <input type="checkbox" class="custom-control-input" id="ai_usage_alert_enabled" name="settings[ai_usage_alert_enabled]" value="1" {{ !empty($settings['ai_usage_alert_enabled']) && (string) $settings['ai_usage_alert_enabled'] === '1' ? 'checked' : '' }}>
                                                    <label class="custom-control-label font-weight-bold" for="ai_usage_alert_enabled">Bật alert</label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4 mb-3 mb-md-0">
                                                    <label class="font-weight-bold small text-muted">Ngưỡng cost 24h (USD)</label>
                                                    <input type="number" min="0" step="0.000001" class="form-control" name="settings[ai_usage_alert_threshold_24h]" value="{{ $settings['ai_usage_alert_threshold_24h'] ?? '1.000000' }}" placeholder="1.000000">
                                                </div>
                                                <div class="col-md-8">
                                                    <label class="font-weight-bold small text-muted">Email nhận cảnh báo</label>
                                                    <input type="text" class="form-control" name="settings[ai_usage_alert_emails]" value="{{ $settings['ai_usage_alert_emails'] ?? ($settings['email'] ?? '') }}" placeholder="admin@example.com, tech@example.com">
                                                    <small class="text-muted">Có thể nhập nhiều email, cách nhau bằng dấu phẩy.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="row mb-4" id="usage-summary-cards">
                                        <div class="col-md-3 mb-3"><div class="p-3 bg-light rounded border"><div class="text-muted text-xs">24h</div><div class="h4 mb-0" id="usage-cost-24h">$0.000000</div></div></div>
                                        <div class="col-md-3 mb-3"><div class="p-3 bg-light rounded border"><div class="text-muted text-xs">7 ngày</div><div class="h4 mb-0" id="usage-cost-7d">$0.000000</div></div></div>
                                        <div class="col-md-3 mb-3"><div class="p-3 bg-light rounded border"><div class="text-muted text-xs">30 ngày</div><div class="h4 mb-0" id="usage-cost-30d">$0.000000</div></div></div>
                                        <div class="col-md-3 mb-3"><div class="p-3 bg-light rounded border"><div class="text-muted text-xs">Tổng tokens</div><div class="h4 mb-0" id="usage-total-tokens">0</div></div></div>
                                    </div>
                                    <div class="filter-bar bg-light p-3 rounded-lg mb-4 border d-flex flex-wrap align-items-center">
                                        <select class="form-control form-control-sm mr-2 mb-2" id="usage-feature" style="width: 150px;">
                                            <option value="">Tất cả feature</option>
                                            <option value="chat">Chat</option>
                                            <option value="extract">Extract</option>
                                            <option value="knowledge">Knowledge</option>
                                            <option value="sync">Sync</option>
                                        </select>
                                        <select class="form-control form-control-sm mr-2 mb-2" id="usage-phase" style="width: 150px;">
                                            <option value="">Tất cả phase</option>
                                            <option value="llm">LLM</option>
                                            <option value="ocr">OCR</option>
                                            <option value="summary">Summary</option>
                                            <option value="embedding">Embedding</option>
                                        </select>
                                        <input type="date" class="form-control form-control-sm mr-2 mb-2" id="usage-date-from" style="width: 160px;">
                                        <input type="date" class="form-control form-control-sm mr-2 mb-2" id="usage-date-to" style="width: 160px;">
                                        <button class="btn btn-sm btn-primary mb-2" id="btn-apply-usage-filter"><i class="fas fa-filter mr-1"></i>Lọc</button>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6 mb-4">
                                            <h6 class="font-weight-bold">Breakdown theo feature</h6>
                                            <div id="usage-feature-breakdown" class="table-responsive"></div>
                                        </div>
                                        <div class="col-lg-6 mb-4">
                                            <h6 class="font-weight-bold">Breakdown theo model</h6>
                                            <div id="usage-model-breakdown" class="table-responsive"></div>
                                        </div>
                                    </div>
                                    <h6 class="font-weight-bold">Chi tiết usage</h6>
                                    <div id="usage-items-container" class="text-center py-5"><div class="spinner-grow text-primary"></div></div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- PHẦN SESSIONS --}}
                        @if($tab == 'sessions')
                        <div class="tab-pane fade show active" id="tab-sessions" role="tabpanel">
                            <div class="card premium-card">
                                <div class="card-header glass-header">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <h4 class="font-weight-bold text-dark mb-0"><i class="fas fa-history mr-2 text-primary"></i>Lịch sử hội thoại</h4>
                                        <div class="export-tools d-flex align-items-center">
                                            <button class="btn btn-sm btn-outline-secondary mr-2" id="btn-refresh-sessions"><i class="fas fa-sync-alt"></i> Tải lại</button>
                                            <div class="btn-group shadow-sm">
                                                <a href="/backend/chatbot-admin/sessions/export?type=json" target="_blank" class="btn btn-sm btn-primary px-3" title="Xuất JSON"><i class="fas fa-file-code mr-1"></i> JSON</a>
                                                <a href="/backend/chatbot-admin/sessions/export?type=csv" target="_blank" class="btn btn-sm btn-info px-3" title="Xuất CSV"><i class="fas fa-file-csv mr-1"></i> CSV</a>
                                                <a href="/backend/chatbot-admin/sessions/export?type=txt" target="_blank" class="btn btn-sm btn-secondary px-3" title="Xuất TXT"><i class="fas fa-file-alt mr-1"></i> TXT</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <!-- FILTER BAR -->
                                    <div class="filter-bar bg-light p-3 rounded-lg mb-4 border d-flex flex-wrap align-items-center gap-3">
                                        <div class="filter-item d-flex align-items-center mr-3">
                                            <label class="mb-0 mr-2 text-sm font-weight-bold text-muted">Ngôn ngữ:</label>
                                            <select class="form-control form-control-sm rounded-md px-3" id="filter-lang" style="width: 100px;">
                                                <option value="">Tất cả</option>
                                                <option value="vi">vi</option>
                                                <option value="en">en</option>
                                            </select>
                                        </div>
                                        <div class="filter-item d-flex align-items-center mr-3">
                                            <label class="mb-0 mr-2 text-sm font-weight-bold text-muted">Intent:</label>
                                            <input type="text" class="form-control form-control-sm rounded-md px-3" id="filter-intent" placeholder="vd: find_project" style="width: 180px;">
                                        </div>
                                        <div class="filter-item d-flex align-items-center mr-3">
                                            <label class="mb-0 mr-2 text-sm font-weight-bold text-muted">Có feedback:</label>
                                            <select class="form-control form-control-sm rounded-md px-3" id="filter-feedback" style="width: 100px;">
                                                <option value="">Tất cả</option>
                                                <option value="1">Có</option>
                                                <option value="0">Không</option>
                                            </select>
                                        </div>
                                        <div class="filter-item d-flex align-items-center mr-3">
                                            <label class="mb-0 mr-2 text-sm font-weight-bold text-muted">Trang:</label>
                                            <input type="number" class="form-control form-control-sm rounded-md px-3 text-center" id="filter-page" value="1" min="1" style="width: 70px;">
                                        </div>
                                        <div class="filter-item d-flex align-items-center">
                                            <label class="mb-0 mr-2 text-sm font-weight-bold text-muted">Size:</label>
                                            <select class="form-control form-control-sm rounded-md px-3" id="filter-size" style="width: 80px;">
                                                <option value="10">10</option>
                                                <option value="20" selected>20</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select>
                                        </div>
                                    </div>

                                    <ul class="nav nav-tabs modern-tabs mb-4" id="sessionTabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="sess-list-tab" data-toggle="tab" href="#sess-list" role="tab"><i class="fas fa-comments mr-1"></i> Phiên Trò chuyện</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="sess-feedback-tab" data-toggle="tab" href="#sess-feedback" role="tab"><i class="fas fa-star mr-1"></i> Đánh giá của User</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content" id="sessionTabsContent">
                                        <div class="tab-pane fade show active" id="sess-list" role="tabpanel">
                                            <div id="sessions-container" class="text-center py-5">
                                                <div class="spinner-grow text-primary" role="status"></div>
                                            </div>
                                            <div id="sessions-footer" class="d-flex justify-content-between align-items-center mt-3 px-2 d-none">
                                                <div class="text-sm text-muted" id="sessions-pagination-info">Trang 1/1 - Tổng 0 sessions</div>
                                                <div class="text-sm text-muted" id="sessions-total-info">0 kết quả</div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="sess-feedback" role="tabpanel">
                                            <div id="feedback-container" class="text-center py-5">
                                                <div class="spinner-grow text-primary" role="status"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- STYLES CỦA PREMIUM UI -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        .premium-chatbot-ui {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            padding-bottom: 50px;
        }
        
        /* Typography & Colors */
        .gradient-text {
            background: linear-gradient(135deg, #1a6fc4 0%, #6f42c1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .tracking-wide { letter-spacing: 1px; }
        
        /* Premium Cards */
        .premium-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.04);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .glass-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1.25rem 1.5rem;
        }

        /* Modern Tabs */
        .modern-tabs {
            border-bottom: 2px solid #edf2f9;
        }
        .modern-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 600;
            padding: 10px 20px;
            position: relative;
            background: transparent;
            transition: all 0.3s;
        }
        .modern-tabs .nav-link:hover { color: #1a6fc4; }
        .modern-tabs .nav-link.active {
            color: #1a6fc4;
            background: transparent;
        }
        .modern-tabs .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px; left: 0; right: 0;
            height: 2px;
            background: #1a6fc4;
            border-radius: 2px 2px 0 0;
        }

        /* Buttons */
        .btn-hover-lift {
            transition: all 0.2s;
        }
        .btn-hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        /* Color Picker */
        .color-picker-wrapper {
            width: 45px; height: 45px; padding: 0;
            border: 2px solid #e9ecef; border-radius: 10px;
            overflow: hidden; transition: all 0.2s;
        }
        .color-picker-wrapper:hover { border-color: #1a6fc4; transform: scale(1.05); }
        .custom-color-input { border: none; padding: 0; width: 150%; height: 150%; margin: -25%; cursor: pointer; }

        /* Sync Blocks */
        .sync-block { border: 1px solid rgba(0,0,0,0.05); transition: all 0.3s; }
        .sync-block:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.08) !important; }
        .active-block { border-left: 4px solid #28a745; background: #fff; }
        .standby-block { border-left: 4px solid #6c757d; background: #f8f9fa; }
        .sync-bg-icon {
            position: absolute;
            right: -20px; bottom: -30px;
            font-size: 100px; opacity: 0.04;
            transform: rotate(-15deg);
        }
        .pulse-dot {
            width: 10px; height: 10px; border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 rgba(40,167,69, 0.4);
            animation: pulse-dot-anim 2s infinite;
        }
        @keyframes pulse-dot-anim {
            0% { box-shadow: 0 0 0 0 rgba(40,167,69, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(40,167,69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(40,167,69, 0); }
        }

        /* Terminal Prompt UI */
        .terminal-accordion .card {
            background: #1e1e1e !important;
            border: 1px solid #333 !important;
            border-radius: 8px !important;
        }
        .terminal-accordion .btn-link {
            color: #569cd6 !important; font-family: monospace; font-size: 1.1rem;
        }
        .terminal-textarea {
            background: #2d2d2d !important;
            color: #d4d4d4 !important;
            font-family: 'Consolas', 'Courier New', monospace;
            border: 1px solid #444 !important;
            border-radius: 6px;
            line-height: 1.5;
            resize: vertical;
        }
        .terminal-textarea:focus { box-shadow: 0 0 0 0.2rem rgba(86, 156, 214, 0.25); border-color: #569cd6 !important; }

        /* Modern Tables */
        .table-modern { border-collapse: separate; border-spacing: 0 8px; width: 100%; table-layout: auto; }
        .table-modern thead th { border: none; background: transparent; color: #6c757d; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 0 15px 10px; white-space: nowrap; }
        .table-modern tbody tr {
            background: #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            border-radius: 10px; transition: all 0.2s;
        }
        .table-modern tbody tr:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0,0,0,0.06); }
        .table-modern td { border: none; padding: 12px 15px; vertical-align: middle; white-space: nowrap; }
        .table-modern td:first-child { border-radius: 10px 0 0 10px; }
        .table-modern td:last-child { border-radius: 0 10px 10px 0; }
        
        /* Cột Intent có thể cho xuống dòng nhưng giới hạn */
        .col-intents { white-space: normal !important; min-width: 150px; max-width: 250px; }
        .col-id { font-size: 0.75rem; letter-spacing: -0.2px; }
        .col-created { font-size: 0.8rem; }
        
        .status-info-table td { padding: 10px 0; border-bottom: 1px solid rgba(0,0,0,0.03); }
        .status-info-table tr:last-child td { border-bottom: none; }
        
        .badge-soft-primary { background-color: rgba(26,111,196,0.1); color: #1a6fc4; font-weight: 600; padding: 6px 10px; border-radius: 6px; }
        .badge-soft-warning { background-color: rgba(255,193,7,0.15); color: #d39e00; font-weight: 600; padding: 6px 10px; border-radius: 6px; }
        .badge-soft-secondary { background-color: rgba(108,117,125,0.1); color: #6c757d; font-weight: 600; padding: 6px 10px; border-radius: 6px; }
        .badge-soft-success { background-color: rgba(40,167,69,0.15); color: #28a745; font-weight: 600; padding: 6px 10px; border-radius: 6px; }
        .badge-soft-danger { background-color: rgba(220,53,69,0.15); color: #dc3545; font-weight: 600; padding: 6px 10px; border-radius: 6px; }
        .alert-soft-info { background-color: #e7f3ff; color: #1a6fc4; border: 1px solid #cce5ff; border-radius: 8px; }
        .prompt-section .nav-tabs .nav-link { border: none; color: #6c757d; font-weight: 500; border-bottom: 2px solid transparent; }
        .prompt-section .nav-tabs .nav-link.active { color: #1a6fc4; border-bottom: 2px solid #1a6fc4; background: transparent; }

        /* iMessage Style Chat Bubbles */
        .chat-bubble-container { display: flex; flex-direction: column; padding: 15px; }
        .chat-bubble-wrapper { display: flex; margin-bottom: 20px; width: 100%; }
        .chat-user { justify-content: flex-end; }
        .chat-ai { justify-content: flex-start; }
        
        .chat-bubble {
            display: inline-block;
            max-width: 85%;
            padding: 12px 18px;
            position: relative;
            font-size: 0.95rem;
            line-height: 1.5;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            word-wrap: break-word;
            word-break: break-word;
        }
        
        /* Markdown Elements Inside Bubble */
        .chat-bubble p { margin-bottom: 0.75rem; }
        .chat-bubble p:last-child { margin-bottom: 0; }
        .chat-bubble a { color: #007bff; text-decoration: underline; font-weight: 500; }
        .chat-bubble-user a { color: #fff; }
        
        .chat-bubble table { 
            width: 100%; border-collapse: collapse; margin: 10px 0; 
            background: #fff; border-radius: 8px; overflow: hidden;
            display: block; overflow-x: auto; /* Scroll ngang cho bảng */
        }
        .chat-bubble table th, .chat-bubble table td { 
            border: 1px solid #dee2e6; padding: 8px 12px; font-size: 0.85rem; 
        }
        .chat-bubble table th { background: #f8f9fa; font-weight: bold; }
        .chat-bubble-user table td, .chat-bubble-user table th { color: #333; }

        .chat-bubble ul, .chat-bubble ol { padding-left: 20px; margin-bottom: 10px; }
        .chat-bubble pre { background: rgba(0,0,0,0.05); padding: 10px; border-radius: 6px; margin: 10px 0; }
        .chat-bubble code { font-family: monospace; font-size: 0.9em; background: rgba(0,0,0,0.03); padding: 2px 4px; border-radius: 4px; }
        .chat-bubble-user code { background: rgba(255,255,255,0.2); color: #fff; }

        .chat-bubble-user {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: #fff;
            border-radius: 18px 18px 4px 18px;
            margin-left: auto; /* Đẩy sang phải cho user */
        }
        
        .chat-bubble-ai {
            background: #fff;
            color: #333;
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 18px 18px 18px 4px;
        }

        .chat-meta { font-size: 0.7rem; margin-top: 6px; opacity: 0.7; }
        .chat-user .chat-meta { text-align: right; color: rgba(255,255,255,0.8); }
        .chat-ai .chat-meta { text-align: left; color: #6c757d; }

        /* Custom scrollbar for modal */
        .modal-chat-body::-webkit-scrollbar { width: 6px; }
        .modal-chat-body::-webkit-scrollbar-track { background: #f1f1f1; }
        .modal-chat-body::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 10px; }
        .modal-chat-body::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
    </style>

    <!-- Modals -->
    <!-- Modal Add Keyword -->
    <div class="modal fade" id="modalAddKeyword" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg rounded-lg">
                <div class="modal-header border-0 bg-light rounded-top">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle text-primary mr-2"></i>Thêm Từ khóa Cấm</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <form id="formAddKeyword">
                        <div class="form-group">
                            <label class="font-weight-bold">Cụm từ khóa</label>
                            <input type="text" class="form-control rounded-lg" name="keyword" required placeholder="Ví dụ: dự án bitcoin...">
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Phân loại Nhóm</label>
                            <select class="form-control rounded-lg custom-select" name="group" required>
                                <option value="out_of_scope">Ngoài phạm vi tư vấn (Out of Scope)</option>
                                <option value="inappropriate">Nhạy cảm / Thô tục (Inappropriate)</option>
                                <option value="competitor">Thông tin Đối thủ (Competitor)</option>
                                <option value="legal_finance">Tư vấn Luật / Tài chính cá nhân</option>
                                <option value="internal_info">Thông tin Mật / Nội bộ</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Loại khớp (Match Type)</label>
                            <select class="form-control rounded-lg custom-select" name="match_type" required>
                                <option value="contains">Khớp một phần (Contains)</option>
                                <option value="exact">Khớp chính xác (Exact)</option>
                            </select>
                        </div>
                        <div class="form-group mb-0 mt-4 bg-light p-3 rounded-lg border">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="customSwitchEnabled" name="enabled" value="1" checked>
                                <label class="custom-control-label font-weight-bold" for="customSwitchEnabled" style="cursor:pointer">Đưa vào hoạt động ngay lập tức</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-md px-4" data-dismiss="modal">Hủy bỏ</button>
                    <button type="button" class="btn btn-primary rounded-md px-4 shadow-sm" id="btnSaveKeyword">Lưu Từ khóa</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Session Detail -->
    <div class="modal fade" id="modalSessionDetail" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg rounded-lg overflow-hidden">
                <div class="modal-header glass-header align-items-center">
                    <div>
                        <h5 class="modal-title font-weight-bold mb-1">Chi tiết Phiên Chat</h5>
                        <p class="mb-0 text-muted text-sm">Session: <code id="detail-session-id" class="bg-light px-2 py-1 rounded"></code></p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body modal-chat-body p-0" style="background: #f4f7f6; max-height: 65vh; overflow-y: auto;">
                    <div id="chat-history-container" class="chat-bubble-container">
                        <div class="text-center py-5"><div class="spinner-grow text-primary" role="status"></div></div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top-0 justify-content-between py-3 px-4">
                    <div class="d-flex gap-2">
                        <span class="badge badge-soft-primary px-3 py-2" id="detail-intent"><i class="fas fa-bullseye mr-1"></i> Stage: N/A</span>
                        <span class="badge badge-soft-secondary px-3 py-2 ml-2" id="detail-tokens"><i class="fas fa-coins mr-1"></i> Tokens: 0</span>
                    </div>
                    <div>
                        <a href="#" id="btnExportSingleSession" target="_blank" class="btn btn-outline-primary rounded-md px-4 btn-hover-lift"><i class="fas fa-download mr-1"></i> Tải JSON</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('backend_assets/js/chatbot-admin.js') }}"></script>
    <script>
        $(function() {
            // Khởi tạo tab tương ứng qua JS
            if (typeof initChatbotTab === 'function') {
                initChatbotTab('{{ $tab }}');
            }
        });
    </script>
@endsection
