@extends('backend.index')

@section('title')
    Quản lý hotspot
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active">Hotspot</li>
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
                            <div class="col-md-4">
                                <div class="form-group">
                                    <select class="form-control" id="slt_vrtour_type" style="height: 38px">
                                        <option value="0">-- Hotspot chính --</option>
                                        <option value="2">-- Lô đất --</option>
                                        <option value="1">-- Toàn bộ --</option>
                                    </select>
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
                                        <th style="width:30%" class="grid_header">Ảnh</th>
                                        <th style="width:10%" class="grid_header">Vị trí</th>
                                        <th style="width:30%" class="grid_header">Mô tả</th>
                                        <th style="width:5%" class="grid_header1">Hiển thị</th>
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
        if (getParam("vrtour") != null && getParam("type") != null) {
            $('#slt_vrtour').val(getParam("vrtour")).change();
            $('#slt_vrtour_type').val(getParam("type")).change();
        }

        $(document).on('click', '#btn_reset', function(){
            if (confirm("Bạn có chắc chắn muốn reset toàn bộ Hotspot của dự án này không?")) {
                renderTable(true, true);
            }
        });

        $('#slt_vrtour').on('change', function() {
            renderTable();
        });

        $('#slt_vrtour_type').on('change', function() {
            renderTable();
        });

        function renderTable(notify = true, reset = false)
        {
            var vrtour  = $('#slt_vrtour').val();
            var type    = $('#slt_vrtour_type').val();
            if (vrtour != 'all') {
                $.ajax({
                    url: assetUrl+'vrtour/hotspot/get-hotspot/'+vrtour+'?type='+type+'&reset='+reset,
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
                        $('#dataGrid').html('');
                        toastr["error"]('Có lỗi xảy ra ! Vui lòng thử lại','Error')
                    }
                });
            } else {
                $('#btn_reset').hide();
                $('#dataGrid').html('');
            }
        }
    </script>
@endsection