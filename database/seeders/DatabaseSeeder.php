<?php

namespace Database\Seeders;

use App\Models\IndustrialProject;
use App\Models\ProductType;
use App\Models\ProjectIndustries;
use App\Models\ProjectType;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // --- XÓA DỮ LIỆU CŨ ---
        DB::table('project_district')->truncate();
        DB::table('projects')->truncate();
        DB::table('districts')->truncate();
        DB::table('project_industries')->truncate();
        DB::table('project_types')->truncate();
        DB::table('product_types')->truncate();
        DB::table('industrial_projects')->truncate();

        $types = [
            1 => "PPP",
            2 => "NNS",
            3 => "Đầu tư công",
        ];

        foreach ($types as $type) {
            ProjectType::factory()->create(['name' => $type]);
        }

        // --- SEED NGÀNH/LĨNH VỰC ---
        $industries = [
            1 => "Cầu đường",
            2 => "Cảng thuỷ nội địa, cảng cạn",
            3 => "Cấp nước, thoát nước, xử lý nước thải, chất thải, công viên cây xanh…",
            4 => "Khu đô thị, Nhà ở",
            5 => "Thương mại",
            6 => "Công nghiệp",
            7 => "Đường sắt đô thị",
            8 => "Du lịch",
            9 => "Nông nghiệp",
            10 => "CNTT và chuyển đổi số",
            11 => "Giáo dục",
            12 => "Bến xe",
        ];

        foreach ($industries as $industry) {
            ProjectIndustries::factory()->create(['name' => $industry]);
        }

        $product_types = [
            1 => "Đất công nghiệp",
            2 => "Nhà xưởng",
            3 => "Kho",
            4 => "Đất dịch vụ",
            5 => "Dịch vụ khác"
        ];

        foreach ($product_types as $type) {
            ProductType::factory()->create(['name' => $type]);
        }

        // --- ĐỌC DỮ LIỆU JSON ---
        $json = file_get_contents(database_path('seeders/data/du_an_ha_noi.json'));
        $projectsData = json_decode($json, true);

        // --- LẤY DANH SÁCH QUẬN/HUYỆN DUY NHẤT ---
        $districtsJson = file_get_contents(database_path('seeders/data/districts_ha_noi.json'));
        $districtsData = json_decode($districtsJson, true);

        $districtIdMap = [];

        // --- SEED BẢNG `districts` ---
        foreach ($districtsData as $districtName) {
            $id = DB::table('districts')->insertGetId([
                'name' => $districtName['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $districtIdMap[$districtName['name']] = $id;
        }

        // --- SEED `projects` + bảng trung gian ---
        foreach ($projectsData as $project) {
            $projectId = DB::table('projects')->insertGetId([
                'name' => $project['name'],
                'slug' => Str::slug($project['name']),
                'lat' => $project['lat'],
                'lng' => $project['lng'],
                'link' => $project['link'] ?? null,
                'price' => $project['price'] ?? null,
                'type_number' => $project['type_number'] ?? null,
                'industry_number' => $project['industry_number'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($project['districts'] as $districtName) {
                DB::table('project_district')->insert([
                    'project_id' => $projectId,
                    'district_id' => $districtIdMap[$districtName],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $jsonPath = database_path('seeders/data/industrial_projects.json');
        $data = json_decode(File::get($jsonPath), true);
    
        // Ghi vào DB
        foreach ($data as $item) {
            IndustrialProject::create($item);
        }
    }
}
