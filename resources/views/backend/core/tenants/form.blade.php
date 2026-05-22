@extends('backend.index')

@section('title', $tenant->exists ? 'Sửa tenant' : 'Thêm tenant')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('backend_core_tenants') }}">Tenants</a></li>
    <li class="breadcrumb-item active">{{ $tenant->exists ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <form method="POST" action="{{ $tenant->exists ? route('backend_core_tenants_update', $tenant) : route('backend_core_tenants_store') }}">
        @csrf
        @if($tenant->exists)
            @method('PUT')
        @endif

        <div class="card">
            <div class="card-header"><h3 class="card-title">Thông tin tenant</h3></div>
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Tên</label>
                        <input name="name" class="form-control" value="{{ old('name', $tenant->name) }}" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Slug</label>
                        <input name="slug" id="tenant-slug" class="form-control" value="{{ old('slug', $tenant->slug) }}">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="active" @selected(old('status', $tenant->status) === 'active')>active</option>
                            <option value="inactive" @selected(old('status', $tenant->status) === 'inactive')>inactive</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="card-title mb-0 float-none">Tenant database</h3>
                        <div class="text-muted small mt-1">Kiểm tra kết nối bằng chính thông tin đang nhập trước khi lưu.</div>
                    </div>
                    <button type="button" class="btn btn-outline-success btn-sm" id="btn-test-db">
                        <i class="fas fa-plug mr-1"></i>Test DB
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="alert d-none" id="db-test-result"></div>
                @php($db = $tenant->database)
                @if($tenant->exists)
                    <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                @endif
                <div class="row">
                    <div class="form-group col-md-2">
                        <label>Driver</label>
                        <input name="database[driver]" class="form-control" value="{{ old('database.driver', $db->driver ?? 'mysql') }}" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Host</label>
                        <input name="database[host]" class="form-control" value="{{ old('database.host', $db->host ?? '127.0.0.1') }}" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Port</label>
                        <input name="database[port]" type="number" class="form-control" value="{{ old('database.port', $db->port ?? 3306) }}" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Database name</label>
                        <input name="database[database_name]" class="form-control" value="{{ old('database.database_name', $db->database_name ?? '') }}" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Username</label>
                        <input name="database[username]" class="form-control" value="{{ old('database.username', $db->username ?? '') }}" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Password</label>
                        <input name="database[password]" id="tenant-db-password" type="password" class="form-control"
                            placeholder="{{ $tenant->exists && $db?->password ? 'Để trống để giữ password hiện tại' : '' }}">
                        @if($tenant->exists && $db?->password)
                            <input type="hidden" name="database[clear_password]" value="0">
                            <div class="form-check mt-2">
                                <input type="checkbox" name="database[clear_password]" value="1" class="form-check-input" id="tenant-db-clear-password"
                                    @checked(old('database.clear_password'))>
                                <label class="form-check-label" for="tenant-db-clear-password">
                                    DB không dùng password / xoá password đã lưu
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Để trống ô password sẽ giữ password cũ. Tích lựa chọn này nếu DB local không có password.
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Organizer user</h3>
            </div>
            <div class="card-body">
                <div class="form-check mb-3">
                    <input type="hidden" name="create_organizer" value="0">
                    <input type="checkbox" name="create_organizer" value="1" class="form-check-input" id="create-organizer"
                        @checked(old('create_organizer', ! $tenant->exists))>
                    <label class="form-check-label" for="create-organizer">Tạo/cập nhật user mặc định cho tenant này</label>
                </div>

                <div class="row" id="organizer-fields">
                    <div class="form-group col-md-4">
                        <label>Tên organizer</label>
                        <input name="organizer[name]" class="form-control" value="{{ old('organizer.name') }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Email organizer</label>
                        <input name="organizer[email]" type="email" class="form-control" value="{{ old('organizer.email') }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Password organizer</label>
                        <input name="organizer[password]" type="password" class="form-control" value="{{ old('organizer.password') }}">
                    </div>
                </div>
                <p class="text-muted mb-0">
                    Nếu để theo gợi ý mặc định, user sẽ có dạng <code>organizer@tenant-slug.local</code> và password <code>123456</code>.
                </p>
            </div>
            <div class="card-footer text-right">
                <button class="btn btn-primary">Lưu</button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const nameInput = document.querySelector('input[name="name"]');
        const slugInput = document.getElementById('tenant-slug');
        const organizerCheckbox = document.getElementById('create-organizer');
        const organizerFields = document.getElementById('organizer-fields');
        const organizerNameInput = document.querySelector('input[name="organizer[name]"]');
        const organizerEmailInput = document.querySelector('input[name="organizer[email]"]');
        const organizerPasswordInput = document.querySelector('input[name="organizer[password]"]');
        const testDbButton = document.getElementById('btn-test-db');
        const testDbResult = document.getElementById('db-test-result');
        const dbPasswordInput = document.getElementById('tenant-db-password');
        const dbClearPasswordInput = document.getElementById('tenant-db-clear-password');
        let slugTouched = Boolean(slugInput.value);
        let organizerNameTouched = Boolean(organizerNameInput.value);
        let organizerEmailTouched = Boolean(organizerEmailInput.value);
        let organizerPasswordTouched = Boolean(organizerPasswordInput.value);

        const toSlug = function (value) {
            return value
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/đ/g, 'd')
                .replace(/Đ/g, 'D')
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        };

        slugInput.addEventListener('input', function () {
            slugTouched = true;
            fillOrganizerDefaults();
        });

        organizerNameInput.addEventListener('input', function () {
            organizerNameTouched = true;
        });

        organizerEmailInput.addEventListener('input', function () {
            organizerEmailTouched = true;
        });

        organizerPasswordInput.addEventListener('input', function () {
            organizerPasswordTouched = true;
        });

        nameInput.addEventListener('input', function () {
            if (!slugTouched) {
                slugInput.value = toSlug(nameInput.value);
            }

            fillOrganizerDefaults();
        });

        const currentSlug = function () {
            return slugInput.value || toSlug(nameInput.value);
        };

        function fillOrganizerDefaults() {
            const slug = currentSlug();

            if (!organizerNameTouched) {
                organizerNameInput.value = nameInput.value ? 'Organizer ' + nameInput.value : '';
            }

            if (!organizerEmailTouched) {
                organizerEmailInput.value = slug ? 'organizer@' + slug + '.local' : '';
            }

            if (!organizerPasswordTouched) {
                organizerPasswordInput.value = '123456';
            }
        }

        const toggleOrganizerFields = function () {
            organizerFields.style.display = organizerCheckbox.checked ? '' : 'none';
        };

        organizerCheckbox.addEventListener('change', toggleOrganizerFields);
        fillOrganizerDefaults();
        toggleOrganizerFields();

        if (dbClearPasswordInput) {
            const toggleDbPasswordInput = function () {
                dbPasswordInput.disabled = dbClearPasswordInput.checked;

                if (dbClearPasswordInput.checked) {
                    dbPasswordInput.value = '';
                }
            };

            dbClearPasswordInput.addEventListener('change', toggleDbPasswordInput);
            toggleDbPasswordInput();
        }

        testDbButton.addEventListener('click', function () {
            const formData = new FormData(testDbButton.closest('form'));
            formData.delete('_method');

            testDbButton.disabled = true;
            testDbResult.className = 'alert alert-info';
            testDbResult.textContent = 'Đang kiểm tra kết nối...';

            fetch("{{ route('backend_tenant_db_test') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData,
            })
                .then(async function (response) {
                    const data = await response.json();
                    if (!response.ok) {
                        throw data;
                    }
                    return data;
                })
                .then(function (data) {
                    testDbResult.className = 'alert alert-success';
                    testDbResult.textContent = data.message;
                })
                .catch(function (error) {
                    testDbResult.className = 'alert alert-danger';
                    const messages = [];

                    if (error.errors) {
                        Object.values(error.errors).forEach(function (fieldErrors) {
                            fieldErrors.forEach(function (message) {
                                messages.push(message);
                            });
                        });
                    }

                    if (messages.length) {
                        testDbResult.textContent = '';

                        const title = document.createElement('strong');
                        title.textContent = 'Thông tin DB chưa hợp lệ:';
                        testDbResult.appendChild(title);

                        const list = document.createElement('ul');
                        list.className = 'mb-0 pl-3';
                        messages.forEach(function (message) {
                            const item = document.createElement('li');
                            item.textContent = message;
                            list.appendChild(item);
                        });
                        testDbResult.appendChild(list);
                        return;
                    }

                    testDbResult.textContent = error.message || 'Kết nối DB thất bại.';
                })
                .finally(function () {
                    testDbButton.disabled = false;
                });
        });
    });
</script>
@endsection
