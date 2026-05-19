@extends('backend.index')

@section('title')
{{ $guest->exists ? 'Sửa thông tin người dùng' : 'Thêm mới người dùng' }}
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend_guest') }}">người dùng</a></li>
<li class="breadcrumb-item active">{{ $guest->exists ? 'Sửa thông tin' : 'Thêm mới' }}</li>
@endsection

@section('content')

<script src="{{ asset('js/ckfinder/ckfinder.js') }}"></script>
<script src="{{ asset('js/ckeditor/ckeditor.js') }}"></script>
<script src="{{ asset('backend_assets/js/globals.js') }}"></script>
<script>
    CKFinder.config({
        connectorPath: '/ckfinder/connector'
    });
</script>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user-edit mr-1"></i>
                            {{ $guest->exists ? 'Thông tin người dùng: ' . $guest->name : 'Nhập thông tin người dùng mới' }}
                        </h3>
                        <div class="card-tools">
                            @can('guest')
                            <x-forms.button-save />
                            @endcan
                            @if($guest->exists)
                            @can('guest')
                            <x-forms.button-url title="Thêm mới" class="btn-info" icon="fa fa-plus"
                                url="{{ route('backend_guest_create') }}" />
                            @endcan
                            @can('guest')
                            <x-forms.button-url title="Xóa" class="btn-danger" icon="fa fa-trash"
                                url="{{ route('backend_guest_delete', $guest->id) }}" />
                            @endcan
                            @endif
                        </div>
                    </div>
                    <form action="{{ route('backend_guest_save', $guest) }}" method="post"
                        enctype="multipart/form-data"
                        class="form-horizontal" id="formDataGrid">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <!-- Cột trái: Thông tin cá nhân & Liên hệ -->
                                <div class="col-md-7">
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <h5 class="text-primary border-bottom pb-2">
                                                <i class="fas fa-id-card mr-1"></i> Thông tin cơ bản
                                            </h5>
                                        </div>
                                    </div>

                                    <x-forms.input name="name" value="{{ old('name') ?: $guest->name }}"
                                        label="<i class='fas fa-user mr-1'></i> Tên người dùng" required="true" :messages="$errors->get('name')" />

                                    <x-forms.input name="email" value="{{ old('email') ?: $guest->email }}"
                                        label="<i class='fas fa-envelope mr-1'></i> Email" type="email" required="true" :messages="$errors->get('email')" />

                                    <x-forms.input name="phone" value="{{ old('phone') ?: $guest->phone }}"
                                        label="<i class='fas fa-phone mr-1'></i> Số điện thoại" :messages="$errors->get('phone')" />

                                    <x-forms.input name="identification_number" value="{{ old('identification_number') ?: $guest->identification_number }}"
                                        label="<i class='fas fa-id-badge mr-1'></i> Số CMND/CCCD" :messages="$errors->get('identification_number')" />

                                    <div class="form-group row">
                                        <label class="col-sm-3 col-form-label"><i class="fas fa-globe mr-1"></i> Quốc gia</label>
                                        <div class="col-sm-9">
                                            <select name="nation_id" class="form-control select2 shadow-sm">
                                                <option value="">Chọn quốc gia</option>
                                                @foreach($nations as $nation)
                                                <option value="{{ $nation->id }}" {{ (old('nation_id') ?: $guest->nation_id) == $nation->id ? 'selected' : '' }}>
                                                    {{ $nation->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                            @if($errors->has('nation_id'))
                                            <span class="text-danger small">{{ $errors->first('nation_id') }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <x-forms.input name="password" value=""
                                        label="<i class='fas fa-lock mr-1'></i> {{ $guest->exists ? 'Đổi mật khẩu' : 'Mật khẩu' }}"
                                        type="password" :required="!$guest->exists" :messages="$errors->get('password')"
                                        help="{{ $guest->exists ? 'Để trống nếu không muốn đổi mật khẩu' : '' }}" />
                                </div>

                                <!-- Cột phải: Ảnh đại diện & Địa chỉ -->
                                <div class="col-md-5">
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <h5 class="text-primary border-bottom pb-2">
                                                <i class="fas fa-image mr-1"></i> Ảnh & Địa chỉ
                                            </h5>
                                        </div>
                                    </div>

                                    <div class="card bg-light mb-4 shadow-sm border-0">
                                        <div class="card-body p-3">
                                            <x-forms.upload name="avatar" value="{{ old('avatar') ?: $guest->avatar }}"
                                                label="<i class='fas fa-camera mr-1'></i> Ảnh đại diện"
                                                type="image" :messages="$errors->get('avatar')"
                                                class_label="col-sm-4" class_input="col-sm-8" />

                                            <p class="text-muted small mt-2 ml-md-auto col-md-8 px-0">
                                                <i class="fas fa-info-circle mr-1"></i> Định dạng: JPG, PNG, GIF. Kích thước tối đa 2MB.
                                            </p>
                                        </div>
                                    </div>

                                    <x-forms.textarea name="address" value="{{ old('address') ?: $guest->address }}"
                                        label="<i class='fas fa-map-marker-alt mr-1'></i> Địa chỉ" :messages="$errors->get('address')"
                                        rows="4" class_label="col-sm-3" class_input="col-sm-9" />
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-right bg-white p-3">
                            <x-forms.button-save title="Lưu thông tin" />
                            <a href="{{ route('backend_guest') }}" class="btn btn-default btn-sm ml-2">Huỷ bỏ</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('css')
<style>
    .card-outline.card-primary {
        border-top: 4px solid #007bff;
    }

    .form-group label {
        font-weight: 600;
        color: #495057;
    }

    .text-primary {
        color: #007bff !important;
    }

    .preview_image img {
        border: 4px solid #fff;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border-radius: 8px;
        transition: transform .2s;
    }

    .preview_image img:hover {
        transform: scale(1.05);
    }

    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .input-group-btn .btn {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    #formDataGrid .card-body {
        padding: 1.5rem;
    }

    .select2-container--default .select2-selection--single {
        border: 1px solid #ced4da;
        height: calc(2.25rem + 2px);
    }
</style>
@endsection