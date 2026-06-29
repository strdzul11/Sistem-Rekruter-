<?php

namespace Database\Seeders;

use App\Models\JobListing;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class JobListingSeeder extends Seeder
{
    public function run(): void
    {
        if (JobListing::count() > 0) {
            $this->command?->warn('Lowongan sudah ada di database. Lewati JobListingSeeder.');
            return;
        }

        $company = SystemSetting::getVal('company_name', 'PT. Sat.Echno');

        $jobs = [
            [
                'position' => 'Software Engineer',
                'company' => $company,
                'location' => 'Jakarta',
                'description' => 'Bergabunglah dengan tim engineering kami untuk membangun dan memelihara aplikasi web yang scalable. Anda akan terlibat dalam seluruh siklus pengembangan, mulai dari perancangan fitur hingga deployment.',
                'requirements' => "• S1 Teknik Informatika atau setara\n• Minimal 2 tahun pengalaman sebagai developer\n• Menguasai PHP/Laravel, JavaScript, MySQL\n• Familiar dengan Git dan REST API\n• Mampu bekerja dalam tim agile",
                'salary_range' => 'Rp 8.000.000 - Rp 15.000.000',
                'employment_type' => 'full-time',
                'status' => 'active',
                'created_by' => 1,
            ],
            [
                'position' => 'Frontend Developer',
                'company' => $company,
                'location' => 'Jakarta (Hybrid)',
                'description' => 'Kami mencari Frontend Developer yang passionate untuk menciptakan antarmuka pengguna yang responsif, cepat, dan mudah digunakan di berbagai perangkat.',
                'requirements' => "• S1 Teknik Informatika/Komputer atau setara\n• Pengalaman 1-3 tahun di frontend development\n• Menguasai HTML, CSS, JavaScript, Tailwind CSS\n• Pengalaman dengan Vue.js atau React menjadi nilai plus\n• Perhatian tinggi terhadap detail UI/UX",
                'salary_range' => 'Rp 7.000.000 - Rp 13.000.000',
                'employment_type' => 'full-time',
                'status' => 'active',
                'created_by' => 1,
            ],
            [
                'position' => 'UI/UX Designer',
                'company' => $company,
                'location' => 'Jakarta',
                'description' => 'Desain pengalaman digital yang intuitif untuk produk internal dan klien kami. Anda akan berkolaborasi erat dengan tim product dan engineering.',
                'requirements' => "• S1 Desain Komunikasi Visual atau setara\n• Portfolio desain web/mobile yang kuat\n• Menguasai Figma dan Adobe Creative Suite\n• Pengalaman 2-4 tahun di bidang UI/UX\n• Memahami prinsip user-centered design",
                'salary_range' => 'Rp 6.500.000 - Rp 11.000.000',
                'employment_type' => 'full-time',
                'status' => 'active',
                'created_by' => 2,
            ],
            [
                'position' => 'Quality Assurance (QA) Engineer',
                'company' => $company,
                'location' => 'Jakarta',
                'description' => 'Pastikan kualitas produk software kami melalui pengujian manual dan otomatis. Anda akan menjadi gatekeeper kualitas sebelum setiap rilis produk.',
                'requirements' => "• S1 Teknik Informatika atau setara\n• Pengalaman 1-2 tahun sebagai QA\n• Mampu membuat test case dan test plan\n• Familiar dengan tools testing (Postman, Selenium)\n• Teliti, sistematis, dan komunikatif",
                'salary_range' => 'Rp 5.500.000 - Rp 9.000.000',
                'employment_type' => 'full-time',
                'status' => 'active',
                'created_by' => 2,
            ],
            [
                'position' => 'DevOps Engineer',
                'company' => $company,
                'location' => 'Jakarta (Remote)',
                'description' => 'Kelola infrastruktur cloud, CI/CD pipeline, dan monitoring sistem agar aplikasi kami selalu available dan performa optimal.',
                'requirements' => "• S1 Teknik Informatika atau setara\n• Pengalaman 2+ tahun di DevOps/SRE\n• Menguasai Linux, Docker, dan cloud (AWS/GCP)\n• Familiar dengan CI/CD (GitHub Actions, GitLab CI)\n• Pengalaman monitoring (Grafana, Prometheus) menjadi nilai plus",
                'salary_range' => 'Rp 10.000.000 - Rp 18.000.000',
                'employment_type' => 'full-time',
                'status' => 'active',
                'created_by' => 1,
            ],
            [
                'position' => 'Data Analyst',
                'company' => $company,
                'location' => 'Bandung',
                'description' => 'Analisis data bisnis dan operasional untuk memberikan insight yang actionable kepada tim manajemen dan product.',
                'requirements' => "• S1 Statistika, Matematika, atau Teknik Industri\n• Pengalaman 1-3 tahun sebagai data analyst\n• Menguasai SQL, Excel, dan Python/R\n• Mampu membuat dashboard dan laporan visual\n• Kemampuan storytelling dengan data",
                'salary_range' => 'Rp 7.000.000 - Rp 12.000.000',
                'employment_type' => 'full-time',
                'status' => 'active',
                'created_by' => 1,
            ],
            [
                'position' => 'Digital Marketing Specialist',
                'company' => $company,
                'location' => 'Jakarta',
                'description' => 'Rancang dan eksekusi strategi pemasaran digital untuk meningkatkan brand awareness dan lead generation produk teknologi kami.',
                'requirements' => "• S1 Marketing, Komunikasi, atau setara\n• Pengalaman 1-2 tahun di digital marketing\n• Menguasai Google Ads, Meta Ads, dan SEO\n• Familiar dengan Google Analytics dan social media\n• Kreatif dan data-driven",
                'salary_range' => 'Rp 6.000.000 - Rp 10.000.000',
                'employment_type' => 'full-time',
                'status' => 'active',
                'created_by' => 2,
            ],
            [
                'position' => 'HR Generalist',
                'company' => $company,
                'location' => 'Jakarta',
                'description' => 'Dukung operasional HR mulai dari rekrutmen, onboarding, administrasi karyawan, hingga pengembangan budaya perusahaan.',
                'requirements' => "• S1 Psikologi, Manajemen, atau HRM\n• Pengalaman 2+ tahun di bidang HR\n• Memahami UU Ketenagakerjaan\n• Mampu mengelola rekrutmen end-to-end\n• Komunikasi interpersonal yang baik",
                'salary_range' => 'Rp 6.000.000 - Rp 9.500.000',
                'employment_type' => 'full-time',
                'status' => 'active',
                'created_by' => 2,
            ],
            [
                'position' => 'IT Support Specialist',
                'company' => $company,
                'location' => 'Jakarta',
                'description' => 'Memberikan dukungan teknis untuk infrastruktur IT kantor dan membantu karyawan menyelesaikan masalah hardware/software sehari-hari.',
                'requirements' => "• D3/S1 Teknik Informatika atau setara\n• Pengalaman 1-2 tahun di IT support\n• Menguasai troubleshooting Windows, jaringan, dan perangkat kantor\n• Responsif dan service-oriented\n• Bersedia on-call jika diperlukan",
                'salary_range' => 'Rp 5.000.000 - Rp 7.500.000',
                'employment_type' => 'full-time',
                'status' => 'active',
                'created_by' => 1,
            ],
            [
                'position' => 'Magang Software Developer',
                'company' => $company,
                'location' => 'Jakarta',
                'description' => 'Program magang 3-6 bulan untuk mahasiswa yang ingin belajar pengembangan web di lingkungan profesional dengan mentor berpengalaman.',
                'requirements' => "• Mahasiswa aktif S1 Teknik Informatika semester 6+\n• Memahami dasar programming (PHP/JavaScript)\n• Familiar dengan Git\n• Motivasi belajar tinggi dan mampu bekerja dalam tim\n• Bersedia magang minimal 3 bulan",
                'salary_range' => 'Rp 3.000.000 - Rp 4.000.000',
                'employment_type' => 'internship',
                'status' => 'active',
                'created_by' => 2,
            ],
            [
                'position' => 'Project Manager (Kontrak)',
                'company' => $company,
                'location' => 'Surabaya',
                'description' => 'Memimpin proyek implementasi sistem untuk klien enterprise. Posisi kontrak 12 bulan dengan kemungkinan perpanjangan.',
                'requirements' => "• S1 Teknik/Manajemen\n• Pengalaman 3+ tahun sebagai project manager IT\n• Memiliki sertifikasi PMP/Agile menjadi nilai plus\n• Mampu mengelola timeline, budget, dan stakeholder\n• Pengalaman proyek ERP/HRIS menjadi keunggulan",
                'salary_range' => 'Rp 12.000.000 - Rp 20.000.000',
                'employment_type' => 'contract',
                'status' => 'active',
                'created_by' => 1,
            ],
            [
                'position' => 'Content Writer (Part-time)',
                'company' => $company,
                'location' => 'Remote',
                'description' => 'Buat konten blog, copywriting produk, dan materi sosial media untuk mendukung strategi content marketing perusahaan.',
                'requirements' => "• S1 Bahasa, Komunikasi, atau Jurnalistik\n• Pengalaman menulis konten digital 1+ tahun\n• Menguasai SEO dasar dan tone of voice brand\n• Portfolio tulisan wajib dilampirkan\n• Bersedia remote part-time 20 jam/minggu",
                'salary_range' => 'Rp 4.000.000 - Rp 6.000.000',
                'employment_type' => 'part-time',
                'status' => 'active',
                'created_by' => 2,
            ],
        ];

        $now = now();

        foreach ($jobs as &$job) {
            $job['created_at'] = $now;
            $job['updated_at'] = $now;
        }

        JobListing::insert($jobs);

        $this->command?->info('Berhasil menambahkan ' . count($jobs) . ' lowongan kerja.');
    }
}
