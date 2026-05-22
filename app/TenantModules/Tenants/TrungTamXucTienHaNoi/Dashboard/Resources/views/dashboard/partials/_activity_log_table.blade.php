<div class="card-body table-responsive p-0">
    <table class="table table-hover text-nowrap">
        <thead>
            <tr>
                <th>Thời gian</th>
                <th>Người thực hiện</th>
                <th>Hành động</th>
                <th>Đối tượng</th>
                <th>Chi tiết</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $activity)
            <tr>
                <td>{{ $activity->created_at->format('H:i d/m/Y') }}</td>
                <td>
                    @if($activity->causer)
                    {{ $activity->causer->name }}
                    @else
                    System
                    @endif
                </td>
                <td>
                    @php
                    $badgeClass = 'secondary';
                    $description = $activity->description;
                    if($activity->event == 'created' || $description == 'created') $badgeClass = 'success';
                    elseif($activity->event == 'updated' || $description == 'updated') $badgeClass = 'info';
                    elseif($activity->event == 'deleted' || $description == 'deleted') $badgeClass = 'danger';
                    elseif($description == 'logged in') $badgeClass = 'primary';
                    elseif($description == 'logged out') $badgeClass = 'warning';
                    @endphp
                    <span class="badge badge-{{ $badgeClass }}">{{ ucfirst($description) }}</span>
                </td>
                <td>
                    @if($activity->subject_type)
                    {{ class_basename($activity->subject_type) }} (ID: {{ $activity->subject_id }})
                    @else
                    -
                    @endif
                </td>
                <td>
                    @if($activity->properties && count($activity->properties) > 0)
                    <button type="button" class="btn btn-xs btn-outline-info" data-toggle="modal" data-target="#activity-{{ $activity->id }}">
                        <i class="fas fa-eye"></i> Xem
                    </button>
                    <div class="modal fade" id="activity-{{ $activity->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Chi tiết thay đổi</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body" style="white-space: normal !important;">
                                    @php
                                    $properties = $activity->properties;
                                    $attributes = $properties['attributes'] ?? null;
                                    $old = $properties['old'] ?? null;

                                    $translations = [
                                    'name' => 'Tên',
                                    'email' => 'Email',
                                    'phone' => 'Số điện thoại',
                                    'title' => 'Tiêu đề',
                                    'content' => 'Nội dung',
                                    'status' => 'Trạng thái',
                                    'password' => 'Mật khẩu',
                                    'avatar' => 'Ảnh đại diện',
                                    'ip' => 'Địa chỉ IP',
                                    'user_agent' => 'Trình duyệt',
                                    'is_approve' => 'Duyệt',
                                    'status_approve' => 'Trạng thái duyệt',
                                    'order_number' => 'Thứ tự',
                                    'slug' => 'Đường dẫn',
                                    ];
                                    @endphp

                                    @if($attributes)
                                    <table class="table table-sm table-bordered" style="white-space: normal; table-layout: fixed; width: 100%;">
                                        <thead class="bg-light">
                                            <tr>
                                                <th style="width: 30%;">Trường</th>
                                                @if($old) <th style="width: 35%;">Giá trị cũ</th> @endif
                                                <th style="width: {{ $old ? '35%' : '70%' }};">Giá trị mới</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($attributes as $key => $value)
                                            @if($key == 'updated_at' || $key == 'created_at') @continue @endif
                                            <tr>
                                                <td class="font-weight-bold" style="word-break: break-word;">{{ $translations[$key] ?? $key }}</td>
                                                @if($old)
                                                <td class="text-muted" style="word-break: break-word;">{{ is_array($old[$key]) ? json_encode($old[$key]) : $old[$key] }}</td>
                                                @endif
                                                <td class="text-success" style="word-break: break-word;">{{ is_array($value) ? json_encode($value) : $value }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @endif

                                    @php
                                    $otherProps = collect($properties)->except(['attributes', 'old']);
                                    @endphp

                                    @if($otherProps->isNotEmpty())
                                    <div class="mt-3">
                                        <h6 class="font-weight-bold">Thông tin bổ sung:</h6>
                                        <ul class="list-unstyled">
                                            @foreach($otherProps as $key => $value)
                                            <li style="word-break: break-word; overflow-wrap: anywhere;"><strong>{{ $translations[$key] ?? $key }}:</strong> {{ $value }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif

                                    @if(!$attributes && $otherProps->isEmpty())
                                    <p class="text-center text-muted">Không có chi tiết bổ sung.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    -
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Không có dữ liệu</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="card-footer clearfix">
    <div class="float-right">
        {{ $activities->links() }}
    </div>
</div>