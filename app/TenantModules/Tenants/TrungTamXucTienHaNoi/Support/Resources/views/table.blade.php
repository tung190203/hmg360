@extends('backend.index')

@section('title', $config['name'] . ' / ' . $table)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route($config['route']) }}">{{ $config['name'] }}</a></li>
    <li class="breadcrumb-item active">{{ $table }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0 float-none"><code>{{ $table }}</code></h3>
            <div class="text-muted small mt-1">Hiển thị tối đa 50 dòng mới nhất. CRUD chi tiết sẽ được triển khai theo từng module.</div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        @foreach($columns as $column)
                            <th>{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            @foreach($columns as $column)
                                @php($value = data_get($row, $column))
                                <td style="max-width: 260px; overflow-wrap: anywhere;">
                                    {{ is_scalar($value) || $value === null ? Str::limit((string) $value, 120) : json_encode($value) }}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) }}" class="text-muted">Chưa có dữ liệu.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
