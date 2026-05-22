@extends('backend.index')

@section('title', 'Tenants')

@section('breadcrumb')
    <li class="breadcrumb-item active">Tenants</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('backend_core_tenants_create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i>Thêm tenant
        </a>
    </div>
    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Tên</th>
                        <th>Slug</th>
                        <th>Database</th>
                        <th>Status</th>
                        <th>Modules</th>
                        <th>Users</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenants as $tenant)
                        <tr>
                            <td>{{ $tenant->name }}</td>
                            <td><code>{{ $tenant->slug }}</code></td>
                            <td>{{ $tenant->database?->database_name ?? '-' }}</td>
                            <td>{{ $tenant->status }}</td>
                            <td>{{ $tenant->modules_count }}</td>
                            <td>{{ $tenant->users_count }}</td>
                            <td class="text-right">
                                <a href="{{ route('backend_core_tenants_edit', $tenant) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted">Chưa có tenant.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $tenants->links() }}
</div>
@endsection
