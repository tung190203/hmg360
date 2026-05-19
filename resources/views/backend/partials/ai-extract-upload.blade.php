@php
    $extractId = $extractId ?? 'ai-extract';
    $extractModel = $extractModel ?? null;
    $extractFileUrl = old('ai_extract_file_url', $extractFileUrl ?? '');

    if (!$extractFileUrl && $extractModel) {
        $rawFiles = '';

        if (isset($extractModel->download) && $extractModel->download) {
            $rawFiles = $extractModel->download;
        } elseif (method_exists($extractModel, 'getTranslation')) {
            $locale = app()->getLocale();
            $rawFiles = $extractModel->getTranslation('files', $locale, false)
                ?: $extractModel->getTranslation('files', 'vi', false)
                ?: $extractModel->getTranslation('files', 'en', false);
        } elseif (isset($extractModel->files)) {
            $rawFiles = $extractModel->files;
        }

        if (is_array($rawFiles)) {
            $rawFiles = reset($rawFiles);
        }

        $fileCandidates = array_values(array_filter(array_map('trim', explode(';', (string) $rawFiles))));
        $extractFileUrl = $fileCandidates[0] ?? '';
    }
@endphp

<div class="ai-extract-panel mt-3 mb-4" id="{{ $extractId }}">
    <div class="ai-extract-panel__header">
        <div>
            <div class="ai-extract-panel__title">
                <i class="fas fa-file-alt"></i>
                <span>Trích xuất nội dung file/ảnh cho AI</span>
            </div>
            <div class="ai-extract-panel__subtitle">Upload tài liệu để lấy text, tóm tắt và lưu vào dữ liệu đồng bộ AI.</div>
        </div>
    </div>

    <div class="ai-extract-panel__body">
        <textarea name="extracted_text" class="d-none">{{ old('extracted_text', $extractModel->extracted_text ?? '') }}</textarea>
        <textarea name="extracted_summary" class="d-none">{{ old('extracted_summary', $extractModel->extracted_summary ?? '') }}</textarea>
        <input type="hidden" name="extracted_language" value="{{ old('extracted_language', $extractModel->extracted_language ?? '') }}">
        <input type="hidden" name="extracted_at" value="{{ old('extracted_at', $extractModel->extracted_at ?? '') }}">

        <div class="ai-extract-grid">
            <div class="ai-extract-field ai-extract-field--file">
                <label>File</label>
                <div class="ai-extract-file-picker">
                    <input type="text" class="form-control js-ai-extract-file-url" name="ai_extract_file_url" value="{{ $extractFileUrl }}" placeholder="/storage/.../files/tai-lieu.pdf" readonly>
                    <button type="button" class="btn btn-outline-primary js-ai-extract-browse">
                        <i class="fas fa-folder-open"></i>
                        <span>Chọn</span>
                    </button>
                </div>
                <small>PDF, Word, ảnh, TXT, MD. Tối đa 20MB.</small>
            </div>

            <div class="ai-extract-field">
                <label>Mức tóm tắt</label>
                <select class="form-control js-ai-extract-summary-mode">
                    <option value="auto">Tự động</option>
                    <option value="short">Ngắn</option>
                    <option value="normal">Bình thường</option>
                    <option value="detailed">Chi tiết</option>
                    <option value="none">Không tóm tắt</option>
                </select>
            </div>

            <div class="ai-extract-field">
                <label>Ngôn ngữ</label>
                <select class="form-control js-ai-extract-language">
                    <option value="auto">Tự nhận diện</option>
                    <option value="vi">Tiếng Việt</option>
                    <option value="en">Tiếng Anh</option>
                </select>
            </div>

            <div class="ai-extract-action">
                <button type="button" class="btn btn-primary js-ai-extract-submit">
                    <i class="fas fa-magic"></i>
                    <span>Trích xuất</span>
                </button>
            </div>
        </div>

        <div class="ai-extract-status js-ai-extract-status d-none"></div>

        <div class="ai-extract-preview">
            <div class="ai-extract-preview__label">
                <span>Tóm tắt đã trích xuất</span>
                @if(!empty($extractModel?->extracted_at))
                    <small>Cập nhật: {{ $extractModel->extracted_at }}</small>
                @endif
            </div>
            <textarea class="form-control js-ai-extract-summary-preview" rows="5" placeholder="AI sẽ điền bản tóm tắt vào đây. Bạn có thể chỉnh sửa lại hoặc tự nhập nội dung tóm tắt thủ công.">{{ old('extracted_summary', $extractModel->extracted_summary ?? '') }}</textarea>
        </div>

        <details class="ai-extract-fulltext">
            <summary>Nội dung text đầy đủ</summary>
            <textarea class="form-control js-ai-extract-text-preview" rows="8" placeholder="AI sẽ điền text đầy đủ vào đây. Bạn cũng có thể tự nhập hoặc chỉnh sửa nội dung nguồn trước khi lưu.">{{ old('extracted_text', $extractModel->extracted_text ?? '') }}</textarea>
        </details>
    </div>
</div>

@once
    <style>
        .ai-extract-panel {
            border: 1px solid #d7dee8;
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
        }

        .ai-extract-panel__header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 16px;
            border-bottom: 1px solid #e6ebf1;
            background: #f8fafc;
        }

        .ai-extract-panel__title {
            display: flex;
            align-items: center;
            gap: 9px;
            color: #1f2937;
            font-size: 16px;
            font-weight: 700;
            line-height: 1.3;
        }

        .ai-extract-panel__title i {
            color: #2563eb;
            font-size: 15px;
        }

        .ai-extract-panel__subtitle {
            margin-top: 3px;
            color: #6b7280;
            font-size: 13px;
            line-height: 1.4;
        }

        .ai-extract-panel__body {
            padding: 16px;
        }

        .ai-extract-grid {
            display: grid;
            grid-template-columns: minmax(280px, 1fr) 190px 170px 142px;
            align-items: start;
            gap: 14px;
        }

        .ai-extract-field label,
        .ai-extract-preview__label {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 6px;
            color: #374151;
            font-size: 13px;
            font-weight: 700;
        }

        .ai-extract-field small,
        .ai-extract-preview__label small {
            display: block;
            margin-top: 5px;
            color: #7b8491;
            font-size: 12px;
            font-weight: 400;
        }

        .ai-extract-field .form-control {
            min-height: 38px;
            border-color: #cfd8e3;
            border-radius: 6px;
            font-size: 14px;
        }

        .ai-extract-file-picker {
            display: flex;
            gap: 8px;
        }

        .ai-extract-file-picker .form-control {
            flex: 1 1 auto;
            min-width: 0;
            background: #fff;
        }

        .ai-extract-file-picker .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-width: 86px;
            min-height: 38px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 700;
        }

        .ai-extract-action .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            min-height: 38px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 700;
            white-space: nowrap;
        }

        .ai-extract-action {
            padding-top: 25px;
        }

        .ai-extract-status {
            margin-top: 14px;
            padding: 9px 12px;
            border-radius: 6px;
            font-size: 13px;
            line-height: 1.4;
        }

        .ai-extract-status.alert-info,
        .ai-extract-status.ai-extract-status--info {
            color: #075985;
            background: #e0f2fe;
            border: 1px solid #bae6fd;
        }

        .ai-extract-preview {
            margin-top: 16px;
        }

        .ai-extract-preview textarea {
            min-height: 118px;
            resize: vertical;
            border-color: #cfd8e3;
            border-radius: 6px;
            background: #fbfdff;
            color: #1f2937;
            font-size: 14px;
            line-height: 1.5;
        }

        .ai-extract-preview textarea:placeholder-shown {
            background: #f8fafc;
        }

        .ai-extract-fulltext {
            margin-top: 12px;
        }

        .ai-extract-fulltext summary {
            cursor: pointer;
            color: #2563eb;
            font-size: 13px;
            font-weight: 700;
            user-select: none;
        }

        .ai-extract-fulltext textarea {
            margin-top: 8px;
            resize: vertical;
            border-color: #cfd8e3;
            border-radius: 6px;
            background: #fbfdff;
            color: #1f2937;
            font-size: 14px;
            line-height: 1.5;
        }

        @media (max-width: 1199px) {
            .ai-extract-grid {
                grid-template-columns: minmax(260px, 1fr) 180px 160px;
            }

            .ai-extract-action {
                grid-column: 1 / -1;
                padding-top: 0;
            }

            .ai-extract-action .btn {
                width: auto;
                min-width: 140px;
            }
        }

        @media (max-width: 767px) {
            .ai-extract-grid {
                grid-template-columns: 1fr;
            }

            .ai-extract-action .btn {
                width: 100%;
            }
        }
    </style>
    <script>
        document.addEventListener('click', function (event) {
            const browseButton = event.target.closest('.js-ai-extract-browse');
            if (!browseButton) return;

            const wrapper = browseButton.closest('.ai-extract-panel');
            const fileUrlInput = wrapper.querySelector('.js-ai-extract-file-url');

            if (typeof CKFinder === 'undefined') {
                alert('CKFinder chưa được tải trên trang này.');
                return;
            }

            CKFinder.popup({
                chooseFiles: true,
                width: 900,
                height: 650,
                onInit: function (finder) {
                    finder.on('files:choose', function (evt) {
                        const file = evt.data.files.first();
                        fileUrlInput.value = file.getUrl();
                    });

                    finder.on('file:choose:resizedImage', function (evt) {
                        fileUrlInput.value = evt.data.resizedUrl;
                    });
                }
            });
        });

        document.addEventListener('click', async function (event) {
            const button = event.target.closest('.js-ai-extract-submit');
            if (!button) return;

                const wrapper = button.closest('.ai-extract-panel');
                const fileUrlInput = wrapper.querySelector('.js-ai-extract-file-url');
                const statusBox = wrapper.querySelector('.js-ai-extract-status');
                const summaryPreview = wrapper.querySelector('.js-ai-extract-summary-preview');

                function setStatus(type, message) {
                    statusBox.className = 'ai-extract-status js-ai-extract-status alert-' + type;
                    statusBox.textContent = message;
                }

                if (!fileUrlInput.value) {
                    setStatus('warning', 'Vui lòng chọn file từ CKFinder trước khi trích xuất.');
                    return;
                }

                const formData = new FormData();
                formData.append('file_url', fileUrlInput.value);
                formData.append('summary_mode', wrapper.querySelector('.js-ai-extract-summary-mode').value);
                formData.append('language', wrapper.querySelector('.js-ai-extract-language').value);

                button.disabled = true;
                setStatus('info', 'Đang trích xuất nội dung, vui lòng chờ...');

                try {
                    const response = await fetch('/backend/chatbot-admin/extract', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Không thể trích xuất file.');
                    }

                    wrapper.querySelector('textarea[name="extracted_text"]').value = data.extracted_text || '';
                    wrapper.querySelector('textarea[name="extracted_summary"]').value = data.summary || '';
                    wrapper.querySelector('input[name="extracted_language"]').value = data.language_detected || '';
                    wrapper.querySelector('input[name="extracted_at"]').value = new Date().toISOString().slice(0, 19).replace('T', ' ');
                    summaryPreview.value = data.summary || '';
                    wrapper.querySelector('.js-ai-extract-text-preview').value = data.extracted_text || '';

                    setStatus('success', 'Trích xuất thành công. Chi phí ước tính: $' + Number(data.cost_usd_total || 0).toFixed(6));
                } catch (error) {
                    setStatus('danger', error.message);
                } finally {
                    button.disabled = false;
                }
        });

        document.addEventListener('input', function (event) {
            const summaryPreview = event.target.closest('.js-ai-extract-summary-preview');
            const textPreview = event.target.closest('.js-ai-extract-text-preview');
            if (!summaryPreview && !textPreview) return;

            const wrapper = event.target.closest('.ai-extract-panel');
            if (summaryPreview) {
                wrapper.querySelector('textarea[name="extracted_summary"]').value = summaryPreview.value;
            }
            if (textPreview) {
                wrapper.querySelector('textarea[name="extracted_text"]').value = textPreview.value;
            }
            wrapper.querySelector('input[name="extracted_at"]').value = new Date().toISOString().slice(0, 19).replace('T', ' ');
        });
    </script>
@endonce
