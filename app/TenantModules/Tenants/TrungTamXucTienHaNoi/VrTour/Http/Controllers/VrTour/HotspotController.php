<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\VrTour\Http\Controllers\VrTour;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use App\Libs\DataGrid;
use App\Libs\Util;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Project;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Hotspot;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\IndustrialProject;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\ProductType;
use Auth;
use Illuminate\Support\Facades\DB;

class HotspotController extends Controller
{
    public $hotspot;

    public function __construct(Hotspot $hotspot)
    {
        $this->hotspot = $hotspot;
        $this->selectedMainMenu = 'vr-tour';
        $this->selectedSubMenu('hotspot');
        parent::__construct();

        if (!Gate::allows('hotspot')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $vrtour     = Project::all();
        return view('ttxt-vrtour::vrtour.hotspot.index', compact(['vrtour']));
    }

    public function getHotspot(Request $request, $vrtour_id)
    {
        $html = '';
        try {
            DB::beginTransaction();
            //get tour
            $vrtour = Project::find($vrtour_id);
            $link_vrtour = $vrtour->link_vrtour;
            $media_index = $vrtour->media_index;
            //get hotspot from db
            $typeFilter = match ((int) $request->type ) {
                0 => [1, 2],
                1 => [1, 2, 3],
                2 => [2],
            };
            $hotspot_db = Hotspot::where('vrtour_id', $vrtour_id)->whereIn('type', $typeFilter)->get();
            if ($request->reset == 'true') {
                $response = getDataVrtour($link_vrtour . 'vista3d/hotspot.json');
                $hotspotsJson = json_decode($response['data'], true);

                $media_index = $response['media_index'];
                $vrtour->media_index = $media_index;
                $vrtour->save();
                $positionsFromJson = collect($hotspotsJson)->pluck('position')->toArray();
                $existingHotspots = Hotspot::where('vrtour_id', $vrtour_id)->get()->keyBy('potision');
                $existingProjects = IndustrialProject::where('project_id', $vrtour_id)->get()->keyBy('code');

                foreach ($hotspotsJson as $hp) {
                    $position = $hp['position'];
                    $existsHotspot = $existingHotspots->has($position);
                    $existsProject = $existingProjects->has($position);
                    if ($existsHotspot && $existsProject) {
                        continue;
                    }
                    if (!$existsHotspot) {
                        $new_hp = new Hotspot;
                        $new_hp->vrtour_id = $vrtour_id;
                        $new_hp->potision = $position;
                        $new_hp->acreage = $hp['acreage'] ?? null;
                        $new_hp->url = $hp['url'];
                        $new_hp->opacity = $hp['opacity'];
                        $new_hp->tooltip = (!str_contains($position, 'cms_eye') && !str_contains($position, 'cms_fly')) ? $hp['tooltip'] : '';
                        $new_hp->tooltip_en = $hp['tooltip_en'] ?? null;
                        $new_hp->type = str_contains($position, 'cms_') ? 1 : (str_contains($position, 'cmss_') ? 2 : 3);
                        $new_hp->user_id = Auth::id();
                        $new_hp->save();
                    }
                    if (str_contains($position, 'cmss_') && !$existsProject) {

                        $translations = [
                            'vi' => $hp['tooltip'] ?? null,
                            'en' => $hp['tooltip_en'] ?? null,
                        ];

                        IndustrialProject::create([
                            'project_id' => $vrtour_id,
                            'code'       => $position,
                            'name'       => $vrtour->name,
                            'acreage'    => $hp['acreage'] ?? null,
                            'description' => $translations,
                            'link'       => $link_vrtour . 'vista3d/search.html?search_link='
                                . $link_vrtour . '?media-index=' . $media_index
                                . '&hct=HOLDER_SELECT_LANGUAGE_CN2&trigger-overlay-name=' . $position
                                . '&focus-overlay-name=' . $position
                                . '&show-overlays-names=' . $position
                                . '&skip-loading',
                        ]);
                    }
                }
                Hotspot::where('vrtour_id', $vrtour_id)
                    ->whereNotIn('potision', $positionsFromJson)
                    ->delete();
                IndustrialProject::where('project_id', $vrtour_id)
                    ->whereNotIn('code', $positionsFromJson)
                    ->delete();
                createFile('vrtour/' . $vrtour->vrtour_code, 'hotspot.js');
                file_put_contents('vrtour/' . $vrtour->vrtour_code . '/hotspot.js', Hotspot::where('vrtour_id', $vrtour_id)->get());
            }
            // --- KẾT THÚC KHỐI SỬA ĐỔI ---
            foreach ($hotspot_db as $key => $hp) {
                $potision = str_starts_with($hp->potision, 'cmss_') ? substr($hp->potision, 5) : $hp->potision;
                $html .= '<tr>';
                $html .= '<td>' . (++$key) . '</td>';
                $html .= '<td><img src="' . $hp->url . '" style="width:100px;height:100px;""></td>';
                $html .= '<td>' . $potision . '</td>';
                $html .= '<td>' . $hp->tooltip . '</td>';
                $html .= '<td>' . $hp->opacity . '</td>';
                $html .= '<td class="grid_row1">';
                $html .= '<a class="btn btn-info btn-sm mr-1" href="' . route('tenant.trung_tam_xuc_tien_ha_noi.vr_tour.hotspot.edit', $hp->id) . '" title="Chỉnh sửa"><i class="fas fa-pencil-alt"></i></a>';
                $html .= '</td>';
                $html .= '</tr>';
            }
            DB::commit();
            return response()->json(['data' => $html]);
        } catch (\Exception $e) {
            dd($e->getMessage());
            DB::rollBack();
            return response()->json(['data' => $html]);
        }
    }
    public function edit($hotspot_id)
    {
        if (!Gate::allows('hotspot/edit')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        $hotspot = Hotspot::with('IndustrialProject.productType')->findOrFail($hotspot_id);
        $productType = ProductType::latest('id')->get();

        $selected = optional($hotspot->IndustrialProject)->product_type;

        $option_product_types = ProductType::makeOptions($productType, $selected);
        $hotspot_unit = Hotspot::makeUnitOptions($hotspot->unit);
        return view('ttxt-vrtour::vrtour.hotspot.edit', compact(['hotspot', 'option_product_types','hotspot_unit']));
    }

    public function store(Request $request, $hotspot_id)
    {
        if (!Gate::allows('hotspot/update')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        $request->validate([
            'hp_url'        => 'required',
            'hp_potision'   => 'required',
            'hp_opacity'    => 'required',
        ]);
        $translations = ['vi' => $request->hp_tooltip, 'en' => $request->hp_tooltip_en ?? null,];
        $new_hp             = Hotspot::findorFail($hotspot_id);
        $new_hp->potision   = $request->hp_potision;
        $new_hp->url        = $request->hp_url;
        $new_hp->url_en     = $request->hp_url_en;
        $new_hp->opacity    = $request->hp_opacity;
        $new_hp->tooltip    = $request->hp_tooltip;
        $new_hp->tooltip_en = $request->hp_tooltip_en;
        $new_hp->acreage    = $request->acreage;
        $new_hp->intended_use    = $request->intended_use;
        $new_hp->unit       = $request->unit ?? 0;

        $new_hp->user_id    = Auth::id();
        $new_hp->save();
        if ($new_hp->IndustrialProject) {
            IndustrialProject::updateOrCreate(
                [
                    'project_id' => $new_hp->vrtour_id,
                    'code'       => $new_hp->potision
                ],
                [
                    'name'        => Project::find($new_hp->vrtour_id)->name,
                    'product_type' => $request->product_type,
                    'intended_use' => $request->intended_use,
                    'unit'        => $request->unit ?? 0,
                    'acreage'     => $request->acreage,
                    'description' => $translations, // Spatie tự JSON
                ]
            );
        }
        file_put_contents('vrtour/' . Project::find($new_hp->vrtour_id)->vrtour_code . '/hotspot.js', Hotspot::where('vrtour_id', $new_hp->vrtour_id)->get());
        return redirect()->to(route('tenant.trung_tam_xuc_tien_ha_noi.vr_tour.hotspot.index') . '?vrtour=' . $new_hp->vrtour_id . '&type=' . ($new_hp->type == 3 ? 1 : 0))->with('success', 'Cập nhật thông tin thành công');
    }
}
