<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use Illuminate\Support\Facades\File;

class RenameVrtourFolders extends Command
{
    protected $signature = 'vrtour:rename-folders';
    protected $description = 'Rename vrtour folders from project name (vi) to vrtour_code';
    public function handle()
    {
        $basePath = public_path('vrtour');
        if (!File::exists($basePath)) {
            $this->error('Folder public/vrtour không tồn tại');
            return Command::FAILURE;
        }

        $projects = Project::all();

        foreach ($projects as $project) {
            $oldFolder = $basePath . DIRECTORY_SEPARATOR . $project->name;
            $newFolder = $basePath . DIRECTORY_SEPARATOR . $project->vrtour_code;

            if (!File::exists($oldFolder)) {
                $this->warn("Không tìm thấy folder: {$project->name}");
                continue;
            }

            if (File::exists($newFolder)) {
                $this->warn("Folder đã tồn tại: {$project->vrtour_code}");
                continue;
            }

            File::move($oldFolder, $newFolder);

            $this->info("✔ Renamed: {$project->name} → {$project->vrtour_code}");
        }

        $this->info('Hoàn tất rename folder vrtour');
        return Command::SUCCESS;
    }
}


