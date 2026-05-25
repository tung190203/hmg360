<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\InvestmentGuide\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Libs\DataGrid;
use App\Libs\Util;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Category;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\InvestmentGuide;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use andreskrey\Readability\Readability;
use andreskrey\Readability\Configuration;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Group;
use \GuzzleHttp\Psr7\Uri;
use \GuzzleHttp\Psr7\UriResolver;
use Illuminate\Support\Facades\Log;

class InvestMentGuideController extends Controller
{
    private $investment_guide;
    public function __construct(InvestmentGuide $investment_guide)
    {
        $this->investment_guide = $investment_guide;
        $this->selectedMainMenu = 'investment_guide';
        parent::__construct();
        if (!Gate::allows('investment_guide')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
    }

    public function index(Request $request)
    {
        $language = App::getLocale();
        $this->selectedSubMenu('investment_guide');
        $category = new Category();
        $category->getParentArray();

        $filter['name'] = $request->get('name', '');
        $filter['cat_id'] = $request->get('cat_id', 0);
        $filter['status'] = $request->get('status', -1);
        $query = $this->investment_guide->with(['category', 'user'])
        ->visibleFor(auth('web')->user())
        ->orderBy('id', 'desc');
        if ($filter['name'] !== '') {
            $query->where('name', 'like', '%' . $filter['name'] . '%');
        }
        if ($filter['cat_id'] > 0) {
            $all_cat = $category->getAllCatStr($filter['cat_id']);
            $all_cat[] = (int) $filter['cat_id'];
            $query->whereIn('cat_id', $all_cat);
        }
        if ($filter['status'] > -1) {
            if ($filter['status'] == 2) {
                $query->onlyTrashed();
            } else {
                $query->where('status', $filter['status']);
            }
        }

        $user = auth('web')->user();

        $scope = $user->getScope('investment_guide');
        if (!empty($scope)) {
            $query->whereIn('id', $scope);
        }

        $investment_guides = $query->paginate(20);
        $options['categories'] = Category::makeListCategoryForInvestMent(0, '', $filter['cat_id']);
        $options['status'] = Util::makeHTMLOptions(InvestmentGuide::STATUS_ARRAY, $filter['status']);
        $option_categories = Category::makeArrayListCategory(0, Category::CATEGORY_TYPE_POST);

        $paginate = 20;
        $route_name = 'backend_investment_guide_edit';
        $option_column_button = InvestmentGuide::makeOptionColumnButton();

        $clsDataGrid = new DataGrid();
        $clsDataGrid->setLinkEdit($route_name);
        // $clsDataGrid->addColumnLabel("name", "Name", "width='20%' nowrap");
        $clsDataGrid->addColumnLabel("name", "Tên dự án", "width='10%' nowrap", 1, '', function ($col, $val, $id, $row) {
            $html = e($row->name);
    
            // Hiển thị nhãn trạng thái
            if ($row->is_draft) {
                $html .= " <span class='badge bg-warning'>Bản chỉnh sửa</span>";
            }
    
            // Hiển thị trạng thái duyệt
            if ($row->status_approve === 'pending') {
                if ($row->approval_level == 0) $html .= " <span class='badge bg-secondary'>Chờ duyệt cấp 1</span>";
                elseif ($row->approval_level == 1) $html .= " <span class='badge bg-primary'>Chờ duyệt cấp 2</span>";
            } elseif ($row->status_approve === 'approved') {
                $html .= " <span class='badge bg-success'>Đã duyệt</span>";
            } elseif ($row->status_approve === 'rejected') {
                $html .= " <span class='badge bg-danger'>Từ chối</span>";
            }
    
            return $html;
        });
        $clsDataGrid->addColumnImage("image", "Image", "", "width='10%' nowrap");
        $clsDataGrid->addColumnSelect("status", "Hiển thị", "width='15%'", ["Không", "Có"]);
        $clsDataGrid->addColumnText("priority", "STT", "width='10%'");
        $clsDataGrid->addColumnDate("created_at", "Ngày tạo", "width='15%' nowrap ", 'd-m-Y');
        $clsDataGrid->addColumnButton('id', '&nbsp', $option_column_button, "width='5%' nowrap ");

        $dataGrid = $clsDataGrid->showDataGrid($investment_guides, $paginate, $investment_guides->total());

        return view('ttxt-investment::investment_guide.index', compact('investment_guides', 'filter', 'options', 'dataGrid'));
    }

    public function saveDataIndex(Request $request)
    {
        // foreach($request->ids as $id) {
        //     $p = InvestmentGuide::find($id);
        //     if(!Gate::allows('investment_guide/edit', $p)) {
        //         abort(403);
        //     }
        // }

        $update = $request->get('update', []);
        foreach ($update as $key => $value) {
            InvestmentGuide::where('id', $key)->update($value);
        }
        return redirect()->route('backend_investment_guide')->with('success', 'Cập nhật thông tin thành công');
    }

    public function edit(InvestmentGuide $investment_guide)
    {
        if($investment_guide->exists && !Gate::allows('investment_guide/edit', $investment_guide)) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        if(!$investment_guide->exists && !Gate::allows('investment_guide/add')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $option_categories = Category::makeListCategoryForInvestMent(0, '', $investment_guide->cat_id);
        $option_projects = Project::makeListProjectArray();
        return view('ttxt-investment::investment_guide.create', compact('investment_guide', 'option_categories', 'option_projects'));
    }

public function save(InvestmentGuide $investment_guide, Request $request)
{
    $user = auth('web')->user();

    // ==== PHÂN QUYỀN ====
    if ($investment_guide->exists && !Gate::allows('investment_guide/edit', $investment_guide)) {
        abort(403, self::MESSAGE_UNAUTHORIZED);
    }
    if (!$investment_guide->exists && !Gate::allows('investment_guide/add')) {
        abort(403, self::MESSAGE_UNAUTHORIZED);
    }

    // ==== VALIDATE ====
    $validated = $request->validate([
        'name' => 'required|array',
        'name.vi' => 'required|string',
        'name.*' => 'nullable|string',
        'slug' => 'nullable|array',
        'slug.*' => 'nullable|alpha_dash',
        'cat_id' => 'nullable|integer',
        'relic_id' => 'nullable|integer',
        'image' => 'nullable|string',
        'priority' => 'nullable|integer',
        'description' => 'nullable|array',
        'description.*' => 'nullable|string',
        'content' => 'nullable|array',
        'content.*' => 'nullable|string',
        'source' => 'nullable|string',
        'is_hot' => 'nullable|boolean',
        'view_num' => 'nullable|integer',
        'meta_title' => 'nullable|array',
        'meta_title.*' => 'nullable|string',
        'meta_keywords' => 'nullable|array',
        'meta_keywords.*' => 'nullable|string',
        'meta_description' => 'nullable|array',
        'meta_description.*' => 'nullable|string',
        'language' => 'nullable|string',
        'project_type' => 'nullable|string',
        'project_id' => 'nullable|integer',
        'projects' => 'nullable|array',
        'projects.*' => ['integer', Rule::exists(Project::class, 'id')],
        
        // Cập nhật Validation cho files (URL)
        'files' => 'nullable|array',
        'files.*' => 'nullable|array', // Mảng URL tệp cho từng locale
        'files.*.*' => 'nullable|string',

        // Cập nhật Validation cho files_descs (Mô tả)
        'files_descs' => 'nullable|array',
        'files_descs.*' => 'nullable|array', // Mảng mô tả cho từng locale
        'files_descs.*.*' => 'nullable|string',

        'published_at' => 'nullable|date',
        'extracted_text' => 'nullable|string',
        'extracted_summary' => 'nullable|string',
        'extracted_language' => 'nullable|string|max:10',
        'extracted_at' => 'nullable|date',
    ]);

    try {
        // Hàm xử lý chung để chuyển mảng dữ liệu đa ngôn ngữ thành định dạng lưu trữ
        $processFileTranslations = function (Request $request, string $requestFieldName, string $modelFieldName) {
            $inputData = $request->input($requestFieldName);
            $translations = [];

            if (is_array($inputData)) {
                foreach ($inputData as $locale => $items) {
                    if (is_array($items)) {
                        // Lọc bỏ giá trị rỗng và trim
                        $cleanedItems = array_map('trim', array_filter($items));

                        if ($modelFieldName === 'short_file_descs') {
                            // short_file_descs cần lưu trữ mảng (JSON string)
                            $translations[$locale] = json_encode($cleanedItems);
                        } else {
                            // files (URL) cần lưu trữ chuỗi phân cách bởi dấu chấm phẩy
                            $translations[$locale] = implode(';', $cleanedItems);
                        }
                    }
                }
            }
            return $translations;
        };
        
        // ==== KHỞI TẠO BẢN MỚI ====
        if (!$investment_guide->exists) {
            // Xử lý dữ liệu đa ngôn ngữ
            $translatableData = [];
            foreach ($investment_guide->translatable as $field) {
                if (isset($validated[$field])) {
                    $translatableData[$field] = $validated[$field];
                }
            }

            $investment_guide->fill(array_diff_key($validated, array_flip($investment_guide->translatable)));
            
            // Set translatable fields (Trừ các trường files/descs)
            foreach ($translatableData as $field => $translations) {
                if ($field !== 'files' && $field !== 'short_file_descs') {
                    $investment_guide->setTranslations($field, $translations);
                }
            }

            // Xử lý files và short_file_descs dưới dạng đa ngôn ngữ
            $fileTranslations = $processFileTranslations($request, 'files', 'files');
            if (!empty($fileTranslations)) {
                $investment_guide->setTranslations('files', $fileTranslations);
            }
            
            $descsTranslations = $processFileTranslations($request, 'files_descs', 'short_file_descs');
            if (!empty($descsTranslations)) {
                $investment_guide->setTranslations('short_file_descs', $descsTranslations);
            }
            
            // ... (Phần logic khác giữ nguyên) ...

            $investment_guide->approval_level = $user->is_super_admin ? 2 : ($user->is_approve ? 1 : 0);
            $investment_guide->max_approval = 2;
            $investment_guide->is_draft = false;
            $investment_guide->status_approve = $user->is_super_admin ? 'approved' : ($user->is_approve ? 'pending' : 'pending');
            $investment_guide->status = $user->is_super_admin ? InvestmentGuide::STATUS_ACTIVE : InvestmentGuide::STATUS_INACTIVE;
            $investment_guide->language = App::getLocale();
            $investment_guide->project_type = $validated['project_type'] ?? null;
            $investment_guide->project_id = $validated['project_id'] ?? 0;

            // Sinh slug unique cho ngôn ngữ mặc định
            $defaultLocale = config('app.locale');
            $slug = Str::slug($investment_guide->getTranslation('slug', $defaultLocale) ?: $investment_guide->getTranslation('name', $defaultLocale));
            $originalSlug = $slug;
            $counter = 1;
            while (InvestmentGuide::where("slug->{$defaultLocale}", $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }
            $investment_guide->setTranslation('slug', $defaultLocale, $slug);

            $investment_guide->save();

            if ($request->filled('projects')) {
                $investment_guide->projects()->syncWithPivotValues(
                    $request->input('projects'),
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }

            if (Gate::allows('investment_guide/add')) {
                $this->addInvestmentGuideToScope($user, $investment_guide->id);
            }
        } else {
            // ==== ĐÃ TỒN TẠI ====
            if ($user->is_super_admin) {
                // --- SUPER ADMIN MERGE NHÁP ---
                $main = $investment_guide->parent_id
                    ? InvestmentGuide::find($investment_guide->parent_id)
                    : $investment_guide;

                // Xử lý dữ liệu đa ngôn ngữ
                $translatableData = [];
                foreach ($investment_guide->translatable as $field) {
                    if (isset($validated[$field])) {
                        $translatableData[$field] = $validated[$field];
                    }
                }

                $main->fill(array_diff_key($validated, array_flip($investment_guide->translatable)));
                
                // Set translatable fields (Trừ các trường files/descs)
                foreach ($translatableData as $field => $translations) {
                     if ($field !== 'files' && $field !== 'short_file_descs') {
                        $main->setTranslations($field, $translations);
                    }
                }

                // Xử lý files và short_file_descs dưới dạng đa ngôn ngữ
                $fileTranslations = $processFileTranslations($request, 'files', 'files');
                if (!empty($fileTranslations)) {
                    $main->setTranslations('files', $fileTranslations);
                }
                
                $descsTranslations = $processFileTranslations($request, 'files_descs', 'short_file_descs');
                if (!empty($descsTranslations)) {
                    $main->setTranslations('short_file_descs', $descsTranslations);
                }

                $main->approval_level = $main->max_approval;
                $main->status_approve = 'approved';
                $main->is_draft = false;
                $main->parent_id = null;

                // Slug unique (remove -draft)
                $defaultLocale = config('app.locale');
                $currentSlug = $main->getTranslation('slug', $defaultLocale) ?: $main->getTranslation('name', $defaultLocale);
                $slug = preg_replace('/-draft$/', '', Str::slug($currentSlug));
                $originalSlug = $slug;
                $counter = 1;
                while (InvestmentGuide::where("slug->{$defaultLocale}", $slug)->where('id', '<>', $main->id)->exists()) {
                    $slug = $originalSlug . '-' . $counter++;
                }
                $main->setTranslation('slug', $defaultLocale, $slug);

                // BỎ PHẦN LƯU files CŨ (SINGLE-LANGUAGE)
                // if ($request->filled('files_images')) {
                //     $main->files = implode(';', array_map('trim', $request->files_images));
                // }

                $main->save();

                if ($request->filled('projects')) {
                    $main->projects()->sync($request->input('projects'));
                } else {
                    $main->projects()->detach();
                }

                // Xoá nháp
                $drafts = InvestmentGuide::where('parent_id', $main->id)->get();
                foreach ($drafts as $draft) {
                    $this->removeInvestmentGuideFromScope($draft->id);
                    $draft->delete();
                }

                $investment_guide = $main;
            } else {
                // --- NGƯỜI DÙNG THƯỜNG ---
                if ($investment_guide->status_approve === 'approved' && !$investment_guide->is_draft) {
                    // Tạo bản nháp mới
                    $draft = $investment_guide->replicate();
                    
                    // Xử lý dữ liệu đa ngôn ngữ
                    $translatableData = [];
                    foreach ($investment_guide->translatable as $field) {
                        if (isset($validated[$field])) {
                            $translatableData[$field] = $validated[$field];
                        }
                    }

                    $draft->fill(array_diff_key($validated, array_flip($investment_guide->translatable)));
                    
                    // Set translatable fields (Trừ các trường files/descs)
                    foreach ($translatableData as $field => $translations) {
                        if ($field !== 'files' && $field !== 'short_file_descs') {
                            $draft->setTranslations($field, $translations);
                        }
                    }

                    // Xử lý files và short_file_descs dưới dạng đa ngôn ngữ
                    $fileTranslations = $processFileTranslations($request, 'files', 'files');
                    if (!empty($fileTranslations)) {
                        $draft->setTranslations('files', $fileTranslations);
                    }
                    
                    $descsTranslations = $processFileTranslations($request, 'files_descs', 'short_file_descs');
                    if (!empty($descsTranslations)) {
                        $draft->setTranslations('short_file_descs', $descsTranslations);
                    }

                    $draft->is_draft = true;
                    $draft->status_approve = 'pending';
                    $draft->approval_level = $user->is_approve ? 1 : 0;
                    $draft->parent_id = $investment_guide->id;
                    $draft->status = InvestmentGuide::STATUS_INACTIVE;
                    
                    $defaultLocale = config('app.locale');
                    $draftSlug = Str::slug($draft->getTranslation('slug', $defaultLocale) ?: $draft->getTranslation('name', $defaultLocale)) . '-draft';
                    $draft->setTranslation('slug', $defaultLocale, $draftSlug);

                    // BỎ PHẦN LƯU files CŨ (SINGLE-LANGUAGE)
                    // if ($request->filled('files_images')) {
                    //     $draft->files = implode(';', array_map('trim', $request->files_images));
                    // }

                    $draft->save();

                    if ($request->filled('projects')) {
                        $draft->projects()->sync($request->input('projects'));
                    }

                    if (Gate::allows('investment_guide/add')) {
                        $this->addInvestmentGuideToScope($user, $draft->id);
                    }

                    $investment_guide = $draft;
                } else {
                    // Cập nhật bản hiện tại / nháp
                    $translatableData = [];
                    foreach ($investment_guide->translatable as $field) {
                        if (isset($validated[$field])) {
                            $translatableData[$field] = $validated[$field];
                        }
                    }

                    $investment_guide->fill(array_diff_key($validated, array_flip($investment_guide->translatable)));
                    
                    // Set translatable fields (Trừ các trường files/descs)
                    foreach ($translatableData as $field => $translations) {
                         if ($field !== 'files' && $field !== 'short_file_descs') {
                            $investment_guide->setTranslations($field, $translations);
                        }
                    }

                    // Xử lý files và short_file_descs dưới dạng đa ngôn ngữ
                    $fileTranslations = $processFileTranslations($request, 'files', 'files');
                    if (!empty($fileTranslations)) {
                        $investment_guide->setTranslations('files', $fileTranslations);
                    }
                    
                    $descsTranslations = $processFileTranslations($request, 'files_descs', 'short_file_descs');
                    if (!empty($descsTranslations)) {
                        $investment_guide->setTranslations('short_file_descs', $descsTranslations);
                    }

                    // BỎ PHẦN LƯU files CŨ (SINGLE-LANGUAGE)
                    // if ($request->filled('files_images')) {
                    //     $investment_guide->files = implode(';', array_map('trim', $request->files_images));
                    // }

                    $investment_guide->status_approve = 'pending';
                    $investment_guide->approval_level = $user->is_approve ? 1 : 0;
                    $investment_guide->save();

                    if ($request->filled('projects')) {
                        $investment_guide->projects()->sync($request->input('projects'));
                    } else {
                        $investment_guide->projects()->detach();
                    }
                }
            }
        }

        return redirect()
            ->route('backend_investment_guide_edit', $investment_guide)
            ->with('success', 'Lưu dữ liệu thành công ' . (
                $user->is_super_admin ? '(Đã duyệt)' : ($user->is_approve ? '(Chờ duyệt cấp 2)' : '')
            ));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors(['error' => 'Lỗi khi lưu dữ liệu: ' . $e->getMessage()]);
    }
}
    public function approve(InvestmentGuide $investment_guide)
    {
        $user = auth('web')->user();

        if (!($user->is_super_admin || $user->is_approve)) {
            abort(403, 'Bạn không có quyền duyệt dự án.');
        }

        if ($user->is_super_admin) {
            $investment_guide->approval_level = $investment_guide->max_approval;
            $investment_guide->status_approve = 'approved';
            $investment_guide->is_draft = false;

            if ($investment_guide->parent_id) {
                $parent = InvestmentGuide::find($investment_guide->parent_id);
                if ($parent) {
                    $draftData = $investment_guide->toArray();
                    $this->removeInvestmentGuideFromScope($investment_guide->id);
                    $investment_guide->delete();

                    $parent->fill($draftData);

                    $parent->parent_id = null;
                    $parent->is_draft = false;
                    $parent->status_approve = 'approved';
                    $parent->approval_level = $parent->max_approval;

                    $slug = Str::slug($parent->name);
                    $originalSlug = $slug;
                    $counter = 1;
                    while (InvestmentGuide::where('slug', $slug)->where('id', '<>', $parent->id)->exists()) {
                        $slug = $originalSlug . '-' . $counter;
                        $counter++;
                    }
                    $parent->slug = $slug;

                    $parent->save();

                    $investment_guide = $parent;
                }
            }
        } elseif ($user->is_approve) {
            if ($investment_guide->approval_level < 1) {
                $investment_guide->approval_level = 1;
                $investment_guide->status_approve = 'pending';
            }
        }

        $investment_guide->save();

        return redirect()
            ->route('backend_investment_guide_edit', $investment_guide->id)
            ->with('success', 'Duyệt cẩm nang đầu tư thành công ' . ($user->is_super_admin ? '(Đã duyệt)' : '(Chờ duyệt cấp 2)'));
    }

    public function reject(InvestmentGuide $investment_guide)
    {
        $user = auth('web')->user();

        if (!($user->is_super_admin || $user->is_approve)) {
            abort(403, 'Bạn không có quyền từ chối duyệt cẩm nang đầu tư.');
        }
        // $investment_guide->delete();
        $investment_guide->status_approve = 'rejected';
        $investment_guide->save();

        return redirect()
            ->route('backend_investment_guide')
            ->with('success', 'Từ chối duyệt cẩm nang đầu tư thành công');
    }

    public function clone(InvestmentGuide $investment_guide)
    {
        if (!Gate::allows('investment_guide/clone')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        $new_id = data_get($investment_guide, 'id', 0);
        $investment_guide = InvestmentGuide::find($new_id);
        if ($investment_guide) {
            $new_investment_guide = $investment_guide->replicate();
            $new_investment_guide->name = $investment_guide->name . ' copy';
            $new_investment_guide->slug = $investment_guide->slug . '-' . strtolower(Str::random(5));
            if ($new_investment_guide->save()) {
                return back()->with('success', 'Sao chép thành công');
            }
        }
        return back()->with('error', 'Sao chép không thành công');
    }

    public function delete(Request $request, $id)
    {
        if (!Gate::allows('investment_guide/delete')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $this->investment_guide->destroy($id);
        return redirect()->to(route('backend_investment_guide'))->with('success', 'Xóa thành công');
    }

    public function bulkDelete(Request $request)
    {
        if (!Gate::allows('investment_guide/delete')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $request->validate(['ids' => 'required|array']);

        $ids = $request->get('ids');
        if (empty($ids)) {
            return $this->responseJsonBadRequest();
        }

        $this->investment_guide->destroy($ids);
        return $this->responseJsonOk();
    }

    public function restore(Request $request, $id)
    {
        if (!Gate::allows('investment_guide/delete')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $investment_guide = InvestmentGuide::withTrashed()->findOrFail($id);
        $investment_guide->restore();
        return redirect()->route('backend_investment_guide')->with('success', 'Khôi phục bài viết thành công');
    }

    public function forceDelete(Request $request, $id)
    {
        if (!Gate::allows('investment_guide/delete')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $investment_guide = InvestmentGuide::withTrashed()->findOrFail($id);
        $investment_guide->forceDelete();
        return redirect()->route('backend_investment_guide', 'status=2')->with('success', 'Xóa bài viết thành công');
    }

    public function showImportForm()
    {
        if (!Gate::allows('investment_guide/import')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        $this->selectedSubMenu('investment_guide');
        $investment_guide = new InvestmentGuide();
        return view('ttxt-investment::investment_guide.import', compact('investment_guide'));
    }

    public function importFromUrl(Request $request)
    {
        if (!Gate::allows('investment_guide/import')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $url = $request->input('url');
        $client = new Client([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            ],
            'timeout' => 10,
            'allow_redirects' => true,
            'verify' => false,
        ]);

        $response = $client->get($url);
        $html = (string) $response->getBody();

        $crawler = new Crawler($html);

        // ---- Title ----
        $title = $crawler->filter('title')->count() ? $crawler->filter('title')->text() : null;

        // ---- Description ----
        $description = null;
        if ($crawler->filterXPath('//meta[@name="description"]')->count()) {
            $description = $crawler->filterXPath('//meta[@name="description"]')->attr('content');
        } elseif ($crawler->filterXPath('//meta[@property="og:description"]')->count()) {
            $description = $crawler->filterXPath('//meta[@property="og:description"]')->attr('content');
        }

        $keywords = null;
        if ($crawler->filterXPath('//meta[@name="keywords"]')->count()) {
            $keywords = $crawler->filterXPath('//meta[@name="keywords"]')->attr('content');
        }

        // ---- Nội dung chính ----
        $config = new Configuration();
        $readability = new Readability($config);
        $readability->parse($html);
        $content = $readability->getContent();
        $excerpt = $readability->getExcerpt();

        if (!$description && $excerpt) {
            $description = $excerpt;
        }

        // ---- Ảnh đại diện (thumbnail) ----
        // $image = null;
        // if ($crawler->filterXPath('//meta[@property="og:image"]')->count()) {
        //     $image = $crawler->filterXPath('//meta[@property="og:image"]')->attr('content');
        // } elseif ($crawler->filterXPath('//meta[@name="twitter:image"]')->count()) {
        //     $image = $crawler->filterXPath('//meta[@name="twitter:image"]')->attr('content');
        // }
        $image = null;
        if ($crawler->filterXPath('//meta[@property="og:image"]')->count()) {
            $image = $crawler->filterXPath('//meta[@property="og:image"]')->attr('content');
        } elseif ($crawler->filterXPath('//meta[@name="twitter:image"]')->count()) {
            $image = $crawler->filterXPath('//meta[@name="twitter:image"]')->attr('content');
        }

        if ($image) {
            try {
                // Tải ảnh từ URL
                $imageContents = file_get_contents($image);

                // Lấy đuôi file (nếu không có thì mặc định jpg)
                $ext = pathinfo(parse_url($image, PHP_URL_PATH), PATHINFO_EXTENSION);
                if (!$ext) {
                    $ext = 'jpg';
                }

                // Tạo tên file duy nhất
                $fileName = 'imported_' . time() . '_' . uniqid() . '.' . $ext;

                // Đường dẫn vật lý (trong public/uploads)
                $savePath = public_path('uploads/' . $fileName);

                // Đảm bảo thư mục tồn tại
                if (!file_exists(dirname($savePath))) {
                    mkdir(dirname($savePath), 0755, true);
                }

                // Ghi file
                file_put_contents($savePath, $imageContents);

                // Trả về path dạng /uploads/...
                $image = '/uploads/' . $fileName;

            } catch (\Exception $e) {
                Log::error("Import image failed: " . $e->getMessage());
            }
        }

        // ---- Fix ảnh trong content ----
        $contentCrawler = new Crawler($content);
        $contentCrawler->filter('img')->each(function (Crawler $node) use ($url) {
            $img = $node->getNode(0);
            if (!$img instanceof \DOMElement)
                return;

            $src = $img->getAttribute('src');
            if ($src) {
                // Nếu src là base64 → lấy data-src, data-original...
                if (str_starts_with($src, 'data:image')) {
                    $newSrc = $node->attr('data-src') ?: $node->attr('data-original') ?: $node->attr('data-lazy');
                    if ($newSrc) {
                        $img->setAttribute('src', $this->makeAbsoluteUrl($url, $newSrc));
                    }
                } else {
                    $img->setAttribute('src', $this->makeAbsoluteUrl($url, $src));
                }
            }

            // Xóa các giới hạn kích thước
            $img->removeAttribute('width');
            $img->removeAttribute('height');
            $img->removeAttribute('style');

            // Thêm style responsive cho an toàn
            $img->setAttribute('style', 'max-width:100%;height:auto;');
        });

        // Lấy lại content sau khi xử lý
        $content = $contentCrawler->html();

        $data = [
            'name.vi' => $title,
            'slug.vi' => $title ? Str::slug($title) : '',
            'description.vi' => $description,
            'content.vi' => $content,
            'meta_title.vi' => $title,
            'meta_keywords.vi' => $keywords,
            'meta_description.vi' => $description,
            'image' => $image,
        ];

        return redirect()
            ->route('backend_investment_guide_create')
            ->withInput($data)
            ->with('success', 'Import nội dung từ URL thành công.');
    }

    /**
     * Convert relative path → absolute URL
     */
    protected function makeAbsoluteUrl($baseUrl, $relativeUrl)
    {
        return (string) UriResolver::resolve(
            new Uri($baseUrl),
            new Uri($relativeUrl)
        );
    }

    protected function addInvestmentGuideToScope($user, $investment_guideId)
    {
        $group = Group::find($user->group_id);
        if (!$group) return;

        $scopeData = $group->scope_data ?? [];
        $resource = 'investment_guide';

        if (empty($scopeData[$resource])) {
            return;
        }

        if (!in_array((string)$investment_guideId, $scopeData[$resource])) {
            $scopeData[$resource][] = (string)$investment_guideId;
            $group->scope_data = $scopeData;
            $group->save();
        }
    }

    protected function removeInvestmentGuideFromScope($investment_guideId)
    {
        $groups = Group::whereJsonContains('scope_data->investment_guide', (string)$investment_guideId)->get();

        foreach ($groups as $group) {
            $scopeData = $group->scope_data ?? [];
    
            if (!isset($scopeData['investment_guide']) || !is_array($scopeData['investment_guide'])) {
                continue;
            }

            if (empty($scopeData['investment_guide'])) {
                continue;
            }

            $scopeData['investment_guide'] = array_values(array_filter(
                $scopeData['investment_guide'],
                fn($id) => (string)$id !== (string)$investment_guideId
            ));
    
            $group->scope_data = $scopeData;
            $group->save();
        }
    }
}
