@extends('backend.index')
@use('\Illuminate\Support\HtmlString')

@section('title')
    {{ $popup->exists ? 'Sửa popup' : 'Thêm mới popup' }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('backend_popup') }}">Popup</a></li>
    <li class="breadcrumb-item active">{{ $popup->exists ? 'Sửa popup' : 'Thêm mới popup' }}</li>
@endsection

@section('content')

    <script src="{{ asset('js/ckfinder/ckfinder.js') }}"></script>
    <script src="{{ asset('js/ckeditor/ckeditor.js') }}"></script>
    <script src="{{ asset('backend_assets/js/globals.js') }}"></script>
    <script>CKFinder.config({ connectorPath: '/ckfinder/connector' });</script>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="float-right mb-3">
                        @can('popup/' . ($popup->exists ? 'edit' : 'add'))
                            <x-forms.button-save />
                        @endcan
                        @if($popup->exists)
                            @can('popup/add')
                                <x-forms.button-url title="Thêm mới" class="btn-info" icon="fa fa-plus"
                                    url="{{ route('backend_popup_create') }}" />
                            @endcan
                            @can('popup/delete')
                                <x-forms.button-url title="Xóa" class="btn-danger" icon="fa fa-trash"
                                    url="{{ route('backend_popup_delete', $popup->id) }}" />
                            @endcan
                            @if(
                                (auth('web')->user()->is_super_admin || auth('web')->user()->is_approve) &&
                                $popup->status_approve === 'pending'
                            )
                                <!-- Button trigger modal -->
                                <button type="button" class="btn btn-sm fw-bold btn-success" data-toggle="modal" data-target="#approveModal-{{ $popup->id }}">
                                    <i class="fa fa-check" aria-hidden="true"></i> Duyệt
                                </button>

                                <!-- Modal -->
                                <div class="modal fade" id="approveModal-{{ $popup->id }}" tabindex="-1" aria-labelledby="approveModalLabel-{{ $popup->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title" id="approveModalLabel-{{ $popup->id }}">
                                                    Xác nhận duyệt popup
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Bạn có chắc chắn muốn duyệt popup
                                                <p class="text-muted mt-2">
                                                    @if(auth('web')->user()->is_super_admin)
                                                        Sau khi duyệt, popup sẽ được cập nhật trạng thái và hiển thị cho người dùng.
                                                    @elseif(auth('web')->user()->is_approve)
                                                        Sau khi duyệt thành công, popup sẽ chờ duyệt lần cuối bởi admin.
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="modal-footer">
                                                <form action="{{ route('backend_popup_reject', $popup->id) }}" method="post" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger fw-bold">Yêu cầu chỉnh sửa</button>
                                                </form>
                                                <form action="{{ route('backend_popup_approve', $popup->id) }}" method="post" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success fw-bold">Duyệt popup</button>
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
                <form action="{{ route('backend_popup_save', $popup) }}" method="post" enctype="multipart/form-data"
                    class="form-horizontal" id="formDataGrid">
                    @csrf
                    <div class="card-body">
                        <x-forms.upload name="image" value="{{ old('image') ?: $popup->image }}" label="Image" type="image"
                            :messages="$errors->get('image')" />
                        <x-forms.input name="link" value="{{ old('link') ?: $popup->link }}" label="Link điều hướng"
                            :messages="$errors->get('link')" />
                    </div>
                </form>
            </div>
        </div>
    </section>

@endsection