@extends('backend.index')

@section('title', $config['name'])

@section('breadcrumb')
    <li class="breadcrumb-item active">{{ $config['name'] }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0 float-none">{{ $config['name'] }}</h3>
            <div class="text-muted small mt-1">Các bảng dữ liệu thuộc module tenant này.</div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Bảng</th>
                        <th>Trạng thái</th>
                        <th>Số dòng</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tables as $table)
                        <tr>
                            <td><code>{{ $table['name'] }}</code></td>
                            <td>
                                @if($table['exists'])
                                    <span class="badge badge-success">exists</span>
                                @else
                                    <span class="badge badge-secondary">missing</span>
                                @endif
                            </td>
                            <td>{{ $table['count'] ?? '-' }}</td>
                            <td class="text-right">
                                @if($table['exists'])
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route($config['table_route'], $table['name']) }}">
                                        Xem dữ liệu
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-muted">Module này chưa khai báo bảng dữ liệu.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
