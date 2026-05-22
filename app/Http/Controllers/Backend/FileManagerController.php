<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;

class FileManagerController extends Controller
{
    protected string $selectedMainMenu = 'file_manager';

    public function index()
    {
        return view('backend.filemanager.index');
    }
}
