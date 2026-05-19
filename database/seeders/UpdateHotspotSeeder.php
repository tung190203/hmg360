<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateHotspotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $data = [
            // --- KHU HH1 & LK1 ---
            ['potision' => 'cmss_hh1', 'intended_use' => null, 'acreage' => 115.402],
            ['potision' => 'cmss_h1.1', 'intended_use' => 'Đất cơ sở lưu trú, căn hộ lưu trú, văn phòng, dịch vụ, thương mại', 'acreage' => 52.496],
            ['potision' => 'cmss_h1.2', 'intended_use' => 'Đất cơ sở lưu trú, căn hộ lưu trú, thương mại', 'acreage' => 25.633],
            ['potision' => 'cmss_cx1.1', 'intended_use' => 'Đất cây xanh công viên, mặt nước', 'acreage' => 31.909],
            ['potision' => 'cmss_cx1.2', 'intended_use' => 'Đất cây xanh công viên', 'acreage' => 5.364],
            ['potision' => 'cmss_lk1', 'intended_use' => null, 'acreage' => 23.548],
            ['potision' => 'cmss_gtlk1', 'intended_use' => 'Đất giao thông, hạ tầng kỹ thuật', 'acreage' => 23.548],

            // --- KHU HỖN HỢP SỐ 2 (HH2 -> HH5) ---
            ['potision' => 'cmss_hh2', 'intended_use' => null, 'acreage' => 196.573],
            ['potision' => 'cmss_h2.1', 'intended_use' => 'Đất cơ sở kinh doanh thương mại, dịch vụ, văn phòng', 'acreage' => 38.723],
            ['potision' => 'cmss_h2.2', 'intended_use' => 'Đất cơ sở kinh doanh thương mại, dịch vụ, văn phòng', 'acreage' => 49.763],
            ['potision' => 'cmss_h2.3', 'intended_use' => 'Đất căn hộ lưu trú, cơ sở lưu trú, văn phòng', 'acreage' => 44.995],
            ['potision' => 'cmss_h2.4', 'intended_use' => 'Đất căn hộ lưu trú, cơ sở lưu trú, văn phòng, dịch vụ, thương mại, dịch vụ giáo dục', 'acreage' => 33.852],
            ['potision' => 'cmss_cx2.1', 'intended_use' => 'Đất cây xanh công viên', 'acreage' => 4.538],
            ['potision' => 'cmss_cx2.2', 'intended_use' => 'Đất cây xanh công viên', 'acreage' => 4.418],
            ['potision' => 'cmss_gt1', 'intended_use' => 'Đất giao thông', 'acreage' => 20.284],

            ['potision' => 'cmss_hh3', 'intended_use' => null, 'acreage' => 159.442],
            ['potision' => 'cmss_h3.1', 'intended_use' => 'Đất cơ sở lưu trú, căn hộ lưu trú, văn phòng', 'acreage' => 44.461],
            ['potision' => 'cmss_h3.2', 'intended_use' => 'Đất cơ sở lưu trú, căn hộ lưu trú, kinh doanh thương mại, dịch vụ, văn phòng (Trung tâm thương mại và dịch vụ FBS)', 'acreage' => 19.246],
            ['potision' => 'cmss_h3.3', 'intended_use' => 'Đất cơ sở lưu trú, căn hộ lưu trú, văn phòng', 'acreage' => 19.920],
            ['potision' => 'cmss_h3.4', 'intended_use' => 'Đất dịch vụ giáo dục (Trường TH-THCS-THPT)', 'acreage' => 26.007],
            ['potision' => 'cmss_cx3.1', 'intended_use' => 'Đất cây xanh công viên', 'acreage' => 24.458],
            ['potision' => 'cmss_cx3.2', 'intended_use' => 'Đất cây xanh công viên', 'acreage' => 17.242],
            ['potision' => 'cmss_p1', 'intended_use' => 'Đất bãi đỗ xe', 'acreage' => 8.108],

            ['potision' => 'cmss_hh4', 'intended_use' => 'Đất cơ sở kinh doanh, thương mại, dịch vụ', 'acreage' => 46.154],

            ['potision' => 'cmss_hh5', 'intended_use' => null, 'acreage' => 224.650],
            ['potision' => 'cmss_h5.1', 'intended_use' => 'Đất cơ sở kinh doanh thương mại, dịch vụ, văn phòng', 'acreage' => 43.513],
            ['potision' => 'cmss_h5.2', 'intended_use' => 'Đất cơ sở kinh doanh, thương mại, dịch vụ, văn phòng kết hợp lưu trú (Hệ thống nhà hàng, cửa hàng tiện ích Twiter Beans)', 'acreage' => 8.663],
            ['potision' => 'cmss_h5.3', 'intended_use' => 'Đất cơ sở kinh doanh thương mại, dịch vụ, văn phòng (Trung tâm thương mại, dịch vụ và giới thiệu sản phẩm nội thất HHPD)', 'acreage' => 9.929],
            ['potision' => 'cmss_h5.4', 'intended_use' => 'Đất cơ sở kinh doanh thương mại, dịch vụ, văn phòng', 'acreage' => 46.614],
            ['potision' => 'cmss_h5.5', 'intended_use' => 'Đất bệnh viện, cơ sở y tế chất lượng cao, trung tâm chăm sóc sức khỏe', 'acreage' => 41.155],
            ['potision' => 'cmss_h5.6', 'intended_use' => 'Đất dịch vụ giáo dục (Trường TH, THCS, THPT TH True Education)', 'acreage' => 25.618],
            ['potision' => 'cmss_h5.7', 'intended_use' => 'Đất dịch vụ giáo dục (Trường MN-TH-THCS-THPT)', 'acreage' => 25.500],
            ['potision' => 'cmss_cx5.1', 'intended_use' => 'Đất cây xanh công viên', 'acreage' => 3.970],
            ['potision' => 'cmss_cx5.2', 'intended_use' => 'Đất cây xanh công viên', 'acreage' => 6.700],
            ['potision' => 'cmss_gt2', 'intended_use' => 'Đất giao thông', 'acreage' => 10.828],
            ['potision' => 'cmss_p2', 'intended_use' => 'Bãi đỗ xe', 'acreage' => 2.160],

            // --- KHU LK2 & KHU HỖN HỢP SỐ 3 ---
            ['potision' => 'cmss_lk2', 'intended_use' => null, 'acreage' => 184.429],
            ['potision' => 'cmss_cxlk', 'intended_use' => 'Đất cây xanh công viên theo quy hoạch chung', 'acreage' => 23.620],
            ['potision' => 'cmss_mnlk', 'intended_use' => 'Đất cây xanh mặt nước theo quy hoạch chung', 'acreage' => 19.555],
            ['potision' => 'cmss_gtlk2', 'intended_use' => 'Đất giao thông theo quy hoạch chung', 'acreage' => 141.254],

            ['potision' => 'cmss_hh6', 'intended_use' => 'Đất dịch vụ giáo dục (Trường MN-TH-THCS-THPT)', 'acreage' => 21.654],
            ['potision' => 'cmss_hh7', 'intended_use' => null, 'acreage' => 37.589],
            ['potision' => 'cmss_h7.1', 'intended_use' => 'Đất cơ sở kinh doanh, thương mại, dịch vụ dân sinh', 'acreage' => 9.891],
            ['potision' => 'cmss_h7.2', 'intended_use' => 'Đất cơ sở kinh doanh, thương mại, dịch vụ dân sinh', 'acreage' => 4.519],
            ['potision' => 'cmss_cx7.1', 'intended_use' => 'Đất cây xanh công viên', 'acreage' => 1.823],
            ['potision' => 'cmss_cx7.2', 'intended_use' => 'Đất cây xanh công viên', 'acreage' => 0.543], // Ảnh ghi 543, thường là 0.543 hoặc 543m2 tùy đơn vị
            ['potision' => 'cmss_gt3', 'intended_use' => 'Đất dự kiến mở rộng đường giao thông khu vực', 'acreage' => 20.813],

            ['potision' => 'cmss_lk3', 'intended_use' => null, 'acreage' => 20.414],
            ['potision' => 'cmss_cx_mn', 'intended_use' => 'Đất cây xanh công viên, mặt nước theo quy hoạch chung', 'acreage' => 3.960],
            ['potision' => 'cmss_gtlk3', 'intended_use' => 'Đất giao thông theo quy hoạch chung', 'acreage' => 16.454],
        ];
        foreach ($data as $item) {
            // 1. Cập nhật bảng hotspot (theo trường potision)
            DB::table('hotspot')
                ->where('potision', $item['potision'])
                ->update([
                    'intended_use' => $item['intended_use'],
                    'acreage'      => $item['acreage'],
                    'updated_at'   => now(),
                ]);

            // 2. Cập nhật bảng industrial_projects (theo trường code)
            DB::table('industrial_projects')
                ->where('code', $item['potision'])
                ->update([
                    'intended_use' => $item['intended_use'],
                    'acreage'      => $item['acreage'],
                    'updated_at'   => now(),
                ]);
        }

        $this->command->info('Đã cập nhật dữ liệu thành công cho bảng hotspot và industrial_projects!');
    }
}
