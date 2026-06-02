<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Dashboard\Http\Controllers;

use Illuminate\Http\Request;
use App\Core\Tenant\TenantDatabaseManager;
use App\Http\Controllers\Controller;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Guest;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\InvestmentGuide;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Post;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Project;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\ProjectIndustries;
use App\Models\User as PlatformUser;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\User as TenantUser;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\VisitLog;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\SiteVisitor;
use Spatie\Activitylog\Models\Activity;
use App\Services\LogExportService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    protected $logExportService;

    public function __construct(LogExportService $logExportService)
    {
        $this->selectedMainMenu = 'dashboard';
        $this->logExportService = $logExportService;
        parent::__construct();
    }

    public function index(Request $request)
    {
        $user = auth('web')->user();

        if ($user?->tenant) {
            app(TenantDatabaseManager::class)->connect($user->tenant);
        }

        if ($user->status !== PlatformUser::STATUS_ACTIVE || ($user->status_approve ?? null) === 'rejected') {
            return view('backend.no_permission', [
                'message' => 'Tài khoản của bạn đã bị thu hồi hoặc không còn hoạt động. Vui lòng liên hệ quản trị viên.',
                'hideSidebar' => true,
                'hideHeader' => true,
                'hideFooter' => true,
                'hideBreadcrumb' => true,
            ]);
        }

        if (
            $user->isPlatformOwner()
            || $user->isTenantOrganizer()
            || $user->hasPermission('dashboard')
            || $user->hasPermission('dashboard/view')
        ) {
            $quantityProjects = Project::count() ?? 0;
            $quantityUser = Guest::count() ?? 0;
            $quantityPost = Post::count() ?? 0;
            $quantityInvestmentGuide = InvestmentGuide::count() ?? 0;

            // Project completion stats
            $totalInvestment = Project::sum('price') ?? 0;
            
            $allProjects = Project::select('id', 'name', 'lat', 'lng', 'link_vrtour', 'vrtour_code', 'legal_file', 'legal_description', 'is_invest')->get();

            $has_general_info_count = 0;
            $has_general_info = collect();
            $missing_general_info = collect();
            $has_location_count = 0;
            $has_location = collect();
            $missing_location = collect();
            $has_vrtour_count = 0;
            $has_vrtour = collect();
            $missing_vrtour = collect();
            $has_legal_count = 0;
            $has_legal_projects = collect();
            $missing_legal = collect();
            $has_investor_count = 0;
            $has_investor = collect();
            $missing_investor = collect();

            foreach ($allProjects as $p) {
                // General info
                if (!empty($p->name)) {
                    $has_general_info_count++;
                    $has_general_info->push($p);
                } else {
                    $missing_general_info->push($p);
                }

                // Location
                if (!empty($p->lat) && !empty($p->lng)) {
                    $has_location_count++;
                    $has_location->push($p);
                } else {
                    $missing_location->push($p);
                }

                // VR Tour
                if (!empty($p->link_vrtour)) {
                    $has_vrtour_count++;
                    $has_vrtour->push($p);
                } else {
                    $missing_vrtour->push($p);
                }

                // Legal
                $has_legal = false;
                if (!empty($p->legal_file) && $p->legal_file !== 'null') {
                    $has_legal = true;
                }
                
                if (!$has_legal && !empty($p->legal_description)) {
                    $vi = $p->getTranslation('legal_description', 'vi', false);
                    $en = $p->getTranslation('legal_description', 'en', false);
                    
                    // Decode if it's a JSON string like "[]" or "["..."]"
                    $vi_decoded = is_string($vi) ? json_decode($vi, true) : $vi;
                    $en_decoded = is_string($en) ? json_decode($en, true) : $en;
                    
                    // Check if decoded is an array and not empty, or if it's a non-empty string
                    $vi_has_content = (!empty($vi) && $vi !== '[]' && $vi !== 'null' && (!is_array($vi_decoded) || count($vi_decoded) > 0));
                    $en_has_content = (!empty($en) && $en !== '[]' && $en !== 'null' && (!is_array($en_decoded) || count($en_decoded) > 0));
                    
                    if ($vi_has_content || $en_has_content) {
                        $has_legal = true;
                    }
                }

                if ($has_legal) {
                    $has_legal_count++;
                    $has_legal_projects->push($p);
                } else {
                    $missing_legal->push($p);
                }

                // Investor status
                if ((int) $p->is_invest === 1) {
                    $has_investor_count++;
                    $has_investor->push($p);
                } else {
                    $missing_investor->push($p);
                }
            }

            $projectStats = [
                'total' => $quantityProjects,
                'has_general_info' => $has_general_info_count,
                'has_location' => $has_location_count,
                'has_vrtour' => $has_vrtour_count,
                'has_legal' => $has_legal_count,
                'has_investor' => $has_investor_count,
            ];

            $missingProjects = [
                'general_info' => $missing_general_info,
                'location' => $missing_location,
                'vrtour' => $missing_vrtour,
                'legal' => $missing_legal,
                'investor' => $missing_investor,
            ];

            $hasProjects = [
                'general_info' => $has_general_info,
                'location' => $has_location,
                'vrtour' => $has_vrtour,
                'legal' => $has_legal_projects,
                'investor' => $has_investor,
            ];

            // Visitor Stats
            $todayDate = now()->toDateString();
            $visitStats = [
                'unique_ips_today' => VisitLog::whereDate('created_at', $todayDate)->count(),
                'bots_today' => VisitLog::whereDate('created_at', $todayDate)->where('is_bot', true)->count(),
            ];

            $visitorMonthInput = $request->get('visitor_month', now()->format('Y-m'));
            try {
                $visitorMonth = \Carbon\Carbon::createFromFormat('Y-m', $visitorMonthInput)->startOfMonth();
            } catch (\Exception $e) {
                $visitorMonth = now()->startOfMonth();
            }
            $visitorMonthStart = $visitorMonth->copy()->startOfMonth();
            $visitorMonthEnd = $visitorMonth->copy()->endOfMonth();

            $totalSiteVisitors = SiteVisitor::whereBetween('visit_date', [
                    $visitorMonthStart->toDateString(),
                    $visitorMonthEnd->toDateString(),
                ])
                ->distinct('ip_address')
                ->count('ip_address');

            $returningVisitors = SiteVisitor::select('ip_address')
                ->whereBetween('visit_date', [
                    $visitorMonthStart->toDateString(),
                    $visitorMonthEnd->toDateString(),
                ])
                ->groupBy('ip_address')
                ->havingRaw('COUNT(DISTINCT visit_date) >= 2')
                ->get()
                ->count();

            $totalHitsInMonth = SiteVisitor::whereBetween('visit_date', [
                    $visitorMonthStart->toDateString(),
                    $visitorMonthEnd->toDateString(),
                ])
                ->sum('hits');

            $siteVisitorStats = [
                'total_visitors' => $totalSiteVisitors,
                'returning_visitors' => $returningVisitors,
                'visitors_in_month' => $totalSiteVisitors,
                'total_hits_in_month' => $totalHitsInMonth,
                'month_label' => $visitorMonth->format('m/Y'),
                'month_value' => $visitorMonth->format('Y-m'),
            ];
            
            $monthIps = SiteVisitor::select(
                    'ip_address',
                    DB::raw('SUM(hits) as hits'),
                    DB::raw('COUNT(DISTINCT visit_date) as visit_days'),
                    DB::raw('MIN(visit_date) as first_visit_date'),
                    DB::raw('MAX(visit_date) as last_visit_date')
                )
                ->whereBetween('visit_date', [
                    $visitorMonthStart->toDateString(),
                    $visitorMonthEnd->toDateString(),
                ])
                ->groupBy('ip_address')
                ->orderByDesc('hits')
                ->get();

            $visitorMonthlyLabels = [];
            $visitorMonthlyData = [];
            $returningVisitorMonthlyData = [];
            for ($i = 11; $i >= 0; $i--) {
                $month = now()->startOfMonth()->subMonths($i);
                $start = $month->copy()->startOfMonth()->toDateString();
                $end = $month->copy()->endOfMonth()->toDateString();

                $visitorMonthlyLabels[] = $month->format('m/Y');
                $visitorMonthlyData[] = SiteVisitor::whereBetween('visit_date', [$start, $end])
                    ->distinct('ip_address')
                    ->count('ip_address');
                $returningVisitorMonthlyData[] = SiteVisitor::select('ip_address')
                    ->whereBetween('visit_date', [$start, $end])
                    ->groupBy('ip_address')
                    ->havingRaw('COUNT(DISTINCT visit_date) >= 2')
                    ->get()
                    ->count();
            }

            // Historical Visit stats for the last 7 days
            $visitChartLabels = [];
            $visitChartData = [];
            $botChartData = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = now()->subDays($i)->format('Y-m-d');
                $visitChartLabels[] = now()->subDays($i)->format('d/m');
                $visitChartData[] = VisitLog::whereDate('created_at', $d)->count();
                $botChartData[] = VisitLog::whereDate('created_at', $d)->where('is_bot', true)->count();
            }

            // Monthly Activity Chart Data
            $range = (int) $request->get('range', 6);
            if ($range <= 0 || $range > 24) $range = 6; // Limit range for performance

            $chartLabels = [];
            $projectChartData = [];
            $postChartData = [];
            $guestChartData = [];
            $investmentGuideChartData = [];

            for ($i = $range - 1; $i >= 0; $i--) {
                $date = now()->startOfMonth()->subMonths($i);
                $month = $date->format('m');
                $year = $date->format('Y');

                $chartLabels[] = 'Tháng ' . (int)$month;

                $projectChartData[] = Project::whereMonth('created_at', $month)
                    ->whereYear('created_at', $year)
                    ->count();

                $postChartData[] = Post::whereMonth('created_at', $month)
                    ->whereYear('created_at', $year)
                    ->count();

                $guestChartData[] = Guest::whereMonth('created_at', $month)
                    ->whereYear('created_at', $year)
                    ->count();

                $investmentGuideChartData[] = InvestmentGuide::whereMonth('created_at', $month)
                    ->whereYear('created_at', $year)
                    ->count();
            }

            // Project Distribution by Industry
            $industries = ProjectIndustries::all();
            $industryLabels = [];
            $industryData = [];
            foreach ($industries as $industry) {
                $industryLabels[] = $industry->getTranslation('name', app()->getLocale());
                $count = Project::where('industry_number', $industry->id)->count();
                $industryData[] = $count;
            }

            // Activity Logs
            $activitySearch = $request->get('search_log');
            $activityEvent = $request->get('event_log');

            $activities = Activity::with('causer')
                ->whereNotNull('causer_id')
                ->latest()
                ->when($activitySearch, function ($query) use ($activitySearch) {
                    return $query->where('description', 'like', "%{$activitySearch}%")
                        ->orWhereHasMorph('causer', [TenantUser::class], function ($q) use ($activitySearch) {
                            $q->where('name', 'like', "%{$activitySearch}%");
                        });
                })
                ->when($activityEvent, function ($query) use ($activityEvent) {
                    return $query->where('event', $activityEvent);
                })
                ->paginate(10, ['*'], 'activity_page')
                ->appends($request->all());

            if ($request->ajax()) {
                return view('ttxt-dashboard::dashboard.partials._activity_log_table', compact('activities'))->render();
            }

            return view('ttxt-dashboard::dashboard.index', compact(
                'quantityProjects',
                'quantityUser',
                'quantityPost',
                'quantityInvestmentGuide',
                'chartLabels',
                'projectChartData',
                'postChartData',
                'guestChartData',
                'investmentGuideChartData',
                'industryLabels',
                'industryData',
                'range',
                'activities',
                'visitStats',
                'visitChartLabels',
                'visitChartData',
                'botChartData',
                'totalInvestment',
                'projectStats',
                'hasProjects',
                'missingProjects',
                'siteVisitorStats',
                'monthIps',
                'visitorMonthlyLabels',
                'visitorMonthlyData',
                'returningVisitorMonthlyData'
            ));
        }
        $permissions = $user->getAllPermissionsFromGroup();
        $permissions = array_values(array_filter($permissions, fn($p) => $p !== 'backend_access'));
        if (empty($permissions)) {
            return view('backend.no_permission', [
                'message' => 'Bạn chưa được cấp quyền truy cập bất kỳ chức năng nào. Vui lòng liên hệ quản trị viên.',
                'hideSidebar' => true,
                'hideHeader' => true,
                'hideFooter' => true,
                'hideBreadcrumb' => true
            ]);
        }
        $routeName = $this->firstRouteForPermissions($permissions);
        if ($routeName) {
            return redirect()->route($routeName);
        }

        return view('backend.no_permission', [
            'message' => 'Không tìm thấy trang phù hợp với quyền của bạn.',
            'hideSidebar' => true
        ]);
    }

    public function exportLogs(Request $request)
    {
        $months = $request->get('months', 3);
        $format = $request->get('format', 'csv'); // Default to csv
        $result = $this->logExportService->exportToZip($months, false, null, $format); // false = recent logs

        if (!$result) {
            return back()->with('error', 'Không có dữ liệu để xuất.');
        }

        return response()->download($result['path'], $result['name'])->deleteFileAfterSend(true);
    }

    private function firstRouteForPermissions(array $permissions): ?string
    {
        foreach ($permissions as $permission) {
            $routeName = $this->routeNameForPermission($permission);

            if ($routeName && app('router')->has($routeName)) {
                return $routeName;
            }
        }

        return null;
    }

    private function routeNameForPermission(string $permission): ?string
    {
        $permission = str_replace('.', '/', $permission);

        if (in_array($permission, ['backend_access', 'dashboard', 'dashboard/view'], true)) {
            return null;
        }

        [$module, $action] = array_pad(explode('/', $permission, 2), 2, null);

        if (! $module) {
            return null;
        }

        $tenantModule = \App\Core\Module\Module::query()
            ->where('tenant_id', auth()->user()?->tenant_id)
            ->where('slug', $module)
            ->where('is_enabled', true)
            ->first();

        if ($tenantModule) {
            $moduleMenu = $tenantModule->config['menu'] ?? $tenantModule->manifest()['menu'] ?? [];

            if (! empty($moduleMenu['route']) && (! $action || $action === 'view')) {
                return $moduleMenu['route'];
            }

            if ($action && ! empty($moduleMenu['items'][$action]['route'])) {
                return $moduleMenu['items'][$action]['route'];
            }
        }

        if (! $action || $action === 'view') {
            return 'backend_' . str_replace('-', '_', $module);
        }

        $actionRouteMap = [
            'add' => 'create',
            'create' => 'create',
            'edit' => 'edit',
            'update' => 'edit',
            'delete' => 'delete',
        ];

        $routeAction = $actionRouteMap[$action] ?? $action;

        return 'backend_' . str_replace('-', '_', $module) . '_' . $routeAction;
    }
}
