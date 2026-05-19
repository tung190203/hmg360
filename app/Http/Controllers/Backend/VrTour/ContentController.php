<?php

namespace App\Http\Controllers\Backend\VrTour;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\Project;
use App\Models\Panorama;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;

class ContentController extends Controller
{
    public function __construct()
    {
        $this->selectedMainMenu = 'vr_tour';
        $this->selectedSubMenu('content');
        parent::__construct();

        if (!Gate::allows('content')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
    }

    public function index()
    {
        $vrtour     = Project::all();
        return view('backend.vrtour.content.index', compact('vrtour'));
    }

    public function getDataAll(Request $request, $vrtour_id)
    {
        $vrtour      = Project::find($vrtour_id);
        $link_vrtour = $vrtour->link_vrtour;
        $panorama    = Panorama::where('vrtour_id', $vrtour_id)->get();

        if ($request->reset == 'true' || $panorama->isEmpty()) {
            $pano = getDataVrtour($link_vrtour . 'vista3d/pano.json');
            if (empty($pano)) {
                return response()->json([
                    'data' => 'Hiện tại data pano không tồn tại'
                ]);
            }
            $dbPanoramas = $panorama->keyBy('title');
            $jsonTitles  = [];
            foreach ($pano as $pn) {
                $title = $pn['title'];
                $jsonTitles[] = $title;

                if (isset($dbPanoramas[$title])) {
                    $dbPanoramas[$title]->update([
                        'ids' => json_encode($pn['ids'])
                    ]);
                } else {
                    Panorama::create([
                        'vrtour_id' => $vrtour_id,
                        'ids'       => json_encode($pn['ids']),
                        'title'     => $title,
                        'user_id'   => Auth::id()
                    ]);
                }
            }

            if ($request->reset == 'true') {
                Panorama::where('vrtour_id', $vrtour_id)->whereNotIn('title', $jsonTitles)->delete();
            }

            $panorama = Panorama::where('vrtour_id', $vrtour_id)->get();
        }
        createFile('vrtour/' . $vrtour->vrtour_code, 'pano.js');
        file_put_contents(
            'vrtour/' . $vrtour->vrtour_code . '/pano.js', $panorama
        );
        $html = '';
        foreach ($panorama as $key => $pn) {
            $content = preg_replace('/<!--.*?-->/s', '', $pn->content);
            $html .= '
            <tr>
                <td>' . (++$key) . '</td>
                <td>' . $pn->title . '</td>
                <td>' . mb_substr($content, 0, 100, "UTF-8") . '...</td>
                <td class="grid_row1">
                    <a class="btn btn-info btn-sm mr-1"
                        href="' . route('backend_vrtour_content_edit', $pn->id) . '"
                        title="Chỉnh sửa">
                        <i class="fas fa-pencil-alt"></i>
                    </a>
                </td>
            </tr>
        ';
        }

        return response()->json([
            'data' => $html
        ]);
    }

    public function edit($id)
    {
        if (!Gate::allows('content/edit')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        $pano   = Panorama::findorFail($id);
        return view('backend.vrtour.content.edit', compact(['pano']));
    }

    public function store(Request $request, $id)
    {
        if (!Gate::allows('content/update')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        $request->validate([
            'ct_title'      => 'required',
            'ct_content'    => 'required',
            'ct_audio'      => 'required',
        ]);

        $new_pano                = Panorama::find($id);
        $new_pano->title         = $request->ct_title;
        $new_pano->title_en      = $request->ct_title_en;
        $new_pano->content       = $request->ct_content;
        $new_pano->content_en    = $request->ct_content_en;
        $new_pano->audio         = $request->ct_audio;
        $new_pano->audio_en      = $request->ct_audio_en;
        $new_pano->user_id       = Auth::id();
        $new_pano->save();
        file_put_contents('vrtour/'.Project::find($new_pano->vrtour_id)->vrtour_code.'/pano.js', Panorama::where('vrtour_id', $new_pano->vrtour_id)->get());
        return redirect()->to(route('backend_vrtour_content_index') . '?vrtour='.$new_pano->vrtour_id)->with('success', 'Cập nhật thông tin thành công');
    }
}
