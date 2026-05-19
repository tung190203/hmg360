<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InterestController extends Controller
{
    public function toggleInterest(Request $request) 
    {
        $guest = auth('guest')->user();
        if (!$guest) {
            return response()->json(['message' => 'Vui lòng đăng nhập để thực hiện thao tác này.'], 401);
        }
        $request->validate([
            'interestable_id'   => 'required|integer',
            'interestable_type' => 'required|string',
        ]);
        
        $guest = auth('guest')->user();
        $interest = $guest->interests()
            ->where('interestable_id', $request->interestable_id)
            ->where('interestable_type', $request->interestable_type)
            ->first();
        if ($interest) {
            $interest->delete();
            return response()->json(['message' => 'Bỏ khỏi mục quan tâm thành công.']);
        } else {
            $guest->interests()->create([
                'interestable_id' => $request->interestable_id,
                'interestable_type' => $request->interestable_type,
            ]);
            return response()->json(['message' => 'Đã thêm vào mục quan tâm.']);
        }
    }
}
