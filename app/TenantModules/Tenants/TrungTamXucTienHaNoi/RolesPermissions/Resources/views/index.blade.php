@extends('backend.index')

@section('title', 'Tenant Roles')

@section('breadcrumb')
    <li class="breadcrumb-item active">Tenant Roles</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('backend_core_roles_create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i>Thêm role
        </a>
    </div>
    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Tên</th>
                        <th>Slug</th>
                        <th>Permissions</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td>{{ $role->name }}</td>
                            <td><code>{{ $role->slug }}</code></td>
                            <td>{{ $role->permissions->count() }}</td>
                            <td class="text-right">
                                @if($role->slug !== 'organizer')
                                    <a href="{{ route('backend_core_roles_edit', $role) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @else
                                    <span class="badge badge-secondary">system</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">Chưa có role.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $roles->links() }}
</div>
@endsection
