@extends('backend.index')

@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        @foreach([
            ['label' => 'Tenants', 'value' => $tenantCount, 'icon' => 'fas fa-building', 'class' => 'info'],
            ['label' => 'Active tenants', 'value' => $activeTenantCount, 'icon' => 'fas fa-check-circle', 'class' => 'success'],
            ['label' => 'Modules enabled', 'value' => $enabledModuleCount . '/' . $moduleCount, 'icon' => 'fas fa-cubes', 'class' => 'primary'],
            ['label' => 'Users', 'value' => $userCount, 'icon' => 'fas fa-users', 'class' => 'secondary'],
        ] as $card)
            <div class="col-lg-3 col-6">
                <div class="small-box bg-{{ $card['class'] }}">
                    <div class="inner">
                        <h3>{{ $card['value'] }}</h3>
                        <p>{{ $card['label'] }}</p>
                    </div>
                    <div class="icon"><i class="{{ $card['icon'] }}"></i></div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tenant mới</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Tên</th>
                                <th>Slug</th>
                                <th>Status</th>
                                <th>Modules</th>
                                <th>Users</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTenants as $tenant)
                                <tr>
                                    <td>{{ $tenant->name }}</td>
                                    <td><code>{{ $tenant->slug }}</code></td>
                                    <td>{{ $tenant->status }}</td>
                                    <td>{{ $tenant->modules_count }}</td>
                                    <td>{{ $tenant->users_count }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted">Chưa có tenant.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Permission Registry</h3>
                </div>
                <div class="card-body">
                    <p class="mb-2">Roles: <strong>{{ $roleCount }}</strong></p>
                    <p class="mb-0">Permissions: <strong>{{ $permissionCount }}</strong></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
