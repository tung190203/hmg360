if (document.getElementById('chatbot_primary_color')) {
    document.getElementById('chatbot_primary_color').addEventListener('input', function(e) {
        document.getElementById('color-hex-preview').value = e.target.value.toUpperCase();
    });
}

$(function() {
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();

    // Hàm khởi tạo theo Tab Name (dùng cho kiến trúc Sidebar Sub-menu mới)
    window.initChatbotTab = function(tabName) {
        console.log("Initializing Chatbot Tab:", tabName);
        switch(tabName) {
            case 'sync': loadSyncSettings(); break;
            case 'prompts': loadPrompts(); break;
            case 'blacklist': loadBlacklistGroups(); break;
            case 'sessions': loadSessions(); break;
            case 'knowledge': loadKnowledgeDashboard(); break;
            case 'usage': loadUsageDashboard(); break;
        }
    };

    // Event listener cho tabs (Dùng cho các tab nội bộ bên trong trang như Blacklist/Sessions)
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href");
        if (target === '#bl-keywords') loadBlacklistGroups();
        else if (target === '#bl-refusals') loadRefusals();
        else if (target === '#bl-logs') loadBlacklistLogs();
        else if (target === '#sess-list') loadSessions();
        else if (target === '#sess-feedback') loadFeedbacks();
        else if (target === '#knowledge-jobs-pane') loadKnowledgeJobs();
        else if (target === '#knowledge-docs-pane') loadKnowledgeDocs();
    });

    // --- SYNC ---
    window.loadSyncSettings = function() {
        $('#sync-content-container').hide();
        $('#sync-status-container').show();
        $.get('/backend/chatbot-admin/sync/settings', function(res) {
            $('#sync-status-container').hide();
            $('#sync-content-container').fadeIn();
            if(res) {
                // Collections
                $('#sync-active-collection').text(res.qdrant_active_collection || 'N/A');
                $('#sync-standby-collection').text(res.qdrant_standby_collection || 'N/A');
                
                // Config Info & Form Inputs
                var delta = res.delta_sync_interval_hours || res.sync_delta_interval || 1;
                var fullHour = (res.full_rebuild_cron_hour !== undefined) ? res.full_rebuild_cron_hour : (res.sync_full_hour !== undefined ? res.sync_full_hour : 3);
                
                $('#info-delta-interval').text(delta);
                $('#sync_delta_interval').val(delta);
                
                $('#info-full-time').text(fullHour.toString().padStart(2, '0') + ':00');
                $('#sync_full_hour').val(fullHour);
                
                var isAuto = (res.sync_enabled === true || res.sync_enabled == 1 || res.sync_enabled === "true" || res.sync_auto_enabled === true);
                $('#sync_auto_enabled').prop('checked', isAuto);
                
                // Status Badge
                if (isAuto) {
                    $('#sync-status-badge').text('Đang bật').removeClass('badge-soft-secondary badge-soft-danger').addClass('badge-soft-success');
                } else {
                    $('#sync-status-badge').text('Đang tắt').removeClass('badge-soft-success badge-soft-secondary').addClass('badge-soft-danger');
                }

                // Last sync times (placeholder if not provided)
                if(res.last_delta_sync) $('#info-last-delta').text(new Date(res.last_delta_sync).toLocaleString('vi-VN'));
                if(res.last_full_sync) $('#info-last-full').text(new Date(res.last_full_sync).toLocaleString('vi-VN'));
            }
        }).fail(function() {
            $('#sync-status-container').html('<div class="text-danger"><i class="fas fa-exclamation-circle mr-1"></i> Không thể kết nối AI Core.</div>');
        });
    }

    $('#btn-save-sync-config').click(function() {
        var data = {
            delta_sync_interval_hours: parseInt($('#sync_delta_interval').val()),
            full_rebuild_cron_hour: parseInt($('#sync_full_hour').val()),
            sync_enabled: $('#sync_auto_enabled').is(':checked'),
            _token: CSRF_TOKEN
        };
        
        var btn = $(this);
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Đang lưu...').prop('disabled', true);
        
        $.post('/backend/chatbot-admin/sync/settings', data, function(res) {
            toastr.success('Đã lưu cấu hình lịch đồng bộ!');
            loadSyncSettings();
        }).fail(function() { toastr.error('Lỗi khi lưu cấu hình!'); })
          .always(function() { btn.html('<i class="fas fa-save mr-2"></i>Lưu cấu hình').prop('disabled', false); });
    });

    $('#btn-trigger-sync-delta').click(function() {
        var btn = $(this);
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Đang chạy...').prop('disabled', true);
        $.post('/backend/chatbot-admin/sync/trigger', { mode: 'delta', _token: CSRF_TOKEN }, function(res) {
            toastr.success('Đã kích hoạt đồng bộ (Delta)');
        }).fail(function() { toastr.error('Lỗi khi đồng bộ!'); })
          .always(function() { btn.html('<i class="fas fa-bolt mr-2"></i>Đồng bộ Delta (Nhanh)').prop('disabled', false); });
    });

    $('#btn-trigger-sync-full').click(function() {
        if(confirm('Đồng bộ Toàn bộ sẽ tốn nhiều thời gian. Bạn chắc chắn chứ?')) {
            var btn = $(this);
            btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Đang chạy...').prop('disabled', true);
            $.post('/backend/chatbot-admin/sync/trigger', { mode: 'full', _token: CSRF_TOKEN }, function(res) {
                toastr.success('Đã kích hoạt đồng bộ (Full)');
            }).fail(function() { toastr.error('Lỗi khi đồng bộ!'); })
              .always(function() { btn.html('<i class="fas fa-sync-alt mr-2"></i>Build Toàn bộ (Chậm)').prop('disabled', false); });
        }
    });

    $('#btn-swap-slots').click(function() {
        if(confirm('Xác nhận đổi Collection Active? Traffic người dùng sẽ được chuyển hướng ngay lập tức.')) {
            $.post('/backend/chatbot-admin/sync/swap', { _token: CSRF_TOKEN }, function(res) {
                toastr.success('Đã đổi Collection thành công!');
                loadSyncSettings();
            }).fail(function() { toastr.error('Lỗi khi swap slots!'); });
        }
    });

    // --- PROMPTS ---
    function loadPrompts() {
        $.get('/backend/chatbot-admin/prompts', function(res) {
            if (res && res.prompts) {
                var promptsByKey = {};
                res.prompts.forEach(function(p) {
                    if(!promptsByKey[p.key]) promptsByKey[p.key] = {};
                    promptsByKey[p.key][p.language] = p;
                });

                var promptDefinitions = [
                    { key: 'empty_context_refusal', title: 'Phản hồi khi không tìm thấy dữ liệu', desc: 'Hiển thị khi AI tìm trong cơ sở dữ liệu nhưng không có thông tin phù hợp. Khác với fallback ở chỗ: câu hỏi đúng phạm vi nhưng dữ liệu thiếu.' },
                    { key: 'fallback', title: 'Phản hồi khi câu hỏi ngoài phạm vi', desc: 'Hiển thị khi AI nhận diện câu hỏi nằm ngoài phạm vi tư vấn.' },
                    { key: 'greeting', title: 'Lời chào đầu phiên', desc: 'Hiển thị khi user vào phiên chat lần đầu hoặc gửi câu chào. Có thể dùng xuống dòng để format đẹp.' }
                ];

                var html = `
                    <div class="p-4 bg-white">
                        <div class="alert alert-soft-info mb-4 border-0">
                            <div class="d-flex">
                                <i class="fas fa-info-circle mt-1 mr-3"></i>
                                <div>
                                    <p class="mb-1 font-weight-bold">Đây là 3 đoạn text mà bot sẽ <span class="text-primary">nói trực tiếp với người dùng</span> trong các tình huống đặc biệt. Sửa ở đây sẽ có <span class="text-danger">hiệu lực ngay</span> với các câu hỏi sau, không cần restart hệ thống.</p>
                                    <small class="text-muted">Lưu ý: Các prompt mang tính kỹ thuật (system prompt, intent rules...) vẫn nằm trong code để đảm bảo bot trả lời đúng.</small>
                                </div>
                            </div>
                        </div>
                `;

                promptDefinitions.forEach(function(def) {
                    var vi = promptsByKey[def.key] ? promptsByKey[def.key]['vi'] : null;
                    var en = promptsByKey[def.key] ? promptsByKey[def.key]['en'] : null;

                    html += `
                        <div class="prompt-section mb-5">
                            <h6 class="font-weight-bold text-dark mb-1">${def.title}</h6>
                            <p class="text-xs text-muted mb-3">${def.desc}</p>
                            
                            <ul class="nav nav-tabs border-0 mb-2 text-xs" role="tablist">
                                <li class="nav-item"><a class="nav-link active py-1" data-toggle="tab" href="#tab-${def.key}-vi">Tiếng Việt <small class="text-muted ml-1">Mặc định</small></a></li>
                                <li class="nav-item"><a class="nav-link py-1" data-toggle="tab" href="#tab-${def.key}-en">English <small class="text-muted ml-1">Mặc định</small></a></li>
                            </ul>
                            
                            <div class="tab-content border rounded bg-white">
                                <div class="tab-pane fade show active p-0" id="tab-${def.key}-vi">
                                    <textarea class="form-control border-0 p-3" rows="5" id="prompt-content-${def.key}-vi" style="font-size: 0.9rem; line-height: 1.5;">${vi ? vi.current : ''}</textarea>
                                </div>
                                <div class="tab-pane fade p-0" id="tab-${def.key}-en">
                                    <textarea class="form-control border-0 p-3" rows="5" id="prompt-content-${def.key}-en" style="font-size: 0.9rem; line-height: 1.5;">${en ? en.current : ''}</textarea>
                                </div>
                            </div>
                            
                            <div class="mt-3 d-flex align-items-center">
                                <button class="btn btn-primary rounded-md px-3 btn-sm btn-save-prompt mr-2" data-key="${def.key}" data-lang="vi">
                                    <i class="fas fa-save mr-1"></i> Lưu thay đổi
                                </button>
                                <button class="btn btn-outline-secondary rounded-md px-3 btn-sm btn-reset-prompt mr-3" data-key="${def.key}" data-lang="vi">
                                    <i class="fas fa-undo-alt mr-1"></i> Khôi phục mặc định
                                </button>
                                <a href="javascript:void(0)" class="text-xs text-muted"><i class="fas fa-caret-right mr-1"></i> Xem giá trị mặc định</a>
                            </div>
                        </div>
                    `;
                });

                html += '</div>';
                $('#prompts-container').html(html).removeClass('text-center py-5');
            }
        }).fail(function() {
            $('#prompts-container').html('<div class="text-danger py-4"><i class="fas fa-exclamation-triangle mr-1"></i> Lỗi kết nối API lấy Prompts.</div>');
        });
    }

    $(document).on('click', '.btn-save-prompt', function() {
        var key = $(this).data('key');
        var lang = $(this).data('lang');
        var content = $('#prompt-content-' + key + '-' + lang).val();
        var btn = $(this);
        var originalText = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Đang deploy...').prop('disabled', true);
        
        $.ajax({
            url: '/backend/chatbot-admin/prompts/' + key + '/' + lang,
            type: 'PUT',
            data: { content: content, _token: CSRF_TOKEN },
            success: function() { toastr.success('Lưu prompt thành công!'); },
            error: function() { toastr.error('Lỗi khi lưu prompt!'); },
            complete: function() { btn.html(originalText).prop('disabled', false); }
        });
    });

    $(document).on('click', '.btn-reset-prompt', function() {
        var key = $(this).data('key');
        var lang = $(this).data('lang');
        if(confirm('Chắc chắn muốn reset prompt này về mặc định ban đầu?')) {
            $.post('/backend/chatbot-admin/prompts/' + key + '/' + lang + '/reset', { _token: CSRF_TOKEN }, function() { 
                toastr.success('Đã reset về mặc định!');
                loadPrompts();
            }).fail(function() { toastr.error('Lỗi reset!'); });
        }
    });

    // --- BLACKLIST ---
    var globalBlacklistGroups = [];
    
    function loadBlacklistGroups() {
        $('#blacklist-container').html('<div class="text-center py-5"><div class="spinner-grow text-primary" role="status"></div></div>');
        $.get('/backend/chatbot-admin/blacklist', function(res) {
            if (res && res.groups) {
                globalBlacklistGroups = res.groups;
                renderBlacklistKeywords();
            } else {
                $('#blacklist-container').html('<div class="text-center py-4 text-muted">Không có dữ liệu.</div>');
            }
        }).fail(function() {
            $('#blacklist-container').html('<div class="text-center text-danger py-4">Lỗi tải dữ liệu Blacklist.</div>');
        });
    }

    function renderBlacklistKeywords() {
        var html = `
            <div class="table-responsive">
            <table class="table table-modern">
                <thead><tr><th width="60">ID</th><th>Nhóm Phân loại</th><th>Từ khóa (Keyword)</th><th>Kiểu khớp</th><th width="80" class="text-center">Thao tác</th></tr></thead>
                <tbody>
        `;
        var hasKeywords = false;
        globalBlacklistGroups.forEach(function(group) {
            if (group.keywords && group.keywords.length > 0) {
                group.keywords.forEach(function(kw) {
                    hasKeywords = true;
                    html += `<tr>
                        <td class="text-muted">#${kw.id}</td>
                        <td><span class="badge badge-soft-secondary font-weight-bold" style="color: ${group.color || '#6c757d'}">${group.label || group.key}</span></td>
                        <td><span class="font-weight-bold text-dark">${kw.keyword}</span></td>
                        <td><span class="badge border bg-light text-muted">${kw.match_type}</span></td>
                        <td class="text-center"><button class="btn btn-sm btn-light text-danger rounded-circle btn-delete-blacklist shadow-sm" data-id="${kw.id}" title="Xóa"><i class="fas fa-trash"></i></button></td>
                    </tr>`;
                });
            }
        });
        
        if (!hasKeywords) html += '<tr><td colspan="5" class="text-center py-5 text-muted">Hệ thống chưa có từ khóa chặn nào.</td></tr>';
        html += '</tbody></table></div>';
        $('#blacklist-container').html(html);
    }

    function loadRefusals() {
        if (globalBlacklistGroups.length === 0) {
            $.get('/backend/chatbot-admin/blacklist', function(res) {
                if (res && res.groups) {
                    globalBlacklistGroups = res.groups;
                    renderRefusals();
                }
            });
        } else {
            renderRefusals();
        }
    }

    function renderRefusals() {
        var html = '<div class="accordion" id="refusalsAccordion">';
        globalBlacklistGroups.forEach(function(group, index) {
            html += `
                <div class="card mb-3 premium-card border-0">
                    <div class="card-header bg-white border-bottom-0" id="heading-ref-${index}">
                        <h2 class="mb-0">
                            <button class="btn btn-link btn-block text-left text-dark font-weight-bold text-decoration-none d-flex justify-content-between align-items-center" type="button" data-toggle="collapse" data-target="#collapse-ref-${index}">
                                <span><i class="fas fa-folder text-warning mr-2"></i> ${group.label}</span>
                                <span class="badge badge-soft-primary px-3 py-2 rounded-pill">${group.keywords ? group.keywords.length : 0} từ khóa</span>
                            </button>
                        </h2>
                    </div>
                    <div id="collapse-ref-${index}" class="collapse" data-parent="#refusalsAccordion">
                        <div class="card-body bg-light border-top pt-4">
            `;
            if (group.refusals) {
                group.refusals.forEach(function(ref) {
                    html += `
                        <div class="mb-4 bg-white p-4 rounded-lg shadow-sm border-0">
                            <label class="font-weight-bold text-secondary mb-3"><i class="fas fa-globe mr-1"></i> Ngôn ngữ: <span class="text-uppercase text-dark">${ref.language}</span></label>
                            <textarea class="form-control mb-3 rounded border-light bg-light text-dark" rows="3" id="refusal-content-${group.key}-${ref.language}">${ref.current}</textarea>
                            <div class="text-right">
                                <button class="btn btn-light btn-sm rounded-pill px-4 border btn-reset-refusal mr-2" data-group="${group.key}" data-lang="${ref.language}"><i class="fas fa-undo mr-1"></i> Về mặc định</button>
                                <button class="btn btn-primary btn-sm rounded-pill px-4 btn-save-refusal shadow-sm btn-hover-lift" data-group="${group.key}" data-lang="${ref.language}"><i class="fas fa-check mr-1"></i> Lưu cấu hình</button>
                            </div>
                        </div>
                    `;
                });
            }
            html += `</div></div></div>`;
        });
        html += '</div>';
        $('#refusals-container').html(html);
    }

    $(document).on('click', '.btn-save-refusal', function() {
        var group = $(this).data('group');
        var lang = $(this).data('lang');
        var content = $('#refusal-content-' + group + '-' + lang).val();
        
        $.ajax({
            url: '/backend/chatbot-admin/blacklist/refusal/' + group + '/' + lang,
            type: 'PUT',
            data: { content: content, _token: CSRF_TOKEN },
            success: function() { toastr.success('Lưu thành công!'); },
            error: function() { toastr.error('Lỗi lưu câu từ chối!'); }
        });
    });

    $(document).on('click', '.btn-reset-refusal', function() {
        var group = $(this).data('group');
        var lang = $(this).data('lang');
        if(confirm('Chắc chắn muốn khôi phục về mặc định?')) {
            $.post('/backend/chatbot-admin/blacklist/refusal/' + group + '/' + lang + '/reset', { _token: CSRF_TOKEN }, function() { 
                toastr.success('Đã reset về mặc định!');
                loadRefusals();
            }).fail(function() { toastr.error('Lỗi reset!'); });
        }
    });

    $('#btnSaveKeyword').click(function() {
        var data = {
            keyword: $('#formAddKeyword input[name="keyword"]').val(),
            group: $('#formAddKeyword select[name="group"]').val(),
            match_type: $('#formAddKeyword select[name="match_type"]').val(),
            enabled: $('#formAddKeyword input[name="enabled"]').is(':checked'),
            _token: CSRF_TOKEN
        };
        if(!data.keyword) return toastr.warning('Vui lòng nhập từ khóa!');
        
        var btn = $(this);
        btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

        $.post('/backend/chatbot-admin/blacklist', data, function() {
            toastr.success('Đã thêm từ khóa!');
            $('#modalAddKeyword').modal('hide');
            $('#formAddKeyword')[0].reset();
            loadBlacklistGroups();
        }).fail(function() { toastr.error('Lỗi thêm từ khóa!'); })
          .always(function() { btn.html('Lưu Từ khóa').prop('disabled', false); });
    });

    $(document).on('click', '.btn-delete-blacklist', function() {
        var id = $(this).data('id');
        if (confirm('Xóa từ khóa này khỏi hệ thống rào chắn?')) {
            $.ajax({
                url: '/backend/chatbot-admin/blacklist/' + id,
                type: 'DELETE',
                data: { _token: CSRF_TOKEN },
                success: function() { toastr.success('Đã xóa!'); loadBlacklistGroups(); },
                error: function() { toastr.error('Xóa thất bại!'); }
            });
        }
    });

    function loadBlacklistLogs() {
        $('#blacklist-logs-container').html('<div class="text-center py-5"><div class="spinner-grow text-primary" role="status"></div></div>');
        $.get('/backend/chatbot-admin/blacklist/log', function(res) {
            if (res && res.items) {
                var html = `
                    <div class="table-responsive">
                    <table class="table table-modern">
                        <thead><tr><th>Thời gian</th><th>Session ID</th><th>Tin nhắn User</th><th>Từ khóa bắt được</th><th>Nhóm chặn</th></tr></thead>
                        <tbody>
                `;
                res.items.forEach(function(item) {
                    var dateStr = new Date(item.created_at).toLocaleString('vi-VN');
                    html += `<tr>
                        <td class="text-muted"><small>${dateStr}</small></td>
                        <td><code class="bg-light px-2 py-1 rounded text-dark">${(item.session_id || '').substring(0,8)}...</code></td>
                        <td><span class="text-danger font-italic">"${item.user_message}"</span></td>
                        <td><span class="badge badge-soft-warning">${item.matched_keyword}</span></td>
                        <td><span class="badge badge-soft-secondary">${item.matched_group}</span></td>
                    </tr>`;
                });
                if(res.items.length === 0) html += '<tr><td colspan="5" class="text-center py-5 text-muted">Không có lịch sử chặn nào gần đây.</td></tr>';
                html += '</tbody></table></div>';
                $('#blacklist-logs-container').html(html);
            } else {
                $('#blacklist-logs-container').html('<div class="text-center py-4 text-muted">Không có dữ liệu logs.</div>');
            }
        }).fail(function() {
            $('#blacklist-logs-container').html('<div class="text-center text-danger py-4">Lỗi tải dữ liệu.</div>');
        });
    }

    // --- SESSIONS & FEEDBACK ---
    var sessionFilters = {
        language: '',
        intent: '',
        has_feedback: '',
        page: 1,
        page_size: 20
    };

    function loadSessions() {
        $('#sessions-container').html('<div class="text-center py-5"><div class="spinner-grow text-primary" role="status"></div></div>');
        $('#sessions-footer').addClass('d-none');

        // Sync local variables to filter inputs
        $('#filter-lang').val(sessionFilters.language);
        $('#filter-intent').val(sessionFilters.intent);
        $('#filter-feedback').val(sessionFilters.has_feedback);
        $('#filter-page').val(sessionFilters.page);
        $('#filter-size').val(sessionFilters.page_size);

        var params = {
            language: sessionFilters.language,
            intent: sessionFilters.intent,
            has_feedback: sessionFilters.has_feedback,
            page: sessionFilters.page,
            page_size: sessionFilters.page_size
        };

        $.get('/backend/chatbot-admin/sessions', params, function(res) {
            if (res && res.sessions) {
                var tbody = '';
                res.sessions.forEach(function(session) {
                    var dateStr = new Date(session.created_at).toLocaleString('vi-VN', {
                        hour: '2-digit', minute: '2-digit', second: '2-digit',
                        day: 'numeric', month: 'numeric', year: 'numeric'
                    });
                    
                    // Format tokens with dots
                    var tokensStr = (session.total_tokens || 0).toLocaleString('de-DE');
                    
                    // Intents badges
                    var intentHtml = '';
                    if (session.intents && session.intents.length > 0) {
                        session.intents.slice(0, 3).forEach(function(it) {
                            intentHtml += `<span class="badge border bg-light text-muted mr-1 px-2">${it}</span>`;
                        });
                        if (session.intents.length > 3) intentHtml += '<span class="text-muted small">...</span>';
                    }

                    // Feedback badges
                    var fbHtml = '';
                    if (session.has_feedback && session.feedback_summary) {
                        var h = session.feedback_summary.helpful || 0;
                        var nh = session.feedback_summary.not_helpful || 0;
                        fbHtml = `<span class="badge badge-soft-primary px-2 py-1" style="font-size: 0.75rem;">feedback ${h}/${nh}</span>`;
                    }

                    tbody += `<tr>
                        <td class="col-id"><code class="bg-light px-2 py-1 rounded text-dark font-weight-bold" style="cursor:pointer;" title="${session.id}">${(session.id || '').substring(0,12)}..</code></td>
                        <td class="col-created text-sm font-weight-bold text-dark">${dateStr}</td>
                        <td><span class="badge border bg-light text-primary text-uppercase px-2">${(session.language || 'vi')}</span></td>
                        <td><span class="text-dark font-weight-medium">${session.stage || 'N/A'}</span></td>
                        <td class="text-center font-weight-bold">${session.message_count || 0}</td>
                        <td class="text-primary font-weight-bold">${tokensStr}</td>
                        <td class="col-intents">${intentHtml}</td>
                        <td>${fbHtml}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-light text-primary rounded-pill btn-view-session shadow-sm px-3 btn-hover-lift" data-id="${session.id}">
                                <i class="fas fa-eye mr-1"></i> Xem
                            </button>
                        </td>
                    </tr>`;
                });

                if(res.sessions.length === 0) tbody = '<tr><td colspan="9" class="text-center py-5 text-muted">Không tìm thấy phiên chat nào phù hợp.</td></tr>';
                
                var html = `
                    <div class="table-responsive">
                    <table class="table table-modern" style="font-size: 0.85rem;">
                        <thead class="text-uppercase text-xs text-muted">
                            <tr>
                                <th>ID</th>
                                <th>Created</th>
                                <th>Lang</th>
                                <th>Stage</th>
                                <th class="text-center">Msgs</th>
                                <th>Tokens</th>
                                <th>Intents</th>
                                <th>FB</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>${tbody}</tbody>
                    </table>
                    </div>
                `;
                $('#sessions-container').html(html);

                // Update Footer
                $('#sessions-pagination-info').text(`Trang ${res.page}/${res.total_pages || 1} - Tổng ${res.total || 0} sessions`);
                $('#sessions-total-info').text(`${res.sessions.length} kết quả`);
                $('#sessions-footer').removeClass('d-none');
            } else {
                $('#sessions-container').html('<div class="text-center py-4 text-muted">Không có dữ liệu.</div>');
            }
        }).fail(function() {
            $('#sessions-container').html('<div class="text-center text-danger py-4">Lỗi tải dữ liệu Sessions.</div>');
        });
    }

    function updateExportLinks() {
        var baseUrl = '/backend/chatbot-admin/sessions/export';
        var query = `?language=${sessionFilters.language}&intent=${sessionFilters.intent}&has_feedback=${sessionFilters.has_feedback}`;
        
        $('.export-tools .btn-group a').each(function() {
            var href = $(this).attr('href').split('?')[0];
            var type = $(this).attr('href').split('type=')[1].split('&')[0];
            $(this).attr('href', `${href}?type=${type}&language=${sessionFilters.language}&intent=${sessionFilters.intent}&has_feedback=${sessionFilters.has_feedback}`);
        });
    }

    // Filter event listeners
    $('#filter-lang, #filter-feedback, #filter-size').on('change', function() {
        sessionFilters.language = $('#filter-lang').val();
        sessionFilters.has_feedback = $('#filter-feedback').val();
        sessionFilters.page_size = $('#filter-size').val();
        sessionFilters.page = 1; // Reset to page 1 on filter change
        updateExportLinks();
        loadSessions();
    });

    $('#filter-page').on('change', function() {
        var val = parseInt($(this).val());
        if (val > 0) {
            sessionFilters.page = val;
            loadSessions();
        }
    });

    var intentTimer;
    $('#filter-intent').on('input', function() {
        clearTimeout(intentTimer);
        intentTimer = setTimeout(function() {
            sessionFilters.intent = $('#filter-intent').val();
            sessionFilters.page = 1;
            updateExportLinks();
            loadSessions();
        }, 500);
    });

    $('#btn-refresh-sessions').click(function() {
        loadSessions();
    });

    $(document).on('click', '.btn-view-session', function() {
        var sessionId = $(this).data('id');
        $('#detail-session-id').text(sessionId);
        $('#chat-history-container').html('<div class="text-center py-5"><div class="spinner-grow text-primary" role="status"></div></div>');
        $('#btnExportSingleSession').attr('href', '/backend/chatbot-admin/sessions/' + sessionId + '/export?type=json');
        $('#modalSessionDetail').modal('show');
        
        $.get('/backend/chatbot-admin/sessions/' + sessionId, function(res) {
            if(res && res.messages) {
                $('#detail-intent').html('<i class="fas fa-bullseye mr-1"></i> Stage: ' + (res.stage || 'N/A'));
                $('#detail-tokens').html('<i class="fas fa-coins mr-1"></i> Tokens: ' + (res.total_tokens_used || 0));
                
                var html = '';
                var primaryColor = $('#chatbot_primary_color').val() || '#1a6fc4';

                // Cấu hình marked
                marked.setOptions({
                    breaks: true,
                    gfm: true,
                    headerIds: false,
                    mangle: false
                });

                res.messages.forEach(function(msg) {
                    if (msg.role === 'system') return;
                    
                    var isUser = msg.role === 'user';
                    var wrapperClass = isUser ? 'chat-user' : 'chat-ai';
                    var bubbleClass = isUser ? 'chat-bubble-user' : 'chat-bubble-ai';
                    var bubbleStyle = isUser ? `style="background: linear-gradient(135deg, ${primaryColor} 0%, #0056b3 100%);"` : '';
                    var iconHtml = !isUser ? '<div class="mr-2"><div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm border" style="width:35px;height:35px;"><i class="fas fa-robot text-primary"></i></div></div>' : '';
                    
                    var timeStr = new Date(msg.created_at).toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'});
                    var modelStr = msg.model_used ? ` · <i class="fas fa-microchip mx-1"></i> ${msg.model_used}` : '';
                    
                    // Sử dụng marked để parse content
                    var renderedContent = marked.parse(msg.content || '');

                    var alignClass = isUser ? 'align-items-end' : 'align-items-start';
                    
                    html += `
                        <div class="chat-bubble-wrapper ${wrapperClass}">
                            ${iconHtml}
                            <div class="d-flex flex-column ${alignClass}" style="flex: 1; min-width: 0;">
                                <div class="chat-bubble ${bubbleClass}" ${bubbleStyle}>
                                    ${renderedContent}
                                </div>
                                <div class="chat-meta">
                                    ${timeStr}${modelStr}
                                </div>
                            </div>
                        </div>
                    `;
                });
                if(res.messages.length === 0) html = '<div class="text-center py-5 text-muted">Chưa có tin nhắn trong phiên này.</div>';
                $('#chat-history-container').html(html);
            } else {
                $('#chat-history-container').html('<div class="text-center text-danger py-5">Không thể tải lịch sử tin nhắn.</div>');
            }
        });
    });

    function loadFeedbacks() {
        $('#feedback-container').html('<div class="text-center py-5"><div class="spinner-grow text-primary" role="status"></div></div>');
        $.get('/backend/chatbot-admin/feedback', function(res) {
            if (res && res.feedbacks) {
                var html = `
                    <div class="table-responsive">
                    <table class="table table-modern">
                        <thead><tr><th>Session</th><th>Phân loại</th><th>Đánh giá</th><th>Bình luận từ User</th><th>Thời gian</th></tr></thead>
                        <tbody>
                `;
                res.feedbacks.forEach(function(fb) {
                    var stars = '';
                    for(var i=1; i<=5; i++) stars += `<i class="fas fa-star ${i<=fb.rating ? 'text-warning' : 'text-light'}"></i>`;
                    var dateStr = new Date(fb.created_at).toLocaleString('vi-VN');
                    
                    html += `<tr>
                        <td><code class="bg-light px-2 py-1 rounded text-dark">${(fb.session_id || '').substring(0,8)}...</code></td>
                        <td><span class="badge badge-soft-secondary">${fb.feedback_type}</span></td>
                        <td><div class="d-flex align-items-center">${stars}</div></td>
                        <td><span class="text-dark font-italic">${fb.comment || '<span class="text-muted font-weight-normal">Không có bình luận</span>'}</span></td>
                        <td class="text-muted"><small>${dateStr}</small></td>
                    </tr>`;
                });
                if(res.feedbacks.length === 0) html += '<tr><td colspan="5" class="text-center py-5 text-muted">Hệ thống chưa ghi nhận đánh giá nào.</td></tr>';
                html += '</tbody></table></div>';
                $('#feedback-container').html(html);
            } else {
                $('#feedback-container').html('<div class="text-center py-4 text-muted">Không có dữ liệu feedbacks.</div>');
            }
        }).fail(function() {
            $('#feedback-container').html('<div class="text-center text-danger py-4">Lỗi tải dữ liệu Feedbacks.</div>');
        });
    }

    // --- KNOWLEDGE ---
    var knowledgePollTimer = null;
    var knowledgeJobsPage = 1;
    var knowledgeDocsPage = 1;
    var knowledgePageSize = 10;
    var usagePage = 1;
    var usagePageSize = 10;

    function selectKnowledgeFile() {
        if (typeof CKFinder === 'undefined') {
            toastr.error('CKFinder chưa được tải.');
            return;
        }
        CKFinder.popup({
            chooseFiles: true,
            width: 900,
            height: 650,
            onInit: function(finder) {
                finder.on('files:choose', function(evt) {
                    var file = evt.data.files.first();
                    $('#knowledge-file-url').val(file.getUrl());
                    $('#knowledge-text').val('');
                });
                finder.on('file:choose:resizedImage', function(evt) {
                    $('#knowledge-file-url').val(evt.data.resizedUrl);
                    $('#knowledge-text').val('');
                });
            }
        });
    }

    window.loadKnowledgeDashboard = function() {
        loadKnowledgeJobs(true, 1);
        loadKnowledgeDocs(1);
    };

    $('#btn-knowledge-browse').on('click', selectKnowledgeFile);
    $('#btn-refresh-knowledge').on('click', loadKnowledgeDashboard);
    $('#knowledge-text').on('input', function() {
        if ($(this).val().trim()) $('#knowledge-file-url').val('');
    });

    $('#btn-create-knowledge').on('click', function() {
        var fileUrl = $('#knowledge-file-url').val().trim();
        var text = $('#knowledge-text').val().trim();
        if (!fileUrl && !text) {
            toastr.warning('Vui lòng chọn file CKFinder hoặc nhập text.');
            return;
        }
        if (fileUrl && text) {
            toastr.warning('Chỉ dùng file hoặc text, không dùng cả hai.');
            return;
        }

        var btn = $(this);
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Đang gửi...').prop('disabled', true);
        $.post('/backend/chatbot-admin/knowledge', {
            file_url: fileUrl || undefined,
            text: text || undefined,
            title: $('#knowledge-title').val(),
            language: $('#knowledge-language').val(),
            summary_mode: $('#knowledge-summary-mode').val(),
            _token: CSRF_TOKEN
        }, function(res) {
            toastr.success('Đã enqueue job: ' + (res.job_id || ''));
            $('#knowledge-title').val('');
            $('#knowledge-file-url').val('');
            $('#knowledge-text').val('');
            loadKnowledgeJobs(true, 1);
            startKnowledgePolling();
        }).fail(function(xhr) {
            toastr.error(extractErrorMessage(xhr, 'Không thể enqueue tài liệu.'));
        }).always(function() {
            btn.html('<i class="fas fa-upload mr-2"></i>Đưa vào AI').prop('disabled', false);
        });
    });

    function startKnowledgePolling() {
        if (knowledgePollTimer) clearInterval(knowledgePollTimer);
        knowledgePollTimer = setInterval(function() {
            if (!document.getElementById('tab-knowledge')) {
                clearInterval(knowledgePollTimer);
                knowledgePollTimer = null;
                return;
            }
            loadKnowledgeJobs(false, knowledgeJobsPage);
        }, 5000);
    }

    function loadKnowledgeJobs(showSpinner, page) {
        knowledgeJobsPage = page || knowledgeJobsPage || 1;
        if (showSpinner !== false) {
            $('#knowledge-jobs-container').html('<div class="text-center py-5"><div class="spinner-grow text-primary"></div></div>');
        }
        $.get('/backend/chatbot-admin/knowledge/jobs', { limit: knowledgePageSize, offset: (knowledgeJobsPage - 1) * knowledgePageSize }, function(res) {
            var items = res.items || res.jobs || [];
            var html = '<div class="table-responsive"><table class="table table-modern table-sm"><thead><tr><th>Job</th><th>Tiêu đề</th><th>Status</th><th>Doc</th><th>Thời gian</th></tr></thead><tbody>';
            if (!items.length) {
                html += '<tr><td colspan="5" class="text-center text-muted py-4">Chưa có job nào.</td></tr>';
            }
            items.forEach(function(job) {
                var status = job.status || job.job_status || 'queued';
                var badgeClass = status === 'completed' ? 'badge-success' : (status === 'failed' ? 'badge-danger' : 'badge-warning');
                html += `<tr>
                    <td><code>${job.job_id || job.id || '-'}</code></td>
                    <td>${escapeHtml(job.title || job.source_filename || '-')}</td>
                    <td><span class="badge ${badgeClass}">${status}</span></td>
                    <td>${job.doc_id ? `<code>${job.doc_id}</code>` : '-'}</td>
                    <td class="text-muted">${formatDateTime(job.finished_at || job.started_at || job.queued_at || job.created_at)}</td>
                </tr>`;
            });
            html += '</tbody></table></div>';
            html += renderPagination('knowledge-jobs', knowledgeJobsPage, knowledgePageSize, res.total, items.length);
            $('#knowledge-jobs-container').html(html);
        }).fail(function(xhr) {
            $('#knowledge-jobs-container').html('<div class="text-danger py-4">' + extractErrorMessage(xhr, 'Không thể tải jobs.') + '</div>');
        });
    }

    function loadKnowledgeDocs(page) {
        knowledgeDocsPage = page || knowledgeDocsPage || 1;
        $('#knowledge-docs-container').html('<div class="text-center py-5"><div class="spinner-grow text-primary"></div></div>');
        $.get('/backend/chatbot-admin/knowledge', { limit: knowledgePageSize, offset: (knowledgeDocsPage - 1) * knowledgePageSize }, function(res) {
            var items = res.items || res.docs || res.documents || [];
            var html = '<div class="table-responsive"><table class="table table-modern table-sm"><thead><tr><th>Doc</th><th>Tiêu đề</th><th>Chunks</th><th>Ngôn ngữ</th><th></th></tr></thead><tbody>';
            if (!items.length) {
                html += '<tr><td colspan="5" class="text-center text-muted py-4">Chưa có tài liệu indexed.</td></tr>';
            }
            items.forEach(function(doc) {
                html += `<tr>
                    <td><code>${doc.doc_id || doc.id || '-'}</code></td>
                    <td>${escapeHtml(doc.title || doc.source_filename || '-')}</td>
                    <td>${doc.chunk_count || 0}</td>
                    <td>${doc.language_detected || doc.language || '-'}</td>
                    <td class="text-right">
                        <button class="btn btn-sm btn-light text-danger btn-delete-knowledge-doc" data-id="${doc.doc_id || doc.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });
            html += '</tbody></table></div>';
            html += renderPagination('knowledge-docs', knowledgeDocsPage, knowledgePageSize, res.total, items.length);
            $('#knowledge-docs-container').html(html);
        }).fail(function(xhr) {
            $('#knowledge-docs-container').html('<div class="text-danger py-4">' + extractErrorMessage(xhr, 'Không thể tải tài liệu.') + '</div>');
        });
    }

    $(document).on('click', '.js-page-btn', function() {
        var target = $(this).data('target');
        var page = parseInt($(this).data('page'));
        if (!page || page < 1) return;

        if (target === 'knowledge-jobs') loadKnowledgeJobs(true, page);
        if (target === 'knowledge-docs') loadKnowledgeDocs(page);
        if (target === 'usage-items') loadUsageItems(page);
    });

    $(document).on('click', '.btn-delete-knowledge-doc', function() {
        var docId = $(this).data('id');
        if (!docId) return;

        showConfirmDialog({
            title: 'Xóa tài liệu khỏi AI?',
            message: 'Tài liệu sẽ bị xóa khỏi index AI và chatbot sẽ không còn dùng nội dung này để trả lời.',
            confirmText: 'Xóa khỏi AI',
            confirmClass: 'btn-danger',
            iconClass: 'fas fa-trash-alt text-danger'
        }).then(function(confirmed) {
            if (!confirmed) return;
            $.ajax({
                url: '/backend/chatbot-admin/knowledge/' + encodeURIComponent(docId),
                type: 'DELETE',
                data: { _token: CSRF_TOKEN },
                success: function() {
                    toastr.success('Đã xóa tài liệu khỏi AI.');
                    loadKnowledgeDocs(knowledgeDocsPage);
                },
                error: function(xhr) {
                    toastr.error(extractErrorMessage(xhr, 'Không thể xóa tài liệu.'));
                }
            });
        });
    });

    // --- USAGE ---
    window.loadUsageDashboard = function() {
        loadUsageSummary();
        loadUsageItems(1);
    };

    $('#btn-refresh-usage').on('click', function() {
        loadUsageSummary();
        loadUsageItems(usagePage);
    });
    $('#btn-apply-usage-filter').on('click', loadUsageDashboard);

    function usageFilters(includePagination) {
        var params = {};
        if (includePagination) {
            params.limit = usagePageSize;
            params.offset = (usagePage - 1) * usagePageSize;
        }
        if ($('#usage-feature').val()) params.feature = $('#usage-feature').val();
        if ($('#usage-phase').val()) params.phase = $('#usage-phase').val();
        if ($('#usage-date-from').val()) params.date_from = $('#usage-date-from').val() + 'T00:00:00Z';
        if ($('#usage-date-to').val()) params.date_to = $('#usage-date-to').val() + 'T23:59:59Z';
        return params;
    }

    function loadUsageSummary() {
        $.get('/backend/chatbot-admin/usage/summary', usageFilters(false), function(res) {
            var byWindow = res.by_window || {};
            $('#usage-cost-24h').text(formatUsd(byWindow['24h'] ? byWindow['24h'].cost_usd : 0));
            $('#usage-cost-7d').text(formatUsd(byWindow['7d'] ? byWindow['7d'].cost_usd : 0));
            $('#usage-cost-30d').text(formatUsd(byWindow['30d'] ? byWindow['30d'].cost_usd : res.total_cost_usd || 0));
            $('#usage-total-tokens').text(Number(res.total_tokens || 0).toLocaleString('vi-VN'));
            renderUsageBreakdown('#usage-feature-breakdown', res.by_feature || []);
            renderUsageBreakdown('#usage-model-breakdown', res.by_model || []);
        }).fail(function(xhr) {
            toastr.error(extractErrorMessage(xhr, 'Không thể tải usage summary.'));
        });
    }

    function loadUsageItems(page) {
        usagePage = page || usagePage || 1;
        $('#usage-items-container').html('<div class="text-center py-5"><div class="spinner-grow text-primary"></div></div>');
        $.get('/backend/chatbot-admin/usage', usageFilters(true), function(res) {
            var items = res.items || [];
            var html = '<div class="table-responsive"><table class="table table-modern table-sm"><thead><tr><th>Thời gian</th><th>Feature</th><th>Phase</th><th>Model</th><th>Tokens</th><th>Cost</th></tr></thead><tbody>';
            if (!items.length) {
                html += '<tr><td colspan="6" class="text-center text-muted py-4">Không có dữ liệu usage.</td></tr>';
            }
            items.forEach(function(item) {
                var tokens = (item.total_tokens !== undefined) ? item.total_tokens : ((item.input_tokens || 0) + (item.output_tokens || 0));
                html += `<tr>
                    <td class="text-muted">${formatDateTime(item.created_at)}</td>
                    <td><span class="badge badge-light border">${item.feature || item.endpoint || '-'}</span></td>
                    <td>${item.phase || '-'}</td>
                    <td><code>${item.model || item.model_used || '-'}</code></td>
                    <td>${Number(tokens || 0).toLocaleString('vi-VN')}</td>
                    <td class="font-weight-bold">${formatUsd(item.cost_usd || 0)}</td>
                </tr>`;
            });
            html += '</tbody></table></div>';
            html += renderPagination('usage-items', usagePage, usagePageSize, res.total, items.length);
            $('#usage-items-container').html(html);
        }).fail(function(xhr) {
            $('#usage-items-container').html('<div class="text-danger py-4">' + extractErrorMessage(xhr, 'Không thể tải usage.') + '</div>');
        });
    }

    function renderUsageBreakdown(target, items) {
        var html = '<table class="table table-sm table-modern"><thead><tr><th>Key</th><th>Rows</th><th>Tokens</th><th>Cost</th></tr></thead><tbody>';
        if (!items.length) html += '<tr><td colspan="4" class="text-muted text-center">Không có dữ liệu.</td></tr>';
        items.forEach(function(item) {
            html += `<tr>
                <td><code>${item.key || '-'}</code></td>
                <td>${Number(item.rows || 0).toLocaleString('vi-VN')}</td>
                <td>${Number(item.total_tokens || 0).toLocaleString('vi-VN')}</td>
                <td class="font-weight-bold">${formatUsd(item.cost_usd || 0)}</td>
            </tr>`;
        });
        html += '</tbody></table>';
        $(target).html(html);
    }

    function renderPagination(target, currentPage, pageSize, total, currentCount) {
        var knownTotal = Number(total || 0);
        var hasKnownTotal = knownTotal > 0;
        var totalPages = hasKnownTotal ? Math.max(1, Math.ceil(knownTotal / pageSize)) : currentPage + (currentCount >= pageSize ? 1 : 0);
        var prevDisabled = currentPage <= 1 ? 'disabled' : '';
        var nextDisabled = hasKnownTotal ? (currentPage >= totalPages ? 'disabled' : '') : (currentCount < pageSize ? 'disabled' : '');
        var from = currentCount ? ((currentPage - 1) * pageSize + 1) : 0;
        var to = (currentPage - 1) * pageSize + currentCount;
        var totalText = hasKnownTotal ? ` / ${knownTotal.toLocaleString('vi-VN')}` : '';

        return `
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted text-sm">Hiển thị ${from}-${to}${totalText}</div>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-secondary js-page-btn" data-target="${target}" data-page="${currentPage - 1}" ${prevDisabled}>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light border" disabled>Trang ${currentPage}${hasKnownTotal ? ' / ' + totalPages : ''}</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary js-page-btn" data-target="${target}" data-page="${currentPage + 1}" ${nextDisabled}>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        `;
    }

    function showConfirmDialog(options) {
        var modalId = 'aiConfirmModal';
        var modal = $('#' + modalId);
        if (!modal.length) {
            $('body').append(`
                <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-body p-4">
                                <div class="d-flex align-items-start">
                                    <div class="ai-confirm-icon mr-3">
                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                    </div>
                                    <div class="flex-fill">
                                        <h5 class="modal-title mb-2 js-confirm-title">Xác nhận thao tác</h5>
                                        <p class="text-muted mb-0 js-confirm-message">Bạn có chắc chắn muốn tiếp tục?</p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light border-0">
                                <button type="button" class="btn btn-light border" data-dismiss="modal">Hủy</button>
                                <button type="button" class="btn btn-danger js-confirm-submit">Xác nhận</button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            modal = $('#' + modalId);
        }

        modal.find('.js-confirm-title').text(options.title || 'Xác nhận thao tác');
        modal.find('.js-confirm-message').text(options.message || 'Bạn có chắc chắn muốn tiếp tục?');
        modal.find('.ai-confirm-icon i').attr('class', options.iconClass || 'fas fa-exclamation-triangle text-warning');
        modal.find('.js-confirm-submit')
            .attr('class', 'btn js-confirm-submit ' + (options.confirmClass || 'btn-danger'))
            .text(options.confirmText || 'Xác nhận');

        return new Promise(function(resolve) {
            var resolved = false;
            modal.off('click.ai-confirm hidden.bs.modal');
            modal.on('click.ai-confirm', '.js-confirm-submit', function() {
                resolved = true;
                modal.modal('hide');
                resolve(true);
            });
            modal.on('hidden.bs.modal', function() {
                if (!resolved) resolve(false);
            });
            modal.modal('show');
        });
    }

    function formatUsd(value) {
        return '$' + Number(value || 0).toFixed(6);
    }

    function formatDateTime(value) {
        if (!value) return '-';
        var date = new Date(value);
        if (isNaN(date.getTime())) return value;
        return date.toLocaleString('vi-VN');
    }

    function escapeHtml(value) {
        return $('<div>').text(value || '').html();
    }

    function extractErrorMessage(xhr, fallback) {
        var json = xhr.responseJSON || {};
        if (json.message) return json.message;
        if (json.detail && typeof json.detail === 'string') return json.detail;
        if (json.detail && json.detail.message) return json.detail.message;
        if (json.errors) {
            var firstKey = Object.keys(json.errors)[0];
            if (firstKey && json.errors[firstKey] && json.errors[firstKey][0]) return json.errors[firstKey][0];
        }
        return fallback;
    }

});
