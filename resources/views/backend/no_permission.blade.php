@extends('backend.index')

@section('title', 'Không có quyền truy cập')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <i class="fas fa-lock fa-5x text-warning mb-4"></i>
                    <h3 class="mb-3">{{ $message }}</h3>
                    <p class="text-muted">Vui lòng liên hệ quản trị viên để được cấp quyền truy cập.</p>
                    <a href="{{ route('logout') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-sign-out-alt"></i> Đăng xuất
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection