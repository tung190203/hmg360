@extends('backend.index')

@section('title', 'Modules')

@section('breadcrumb')
    <li class="breadcrumb-item active">Modules</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <form method="GET" action="{{ route('backend_core_modules') }}" class="form-inline">
            <label class="mr-2 mb-0">Tenant</label>
            <select name="tenant_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">Tất cả tenant</option>
                @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}" @selected((int) $selectedTenantId === $tenant->id)>{{ $tenant->name }}</option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('backend_core_modules_create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i>Thêm module
        </a>
    </div>
    <div class="alert alert-info py-2">
        <i class="fas fa-info-circle mr-1"></i>
        Mỗi dòng là một module tương ứng một đầu mục sidebar. Kéo biểu tượng <i class="fas fa-grip-vertical mx-1"></i> để đổi thứ tự; tắt module sẽ ẩn và chặn toàn bộ chức năng của đầu mục đó.
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Modules</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0 modules-table">
                <colgroup>
                    <col class="modules-table__handle">
                    <col class="modules-table__tenant">
                    <col class="modules-table__group">
                    <col class="modules-table__name">
                    <col class="modules-table__slug">
                    <col class="modules-table__route">
                    <col class="modules-table__status">
                    <col class="modules-table__sort">
                    <col class="modules-table__actions">
                </colgroup>
                <thead>
                    <tr>
                        <th></th>
                        <th>Tenant</th>
                        <th>Nhóm</th>
                        <th>Tên</th>
                        <th>Slug</th>
                        <th>Route</th>
                        <th>Status</th>
                        <th>Sort</th>
                        <th></th>
                    </tr>
                </thead>
                @forelse($modules->groupBy('tenant_id') as $tenantModules)
                    <tbody class="js-module-sortable" data-tenant-id="{{ $tenantModules->first()->tenant_id }}">
                        @foreach($tenantModules as $module)
                            @php
                                $menuConfig = $module->config['menu'] ?? [];
                            @endphp
                            <tr data-module-id="{{ $module->id }}">
                                <td class="text-muted text-center align-middle">
                                    <span class="js-sort-handle" title="Kéo để sắp xếp">
                                        <i class="fas fa-grip-vertical"></i>
                                    </span>
                                </td>
                                <td class="modules-table__text">{{ $module->tenant?->name }}</td>
                                <td class="text-nowrap">{{ ($menuConfig['section'] ?? 'content') === 'systems' ? 'SYSTEMS' : 'CONTENT' }}</td>
                                <td>
                                    <span class="modules-table__module-name">
                                        <i class="{{ $menuConfig['icon'] ?? 'fas fa-cube' }} mr-2 text-muted"></i>
                                        <span>{{ $module->name }}</span>
                                    </span>
                                    @if(!empty($menuConfig['items']))
                                        <span class="badge badge-light border ml-2">có menu con</span>
                                    @endif
                                </td>
                                <td><code class="modules-table__code">{{ $module->slug }}</code></td>
                                <td>
                                    @if(!empty($menuConfig['route']))
                                        <code class="modules-table__code">{{ $menuConfig['route'] }}</code>
                                    @else
                                        <span class="text-muted text-nowrap">Tree menu</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">{{ $module->is_enabled ? 'enabled' : 'disabled' }}</td>
                                <td class="js-sort-order text-nowrap">{{ $module->sort_order }}</td>
                                <td class="text-right">
                                    <div class="modules-table__actions-wrap">
                                    <form method="POST" action="{{ route('backend_core_modules_toggle', $module) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-outline-secondary btn-sm" title="{{ $module->is_enabled ? 'Đang bật' : 'Đang tắt' }}">
                                            <i class="fas fa-power-off {{ $module->is_enabled ? 'text-success' : 'text-danger' }}"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('backend_core_modules_edit', $module) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                @empty
                    <tbody>
                        <tr><td colspan="9" class="text-muted">Chưa có module.</td></tr>
                    </tbody>
                @endforelse
            </table>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .modules-table {
        min-width: 1180px;
        table-layout: fixed;
    }

    .modules-table th,
    .modules-table td {
        vertical-align: middle;
    }

    .modules-table__handle {
        width: 54px;
    }

    .modules-table__tenant {
        width: 220px;
    }

    .modules-table__group {
        width: 110px;
    }

    .modules-table__name {
        width: 260px;
    }

    .modules-table__slug {
        width: 170px;
    }

    .modules-table__route {
        width: 260px;
    }

    .modules-table__status {
        width: 100px;
    }

    .modules-table__sort {
        width: 78px;
    }

    .modules-table__actions {
        width: 88px;
    }

    .modules-table__text {
        overflow-wrap: anywhere;
    }

    .modules-table__module-name {
        align-items: center;
        display: inline-flex;
        max-width: 100%;
        min-width: 0;
    }

    .modules-table__module-name span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .modules-table__code {
        display: inline-block;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
        white-space: nowrap;
    }

    .modules-table__actions-wrap {
        display: inline-flex;
        gap: 6px;
        white-space: nowrap;
    }

    .js-sort-handle {
        cursor: move;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
    }

    .module-sort-placeholder {
        height: 54px;
        background: #f4f6f9;
        outline: 2px dashed #adb5bd;
    }

    .ui-sortable-helper {
        display: table;
        background: #fff;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, .15);
    }

    .js-module-sortable.is-saving {
        opacity: .65;
    }
</style>
@endsection

@section('script')
<script src="{{ asset('backend_assets/js/jquery-ui.min.js') }}"></script>
<script>
    $(function () {
        $('.js-module-sortable').sortable({
            axis: 'y',
            handle: '.js-sort-handle',
            items: '> tr',
            placeholder: 'module-sort-placeholder',
            start: function (event, ui) {
                ui.placeholder.html('<td colspan="9"></td>');
            },
            helper: function (event, ui) {
                ui.children().each(function () {
                    $(this).width($(this).width());
                });

                return ui;
            },
            update: function () {
                const $tbody = $(this);
                const moduleIds = $tbody.children('tr').map(function () {
                    return $(this).data('module-id');
                }).get();

                $tbody.addClass('is-saving');

                $.ajax({
                    url: "{{ route('backend_core_modules_reorder') }}",
                    method: 'POST',
                    data: {
                        tenant_id: $tbody.data('tenant-id'),
                        module_ids: moduleIds,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        $tbody.children('tr').each(function (index) {
                            $(this).find('.js-sort-order').text((index + 1) * 10);
                        });

                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message || 'Đã lưu thứ tự module.');
                        }
                    },
                    error: function (xhr) {
                        $tbody.sortable('cancel');

                        if (typeof toastr !== 'undefined') {
                            const message = xhr.responseJSON && xhr.responseJSON.message
                                ? xhr.responseJSON.message
                                : 'Không thể lưu thứ tự module.';
                            toastr.error(message);
                        }
                    },
                    complete: function () {
                        $tbody.removeClass('is-saving');
                    }
                });
            }
        });
    });
</script>
@endsection
