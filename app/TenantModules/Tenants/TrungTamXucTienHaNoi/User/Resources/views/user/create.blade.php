@extends('backend.index')
@use('\Illuminate\Support\HtmlString')

@section('title')
    {{ $user->exists ? 'Sửa user' : 'Thêm mới user' }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tenant.trung_tam_xuc_tien_ha_noi.user.users.index') }}">User</a></li>
    <li class="breadcrumb-item active">{{ $user->exists ? 'Sửa user' : 'Thêm mới user' }}</li>
@endsection

@section('content')

    <script src="{{ asset('js/ckfinder/ckfinder.js') }}"></script>
    <script src="{{ asset('js/ckeditor/ckeditor.js') }}"></script>
    <script src="{{ asset('backend_assets/js/globals.js') }}"></script>
    <script>CKFinder.config({connectorPath: '/ckfinder/connector'});</script>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="float-right mb-3">
                        @can('user/' . ($user->exists ? 'edit' : 'add'))
                            <x-forms.button-save/>
                        @endcan
                        @if($user->exists)
                            @can('user/add')
                                <x-forms.button-url title="Thêm mới" class="btn-info" icon="fa fa-plus"
                                                    url="{{ route('tenant.trung_tam_xuc_tien_ha_noi.user.users.create') }}"/>
                            @endcan
                            @can('user/delete')
                                <x-forms.button-url title="Xóa" class="btn-danger" icon="fa fa-trash"
                                                    url="{{ route('tenant.trung_tam_xuc_tien_ha_noi.user.users.delete', $user->id) }}"/>
                            @endcan
                            @if(
                                (auth('web')->user()->is_super_admin || auth('web')->user()->is_approve) &&
                                $user->status_approve === 'pending'
                            )
                                <!-- Button trigger modal -->
                                <button type="button" class="btn btn-sm fw-bold btn-success" data-toggle="modal" data-target="#approveModal-{{ $user->id }}">
                                    <i class="fa fa-check" aria-hidden="true"></i> Duyệt
                                </button>

                                <!-- Modal -->
                                <div class="modal fade" id="approveModal-{{ $user->id }}" tabindex="-1" aria-labelledby="approveModalLabel-{{ $user->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title" id="approveModalLabel-{{ $user->id }}">
                                                    Xác nhận duyệt user
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Bạn có chắc chắn muốn duyệt user: <strong>{{ $user->name }}</strong>?
                                                <p class="text-muted mt-2">
                                                    @if(auth('web')->user()->is_super_admin)
                                                        Sau khi duyệt, user sẽ được cập nhật trạng thái và hiển thị cho người dùng.
                                                    @elseif(auth('web')->user()->is_approve)
                                                        Sau khi duyệt thành công, user sẽ chờ duyệt lần cuối bởi admin.
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="modal-footer">
                                                <form action="{{ route('tenant.trung_tam_xuc_tien_ha_noi.user.users.reject', $user->id) }}" method="post" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger fw-bold">Yêu cầu chỉnh sửa</button>
                                                </form>
                                                <form action="{{ route('tenant.trung_tam_xuc_tien_ha_noi.user.users.approve', $user->id) }}" method="post" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success fw-bold">Duyệt user</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            <div class="card card-primary">
                <form action="{{ route('tenant.trung_tam_xuc_tien_ha_noi.user.users.save', $user) }}" method="post"
                      enctype="multipart/form-data"
                      class="form-horizontal" id="formDataGrid">
                    @csrf
                    <div class="card-body">

                        <x-forms.input name="name" value="{{ old('name') ?: $user->name }}" label="Họ và tên"
                                       :required="true"
                                       :messages="$errors->get('name')"/>
                        <x-forms.input name="email" value="{{ old('email') ?: $user->email }}" label="Email"
                                       :messages="$errors->get('email')"/>
                        <x-forms.input name="phone" value="{{ old('phone') ?: $user->phone }}" label="Số điện thoại"
                                       :messages="$errors->get('phone')"/>

                        <x-forms.select name="group_id" label="Group Admin" :options="new HtmlString($option_groups)"
                                        :messages="$errors->get('group_id')"
                                        help="Select group admin to assign permission to this account"/>

                        <x-forms.upload name="avatar" value="{{ old('avatar') ?: $user->avatar }}" label="Avatar"
                                        type="image" :messages="$errors->get('avatar')"/>

                        <x-forms.switch name="status" value="{{ $user->status ?? 1 }}" label="Trạng thái"
                                        :messages="$errors->get('status')"/>
                        <x-forms.switch name="is_super_admin" value="{{ $user->is_super_admin ?? 1 }}" label="Is Super Admin"
                                        :messages="$errors->get('is_super_admin')"/>
                        <x-forms.switch name="is_approve" value="{{ $user->is_approve ?? 1 }}" label="Is Approve"
                                        :messages="$errors->get('is_approve')"/>

                        <x-forms.input name="password" value="" label="Password"
                                       :messages="$errors->get('password')" type="password"
                                       help="If you do not change your password, Please leave this field blank"/>

                    </div>
                </form>
            </div>
        </div>
    </section>

@endsection
