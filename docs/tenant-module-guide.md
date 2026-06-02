# Hướng Dẫn Tạo Tenant Và Module

Tài liệu này mô tả quy trình tạo tenant mới và phát triển module tenant trong HMG360.

Kiến trúc hiện tại được tách thành:

- Database trung tâm: lưu `tenants`, `tenant_databases`, `modules`, `roles`, `permissions`, `users` để login và điều phối tenant.
- Database tenant: lưu dữ liệu nghiệp vụ của từng tenant, ví dụ `projects`, `posts`, `guests`, `groups`, `settings`.
- Source module tenant: nằm trong `app/TenantModules/Tenants/{TenantStudly}/{ModuleName}`.

## 0. Cách Đọc Placeholder Trong Tài Liệu

Các giá trị đặt trong `{...}` là placeholder, bắt buộc thay bằng giá trị thật của tenant/module đang làm.

Ví dụ:

```text
{tenant}        => slug tenant thật, ví dụ: demo-tenant
{tenant_studly} => tenant slug dạng StudlyCase, ví dụ: DemoTenant
{tenant_db}     => tên database tenant thật, ví dụ: tenant_demo
{module}        => slug module thật, ví dụ: reports
{module_name}   => tên module hiển thị, ví dụ: Reports
{module_studly} => tên module dạng StudlyCase, ví dụ: Reports
{tenant_route_key} => tenant slug đổi dấu gạch ngang thành gạch dưới, ví dụ: demo_tenant
{module_route_key} => module slug đổi dấu gạch ngang thành gạch dưới, ví dụ: reports
{table}         => tên bảng tenant DB, ví dụ: reports
```

Các giá trị `demo-tenant`, `tenant_demo`, `Reports`, `reports`, `DemoTenant` trong tài liệu chỉ là ví dụ minh họa. Không dùng nguyên các giá trị này cho tenant thật nếu không đúng với dự án đang triển khai.

## 1. Khái Niệm Cần Nắm

### Tenant

Mỗi tenant có một record trong bảng `tenants` và một cấu hình database trong `tenant_databases`.

Ví dụ tenant slug `trung-tam-xuc-tien-ha-noi` sẽ có:

```text
app/TenantModules/Tenants/TrungTamXucTienHaNoi
database tenant: tenant_trung_tam_xuc_tien_ha_noi
```

Slug tenant được dùng để:

- Đặt folder module theo `Str::studly($tenant->slug)`.
- Gắn user vào tenant qua `users.tenant_id` trong DB trung tâm.
- Lấy config DB tenant khi middleware `tenant.db` chạy.

### Module

Mỗi module có:

- Record trong bảng trung tâm `modules`.
- File `module.php` mô tả tên, slug, namespace view, menu, permission.
- File `routes.php` khai báo route backend của module.
- Thư mục code riêng: controller, model, view, migration.

Module chỉ truy cập được khi:

- User đã login.
- User active.
- User có tenant.
- Middleware `tenant.db` connect được DB tenant.
- Module đang enabled trong bảng `modules`.
- User có permission cần thiết.

Menu sidebar không lấy trực tiếp từ việc folder module có tồn tại. Sidebar lấy từ các module enabled trong bảng `modules`, sau đó đọc `modules.config.menu` hoặc `module.php` (`menu` / `menu_items`) để biết title, icon, route và nhóm hiển thị.

Platform owner và tenant user nhìn sidebar khác nhau:

- Platform owner chỉ thấy menu quản trị platform từ `config('cms.backend_module')`.
- Tenant user/organizer mới thấy module nghiệp vụ của tenant qua `ModuleManager::menuForTenant($user->tenant)`.
- Vì vậy owner thấy tenant có 13 module không có nghĩa là owner sẽ thấy 13 mục nghiệp vụ trong sidebar.

### Quy ước route multi-tenant bắt buộc

Route tenant module phải được scope theo tenant. Không dùng route global cho module nghiệp vụ nếu module đó có thể tồn tại ở nhiều tenant.

Quy ước chuẩn:

```text
URI prefix:        backend/tenants/{tenant}/modules/{module}
Route name prefix: tenant.{tenant_route_key}.{module_route_key}.
Middleware path:  Tenants/{tenant_studly}/{module_studly}
Menu route:       tenant.{tenant_route_key}.{module_route_key}.index
```

Ví dụ với tenant `hoa-lac`, module `projects`:

```text
URI:              backend/tenants/hoa-lac/modules/projects
Route name:       tenant.hoa_lac.projects.index
Middleware:       module.enabled:projects,Tenants/HoaLac/Projects
modules.path:     Tenants/HoaLac/Projects
module.php route: tenant.hoa_lac.projects.index
```

Không copy quy ước legacy từ tenant đầu như `backend/project`, `backend_project`, `backend/modules/projects` cho tenant mới. Các route/name đó là global, không chứa tenant, nên khi có nhiều tenant cùng module Laravel có thể match route của tenant khác và middleware sẽ báo `Module chưa khả dụng`.

## 2. Quy Trình Tạo Tenant Mới

### Bước 1: Tạo database tenant trên MySQL

Command `tenant:create` chỉ lưu cấu hình DB vào platform DB, không tạo schema database vật lý. Cần tạo database trước:

```bash
mysql -u {db_user} -p -e "CREATE DATABASE {tenant_db} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Nếu dùng user DB riêng cho tenant, tạo user và grant quyền:

```sql
CREATE USER '{tenant_db_user}'@'%' IDENTIFIED BY '{tenant_db_password}';
GRANT ALL PRIVILEGES ON {tenant_db}.* TO '{tenant_db_user}'@'%';
FLUSH PRIVILEGES;
```

### Bước 2: Tạo tenant trong platform DB

Không tạo record tenant bằng tay trong database. Dùng command `tenant:create` hoặc form tạo tenant trong UI quản trị.

Cả UI và command đều dùng chung service `TenantProvisioner`, nên kết quả giống nhau:

- Lưu record `tenants`.
- Lưu record `tenant_databases`.
- Tạo folder `app/TenantModules/Tenants/{tenant_studly}` nếu chưa có.
- Tạo hoặc cập nhật organizer nếu có truyền thông tin organizer.

Lệnh dưới đây là mẫu đầy đủ. Phải thay placeholder bằng giá trị thật trước khi chạy:

```bash
php artisan tenant:create "{tenant_name}" \
  --slug={tenant} \
  --db={tenant_db} \
  --host={tenant_db_host} \
  --port={tenant_db_port} \
  --username={tenant_db_user} \
  --password={tenant_db_password} \
  --organizer-email={organizer_email} \
  --organizer-name="{organizer_name}" \
  --organizer-password={organizer_password}
```

Kết quả:

- Tạo hoặc cập nhật `tenants.slug = {tenant}`.
- Tạo hoặc cập nhật `tenant_databases.database_name = {tenant_db}`.
- Tạo folder `app/TenantModules/Tenants/{tenant_studly}`.
- Tạo central user organizer nếu có `--organizer-email`.
- Tạo role `organizer` cho tenant.

Lưu ý quan trọng:

- Bước này không tự tạo record trong `modules`.
- Bước này không tự tạo permission module.
- Bước này không tự tạo menu sidebar cho module.
- Bước này không tự migrate schema tenant DB.
- Nếu tenant DB đã có sẵn data/schema và source module đã có sẵn, vẫn phải register module vào platform DB.

Ví dụ minh họa:

```bash
php artisan tenant:create "Demo Tenant" \
  --slug=demo-tenant \
  --db=tenant_demo \
  --host=127.0.0.1 \
  --port=3306 \
  --username=root \
  --password= \
  --organizer-email=organizer.demo@example.com \
  --organizer-name="Demo Organizer" \
  --organizer-password=Password@123
```

### Bước 3: Tạo module cho tenant mới

Tenant mới không dùng lại module cũ theo config cố định. Sau khi tạo tenant, tạo từng module mới bằng:

```bash
php artisan tenant:module-create {tenant} "{module_name}" --slug={module}
```

Sau đó code các file của module. Command chỉ tạo khung và một view placeholder; nó không tự tạo migration/model/controller nghiệp vụ vì không biết module thật cần bảng và field gì.

Các việc thường phải làm:

- Sửa `app/TenantModules/Tenants/{tenant_studly}/{module_studly}/module.php` để đúng tên menu, icon, route, permission.
- Sửa `app/TenantModules/Tenants/{tenant_studly}/{module_studly}/routes.php` để trỏ tới controller thật.
- Tạo migration trong `app/TenantModules/Tenants/{tenant_studly}/{module_studly}/Database/migrations` nếu module cần bảng mới hoặc cần đổi schema.
- Tạo model trong `app/TenantModules/Tenants/{tenant_studly}/{module_studly}/Models` nếu module có dữ liệu nghiệp vụ.
- Tạo controller trong `app/TenantModules/Tenants/{tenant_studly}/{module_studly}/Http/Controllers`.
- Tạo/sửa view trong `app/TenantModules/Tenants/{tenant_studly}/{module_studly}/Resources/views`.

Chỉ chạy migration module sau khi đã có ít nhất một file migration trong thư mục `Database/migrations`. Nếu thư mục này còn trống, bỏ qua lệnh migrate ở bước này.

#### Register module là gì?

Register module là bước ghi module vào DB trung tâm, chủ yếu là bảng `modules` và `permissions`. Hệ thống dựa vào dữ liệu này để biết:

- Tenant nào đang có module nào.
- Module có đang bật hay không.
- Path source của module ở đâu để middleware `module.enabled:{module},{path}` kiểm tra.
- Menu module hiển thị route/icon/title nào.
- Role `organizer` và các group/user có những permission nào.

Nếu chỉ tạo folder code mà không register module vào DB trung tâm, route vẫn có thể tồn tại, nhưng menu không hiện, middleware `module.enabled` sẽ chặn, và permission không có dữ liệu để cấp.

#### Khi nào không cần register thủ công?

Không cần register thủ công nếu dùng command:

```bash
php artisan tenant:module-create {tenant} "{module_name}" --slug={module}
```

Command này đã tự:

- Tạo folder module.
- Tạo `module.php`, `routes.php`, view placeholder.
- Tạo/cập nhật record trong bảng `modules`.
- Tạo permission cơ bản `view/create/update/delete`.
- Gán permission cho role `organizer`.

Đây là cách mặc định nên dùng.

#### Khi nào mới cần register thủ công?

Chỉ register thủ công khi không dùng `tenant:module-create`, ví dụ:

- Copy sẵn một folder module từ nơi khác vào `app/TenantModules/Tenants/{tenant_studly}/{module_studly}`.
- Tự tạo folder/file module bằng tay.
- Module code đã có trong repo nhưng chưa có record tương ứng trong bảng `modules`.
- Cần sửa lại `modules.path`, `modules.config`, `sort_order`, hoặc bật module cho tenant mà command không phù hợp.

Khi tự tạo module hoàn toàn thủ công, phải tự làm đủ bốn việc:

1. Tạo folder module đúng quy ước.
2. Tạo `module.php` và `routes.php`.
3. Tạo record trong bảng trung tâm `modules`.
4. Tạo permission trong bảng `permissions` và gán cho role/group cần dùng.
5. Đảm bảo menu có `route` hợp lệ qua `modules.config.menu` hoặc `module.php`.

Ví dụ register module thủ công qua Tinker. Chỉ dùng đoạn này trong các trường hợp thủ công ở trên, không cần chạy nếu đã dùng `tenant:module-create`:

```bash
php artisan tinker
```

```php
$tenant = \App\Core\Tenant\Tenant::where('slug', '{tenant}')->firstOrFail();

\App\Core\Module\Module::updateOrCreate(
    ['tenant_id' => $tenant->id, 'slug' => '{module}'],
    [
        'name' => '{module_name}',
        'path' => 'Tenants/{tenant_studly}/{module_studly}',
        'is_enabled' => true,
        'sort_order' => 10,
        'config' => [
            'menu' => [
                'title' => '{module_name}',
                'icon' => 'fas fa-chart-bar',
                'route' => 'tenant.{tenant_route_key}.{module_route_key}.index',
                'section' => 'content',
            ],
        ],
    ],
);

$permissionIds = collect(['view', 'create', 'update', 'delete'])->map(function ($permission) {
    return \App\Core\Permission\Permission::firstOrCreate([
        'module' => '{module}',
        'permission' => $permission,
    ])->id;
});

\App\Core\Permission\Role::where('tenant_id', $tenant->id)
    ->where('slug', 'organizer')
    ->get()
    ->each(fn ($role) => $role->permissions()->syncWithoutDetaching($permissionIds));
```

#### Import module đã code sẵn cho tenant đã có

Dùng phần này khi:

- Tenant đã có trong bảng `tenants`.
- DB config đã có trong `tenant_databases`.
- Source module đã nằm trong `app/TenantModules/Tenants/{tenant_studly}`.
- Bảng `modules` vẫn chưa có record module, hoặc module có record nhưng không hiện sidebar.

Trước tiên kiểm tra đúng tenant và folder:

```bash
php artisan tinker
```

```php
\App\Core\Tenant\Tenant::select('id', 'name', 'slug')->get();
\File::directories(app_path('TenantModules/Tenants'));
```

Nếu tenant slug là `{tenant}` và folder là `{tenant_studly}`, register tất cả module có `module.php` trong folder đó:

```php
eval(<<<'PHP'
$tenant = \App\Core\Tenant\Tenant::where('slug', '{tenant}')->firstOrFail();
$tenantFolder = '{tenant_studly}';
$basePath = app_path("TenantModules/Tenants/{$tenantFolder}");

foreach (\File::directories($basePath) as $moduleDir) {
    $manifestPath = $moduleDir . '/module.php';

    if (! file_exists($manifestPath)) {
        continue;
    }

    $manifest = require $manifestPath;
    $folder = basename($moduleDir);
    $slug = $manifest['slug'] ?? \Illuminate\Support\Str::kebab($folder)->toString();
    $permissions = $manifest['permissions'] ?? ['view', 'create', 'update', 'delete'];
    $menu = $manifest['menu'] ?? null;
    $menuItems = $manifest['menu_items'] ?? null;
    $config = $manifest;

    if (! is_array($config['menu'] ?? null) && is_array($menuItems)) {
        $firstMenuItem = collect($menuItems)->first(fn ($item) => is_array($item));

        if (is_array($firstMenuItem)) {
            $config['menu'] = $firstMenuItem;
        }
    }

    \App\Core\Module\Module::updateOrCreate(
        ['tenant_id' => $tenant->id, 'slug' => $slug],
        [
            'name' => $manifest['name'] ?? $folder,
            'path' => "Tenants/{$tenantFolder}/{$folder}",
            'is_enabled' => true,
            'sort_order' => $menu['sort_order'] ?? 0,
            'config' => $config,
        ]
    );

    $permissionIds = collect($permissions)->map(function ($permission) use ($slug) {
        return \App\Core\Permission\Permission::firstOrCreate([
            'module' => $slug,
            'permission' => $permission,
        ])->id;
    })->all();

    \App\Core\Permission\Role::firstOrCreate(
        ['tenant_id' => $tenant->id, 'slug' => 'organizer'],
        ['name' => 'Organizer']
    )->permissions()->syncWithoutDetaching($permissionIds);
}

dump(\App\Core\Module\Module::where('tenant_id', $tenant->id)->select('slug', 'name', 'path', 'is_enabled', 'config')->get()->toArray());
PHP);
```

Nếu một module đã register nhưng không hiện sidebar, nguyên nhân thường là `module.php` không có `menu`, hoặc `modules.config.menu.route` trỏ tới route không tồn tại. Khi đó thêm `config.menu` cho module:

```php
\App\Core\Module\Module::where('tenant_id', $tenant->id)
    ->where('slug', '{module}')
    ->update([
        'is_enabled' => true,
        'sort_order' => 10,
        'config' => [
            'menu' => [
                'title' => '{module_name}',
                'icon' => 'fas fa-cube',
                'route' => '{route_name}',
                'section' => 'content',
            ],
        ],
    ]);
```

Với module có menu con, dùng `items` thay vì `route` cấp cha:

```php
'config' => [
    'menu' => [
        'title' => '{module_name}',
        'icon' => 'fas fa-cube',
        'section' => 'systems',
        'items' => [
            'general' => ['title' => 'Cấu hình chung', 'route' => '{route_name}'],
        ],
    ],
],
```

Sau khi import hoặc sửa menu:

```bash
php artisan optimize:clear
php artisan tenant:module-list {tenant}
```

### Bước 4: Sync user có sẵn nếu tenant DB đã có data

Nếu tenant DB đã có sẵn bảng `users`, không cần copy tay từng user sang platform DB. Chạy:

```bash
php artisan tenant:sync-users {tenant}
```

Lệnh này đọc `tenant.users` rồi tạo/cập nhật user tương ứng trong platform DB `users` để họ login được.

Lưu ý:

- Dùng lệnh này cho dữ liệu user đã có sẵn hoặc import từ nguồn khác.
- Với user tạo mới qua UI/module tenant, không cần chạy lệnh này vì model user tenant sẽ tự sync qua trait `SyncsTenantUserToPlatform`.
- Lệnh này chỉ sync thông tin đăng nhập cơ bản: `name`, `email`, `phone`, `avatar`, `password`, `status`, `tenant_id`.
- Quyền của user thường vẫn lấy từ `groups.permission_data` trong tenant DB, không lấy từ `role_id` platform.

### Bước 5: Kiểm tra

```bash
php artisan route:list --path=backend
php artisan optimize:clear
php artisan tenant:module-list {tenant}
```

Login bằng organizer và vào `/backend/tenants/trung-tam-xuc-tien-ha-noi/modules/dashboard`.

## 3. Tạo Module Mới Cho Tenant

### Bước 1: Scaffold module

Khuyến nghị dùng command scaffold để tránh thiếu record `modules` và `permissions`:

```bash
php artisan tenant:module-create {tenant} "{module_name}" --slug={module}
```

Nếu tự tạo folder/file mà không dùng command này, xem phần "Tự tạo module cho tenant mới" ở Bước 3 bên trên để register module thủ công vào DB trung tâm.

Lệnh tạo:

```text
app/TenantModules/Tenants/{tenant_studly}/{module_studly}
├── Database/migrations
├── Http/Controllers
├── Http/Requests
├── Models
├── Repositories
├── Resources/views
├── Services
├── module.php
└── routes.php
```

Và tạo hoặc cập nhật:

- `modules.slug = {module}`
- `permissions`: `{module}.view`, `{module}.create`, `{module}.update`, `{module}.delete`
- Gán permission cho role `organizer`

### Bước 2: Sửa `module.php`

File mẫu:

```php
<?php

return [
    'name' => '{module_name}',
    'slug' => '{module}',
    'view_namespace' => 'tenant-{tenant}-{module}',
    'menu' => [
        'title' => '{module_name}',
        'icon' => 'fas fa-chart-bar',
        'route' => 'tenant.{tenant_route_key}.{module_route_key}.index',
        'section' => 'content',
    ],
    'permissions' => ['view', 'create', 'update', 'delete'],
];
```

Quy ước:

- `slug` phải trùng với `modules.slug`.
- `view_namespace` dùng khi render view: `view('tenant-{tenant}-{module}::index')`.
- `menu.route` phải tồn tại trong route list, nếu không menu sẽ bị lọc.
- `permissions` sẽ hiện trong màn hình group/permission.

Nếu module có nhiều menu con:

```php
'menu' => [
    'title' => '{module_name}',
    'icon' => 'fas fa-chart-bar',
    'section' => 'content',
    'items' => [
        'overview' => ['title' => 'Tổng quan', 'route' => 'tenant.{tenant_route_key}.{module_route_key}.index'],
        'exports' => ['title' => 'Xuất báo cáo', 'route' => 'tenant.{tenant_route_key}.{module_route_key}.exports'],
    ],
],
```

### Bước 3: Sửa `routes.php`

Route phải có middleware tenant:

```php
<?php

use App\TenantModules\Tenants\{TenantStudly}\{ModuleStudly}\Http\Controllers\{ModuleSingular}Controller;
use Illuminate\Support\Facades\Route;

Route::middleware([
        'auth',
        'active.user',
        'tenant.db',
        'module.enabled:{module},Tenants/{tenant_studly}/{module_studly}',
    ])
    ->prefix('backend/tenants/{tenant}/modules/{module}')
    ->name('tenant.{tenant_route_key}.{module_route_key}.')
    ->group(function () {
        Route::get('/', [{ModuleSingular}Controller::class, 'index'])->name('index');
        Route::get('/create', [{ModuleSingular}Controller::class, 'create'])->name('create');
        Route::post('/', [{ModuleSingular}Controller::class, 'store'])->name('store');
        Route::get('/{record}/edit', [{ModuleSingular}Controller::class, 'edit'])->name('edit');
        Route::put('/{record}', [{ModuleSingular}Controller::class, 'update'])->name('update');
        Route::delete('/{record}', [{ModuleSingular}Controller::class, 'destroy'])->name('destroy');
    });
```

Quan trọng:

- Luôn có `tenant.db` trước khi dùng model tenant.
- Hệ thống đã cấu hình middleware priority để `tenant.db` chạy trước `SubstituteBindings`. Vì vậy implicit binding `{report}` dùng được nếu model dùng connection `tenant`.
- `module.enabled:{slug},{path}` giúp chặn module chưa bật hoặc sai path.
- `prefix()` phải có tenant slug: `backend/tenants/{tenant}/modules/{module}`.
- `name()` phải có tenant route key: `tenant.{tenant_route_key}.{module_route_key}.`.
- Không dùng lại route name global kiểu `backend_project`, `backend_vrtour_skin_index` cho tenant mới.
- `module.php['menu']['route']` phải trỏ tới route scoped, ví dụ `tenant.{tenant_route_key}.{module_route_key}.index`.

Nếu route chỉ render view placeholder bằng closure, phải truyền biến sidebar vì closure không đi qua base `Controller` để share biến:

```php
Route::get('/', fn () => view('tenant-{tenant}-{module}::index', [
    'selectedMainMenu' => '{module}',
    'selectedSubMenu' => '',
]))->name('index');
```

Khi đã có controller riêng, nên set menu active trong controller:

```php
class {ModuleSingular}Controller extends Controller
{
    protected string $selectedMainMenu = '{module}';
}
```

### Bước 4: Tạo model dùng tenant connection

Cách đơn giản. Đoạn dưới đây dùng `DemoTenant/Reports/Report` làm ví dụ, khi làm module thật phải đổi namespace/class/table theo tenant và module thật:

```php
<?php

namespace App\TenantModules\Tenants\DemoTenant\Reports\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'status',
    ];
}
```

Hoặc dùng trait nếu module có nhiều model:

```php
trait UsesTenantConnection
{
    public function getConnectionName()
    {
        return 'tenant';
    }
}
```

Tất cả model đọc/ghi tenant DB bắt buộc dùng connection `tenant`. Nếu quên, dữ liệu sẽ query nhầm DB trung tâm hoặc gây lỗi.

### Bước 5: Tạo migration trong module nếu cần schema

Nếu module cần tạo bảng mới hoặc thay đổi schema tenant DB, tạo migration trong:

```text
app/TenantModules/Tenants/{tenant_studly}/{module_studly}/Database/migrations/{timestamp}_create_{table}_table.php
```

Ví dụ:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('reports');
    }
};
```

Sau khi đã tạo file migration, chạy:

```bash
php artisan tenant:module-migrate {tenant} {module} --force
```

Nếu module chỉ là màn hình đọc dữ liệu đã có sẵn và không cần thay đổi schema, bỏ qua bước migrate.

### Bước 6: Tạo controller

Đoạn dưới đây dùng `DemoTenant/Reports/ReportController` làm ví dụ, khi làm module thật phải đổi namespace, model, route name, view namespace và permission key theo module thật.

```php
<?php

namespace App\TenantModules\Tenants\DemoTenant\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\TenantModules\Tenants\DemoTenant\Reports\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $this->authorizeModule('view');

        return view('tenant-demo-tenant-reports::index', [
            'reports' => Report::latest()->paginate(20),
            'canCreate' => $this->canModule('create'),
            'canUpdate' => $this->canModule('update'),
            'canDelete' => $this->canModule('delete'),
        ]);
    }

    public function create()
    {
        $this->authorizeModule('create');

        return view('tenant-demo-tenant-reports::form', [
            'report' => new Report(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeModule('create');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $report = Report::create($data);

        return redirect()->route('tenant.demo_tenant.reports.edit', $report);
    }

    public function edit(Report $report)
    {
        $this->authorizeModule('update');

        return view('tenant-demo-tenant-reports::form', compact('report'));
    }

    public function update(Request $request, Report $report)
    {
        $this->authorizeModule('update');

        $report->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
        ]));

        return back()->with('success', 'Cập nhật thành công');
    }

    public function destroy(Report $report)
    {
        $this->authorizeModule('delete');

        $report->delete();

        return redirect()->route('tenant.demo_tenant.reports.index')
            ->with('success', 'Xóa thành công');
    }

    private function authorizeModule(string $permission): void
    {
        abort_unless($this->canModule($permission), 403, self::MESSAGE_UNAUTHORIZED);
    }

    private function canModule(string $permission): bool
    {
        $user = auth()->user();

        return (bool) (
            $user?->isPlatformOwner()
            || $user?->isTenantOrganizer()
            || $user?->hasPermission('reports.' . $permission)
            || $user?->hasPermission('reports/' . $permission)
        );
    }
}
```

Quy ước permission:

- Core role permission trong platform DB dùng dot: `reports.view`.
- Group permission cũ trong tenant DB có thể dùng slash: `reports/view`.
- `User::hasPermission()` hiện đã map dot/slash và map `create/add`, `update/edit`.

### Bước 7: Tạo views

Đặt view tại:

```text
app/TenantModules/Tenants/{tenant_studly}/{module_studly}/Resources/views/index.blade.php
app/TenantModules/Tenants/{tenant_studly}/{module_studly}/Resources/views/form.blade.php
```

Render:

```php
return view('tenant-{tenant}-{module}::index');
```

Nếu vừa tạo view mới nhưng Laravel chưa nhận:

```bash
php artisan view:clear
php artisan optimize:clear
```

## 4. Permission Và Role

### Organizer

Organizer là user trung tâm có:

- `tenant_id` khác null.
- `role_id` trỏ tới role `organizer`.
- `role.slug = organizer`.

Trong `App\Models\User`, organizer được xem như full quyền của tenant:

```php
$user->isTenantOrganizer()
```

### User thường trong tenant

User thường login qua bảng `users` trung tâm nhưng quyền lấy từ tenant DB:

- Tìm user tenant theo email trong `tenant.users`.
- Lấy `group_id`.
- Đọc `groups.permission_data`.
- Đọc `groups.scope_data` nếu cần scope theo record.

Vì vậy khi tạo/sửa user trong tenant, model tenant user sẽ sync sang central user.

Batch command `tenant:sync-users` chỉ dùng cho dữ liệu user đã có sẵn trong tenant DB. Với user tạo mới qua UI/module tenant, hệ thống sync ngay khi save model.

Nếu tenant mới có module quản lý user riêng, model user của tenant phải dùng trait sync chung:

Đoạn dưới đây dùng `DemoTenant/User` làm ví dụ, khi làm tenant thật phải đổi namespace theo module user thật.

```php
<?php

namespace App\TenantModules\Tenants\DemoTenant\User\Models;

use App\Core\Tenant\SyncsTenantUserToPlatform;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use SyncsTenantUserToPlatform;

    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'password',
        'status',
        'group_id',
    ];
}
```

Trait `SyncsTenantUserToPlatform` sẽ:

- Khi tenant user được tạo/cập nhật: tạo/cập nhật record tương ứng trong platform DB `users`.
- Khi tenant user bị xóa: xóa platform user tương ứng của tenant đó.
- Gán `tenant_id`, `password`, `status`, `name`, `phone`, `avatar` vào platform user.
- Đặt `role_id = null` cho user thường; quyền của user thường vẫn lấy từ `groups.permission_data` trong tenant DB.

Nếu tạo user tenant bằng raw query hoặc `DB::table('users')->insert(...)`, Eloquent event sẽ không chạy, nên sẽ không tự sync sang platform DB. Luôn tạo user qua model có trait này.

### Khi thêm permission mới

Nếu thêm action mới, ví dụ `export`:

1. Thêm vào `module.php`:

```php
'permissions' => ['view', 'create', 'update', 'delete', 'export'],
```

2. Tạo permission vào platform DB:

```php
\App\Core\Permission\Permission::firstOrCreate([
    'module' => 'reports',
    'permission' => 'export',
]);
```

3. Cấp quyền trong giao diện Group/Role.

## 5. Route Và Model Binding: Lỗi Hay Gặp

### Lỗi "No database selected"

Thông báo:

```text
SQLSTATE[3D000]: Invalid catalog name: 1046 No database selected
Connection: tenant
```

Nguyên nhân:

- Model dùng `$connection = 'tenant'`.
- Laravel query model trước khi `tenant.db` connect DB.
- Thường xảy ra với route implicit binding: `/{guest}` + `Guest $guest`.

Fix trong hệ thống:

- `bootstrap/app.php` đã ép `UseTenantDatabase` chạy trước `SubstituteBindings`.
- Mọi route tenant vẫn phải khai báo middleware `tenant.db`.

Nếu module mới vẫn lỗi, kiểm tra:

```bash
php artisan route:list --path=backend/tenants/{tenant}/modules/{module}
php artisan optimize:clear
```

Và đảm bảo route có:

```php
Route::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:{module},Tenants/{tenant_studly}/{module_studly}'])
```

### Lỗi "Module chưa được bật"

Kiểm tra module:

```bash
php artisan tenant:module-list {tenant}
```

Bật/tắt module:

```bash
php artisan tenant:module-enable {tenant} {module}
php artisan tenant:module-disable {tenant} {module}
php artisan tenant:module-toggle {tenant} {module} --enabled=1
```

Kiểm tra path trong bảng `modules.path` phải trùng với path middleware:

```text
Tenants/{tenant_studly}/{module_studly}
```

### Menu không hiện

Kiểm tra:

- Module enabled.
- Login bằng tenant user/organizer, không phải platform owner.
- `modules.config.menu` có `route` hoặc `items`, hoặc `module.php` có `menu` / `menu_items`.
- Route name tồn tại trong `php artisan route:list`.
- User có permission module.
- `config` trong bảng `modules` có override menu sai không.
- Nếu module đã có trong `tenant:module-list` nhưng sidebar không hiện, kiểm tra route:

```bash
php artisan route:list --name={route_name}
```

Nếu route không tồn tại, menu sẽ bị `ModuleManager` lọc ra.

Nếu chỉ một vài module hiện dù `tenant:module-list` có nhiều module, thường là các module hiện có `menu` hợp lệ, còn module ẩn chưa có `config.menu` hoặc `module.php['menu']`.

### Báo `Module chưa khả dụng`

Thông báo này đến từ middleware `module.enabled`. Module đã enabled trong platform chưa chắc đã pass middleware; middleware còn kiểm tra tenant của user và path trong route.

Kiểm tra theo thứ tự:

```php
auth()->user()->tenant?->slug;

$tenant = \App\Core\Tenant\Tenant::where('slug', '{tenant}')->firstOrFail();

\App\Core\Module\Module::where('tenant_id', $tenant->id)
    ->where('slug', '{module}')
    ->first(['slug', 'path', 'is_enabled', 'config'])
    ->toArray();
```

Route đang truy cập phải có middleware path khớp `modules.path`:

```text
module.enabled:{module},Tenants/{tenant_studly}/{module_studly}
modules.path = Tenants/{tenant_studly}/{module_studly}
```

Nếu tenant mới copy module từ tenant cũ mà vẫn truy cập route global như `backend/project` hoặc route name `backend_project`, request có thể đang vào route tenant cũ. Khi đó middleware nhận path cũ, ví dụ `Tenants/TrungTamXucTienHaNoi/Projects`, nên user tenant mới bị chặn.

Debug route:

```bash
php artisan route:list | grep {module}
php artisan route:list --path=backend/tenants/{tenant}/modules/{module} -v
```

### Báo lỗi 500 `Undefined variable $selectedMainMenu`

Lỗi này thường xảy ra khi route closure render view extend `backend.index` nhưng không truyền biến cho sidebar.

Sửa closure:

```php
Route::get('/', fn () => view('tenant-{tenant}-{module}::index', [
    'selectedMainMenu' => '{module}',
    'selectedSubMenu' => '',
]))->name('index');
```

Hoặc dùng controller kế thừa `App\Http\Controllers\Controller` và set:

```php
protected string $selectedMainMenu = '{module}';
```

## 6. Checklist Tạo Tenant Mới

1. Tạo database vật lý cho tenant.
2. Chạy `php artisan tenant:create ...`.
3. Nếu module đã code sẵn, import/register module vào bảng `modules`; nếu module mới hoàn toàn, chạy `tenant:module-create`.
4. Tạo/cập nhật permission module và gán cho role `organizer`.
5. Đảm bảo mỗi module cần hiện sidebar có `config.menu` hoặc `module.php` có `menu` / `menu_items`.
6. Chạy migration module nếu module có file migration.
7. Nếu tenant DB đã có sẵn user, chạy `php artisan tenant:sync-users {tenant}`.
8. Tạo organizer/user nếu chưa có.
9. Đảm bảo route module dùng prefix scoped `backend/tenants/{tenant}/modules/{module}`.
10. Đảm bảo route name module dùng prefix scoped `tenant.{tenant_route_key}.{module_route_key}.`.
11. Đảm bảo `module.php['menu']['route']` trỏ tới route scoped đang tồn tại.
12. Chạy `php artisan optimize:clear`.
13. Login bằng tenant organizer, không dùng platform owner để kiểm tra module nghiệp vụ.
14. Kiểm tra menu module và mở từng route chính.
15. Tạo group permission cho user thường.
16. Tạo user thường và test các route create/edit/delete.

## 7. Checklist Tạo Module Mới

1. `php artisan tenant:module-create {tenant} "{module_name}" --slug={module}`, hoặc tự tạo folder và register module thủ công vào bảng `modules`.
2. Sửa `module.php`: name, slug, namespace view, menu, permissions.
3. Sửa `routes.php`: prefix có tenant, route name có tenant, middleware đúng slug/path.
4. Tạo model với connection `tenant`.
5. Tạo migration trong `Database/migrations` nếu module cần schema mới.
6. Chạy `php artisan tenant:module-migrate {tenant} {module} --force` sau khi đã có file migration; nếu không có migration thì bỏ qua.
7. Tạo controller và check permission.
8. Tạo view trong `Resources/views`.
9. Chạy `php artisan route:list` và `php artisan optimize:clear`.
10. Gán permission cho role/group cần dùng.
11. Login user thường và test CRUD.

## 8. Quy Ước Đặt Tên

Quy ước trong phần này là quy ước chuẩn cho module tenant mới. Một số module cũ của tenant đầu đang dùng route/name global kiểu `backend_project`, `backend/project`, `backend_vrtour_*`; đó là legacy, không dùng làm mẫu khi copy sang tenant khác.

### Tenant

```text
Tenant name: Demo Tenant
Tenant slug: demo-tenant
Tenant studly: DemoTenant
DB: tenant_demo
```

Quy tắc:

- `Tenant slug` dùng kebab-case, ví dụ `hoa-lac`.
- `Tenant studly` dùng `Str::studly($tenant->slug)`, ví dụ `HoaLac`.
- Folder source tenant là `app/TenantModules/Tenants/{tenant_studly}`.
- Không tự đặt folder khác với `Str::studly($tenant->slug)` nếu không có lý do đặc biệt, vì `modules.path` và middleware path sẽ dễ lệch.

### Module

```text
Module name: Reports
Module slug: reports
Folder: app/TenantModules/Tenants/DemoTenant/Reports
Route prefix: backend/tenants/demo-tenant/modules/reports
Route name prefix: tenant.demo_tenant.reports.
View namespace: tenant-demo-tenant-reports
Permission keys: reports.view, reports.create, reports.update, reports.delete
```

Với tenant `hoa-lac`, module `projects`, quy ước đúng là:

```text
Folder: app/TenantModules/Tenants/HoaLac/Projects
modules.slug: projects
modules.path: Tenants/HoaLac/Projects
Middleware: module.enabled:projects,Tenants/HoaLac/Projects
Route prefix: backend/tenants/hoa-lac/modules/projects
Route name prefix: tenant.hoa_lac.projects.
Index route name: tenant.hoa_lac.projects.index
Menu route: tenant.hoa_lac.projects.index
View namespace: tenant-hoa-lac-projects
```

Không dùng cho tenant mới:

```text
Route prefix: backend/project
Route prefix: backend/modules/projects
Route name: backend_project
Route name: backend_project_create
Route name: backend_vrtour_skin_index
```

Các route/name trên không chứa tenant nên chỉ phù hợp với code legacy một tenant. Khi copy module sang tenant khác, phải đổi sang route scoped; nếu không menu hoặc URL sẽ chạy nhầm route tenant cũ.

## 9. Lệnh Thường Dùng

Các lệnh dưới đây dùng placeholder. Phải thay `{tenant}`, `{module}`, `{tenant_db}`... bằng giá trị thật trước khi chạy.

```bash
# Tạo tenant
php artisan tenant:create "{tenant_name}" --slug={tenant} --db={tenant_db} --organizer-email={organizer_email}

# Tạo module mới
php artisan tenant:module-create {tenant} "{module_name}" --slug={module}

# Chạy migration module, chỉ dùng khi module đã có file migration
php artisan tenant:module-migrate {tenant} {module} --force

# Liệt kê module
php artisan tenant:module-list {tenant}

# Bật/tắt module
php artisan tenant:module-enable {tenant} {module}
php artisan tenant:module-disable {tenant} {module}

# Sync users có sẵn từ tenant DB về platform DB
php artisan tenant:sync-users {tenant}

# Clear cache
php artisan optimize:clear
```

Ví dụ minh họa với tenant mẫu `demo-tenant` và module mẫu `reports`:

```bash
php artisan tenant:create "Demo Tenant" --slug=demo-tenant --db=tenant_demo --organizer-email=organizer.demo@example.com
php artisan tenant:module-create demo-tenant "Reports" --slug=reports
# Chỉ chạy dòng migrate này nếu module reports đã có file migration.
php artisan tenant:module-migrate demo-tenant reports --force
php artisan tenant:module-list demo-tenant
php artisan tenant:sync-users demo-tenant
```

## 10. Nguyên Tắc Khi Viết Module

- Không query data nghiệp vụ trên DB trung tâm.
- Mọi model nghiệp vụ phải dùng connection `tenant`.
- Mọi route tenant phải có `tenant.db` và `module.enabled`.
- Mọi route module tenant mới phải scope theo tenant trong cả URI và route name.
- Không dùng route model binding nếu route không chạy `tenant.db`.
- Permission nên check trong controller, không chỉ ẩn/hiện button ở view.
- Migration tenant module phải đặt trong module và chỉ chạy qua `tenant:module-migrate` sau khi file migration đã tồn tại.
- Module muốn hiện sidebar phải khai báo menu rõ ràng: `modules.config.menu`, `module.php['menu']`, hoặc `module.php['menu_items']`.
- Menu route phải là route name đang tồn tại; nếu route không tồn tại, menu sẽ bị lọc.
- Không copy route/name global từ tenant legacy sang tenant mới.
- View namespace nên lấy từ `module.php`, không hardcode lung tung.
- Sau khi đổi route/view/config, chạy `php artisan optimize:clear`.
