<?php

namespace App\Http\Controllers\Backend\Core;

use App\Core\Tenant\Tenant;
use App\Http\Controllers\Controller;
use App\Core\Tenant\TenantProvisioner;
use App\Models\User;
use App\Core\Tenant\TenantDatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class TenantController extends Controller
{
    protected string $selectedMainMenu = 'tenants';

    public function __construct(private readonly TenantProvisioner $provisioner)
    {
        parent::__construct();
    }

    public function index()
    {
        return view('backend.core.tenants.index', [
            'tenants' => Tenant::with(['database'])->withCount(['modules', 'users'])->orderBy('name')->paginate(20),
        ]);
    }

    public function create()
    {
        return view('backend.core.tenants.form', [
            'tenant' => new Tenant(['status' => Tenant::STATUS_ACTIVE]),
        ]);
    }

    public function edit(Tenant $tenant)
    {
        $tenant->load('database');

        return view('backend.core.tenants.form', compact('tenant'));
    }

    public function store(Request $request)
    {
        $tenant = new Tenant();

        return $this->save($request, $tenant);
    }

    public function update(Request $request, Tenant $tenant)
    {
        return $this->save($request, $tenant);
    }

    public function testConnectionInput(Request $request, TenantDatabaseManager $databaseManager)
    {
        $validated = $request->validate([
            'tenant_id' => ['nullable', 'exists:tenants,id'],
            'database.driver' => ['required', 'string', 'max:32'],
            'database.host' => ['required', 'string', 'max:255'],
            'database.port' => ['required', 'integer', 'min:1', 'max:65535'],
            'database.database_name' => ['required', 'string', 'max:255'],
            'database.username' => ['required', 'string', 'max:255'],
            'database.password' => ['nullable', 'string'],
            'database.clear_password' => ['nullable', 'boolean'],
        ]);

        $database = $validated['database'];

        if (! empty($database['clear_password'])) {
            $database['password'] = null;
        } elseif (blank($database['password'] ?? null) && ! blank($validated['tenant_id'] ?? null)) {
            $tenant = Tenant::with('database')->find($validated['tenant_id']);
            $database['password'] = $tenant?->database?->password;
        }

        try {
            $result = $databaseManager->testConfig($database);

            return response()->json([
                'success' => true,
                'message' => 'Kết nối DB thành công: ' . $result['database'] . ' (' . $result['duration_ms'] . 'ms).',
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Kết nối DB thất bại: ' . $exception->getMessage(),
            ], 422);
        }
    }

    private function save(Request $request, Tenant $tenant)
    {
        $existingOrganizerId = null;
        if ($tenant->exists && $request->filled('organizer.email')) {
            $existingOrganizerId = User::where('email', $request->input('organizer.email'))
                ->where('tenant_id', $tenant->id)
                ->value('id');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('tenants')->ignore($tenant->id)],
            'status' => ['required', Rule::in([Tenant::STATUS_ACTIVE, Tenant::STATUS_INACTIVE])],
            'database.driver' => ['required', 'string', 'max:32'],
            'database.host' => ['required', 'string', 'max:255'],
            'database.port' => ['required', 'integer', 'min:1', 'max:65535'],
            'database.database_name' => ['required', 'string', 'max:255'],
            'database.username' => ['required', 'string', 'max:255'],
            'database.password' => ['nullable', 'string'],
            'database.clear_password' => ['nullable', 'boolean'],
            'create_organizer' => ['nullable', 'boolean'],
            'organizer.name' => ['required_if:create_organizer,1', 'nullable', 'string', 'max:255'],
            'organizer.email' => [
                'required_if:create_organizer,1',
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($existingOrganizerId),
            ],
            'organizer.password' => ['required_if:create_organizer,1', 'nullable', 'string', 'min:6'],
        ]);

        $this->provisioner->provision([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?: Str::slug($validated['name']),
            'status' => $validated['status'],
            'database' => $validated['database'],
            'create_organizer' => $request->boolean('create_organizer'),
            'organizer' => $validated['organizer'] ?? [],
        ], $tenant);

        return redirect()->route('backend_core_tenants')->with('success', 'Đã lưu tenant.');
    }
}
