<nav class="main-header navbar navbar-expand navbar-dark navbar-gray-dark">
    <ul class="navbar-nav align-items-center">
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center" data-widget="pushmenu" href="#!">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ route('backend_dashboard') }}" class="nav-link d-flex align-items-center">Home</a>
        </li>
    </ul>
    <ul class="navbar-nav ml-auto align-items-center">
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center" href="{{ route('home_page') }}" target="_blank" title="Xem website">
                <i class="fas fa-globe-americas mr-1"></i> Xem website
            </a>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link d-flex align-items-center" data-toggle="dropdown" href="#!" title="Profiles" style="height: 100%;">
                <i class="fas fa-th-large"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <a href="#!" class="dropdown-item" data-toggle="modal" data-target="#adminProfileModal">
                    <div class="media align-items-center">
                        <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('backend_assets/images/logo.png') }}"
                            alt="User Avatar"
                            class="img-size-50 mr-3 img-circle"
                            style="width: 50px; height: 50px; object-fit: cover;"
                            id="header-avatar-preview">
                        <div class="media-body">
                            <h3 class="dropdown-item-title">
                                <span id="header-user-name">{{ Auth::user()->name ?? '' }}</span>
                                <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                            </h3>
                            <p class="text-sm" id="header-user-email">{{ Auth::user()->email ?? '' }}</p>
                            <p class="text-sm text-muted mb-0">
                                <i class="far fa-clock mr-1"></i>
                                {{ date('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                </a>
                <div class="dropdown-divider"></div>
                <a href="{{ route('logout') }}" class="dropdown-item dropdown-footer" onclick="event.preventDefault();
                                       document.getElementById('logout-form').submit();">Đăng xuất</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                    style="display: none;">
                    @csrf
                </form>
            </div>
        </li>
    </ul>
</nav>

<!-- Modal Cập nhật Profile -->
<div class="modal fade" id="adminProfileModal" tabindex="-1" role="dialog" aria-labelledby="adminProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adminProfileModalLabel">Thông tin cá nhân</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="adminProfileForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group text-center">
                        <div class="position-relative d-inline-block">
                            <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('backend_assets/images/logo.png') }}"
                                alt="Avatar Preview"
                                id="profileAvatarPreview"
                                class="img-circle elevation-2"
                                style="width: 100px; height: 100px; object-fit: cover; cursor: pointer"
                                onclick="document.getElementById('profileAvatarInput').click()">
                            <div class="position-absolute" style="bottom: 0; right: 0; background: rgba(0,0,0,0.5); border-radius: 50%; width: 32px; height: 32px; line-height: 32px; text-align: center; cursor: pointer" onclick="document.getElementById('profileAvatarInput').click()">
                                <i class="fas fa-camera text-white"></i>
                            </div>
                        </div>
                        <input type="file" id="profileAvatarInput" name="avatar_file" accept="image/*" style="display: none;" onchange="previewProfileAvatar(this)">
                        <small class="form-text text-muted mt-2">Nhấn vào ảnh để thay đổi Avatar</small>
                    </div>

                    <div class="form-group">
                        <label for="profileName">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="profileName" name="name" value="{{ Auth::user()->name }}" required>
                    </div>

                    <div class="form-group">
                        <label for="profileEmail">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="profileEmail" name="email" value="{{ Auth::user()->email }}" required>
                    </div>

                    <hr>
                    <h6>Đổi mật khẩu (Bỏ trống nếu không muốn đổi)</h6>

                    <div class="form-group">
                        <label for="profileCurrentPassword">Mật khẩu hiện tại</label>
                        <input type="password" class="form-control" id="profileCurrentPassword" name="current_password" placeholder="Nhập mật khẩu hiện tại">
                    </div>

                    <div class="form-group">
                        <label for="profileNewPassword">Mật khẩu mới</label>
                        <input type="password" class="form-control" id="profileNewPassword" name="password" placeholder="Nhập mật khẩu mới">
                        <small class="form-text text-muted">Mật khẩu phải lớn hơn 8 ký tự, chứa ít nhất 1 chữ hoa và 1 ký tự đặc biệt.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveProfile">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function previewProfileAvatar(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#profileAvatarPreview').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('adminProfileForm');

        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);
                let btn = document.getElementById('btnSaveProfile');
                let spinner = btn.querySelector('.spinner-border');

                // Reset styling
                btn.disabled = true;
                spinner.classList.remove('d-none');
                // Using fetch API replacing jQuery
                fetch("{{ route('backend.profile.update') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update UI seamlessly
                            document.getElementById('header-user-name').innerText = data.data.name;
                            document.getElementById('header-user-email').innerText = data.data.email;
                            document.getElementById('header-avatar-preview').src = data.data.avatar;

                            // Clear password fields
                            document.getElementById('profileCurrentPassword').value = '';
                            document.getElementById('profileNewPassword').value = '';

                            // Optional: auto-close after a delay
                            setTimeout(() => {
                                $('#adminProfileModal').modal('hide');
                            }, 500);

                            if (typeof toastr !== 'undefined') {
                                toastr.success(data.message);
                            }
                        } else if (data.errors) {
                            let errorHtml = '<div class="alert alert-danger"><ul class="mb-0">';
                            for (const key in data.errors) {
                                errorHtml += '<li>' + data.errors[key][0] + '</li>';
                            }
                            errorHtml += '</ul></div>';
                            toastr.error(errorHtml);
                        }
                    })
                    .catch(error => {
                        toastr.error('Đã có lỗi xảy ra. Vui lòng thử lại.');
                        console.error('Error:', error);
                    })
                    .finally(() => {
                        btn.disabled = false;
                        spinner.classList.remove('d-none');
                        spinner.classList.add('d-none');
                    });
            });
        }
    });
</script>