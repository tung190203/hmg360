@extends('backend.index')

@section('title')
    Quản lý nội dung
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active">Nội dung</li>
@endsection

@section('content')
    <hr class="mt-0">
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-6 text-center">
                    <form action="" method="GET" class="form-filter-top-index">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <x-forms.select2 
                                        name="slt_vrtour" 
                                        id="slt_vrtour" 
                                        :options="$vrtour" 
                                        placeholder="-- Chọn dự án --"
                                        :selected="old('slt_vrtour')" 
                                    />
                                </div>
                            </div>
                            <div class="col-md-2 text-left">
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary btn-sm" id="btn_search_tour" style="height: 38px">Lấy dữ liệu</button>
                                </div>
                            </div>
                            <div class="col-md-3 text-left">
                                <div class="form-group">
                                    <button type="button" class="btn btn-danger btn-sm" id="btn_reset" style="display:none; height: 38px;">Reset</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-grid-admin text-center">
                                <thead>
                                    <tr>
                                        <th style="width:5%" class="grid_header">STT</th>
                                        <th style="width:30%" class="grid_header">Tiêu đề</th>
                                        <th style="width:55%" class="grid_header">Nội dung</th>
                                        <th style="width:10%" class="grid_header1"></th>
                                    </tr>
                                </thead>
                                <tbody id="dataGrid">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
    <script>
        function getParam(name, url = null) {
            let params;
            if (url) {
                // Nếu truyền url cụ thể
                params = new URL(url).searchParams;
            } else {
                // Nếu không truyền thì lấy từ window.location
                params = new URLSearchParams(window.location.search);
            }
            return params.get(name);
        }
        if (getParam("vrtour") != null) {
            $('#slt_vrtour').val(getParam("vrtour")).change();
            renderTable(false);
        }
        $('#btn_search_tour').on('click', function() {
            renderTable();
        });
        $(document).on('click', '#btn_reset', function(){
            if (confirm("Bạn có chắc chắn muốn reset toàn bộ nội dung để cập nhật mới không?")) {
                renderTable(true, true);
            }
        });

        const contentGetDataUrl = @json(route('tenant.trung_tam_xuc_tien_ha_noi.vr_tour.content.getdata', ['vrtour_id' => '__VRTOUR__']));

        function buildContentGetDataUrl(vrtour, reset) {
            const url = new URL(contentGetDataUrl.replace('__VRTOUR__', encodeURIComponent(vrtour)), window.location.origin);
            url.searchParams.set('reset', reset);
            return url.toString();
        }

        function renderTable(notify = true,reset = false)
        {
            
            var vrtour  = $('#slt_vrtour').val();
            if (vrtour != 'all') {
                $.ajax({
                    url: buildContentGetDataUrl(vrtour, reset),
                    type: "GET",
                    success: function(response) {
                        $('#dataGrid').html(response.data);
                        if (notify == true) {
                            toastr["success"]('Lấy dữ liệu thành công','Success')
                        }
                        $('#btn_reset').show();
                    },
                    error: function(xhr, status, error) { 
                        $('#btn_reset').hide();
                        toastr["error"]('Có lỗi xảy ra ! Vui lòng thử lại','Error')
                    }
                });
            } else {
                $('#btn_reset').hide();
                toastr["error"]('Vui lòng chọn dự án','Error')
            }
        }
    </script>
@endsection
