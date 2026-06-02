@extends('backend.index')
@use('\Illuminate\Support\HtmlString')

@section('title')
    {{ $menu->exists ? 'Sửa menu' : 'Thêm mới menu' }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tenant.trung_tam_xuc_tien_ha_noi.menu.index') }}">Menu</a></li>
    <li class="breadcrumb-item active">{{ $menu->exists ? 'Sửa menu' : 'Thêm mới menu' }}</li>
@endsection

@section('content')

    <script src="{{ asset('js/ckfinder/ckfinder.js') }}"></script>
    <script src="{{ asset('js/ckeditor/ckeditor.js') }}"></script>
    <script src="{{ asset('backend_assets/js/globals.js') }}"></script>
    <script>CKFinder.config({connectorPath: '/ckfinder/connector'});</script>

    @php
        $locales = config('app.locales', ['vi' => 'Tiếng Việt', 'en' => 'Tiếng Anh']);
    @endphp

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="float-right mb-3">
                        @can('menu/' . ($menu->exists ? 'edit' : 'add'))
                            <x-forms.button-save/>
                        @endcan
                        @if($menu->exists)
                            @can('menu/add')
                                <x-forms.button-url title="Thêm mới" class="btn-info" icon="fa fa-plus"
                                                    url="{{ route('tenant.trung_tam_xuc_tien_ha_noi.menu.create') }}"/>
                            @endcan
                            @can('menu/delete')
                                <x-forms.button-url title="Xóa" class="btn-danger" icon="fa fa-trash"
                                                    url="{{ route('tenant.trung_tam_xuc_tien_ha_noi.menu.delete', $menu->id) }}"/>
                            @endcan
                            @if(
                                (auth('web')->user()->is_super_admin || auth('web')->user()->is_approve) &&
                                $menu->status_approve === 'pending'
                            )
                                <button type="button" class="btn btn-sm fw-bold btn-success" data-toggle="modal" data-target="#approveModal-{{ $menu->id }}">
                                    <i class="fa fa-check" aria-hidden="true"></i> Duyệt
                                </button>

                                <div class="modal fade" id="approveModal-{{ $menu->id }}" tabindex="-1" aria-labelledby="approveModalLabel-{{ $menu->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title" id="approveModalLabel-{{ $menu->id }}">
                                                    Xác nhận duyệt menu
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Bạn có chắc chắn muốn duyệt menu: <strong>{{ $menu->name }}</strong>?
                                                <p class="text-muted mt-2">
                                                    @if(auth('web')->user()->is_super_admin)
                                                        Sau khi duyệt, menu sẽ được cập nhật trạng thái và hiển thị cho người dùng.
                                                    @elseif(auth('web')->user()->is_approve)
                                                        Sau khi duyệt thành công, menu sẽ chờ duyệt lần cuối bởi admin.
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="modal-footer">
                                                <form action="{{ route('tenant.trung_tam_xuc_tien_ha_noi.menu.reject', $menu->id) }}" method="post" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger fw-bold">Yêu cầu chỉnh sửa</button>
                                                </form>
                                                <form action="{{ route('tenant.trung_tam_xuc_tien_ha_noi.menu.approve', $menu->id) }}" method="post" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success fw-bold">Duyệt menu</button>
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
                <form action="{{ route('tenant.trung_tam_xuc_tien_ha_noi.menu.save', $menu) }}" method="post"
                      enctype="multipart/form-data"
                      class="form-horizontal" id="formDataGrid">
                    @csrf
                    <div class="card-body">
                        
                        {{-- Tabs ngôn ngữ --}}
                        <div class="form-group">
                            <h5 class="mb-3">Tên Menu đa ngôn ngữ</h5>
                            
                            <ul class="nav nav-tabs" role="tablist">
                                @foreach($locales as $locale => $label)
                                    <li class="nav-item">
                                        <a class="nav-link {{ $loop->first ? 'active' : '' }}" 
                                           data-toggle="tab" 
                                           href="#lang-{{ $locale }}"
                                           role="tab">
                                            <i class="fas fa-language mr-1"></i> {{ $label }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="tab-content border border-top-0 p-3">
                                @foreach($locales as $locale => $label)
                                    <div id="lang-{{ $locale }}" 
                                         class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                         role="tabpanel">
                                        
                                        {{-- Trường Name đa ngôn ngữ --}}
                                        <x-forms.input 
                                            name="name[{{ $locale }}]" 
                                            value="{{ old('name.'.$locale) ?: $menu->getTranslation('name', $locale, false) }}" 
                                            label="Tên Menu ({{ $label }})"
                                            :required="$loop->first" {{-- Bắt buộc cho ngôn ngữ đầu tiên --}}
                                            :messages="$errors->get('name.'.$locale)" />

                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Các trường đơn ngữ --}}
                        <x-forms.select name="parent_id" label="Menu cha" :options="new HtmlString($option_menu)"
                                        :messages="$errors->get('parent_id')"/>
                        <x-forms.input name="priority" value="{{ (old('priority') ?: $menu->priority) ?: 9999 }}"
                                       label="Sắp xếp" type="number" :messages="$errors->get('priority')"/>
                        <x-forms.switch name="status" value="{{ $menu->status ?? 1 }}" label="Hiển thị"
                                        :messages="$errors->get('status')"/>
                    </div>
                </form>
            </div>
        </div>
    </section>

@endsection