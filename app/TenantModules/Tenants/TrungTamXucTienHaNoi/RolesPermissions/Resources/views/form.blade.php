@extends('backend.index')

@section('title', $role->exists ? 'Sửa tenant role' : 'Thêm tenant role')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('backend_core_roles') }}">Tenant Roles</a></li>
    <li class="breadcrumb-item active">{{ $role->exists ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <form method="POST" action="{{ $role->exists ? route('backend_core_roles_update', $role) : route('backend_core_roles_store') }}">
        @csrf
        @if($role->exists)
            @method('PUT')
        @endif
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Tên</label>
                        <input name="name" class="form-control" value="{{ old('name', $role->name) }}" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Slug</label>
                        <input name="slug" class="form-control" value="{{ old('slug', $role->slug) }}">
                    </div>
                </div>

                <h5>Permissions của tenant {{ $tenant->name }}</h5>
                @forelse($permissions as $module => $items)
                    <div class="border rounded p-3 mb-3">
                        <strong>{{ $module }}</strong>
                        <div class="row mt-2">
                            @foreach($items as $permission)
                                <div class="col-md-3">
                                    <label class="font-weight-normal">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                            @checked(in_array($permission->id, old('permissions', $role->permissions?->pluck('id')->all() ?? []), true))>
                                        {{ $permission->permission }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-muted">Tenant chưa có module nào được bật, nên chưa có permission để gán.</p>
                @endforelse
            </div>
            <div class="card-footer text-right">
                <button class="btn btn-primary">Lưu</button>
            </div>
        </div>
    </form>
</div>
@endsection
