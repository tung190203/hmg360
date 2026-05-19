$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});

$(document).ready(function () {

    $('.menu-item .menu-link').on('click', function (e) {
        $('.menu-item .menu-link').removeClass('active');
        $(this).addClass('active');
        $(".js-navbar-toggle").trigger('click');
    });

    $('#registerButton').on('click', function (e) {
        e.preventDefault();

        const $form = $(this).closest('form');
        const url = $(this).data('url');
        $(this).prop('disabled', true).text('Đang xác minh thông tin...');

        let _this = $(this);

        let formData = new FormData();

        // Lấy các giá trị của input text và select
        formData.append('name', $form.find('input[name="name"]').val());
        formData.append('phone', $form.find('input[name="phone"]').val());
        formData.append('product_code', $form.find('select[name="product_code"]').val());
        formData.append('address', $form.find('input[name="address"]').val());
        formData.append('province_name', $form.find('select[name="province_name"]').val());
        formData.append('vin', $form.find('input[name="vin"]').val());
        formData.append('engine_number', $form.find('input[name="engine_number"]').val());
        formData.append('identifier_type', $form.find('input[name="identifier_type"]:checked').val());
        formData.append('identifier_number', $form.find('input[name="identifier_number"]').val());
        formData.append('identifier_place_register', $form.find('select[name="identifier_place_register"]').val());
        formData.append('identifier_date', $form.find('input[name="identifier_date"]').val());
        formData.append('invoice_number', $form.find('input[name="invoice_number"]').val());
        formData.append('invoice_date', $form.find('input[name="invoice_date"]').val());

        // Lấy các file ảnh từ input file
        formData.append('identifier_front_image', $form.find('input[name="identifier_front_image"]')[0].files[0]);
        formData.append('identifier_back_image', $form.find('input[name="identifier_back_image"]')[0].files[0]);
        formData.append('invoice_image', $form.find('input[name="invoice_image"]')[0].files[0]);

        // Reset lại các thông báo lỗi cũ
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').remove();
        $('#ajax_response').html('Kiểm tra kỹ thông tin trước khi nhấn nút');

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,

            success: function (response) {
                _this.removeAttr('disabled').text('Hoàn thành & quay thưởng');
                const $modal = $('.md-confirm');

                const $previewIdFront = $('.js-preview-id-front');
                const $previewIdBack = $('.js-preview-id-back');
                const $previewBill = $('.js-preview-bill');

                const $mdPreviewIdFront = $('.js-modal-preview-id-front');
                const $mdPreviewIdBack = $('.js-modal-preview-id-back');
                const $mdPreviewBill = $('.js-modal-preview-bill');

                // $('#ajax_response').html('<p style="color: green;">' + response.message + '</p>');
                // $form[0].reset(); // Reset form nếu đăng ký thành công

                $form.serializeArray().forEach(({name, value}) => {
                    const $field = $modal.find(`.md-confirm__field[data-name="${name}"]`);

                    if (!$field.length) return;

                    $field.find('strong').text(value);
                });

                $mdPreviewIdFront.empty().append($previewIdFront.children().clone());
                $mdPreviewIdBack.empty().append($previewIdBack.children().clone());
                $mdPreviewBill.empty().append($previewBill.children().clone());

                $modal.modal('show');
            },
            error: function (xhr) {
                _this.removeAttr('disabled').text('Hoàn thành & quay thưởng');
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;

                    $.each(errors, function (field, messages) {
                        let $input = $form.find(`[name="${field}"]`);

                        // Thêm class is-invalid vào input hoặc select bị lỗi
                        $input.addClass('is-invalid');

                        // Thêm thông báo lỗi bên dưới input
                        // let errorMessage = `<!--<div class="invalid-feedback">${messages[0]}</div>-->`;
                        // $input.after(errorMessage);
                    });

                    let firstFieldError = Object.keys(errors)[0];
                    $('#ajax_response').html(errors[firstFieldError][0]);
                }
            }
        });
    });

    $('#buttonContact').on('click', function (e) {
        e.preventDefault();

        const $form = $(this).closest('form');
        const url = $(this).data('url');
        $(this).prop('disabled', true).text('Đang gửi...');

        let _this = $(this);

        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.ajax_response').html('');

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                name: $form.find('input[name="name"]').val(),
                phone: $form.find('input[name="phone"]').val(),
                address: $form.find('input[name="address"]').val(),
                dealer_id: $form.find('select[name="dealer_id"]').val(),
                motorcycle_type: $form.find('select[name="motorcycle_type"]').val(),
                content: $form.find('textarea[name="content"]').val(),
            },
            success: function (response) {
                _this.removeAttr('disabled').text('Liên hệ đại lý');
                $form.find('.ajax_response').html(`<span class="text-secondary">Gửi thông tin thành công. Vui lòng giữ máy, chúng tôi sẽ liên hệ lại trong vòng 24h</span>`);
                $form[0].reset();
            },
            error: function (xhr) {
                _this.removeAttr('disabled').text('Liên hệ đại lý');
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;

                    $.each(errors, function (field, messages) {
                        let $input = $form.find(`[name="${field}"]`);

                        $input.addClass('is-invalid');

                        // let errorMessage = `<!--<div class="invalid-feedback">${messages[0]}</div>-->`;
                        // $input.after(errorMessage);
                    });

                    let firstFieldError = Object.keys(errors)[0];
                    $form.find('.ajax_response').html('<span class="text-danger">' + errors[firstFieldError][0] + '</span>');
                }
            }
        });
    });

    $('#buttonChangePassword').on('click', function (e) {
        e.preventDefault();

        const $form = $(this).closest('form');
        const url = $(this).data('url');
        $(this).prop('disabled', true).text('Đang gửi...');

        let _this = $(this);

        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.ajax_response').html('');

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                current_password: $form.find('input[name="current_password"]').val(),
                new_password: $form.find('input[name="new_password"]').val(),
                new_password_confirmation: $form.find('input[name="new_password_confirmation"]').val()
            },
            success: function (response) {
                _this.removeAttr('disabled').text('Đổi mật khẩu');
                $form.find('.ajax_response').html('<span class="text-success mb-3 d-block">' + 'Đổi mật khẩu thành công' + '</span>');
                $form[0].reset();
            },
            error: function (xhr) {
                _this.removeAttr('disabled').text('Đổi mật khẩu');
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;

                    $.each(errors, function (field, messages) {
                        let $input = $form.find(`[name="${field}"]`);

                        $input.addClass('is-invalid');
                    });

                    let firstFieldError = Object.keys(errors)[0];
                    $form.find('.ajax_response').html('<span class="text-danger mb-3 d-block">' + errors[firstFieldError][0] + '</span>');
                }
            }
        });
    });

    if ($('#contact_select').length > 0) {
        updateContactInfo();

        // Lắng nghe sự kiện thay đổi option và cập nhật thông tin
        $('#contact_select').on('change', function () {
            updateContactInfo();
        });
    }

});

function updateContactInfo() {
    const selectedOption = $('#contact_select').find(':selected');
    const name = selectedOption.data('name');
    const hotline = selectedOption.data('hotline');
    const email = selectedOption.data('email');

    $('#contact_name').text(name || '');
    $('#contact_hotline').text(hotline || '');
    $('#contact_email').text(email || '');
}

$(document).ready(function () {
    $('#contact_province_select').on('change', function () {
        const selectedProvince = $(this).val();

        // Gửi request AJAX lên server
        $.ajax({
            url: '/api/get-dealer',
            method: 'GET',
            data: {
                province_code: selectedProvince
            },
            beforeSend: function () {
                $('#contact_select').html('<option>Loading...</option>');
            },
            success: function (response) {
                $('#contact_select').html(response);
                updateContactInfo();
            },
            error: function (xhr, status, error) {
                console.error('Lỗi khi gọi API:', error);
                $('#contact_select').html('<option value="">Không thể tải danh sách đại lý</option>');
            }
        });
    });
});

$(document).ready(function () {
    // Tìm bảng trong #section_winners
    const $table = $('#section_winners table');

    // Kiểm tra nếu bảng tồn tại và chưa được bọc bởi div.table-responsive
    if ($table.length && !$table.parent().hasClass('table-responsive')) {
        // Bọc bảng trong một thẻ div và thêm class
        $table.wrap('<div class="table-responsive"></div>');
    }
});

$(document).ready(function () {
    const sections = $('.section'); // Tất cả các section
    const menuLinks = $('.menu-link'); // Tất cả các menu link

    // Lấy menu Trang chủ dựa trên text
    const homeLink = menuLinks.filter(function () {
        return $(this).text().trim() === 'Trang chủ';
    });

    // Hàm kiểm tra và cập nhật class active
    function handleScroll() {
        const scrollPosition = $(window).scrollTop(); // Vị trí hiện tại của trang
        let activeSection = null;

        // Duyệt qua từng section
        sections.each(function () {
            const sectionTop = $(this).offset().top; // Vị trí trên của section
            const sectionBottom = sectionTop + $(this).outerHeight(); // Vị trí dưới của section

            // Kiểm tra nếu vị trí cuộn nằm trong section
            if (scrollPosition >= sectionTop - 150 && scrollPosition < sectionBottom - 150) {
                activeSection = $(this);
                return false; // Thoát khỏi vòng lặp khi tìm thấy section
            }
        });

        // Loại bỏ class active khỏi tất cả các menu link
        menuLinks.removeClass('active');
        if (activeSection) {
            const sectionId = activeSection.attr('id'); // Lấy ID của section
            // console.log(sectionId)

            // Nếu sectionId là home_page, kích hoạt menu Trang chủ
            if (sectionId === 'home_page') {
                homeLink.addClass('active');
            } else {
                // Thêm class active cho menu link tương ứng với section khác
                menuLinks.each(function () {
                    if ($(this).attr('href').includes(`#${sectionId}`)) {
                        $(this).addClass('active');
                    }
                });
            }
        }

    }

    // Lắng nghe sự kiện scroll
    $(window).on('scroll', handleScroll);

    // Gọi handleScroll khi trang load để set menu ban đầu
    handleScroll();
});

