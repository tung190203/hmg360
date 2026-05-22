<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Post\Http\Controllers;

use App\Libs\DataGrid;
use App\Libs\Util;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Post;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Category;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Project;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;
use andreskrey\Readability\Readability;
use andreskrey\Readability\Configuration;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Group;
use \GuzzleHttp\Psr7\Uri;
use \GuzzleHttp\Psr7\UriResolver;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    private Post $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
        $this->selectedMainMenu = 'post';

        parent::__construct();

        if (!Gate::allows('post')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
    }

    public function index(Request $request)
    {
        $language = App::getLocale();
        $this->selectedSubMenu('post');
        $category = new Category();
        $category->getParentArray();

        $filter['name'] = $request->get('name', '');
        $filter['cat_id'] = $request->get('cat_id', 0);
        $filter['status'] = $request->get('status', -1);
        $query = $this->post->with(['category', 'user'])
            ->visibleFor(auth('web')->user())
            ->orderBy('id', 'desc');
        if ($filter['name'] !== '') {
            $query->where('name', 'like', '%' . $filter['name'] . '%');
        }
        if ($filter['cat_id'] > 0) {
            $all_cat = $category->getAllCatStr($filter['cat_id']);
            $all_cat[] = (int)$filter['cat_id'];
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

        $scope = $user->getScope('post');
        if (!empty($scope)) {
            $query->whereIn('id', $scope);
        }

        $posts = $query->paginate(20);
        $options['categories'] = Category::makeListCategoryForPost(0,'', $filter['cat_id']);
        $options['status'] = Util::makeHTMLOptions(Post::STATUS_ARRAY, $filter['status']);
        $option_categories = Category::makeArrayListCategory(0, Category::CATEGORY_TYPE_POST);

        $paginate = 20;
        $route_name = 'backend_post_edit';
        $option_column_button = Post::makeOptionColumnButton();

        $clsDataGrid = new DataGrid();
        $clsDataGrid->setLinkEdit($route_name);
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

        $dataGrid = $clsDataGrid->showDataGrid($posts, $paginate, $posts->total());

        return view('ttxt-content::post.index', compact('posts', 'filter', 'options', 'dataGrid'));
    }

    public function saveDataIndex(Request $request)
    {
        // foreach($request->ids as $id) {
        //     $p = Post::find($id);
        //     if(!Gate::allows('post/edit', $p)) {
        //         abort(403);
        //     }
        // }

        $update = $request->get('update', []);
        foreach ($update as $key => $value) {
            Post::where('id', $key)->update($value);
        }
        return redirect()->route('backend_post')->with('success', 'Cập nhật thông tin thành công');
    }

    public function edit(Post $post)
    {
        if($post->exists && !Gate::allows('post/edit', $post)) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        if(!$post->exists && !Gate::allows('post/add')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $option_categories = Category::makeListCategoryForPost(0, '', $post->cat_id);
        // $option_projects = Project::makeListProject($post->project_id);
        $option_projects = Project::makeListProjectArray();
        return view('ttxt-content::post.create', compact('post', 'option_categories', 'option_projects'));
    }

    public function save(Post $post, Request $request)
    {
        $user = auth('web')->user();
    
        if ($post->exists && !Gate::allows('post/edit', $post)) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        if (!$post->exists && !Gate::allows('post/add')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
    
        $validated = $request->validate([
            'name' => 'required|array',
            'name.vi' => 'required|string',
            'name.*' => 'nullable|string',
            'slug' => 'nullable|array',
            'slug.*' => 'nullable|alpha_dash',
            'cat_id' => 'nullable|integer',
            'relic_id' => 'nullable|integer',
            'image' => 'nullable|string|max:2048',
            'priority' => 'nullable|integer',
            'description' => 'nullable|array',
            'description.*' => 'nullable|string',
            'content' => 'nullable|array',
            'content.*' => 'nullable|string',
            'source' => 'nullable|string|max:255',
            'is_hot' => 'nullable|boolean',
            'view_num' => 'nullable|integer',
            'meta_title' => 'nullable|array',
            'meta_title.*' => 'nullable|string',
            'meta_keywords' => 'nullable|array',
            'meta_keywords.*' => 'nullable|string',
            'meta_description' => 'nullable|array',
            'meta_description.*' => 'nullable|string',
            'project_type' => 'nullable',
            'project_id' => 'nullable|integer',
            'published_at' => 'nullable|date',
            'projects' => 'nullable|array',
            'projects.*' => 'integer|exists:projects,id',
            // Cập nhật Validation cho files (URL)
            'files' => 'nullable|array',
            'files.*' => 'nullable|array', // Mảng URL tệp cho từng locale
            'files.*.*' => 'nullable|string',

            // Cập nhật Validation cho files_descs (Mô tả)
            'files_descs' => 'nullable|array',
            'files_descs.*' => 'nullable|array', // Mảng mô tả cho từng locale
            'files_descs.*.*' => 'nullable|string',
            'extracted_text' => 'nullable|string',
            'extracted_summary' => 'nullable|string',
            'extracted_language' => 'nullable|string|max:10',
            'extracted_at' => 'nullable|date',
        ]);
    
        try {
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
            // 🟩 Tạo mới (bản chính)
            if (!$post->exists) {
                // Xử lý dữ liệu đa ngôn ngữ
                $translatableData = [];
                foreach ($post->translatable as $field) {
                    if (isset($validated[$field])) {
                        $translatableData[$field] = $validated[$field];
                    }
                }
    
                $post->fill(array_diff_key($validated, array_flip($post->translatable)));
                
                // Set translatable fields
                // foreach ($translatableData as $field => $translations) {
                //     $post->setTranslations($field, $translations);
                // }
                foreach ($translatableData as $field => $translations) {
                    if ($field !== 'files' && $field !== 'short_file_descs') {
                        $post->setTranslations($field, $translations);
                    }
                }
    
                // Xử lý files và short_file_descs dưới dạng đa ngôn ngữ
                $fileTranslations = $processFileTranslations($request, 'files', 'files');
                if (!empty($fileTranslations)) {
                    $post->setTranslations('files', $fileTranslations);
                }
                
                $descsTranslations = $processFileTranslations($request, 'files_descs', 'short_file_descs');
                if (!empty($descsTranslations)) {
                    $post->setTranslations('short_file_descs', $descsTranslations);
                }
    
                $post->approval_level = $user->is_super_admin ? 2 : ($user->is_approve ? 1 : 0);
                $post->max_approval = 2;
                $post->is_draft = false;
                $post->status_approve = $user->is_super_admin ? 'approved' : ($user->is_approve ? 'pending' : 'pending');
                $post->status = $user->is_super_admin ? Post::STATUS_ACTIVE : Post::STATUS_INACTIVE;
                $post->language = App::getLocale();
                $post->project_type = $validated['project_type'] ?? null;
                $post->project_id = $validated['project_id'] ?? 0;
    
                // Sinh slug unique cho ngôn ngữ mặc định
                $defaultLocale = config('app.locale');
                $slug = Str::slug($post->getTranslation('slug', $defaultLocale) ?: $post->getTranslation('name', $defaultLocale));
                $originalSlug = $slug;
                $counter = 1;
                while (Post::where("slug->{$defaultLocale}", $slug)->exists()) {
                    $slug = $originalSlug . '-' . $counter++;
                }
                $post->setTranslation('slug', $defaultLocale, $slug);
    
                $post->save();
    
                // Đồng bộ project liên quan
                if ($request->filled('projects')) {
                    $post->projects()->syncWithPivotValues(
                        $request->input('projects'),
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                }
    
                if (Gate::allows('post/add')) {
                    $this->addPostToScope($user, $post->id);
                }
            } else {
                // 🟦 Super admin merge
                if ($user->is_super_admin) {
                    $mainPost = $post->parent_id ? Post::find($post->parent_id) : $post;
    
                    // Xử lý dữ liệu đa ngôn ngữ
                    $translatableData = [];
                    foreach ($post->translatable as $field) {
                        if (isset($validated[$field])) {
                            $translatableData[$field] = $validated[$field];
                        }
                    }
    
                    $mainPost->fill(array_diff_key($validated, array_flip($post->translatable)));
                    
                    // Set translatable fields
                    // foreach ($translatableData as $field => $translations) {
                    //     $mainPost->setTranslations($field, $translations);
                    // }
                    foreach ($translatableData as $field => $translations) {
                        if ($field !== 'files' && $field !== 'short_file_descs') {
                           $mainPost->setTranslations($field, $translations);
                       }
                   }
   
                   // Xử lý files và short_file_descs dưới dạng đa ngôn ngữ
                   $fileTranslations = $processFileTranslations($request, 'files', 'files');
                   if (!empty($fileTranslations)) {
                       $mainPost->setTranslations('files', $fileTranslations);
                   }
                   
                   $descsTranslations = $processFileTranslations($request, 'files_descs', 'short_file_descs');
                   if (!empty($descsTranslations)) {
                       $mainPost->setTranslations('short_file_descs', $descsTranslations);
                   }
    
                    $mainPost->approval_level = $mainPost->max_approval;
                    $mainPost->status_approve = 'approved';
                    $mainPost->is_draft = false;
                    $mainPost->parent_id = null;

                    // Slug unique (remove -draft)
                    $defaultLocale = config('app.locale');
                    $currentSlug = $mainPost->getTranslation('slug', $defaultLocale) ?: $mainPost->getTranslation('name', $defaultLocale);
                    $slug = preg_replace('/-draft$/', '', Str::slug($currentSlug));
                    $originalSlug = $slug;
                    $counter = 1;
                    while (Post::where("slug->{$defaultLocale}", $slug)->where('id', '<>', $mainPost->id)->exists()) {
                        $slug = $originalSlug . '-' . $counter++;
                    }
                    $mainPost->setTranslation('slug', $defaultLocale, $slug);
                    $mainPost->save();

                    // Sync projects
                    if ($request->filled('projects')) {
                        $mainPost->projects()->sync($request->input('projects'));
                    } else {
                        $mainPost->projects()->detach();
                    }

                    // Xoá nháp
                    $drafts = Post::where('parent_id', $mainPost->id)->get();
                    foreach ($drafts as $draft) {
                        $this->removePostFromScope($draft->id);
                        $draft->delete();
                    }

                    $post = $mainPost;
                } else {
                    // 🟨 Người dùng thường
                    if ($post->status_approve === 'approved' && !$post->is_draft) {
                        // Bản chính đã duyệt → tạo bản nháp
                        $draft = $post->replicate();
                        
                        // Xử lý dữ liệu đa ngôn ngữ
                        $translatableData = [];
                        foreach ($post->translatable as $field) {
                            if (isset($validated[$field])) {
                                $translatableData[$field] = $validated[$field];
                            }
                        }
    
                        $draft->fill(array_diff_key($validated, array_flip($post->translatable)));
                        
                        // Set translatable fields
                        // foreach ($translatableData as $field => $translations) {
                        //     $draft->setTranslations($field, $translations);
                        // }
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
                        $draft->parent_id = $post->id;
                        $draft->status = Post::STATUS_INACTIVE;
                        
                        $defaultLocale = config('app.locale');
                        $draftSlug = Str::slug($draft->getTranslation('slug', $defaultLocale) ?: $draft->getTranslation('name', $defaultLocale)) . '-draft';
                        $draft->setTranslation('slug', $defaultLocale, $draftSlug);
                        $draft->save();

                        if ($request->filled('projects')) {
                            $draft->projects()->sync($request->input('projects'));
                        }

                        if (Gate::allows('post/add')) {
                            $this->addPostToScope($user, $draft->id);
                        }

                        $post = $draft;
                    } else {
                        // Cập nhật bản hiện tại (chưa duyệt hoặc nháp)
                        $translatableData = [];
                        foreach ($post->translatable as $field) {
                            if (isset($validated[$field])) {
                                $translatableData[$field] = $validated[$field];
                            }
                        }

                        $post->fill(array_diff_key($validated, array_flip($post->translatable)));

                        // Set translatable fields
                        // foreach ($translatableData as $field => $translations) {
                        //     $post->setTranslations($field, $translations);
                        // }
                        foreach ($translatableData as $field => $translations) {
                            if ($field !== 'files' && $field !== 'short_file_descs') {
                               $post->setTranslations($field, $translations);
                           }
                       }
   
                       // Xử lý files và short_file_descs dưới dạng đa ngôn ngữ
                       $fileTranslations = $processFileTranslations($request, 'files', 'files');
                       if (!empty($fileTranslations)) {
                           $post->setTranslations('files', $fileTranslations);
                       }
                       
                       $descsTranslations = $processFileTranslations($request, 'files_descs', 'short_file_descs');
                       if (!empty($descsTranslations)) {
                           $post->setTranslations('short_file_descs', $descsTranslations);
                       }

                        $post->status_approve = 'pending';
                        $post->approval_level = $user->is_approve ? 1 : 0;
                        $post->save();

                        if ($request->filled('projects')) {
                            $post->projects()->sync($request->input('projects'));
                        } else {
                            $post->projects()->detach();
                        }
                    }
                }
            }

            return redirect()
                ->route('backend_post_edit', $post)
                ->with('success', 'Lưu dữ liệu thành công ' . (
                    $user->is_super_admin ? '(Đã duyệt)' : ($user->is_approve ? '(Chờ duyệt cấp 2)' : '')
                ));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Lỗi khi lưu dữ liệu: ' . $e->getMessage()]);
        }
    }
    public function approve(Post $post)
    {
        $user = auth('web')->user();

        if (!($user->is_super_admin || $user->is_approve)) {
            abort(403, 'Bạn không có quyền duyệt dự án.');
        }
    
        if ($user->is_super_admin) {
            $post->approval_level = $post->max_approval;
            $post->status_approve = 'approved';
            $post->is_draft = false;
    
            if ($post->parent_id) {
                $parent = Post::find($post->parent_id);
                if ($parent) {
                    $draftData = $post->toArray();
                    $this->removePostFromScope($post->id);
                    $post->delete();

                    $parent->fill($draftData);

                    $parent->parent_id = null;
                    $parent->is_draft = false;
                    $parent->status_approve = 'approved';
                    $parent->approval_level = $parent->max_approval;

                    $slug = Str::slug($parent->name);
                    $originalSlug = $slug;
                    $counter = 1;
                    while (Post::where('slug', $slug)->where('id', '<>', $parent->id)->exists()) {
                        $slug = $originalSlug . '-' . $counter;
                        $counter++;
                    }
                    $parent->slug = $slug;

                    $parent->save();

                    $post = $parent;
                }
            }
        } elseif ($user->is_approve) {
            if ($post->approval_level < 1) {
                $post->approval_level = 1;
                $post->status_approve = 'pending';
            }
        }
    
        $post->save();

        return redirect()
            ->route('backend_post_edit', $post->id)
            ->with('success', 'Duyệt bài viết thành công ' . ($user->is_super_admin ? '(Đã duyệt)' : '(Chờ duyệt cấp 2)'));
    }

    public function reject(Post $post)
    {
        $user = auth('web')->user();

        if (!($user->is_super_admin || $user->is_approve)) {
            abort(403, 'Bạn không có quyền từ chối duyệt bài viết.');
        }
        // $post->delete();
        $post->status_approve = 'rejected';
        $post->save();

        return redirect()
            ->route('backend_post')
            ->with('success', 'Từ chối duyệt bài viết thành công');
    }

    public function clone(Post $post)
    {
        if (!Gate::allows('post/clone')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $new_id = data_get($post, 'id', 0);
        $post = Post::find($new_id);
        if ($post) {
            $new_post = $post->replicate();
            $new_post->name = $post->name . ' copy';
            $new_post->slug = $post->slug . '-' . strtolower(Str::random(5));
            if ($new_post->save()) {
                return back()->with('success', 'Sao chép thành công');
            }
        }
        return back()->with('error', 'Sao chép không thành công');
    }

    public function delete(Request $request, $id)
    {
        if (!Gate::allows('post/delete')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $this->post->destroy($id);
        return redirect()->to(route('backend_post'))->with('success', 'Xóa thành công');
    }

    public function bulkDelete(Request $request)
    {
        if (!Gate::allows('post/delete')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $request->validate(['ids' => 'required|array']);

        $ids = $request->get('ids');
        if (empty($ids)) {
            return $this->responseJsonBadRequest();
        }

        $this->post->destroy($ids);
        return $this->responseJsonOk();
    }

    public function restore(Request $request, $id)
    {
        if (!Gate::allows('post/delete')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $post = Post::withTrashed()->findOrFail($id);
        $post->restore();
        return redirect()->route('backend_post')->with('success', 'Khôi phục bài viết thành công');
    }

    public function forceDelete(Request $request, $id)
    {
        if (!Gate::allows('post/delete')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $post = Post::withTrashed()->findOrFail($id);
        $post->forceDelete();
        return redirect()->route('backend_post', 'status=2')->with('success', 'Xóa bài viết thành công');
    }

    public function showImportForm()
    {
        if (!Gate::allows('post/import')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        $this->selectedSubMenu('post');
        $post = new Post();
        return view('ttxt-content::post.import', compact('post'));
    }

    public function importFromUrl(Request $request)
    {
        if (!Gate::allows('post/import')) {
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

       // ---- Chuẩn bị dữ liệu ----
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
            ->route('backend_post_create')
            ->withInput($data)
            ->with('success', 'Đã import dữ liệu thành công, vui lòng kiểm tra và lưu bài viết.');
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

    protected function addPostToScope($user, $postId)
    {
        $group = Group::find($user->group_id);
        if (!$group) return;

        $scopeData = $group->scope_data ?? [];
        $resource = 'post';

        if (empty($scopeData[$resource])) {
            return;
        }

        if (!in_array((string)$postId, $scopeData[$resource])) {
            $scopeData[$resource][] = (string)$postId;
            $group->scope_data = $scopeData;
            $group->save();
        }
    }

    protected function removePostFromScope($postId)
    {
        $groups = Group::whereJsonContains('scope_data->post', (string)$postId)->get();

        foreach ($groups as $group) {
            $scopeData = $group->scope_data ?? [];
    
            if (!isset($scopeData['post']) || !is_array($scopeData['post'])) {
                continue;
            }

            if (empty($scopeData['post'])) {
                continue;
            }

            $scopeData['post'] = array_values(array_filter(
                $scopeData['post'],
                fn($id) => (string)$id !== (string)$postId
            ));
    
            $group->scope_data = $scopeData;
            $group->save();
        }
    }
}
