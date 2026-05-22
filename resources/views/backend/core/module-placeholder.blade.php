@extends('backend.index')

@section('title', $title)

@section('breadcrumb')
    <li class="breadcrumb-item active">{{ $title }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0 float-none">{{ $title }}</h3>
            <div class="text-muted small mt-1">Module legacy <code>{{ $slug }}</code> đã có migration tenant riêng. CRUD sẽ được triển khai trong module này.</div>
        </div>
        <div class="card-body">
            <p class="mb-0 text-muted">
                Dữ liệu module này chạy trên tenant database qua middleware <code>tenant.db</code>.
            </p>
        </div>
    </div>
</div>
@endsection
