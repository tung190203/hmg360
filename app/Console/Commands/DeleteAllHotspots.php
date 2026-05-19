<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Hotspot;
use Illuminate\Support\Facades\DB;

class DeleteAllHotspots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotspot:clear';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xóa toàn bộ hotspot và industrial project liên quan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::transaction(function () {

            $hotspots = Hotspot::with('IndustrialProject')->get();

            foreach ($hotspots as $hotspot) {
                if ($hotspot->IndustrialProject) {
                    $hotspot->IndustrialProject->delete();
                }
                $hotspot->delete();
            }
        });

        $this->info('Đã xóa toàn bộ hotspot và industrial project liên quan!');
    }
}
