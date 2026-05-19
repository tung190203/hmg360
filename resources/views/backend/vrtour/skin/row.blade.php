<div class="form-group row row_detail">
    <input hidden id="document_id-">
    <div class="col-lg-12 text-right mb-3">
        <button type="button" class="btn btn-sm fw-bold btn-danger delete_detail" role="button">
            <i class="fa fa-trash" aria-hidden="true"></i> Xóa
        </button>
    </div>
    <div class="col-lg-12">
        <x-forms.textarea name="document_name-" value="" label="Tên văn bản" />
        <x-forms.textarea name="document_name_en-" value="" label="Tên văn bản EN" />
        <x-forms.upload name="download-" value="" label="File download" type="Files"/>
    </div>
</div>