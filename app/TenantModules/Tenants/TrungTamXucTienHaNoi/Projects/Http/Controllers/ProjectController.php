<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Projects\Exports\ProjectsExport;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Projects\Models\District;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Projects\Models\Project;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Projects\Models\ProjectIndustry;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Projects\Models\ProjectType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelType;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->selectedMainMenu = 'projects';
        parent::__construct();
        View::share('selectedMainMenu', 'projects');
    }

    public function index(Request $request)
    {
        $this->authorizeModule('view');

        $filter = [
            'name' => $request->get('name', ''),
            'type_number' => $request->filled('type_number') ? $request->integer('type_number') : null,
            'industry_number' => $request->filled('industry_number') ? $request->integer('industry_number') : null,
            'district_id' => $request->filled('district_id') ? $request->integer('district_id') : null,
        ];

        $projects = Project::query()
            ->with(['type', 'industry', 'districts', 'draft', 'parent'])
            ->visibleFor($request->user())
            ->filterAdmin($request)
            ->orderBy('is_pinned', 'desc')
            ->orderByRaw('CASE WHEN pin_order IS NULL THEN 999999 ELSE pin_order END ASC')
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('legacy-projects::index', [
            'projects' => $projects,
            'filter' => $filter,
            'types' => ProjectType::all()->pluck('name', 'id'),
            'industries' => ProjectIndustry::all()->pluck('name', 'id'),
            'districts' => District::all()->pluck('name', 'id'),
            'canCreate' => $this->canModule('create'),
            'canUpdate' => $this->canModule('update'),
            'canDelete' => $this->canModule('delete'),
        ]);
    }

    public function create()
    {
        $this->authorizeModule('create');

        return $this->form(new Project());
    }

    public function edit(int|string $project)
    {
        $this->authorizeModule('update');

        return $this->form(Project::findOrFail($project));
    }

    public function save(Request $request, int|string|null $project = null)
    {
        $project = $project ? Project::findOrFail($project) : new Project();
        $this->authorizeModule($project->exists ? 'update' : 'create');

        $locales = config('app.locales', ['vi' => 'Tiếng Việt', 'en' => 'Tiếng Anh']);
        $firstLocale = array_key_first($locales);

        $rules = [
            "name.{$firstLocale}" => ['required', 'max:255'],
            "short_desc.{$firstLocale}" => ['nullable'],
            "description.{$firstLocale}" => ['nullable'],
            'banner_image' => ['nullable', 'max:2048'],
            'detail_image' => ['nullable', 'max:2048'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'boundary' => ['nullable', 'string'],
            'area' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'integer'],
            'type_number' => ['nullable', 'integer', 'exists:tenant.project_types,id'],
            'industry_number' => ['nullable', 'integer', 'exists:tenant.project_industries,id'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'link' => ['nullable', 'url'],
            'location_image' => ['nullable', 'max:2048'],
            'districts' => ['nullable', 'array'],
            'districts.*' => ['integer', 'exists:tenant.districts,id'],
            'link_vrtour' => ['nullable', 'url'],
            'link_sand_table' => ['nullable', 'url'],
            'layout_id' => ['nullable', 'integer', 'min:1', 'max:3'],
            'is_invest' => ['nullable', 'boolean'],
            'is_pinned' => ['nullable', 'boolean'],
            'pin_order' => ['nullable', 'integer', 'min:1'],
            'is_hidden' => ['nullable', 'boolean'],
        ];

        foreach (array_keys($locales) as $locale) {
            $rules["name.{$locale}"] ??= ['nullable', 'max:255'];
            $rules["slug.{$locale}"] = ['nullable', 'alpha_dash', 'max:255'];
            $rules["short_desc.{$locale}"] ??= ['nullable'];
            $rules["description.{$locale}"] ??= ['nullable'];
            $rules["design_short_desc.{$locale}"] = ['nullable'];
            $rules["legal_short_desc.{$locale}"] = ['nullable'];
        }

        $validated = $request->validate($rules);
        $project->fill(collect($validated)->except(['name', 'slug', 'short_desc', 'description', 'design_short_desc', 'legal_short_desc', 'districts'])->all());

        foreach (['is_invest', 'is_pinned', 'is_hidden'] as $field) {
            $project->{$field} = $request->boolean($field);
        }

        foreach (['name', 'short_desc', 'description', 'design_short_desc', 'legal_short_desc'] as $field) {
            $project->setTranslations($field, $this->translationsFromRequest($request, $field, array_keys($locales)));
        }

        $project->setTranslations('slug', $this->slugTranslations($request, $project, array_keys($locales), $firstLocale));

        if (! $project->exists) {
            $project->approval_level = 2;
            $project->max_approval = 2;
            $project->is_draft = false;
            $project->status = 'approved';
        }

        $project->layout_id = $project->layout_id ?: 1;
        $project->vrtour_code = $project->vrtour_code ?: 'vrtour-' . $project->getTranslation('slug', $firstLocale);
        $project->link = $request->input('link') ?: url('/project-detail/' . $project->getTranslation('slug', $firstLocale));
        $project->save();
        $project->districts()->sync($request->input('districts', []));

        return redirect()->route('backend_project_edit', $project)->with('success', 'Lưu dữ liệu thành công');
    }

    public function saveDataIndex(Request $request)
    {
        $this->authorizeModule('update');

        foreach ($request->input('update', []) as $id => $values) {
            Project::whereKey($id)->update($values);
        }

        return redirect()->route('backend_project')->with('success', 'Cập nhật thông tin thành công');
    }

    public function delete(int|string $project)
    {
        $this->authorizeModule('delete');
        $project = Project::findOrFail($project);
        $project->districts()->detach();
        $project->delete();

        return redirect()->route('backend_project')->with('success', 'Xóa dự án thành công');
    }

    public function bulkDelete(Request $request)
    {
        $this->authorizeModule('delete');

        $ids = $request->input('ids', []);
        Project::whereIn('id', $ids)->get()->each(function (Project $project) {
            $project->districts()->detach();
            $project->delete();
        });

        return response()->json(['success' => true]);
    }

    public function exportCsv()
    {
        $this->authorizeModule('view');

        return Excel::download(new ProjectsExport(), 'projects.xlsx', ExcelType::XLSX);
    }

    private function form(Project $project)
    {
        return view('legacy-projects::form', [
            'project' => $project,
            'types' => ProjectType::all()->pluck('name', 'id'),
            'industries' => ProjectIndustry::all()->pluck('name', 'id'),
            'districts' => District::all()->pluck('name', 'id'),
            'units' => Project::UNIT_OPTIONS,
            'layouts' => Project::LAYOUTS,
            'canCreate' => $this->canModule('create'),
            'canUpdate' => $this->canModule('update'),
            'canDelete' => $this->canModule('delete'),
        ]);
    }

    private function translationsFromRequest(Request $request, string $field, array $locales): array
    {
        $values = [];
        foreach ($locales as $locale) {
            $values[$locale] = $request->input("{$field}.{$locale}");
        }

        return $values;
    }

    private function slugTranslations(Request $request, Project $project, array $locales, string $firstLocale): array
    {
        $slugs = [];

        foreach ($locales as $locale) {
            $name = $request->input("name.{$locale}") ?: $request->input("name.{$firstLocale}");
            $base = Str::slug($request->input("slug.{$locale}") ?: $name);
            $base = $base ?: Str::random(8);
            $slug = $base;
            $counter = 1;

            while (Project::where("slug->{$locale}", $slug)->when($project->exists, fn ($q) => $q->whereKeyNot($project->id))->exists()) {
                $slug = $base . '-' . $counter++;
            }

            $slugs[$locale] = $slug;
        }

        return $slugs;
    }

    private function authorizeModule(string $permission): void
    {
        abort_unless($this->canModule($permission), 403, self::MESSAGE_UNAUTHORIZED);
    }

    private function canModule(string $permission): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isPlatformOwner() || $user?->hasPermission('projects.' . $permission));
    }
}
