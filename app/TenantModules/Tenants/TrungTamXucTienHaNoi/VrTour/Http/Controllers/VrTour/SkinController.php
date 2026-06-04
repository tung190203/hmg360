<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\VrTour\Http\Controllers\VrTour;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Project;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\ConnectMap;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Investor;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\WelcomeScreen;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Plan;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\LegalDocument;
use Illuminate\Support\Facades\DB;
use Auth;
use Illuminate\Support\Facades\File;

class SkinController extends Controller
{
    public function __construct()
    {
        $this->selectedMainMenu = 'vr-tour';
        $this->selectedSubMenu('skin');
        parent::__construct();

        if (!Gate::allows('skin')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
    }

    public function index()
    {
        $vrtour     = Project::all();
        return view('ttxt-vrtour::vrtour.skin.index', compact('vrtour'));
    }

    public function getDataAll($vrtour_id, $type)
    {
            $vrtour         = Project::find($vrtour_id);
            $link_vrtour    = $vrtour->link_vrtour;
            //connnect_map
            $connect_map    = ConnectMap::where('vrtour_id', $vrtour_id)->first();
            //location
            $location       = Project::find($vrtour_id)->location_in_tour;
            //investor
            $investor       = Investor::where('vrtour_id', $vrtour_id)->first();
            //screen
            $screen         = WelcomeScreen::where('vrtour_id', $vrtour_id)->first();
            //plan
            $plan           = Plan::where('vrtour_id', $vrtour_id)->first();
            //document 
            $document       = LegalDocument::where('vrtour_id', $vrtour_id)->get();
            $data['connect_map']    = $connect_map;
            $data['location']       = $vrtour->location_in_tour;
            $data['investor']       = $investor;
            $data['screen']         = $screen;
            $data['plan']           = $plan;
            $data['document']       = $document; 
            return response()->json([
                'data'      => $data,
                'status'    => true,
                'message'   => 'Lấy dữ liệu thành công !'
            ], 200);
    }

    public function updateDataAll(Request $request, $vrtour_id)
    {
        try {
            DB::beginTransaction();
            //get tour
            $vrtour                     = Project::find($vrtour_id);
            $link_vrtour                = $vrtour->link_vrtour;
            //welcome screen
            if (in_array($request->type, [0,1])) {
                if ($request['screen']['id'] != null) {
                    $screen                = WelcomeScreen::findorFail($request['screen']['id']);
                } else {
                    $screen                = new WelcomeScreen();
                }
                $screen->vrtour_id          = $vrtour_id;
                $screen->title              = $request['screen']['title'];
                $screen->description        = $request['screen']['description'];
                $screen->voice              = $request['screen']['voice'];
                $screen->show_investor      = $request['screen']['status'] == true ? 1 : 0;
                $screen->investor_img       = $request['screen']['investor_img'];
                $screen->investor_desc1     = $request['screen']['investor_desc1'];
                $screen->investor_desc2     = $request['screen']['investor_desc2'];
                $screen->investor_desc3     = $request['screen']['investor_desc3'];
                $screen->user_id            = Auth::id();
                $screen->save();
                createFile('vrtour/'.$vrtour->vrtour_code, 'welcome_screen.js');
                file_put_contents('vrtour/'.$vrtour->vrtour_code.'/welcome_screen.js', $screen);
            }

            //connect map
            if (in_array($request->type, [0,3])) {
                if ($request['connect_data']['id'] != null) {
                    $connect_map                = ConnectMap::findorFail($request['connect_data']['id']);
                } else {
                    $connect_map                = new ConnectMap();
                }
                $connect_map->vrtour_id     = $vrtour_id;
                $connect_map->image         = $request['connect_data']['image'];
                $connect_map->image_en      = $request['connect_data']['image_en'];
                $connect_map->content       = $request['connect_data']['content'];
                $connect_map->content_en    = $request['connect_data']['content_en'];
                $connect_map->user_id       = Auth::id();
                $connect_map->save();
                createFile('vrtour/'.$vrtour->vrtour_code, 'connectmap.js');
                file_put_contents('vrtour/'.$vrtour->vrtour_code.'/connectmap.js', $connect_map);
            }

            //document
            if (in_array($request->type, [0, 4])) {
                if (!empty($request->document)) {
                    foreach ($request->document as $key => $doc) {
                        if ($doc['id'] != null) {
                            $_document = LegalDocument::findorFail($doc['id']);
                        } else {
                            $_document = new LegalDocument();
                        }
                        $_document->vrtour_id   = $vrtour_id;
                        $_document->name        = $doc['document_name'];
                        $_document->name_en     = $doc['document_name_en'];
                        $_document->download    = $doc['download'];
                        $_document->extracted_text = $doc['extracted_text'] ?? $_document->extracted_text;
                        $_document->extracted_summary = $doc['extracted_summary'] ?? $_document->extracted_summary;
                        $_document->extracted_language = $doc['extracted_language'] ?? $_document->extracted_language;
                        $_document->extracted_at = !empty($doc['extracted_at']) ? $doc['extracted_at'] : $_document->extracted_at;
                        $_document->user_id     = Auth::id();
                        $_document->save();
                    }
                } else {
                    $all_document = LegalDocument::where('vrtour_id', $vrtour_id)->delete();
                }
                $all_document = LegalDocument::where('vrtour_id', $vrtour_id)->get();
                createFile('vrtour/'.$vrtour->vrtour_code, 'document.js');
                file_put_contents('vrtour/'.$vrtour->vrtour_code.'/document.js', $all_document);
            }
            
            //plan
            if (in_array($request->type, [0,5])) {
                if ($request['plan']['id'] != null) {
                    $plan                   = Plan::findorFail($request['plan']['id']);
                } else {
                    $plan                   = new Plan();
                }
                $plan->vrtour_id            = $vrtour_id;
                $plan->show                 = $request['plan']['show'] == true ? 1 : 0;
                $plan->background           = $vrtour->banner_image;

                $plan->image1              = $request['plan']['image1'];
                $plan->title1              = $request['plan']['title1'];
                $plan->title1_en           = $request['plan']['title1_en'];
                $plan->content1            = $request['plan']['content1'];
                $plan->content1_en         = $request['plan']['content1_en'];

                $plan->image2              = $request['plan']['image2'];
                $plan->title2              = $request['plan']['title2'];
                $plan->title2_en           = $request['plan']['title2_en'];
                $plan->content2            = $request['plan']['content2'];
                $plan->content2_en         = $request['plan']['content2_en'];

                $plan->image3              = $request['plan']['image3'];
                $plan->title3              = $request['plan']['title3'];
                $plan->title3_en           = $request['plan']['title3_en'];
                $plan->content3            = $request['plan']['content3'];
                $plan->content3_en         = $request['plan']['content3_en'];
                $plan->user_id             = Auth::id();
                $plan->save();
                createFile('vrtour/'.$vrtour->vrtour_code, 'plan.js');
                file_put_contents('vrtour/'.$vrtour->vrtour_code.'/plan.js', $plan);
            }
            
            //investor
            if (in_array($request->type, [0,6])) {
                if ($request['investor']['id'] != null) {
                    $investor                = Investor::findorFail($request['investor']['id']);
                } else {
                    $investor                = new Investor();
                }
                $investor->vrtour_id    = $vrtour_id;
                $investor->name         = $request['investor']['name'];
                $investor->name_en      = $request['investor']['name_en'];
                $investor->image        = $request['investor']['image'];
                $investor->content1     = $request['investor']['content1'];
                $investor->content2     = $request['investor']['content2'];
                $investor->content3     = $request['investor']['content3'];
                $investor->content1_en  = $request['investor']['content1_en'];
                $investor->content2_en  = $request['investor']['content2_en'];
                $investor->content3_en  = $request['investor']['content3_en'];
                $investor->website      = $request['investor']['website'];
                $investor->sologan      = $request['investor']['sologan'];
                $investor->sologan_en   = $request['investor']['sologan_en'];
                $investor->status       = $request['investor']['status'] == true ? 1 : 0;
                $investor->user_id      = Auth::id();
                $investor->save();
                createFile('vrtour/'.$vrtour->vrtour_code, 'investor.js');
                file_put_contents('vrtour/'.$vrtour->vrtour_code.'/investor.js', $investor);
            }
            
            //location
            if (in_array($request->type, [0,7])) {
                $vrtour->location_in_tour   = $request['location']['map'];
                $vrtour->save();
                createFile('vrtour/'.$vrtour->vrtour_code, 'location.js');
                file_put_contents('vrtour/'.$vrtour->vrtour_code.'/location.js', json_encode([
                        'location'  => $vrtour->location_in_tour,
                        'general'   => $vrtour->link
                    ]));
            }

            DB::commit();
            return response()->json([
                'data'      => [],
                'status'    => true,
                'message'   => 'Cập nhật dữ liệu thành công !'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data'      => [],
                'status'    => false,
                'message'   => $e->getMessage()
            ], 500);
        }
    }
}
