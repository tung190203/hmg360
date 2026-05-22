@extends('backend.index')

@section('title', $module->exists ? 'Sửa module' : 'Thêm module')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('backend_core_modules') }}">Modules</a></li>
    <li class="breadcrumb-item active">{{ $module->exists ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <form method="POST" action="{{ $module->exists ? route('backend_core_modules_update', $module) : route('backend_core_modules_store') }}">
        @csrf
        @if($module->exists)
            @method('PUT')
        @endif
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-4">
                        <label>Tenant</label>
                        <select name="tenant_id" class="form-control" required>
                            @foreach($tenants as $tenant)
                                <option value="{{ $tenant->id }}" @selected((int) old('tenant_id', $module->tenant_id) === $tenant->id)>{{ $tenant->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Tên</label>
                        <input name="name" class="form-control" value="{{ old('name', $module->name) }}" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Slug</label>
                        <input name="slug" class="form-control" value="{{ old('slug', $module->slug) }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Nhóm sidebar</label>
                        @php
                            $menuSection = old('menu_section', data_get($module->config, 'menu.section', data_get($module->manifest(), 'menu.section', 'content')));
                        @endphp
                        <select name="menu_section" class="form-control">
                            <option value="content" @selected($menuSection === 'content')>CONTENT</option>
                            <option value="systems" @selected($menuSection === 'systems')>SYSTEMS</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Path</label>
                        <input name="path" class="form-control" value="{{ old('path', $module->path) }}" placeholder="TenantA/VrTour" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Sort</label>
                        <input name="sort_order" type="number" class="form-control" value="{{ old('sort_order', $module->sort_order ?? 0) }}">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Status</label>
                        <div class="form-check mt-2">
                            <input type="hidden" name="is_enabled" value="0">
                            <input type="checkbox" name="is_enabled" value="1" class="form-check-input" id="is_enabled" @checked(old('is_enabled', $module->is_enabled))>
                            <label for="is_enabled" class="form-check-label">Enabled</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-right">
                <button class="btn btn-primary">Lưu</button>
            </div>
        </div>
    </form>
</div>
@endsection
