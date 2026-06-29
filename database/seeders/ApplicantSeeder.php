<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ApplicantProfile;
use App\Models\Application;
use App\Models\JobListing;
use App\Services\SawRankingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ApplicantSeeder extends Seeder
{
    public function run(): void
    {
        // Check if applicants already exist to avoid duplicates
        if (User::where('role', 'applicant')->count() > 0) {
            $this->command?->warn('Data pelamar sudah ada di database. Lewati ApplicantSeeder.');
            return;
        }

        // 1. Data Pelamar (Users)
        $applicantsData = [
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@gmail.com',
                'phone' => '081234567890',
                'address' => 'Jl. Sudirman No. 12, Jakarta',
                'profile' => [
                    'education' => 'S1 Teknik Informatika - Universitas Indonesia',
                    'experience' => '3 tahun sebagai Backend Developer di PT Solusi Tekno',
                    'skills' => 'PHP, Laravel, MySQL, Redis, Git, REST API',
                    'portfolio_url' => 'https://github.com/ahmadfauzi',
                    'linkedin_url' => 'https://linkedin.com/in/ahmadfauzi',
                ],
                'applications' => [
                    [
                        'position' => 'Software Engineer',
                        'age' => 25,
                        'gender' => 'Laki-laki',
                        'work_experience' => '3 Tahun',
                        'cover_letter' => 'Saya sangat tertarik dengan posisi Software Engineer di perusahaan Anda karena memiliki latar belakang yang relevan.',
                        'status' => 'accepted',
                    ]
                ]
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@gmail.com',
                'phone' => '081234567891',
                'address' => 'Jl. Thamrin No. 45, Jakarta',
                'profile' => [
                    'education' => 'S1 Sistem Informasi - Institut Teknologi Bandung',
                    'experience' => '1.5 tahun sebagai Frontend Developer di Startup Maju',
                    'skills' => 'HTML, CSS, JavaScript, Vue.js, Tailwind CSS',
                    'portfolio_url' => 'https://budi-portfolio.dev',
                    'linkedin_url' => 'https://linkedin.com/in/budisantoso',
                ],
                'applications' => [
                    [
                        'position' => 'Frontend Developer',
                        'age' => 24,
                        'gender' => 'Laki-laki',
                        'work_experience' => '1.5 Tahun',
                        'cover_letter' => 'Saya ingin mengaplikasikan keahlian Vue.js dan Tailwind CSS saya untuk posisi Frontend Developer di PT. Sat.Echno.',
                        'status' => 'reviewed',
                    ]
                ]
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@gmail.com',
                'phone' => '081234567892',
                'address' => 'Jl. Merdeka No. 9, Bandung',
                'profile' => [
                    'education' => 'S1 Desain Komunikasi Visual - Institut Teknologi Bandung',
                    'experience' => '4 tahun sebagai UI/UX Designer di Agensi Kreatif',
                    'skills' => 'Figma, Adobe XD, Adobe Illustrator, User Research, Wireframing',
                    'portfolio_url' => 'https://behance.net/dewilestari',
                    'linkedin_url' => 'https://linkedin.com/in/dewilestari',
                ],
                'applications' => [
                    [
                        'position' => 'UI/UX Designer',
                        'age' => 26,
                        'gender' => 'Perempuan',
                        'work_experience' => '4 Tahun',
                        'cover_letter' => 'Dengan pengalaman 4 tahun mendesain antarmuka aplikasi web dan mobile, saya yakin dapat meningkatkan user experience produk Anda.',
                        'status' => 'interview_scheduled',
                    ]
                ]
            ],
            [
                'name' => 'Eko Prasetyo',
                'email' => 'eko.prasetyo@gmail.com',
                'phone' => '081234567893',
                'address' => 'Jl. Gajah Mada No. 101, Surabaya',
                'profile' => [
                    'education' => 'S1 Teknik Informatika - Universitas Airlangga',
                    'experience' => '2 tahun sebagai QA Engineer di PT Sejahtera Finansial',
                    'skills' => 'Manual Testing, Automated Testing, Postman, Selenium, Test Cases',
                    'portfolio_url' => null,
                    'linkedin_url' => 'https://linkedin.com/in/ekoprasetyo',
                ],
                'applications' => [
                    [
                        'position' => 'Quality Assurance (QA) Engineer',
                        'age' => 25,
                        'gender' => 'Laki-laki',
                        'work_experience' => '2 Tahun',
                        'cover_letter' => 'Saya berfokus pada ketelitian pengujian sistem dan pembuatan skenario uji coba otomatis menggunakan Selenium.',
                        'status' => 'pending',
                    ]
                ]
            ],
            [
                'name' => 'Fitriani',
                'email' => 'fitriani@gmail.com',
                'phone' => '081234567894',
                'address' => 'Jl. Diponegoro No. 23, Yogyakarta',
                'profile' => [
                    'education' => 'S1 Ilmu Komputer - Universitas Gadjah Mada',
                    'experience' => '2 tahun sebagai Junior DevOps di Cloud Tech',
                    'skills' => 'Linux, Docker, AWS, CI/CD, GitHub Actions, Nginx',
                    'portfolio_url' => 'https://fitriani.me',
                    'linkedin_url' => 'https://linkedin.com/in/fitriani',
                ],
                'applications' => [
                    [
                        'position' => 'DevOps Engineer',
                        'age' => 24,
                        'gender' => 'Perempuan',
                        'work_experience' => '2 Tahun',
                        'cover_letter' => 'Tertarik mengoptimalkan infrastruktur server cloud dan otomatisasi deployment di PT. Sat.Echno.',
                        'status' => 'rejected',
                    ]
                ]
            ],
            [
                'name' => 'Hendra Wijaya',
                'email' => 'hendra.wijaya@gmail.com',
                'phone' => '081234567895',
                'address' => 'Jl. Gatot Subroto No. 56, Jakarta',
                'profile' => [
                    'education' => 'S1 Statistika - Universitas Indonesia',
                    'experience' => '1 tahun sebagai Junior Data Analyst',
                    'skills' => 'SQL, Python, Pandas, Tableau, Microsoft Excel',
                    'portfolio_url' => null,
                    'linkedin_url' => 'https://linkedin.com/in/hendrawijaya',
                ],
                'applications' => [
                    [
                        'position' => 'Data Analyst',
                        'age' => 23,
                        'gender' => 'Laki-laki',
                        'work_experience' => '1 Tahun',
                        'cover_letter' => 'Saya senang mengolah dataset besar dan menyajikannya dalam dashboard interaktif menggunakan Tableau.',
                        'status' => 'reviewed',
                    ]
                ]
            ],
            [
                'name' => 'Indah Permatasari',
                'email' => 'indah.p@gmail.com',
                'phone' => '081234567896',
                'address' => 'Jl. Pemuda No. 78, Semarang',
                'profile' => [
                    'education' => 'S1 Ilmu Komunikasi - Universitas Diponegoro',
                    'experience' => '2.5 tahun sebagai Digital Marketing Specialist di E-commerce lokal',
                    'skills' => 'Social Media Management, SEO/SEM, Google Ads, Content Strategy',
                    'portfolio_url' => 'https://indahpermatasari.myportfolio.com',
                    'linkedin_url' => 'https://linkedin.com/in/indahpermatasari',
                ],
                'applications' => [
                    [
                        'position' => 'Digital Marketing Specialist',
                        'age' => 25,
                        'gender' => 'Perempuan',
                        'work_experience' => '2.5 Tahun',
                        'cover_letter' => 'Siap berkontribusi menaikkan traffic organic dan lead conversion melalui SEO & Meta Ads.',
                        'status' => 'accepted',
                    ]
                ]
            ],
            [
                'name' => 'Joko Widodo',
                'email' => 'joko.w@gmail.com',
                'phone' => '081234567897',
                'address' => 'Jl. Slamet Riyadi No. 89, Solo',
                'profile' => [
                    'education' => 'S1 Psikologi - Universitas Sebelas Maret',
                    'experience' => '3 tahun sebagai HR Staff / Recruiter di PT Manufaktur Agung',
                    'skills' => 'End-to-end Recruitment, Behavioral Interviewing (BEI), UU Ketenagakerjaan',
                    'portfolio_url' => null,
                    'linkedin_url' => 'https://linkedin.com/in/jokowidodo-hr',
                ],
                'applications' => [
                    [
                        'position' => 'HR Generalist',
                        'age' => 26,
                        'gender' => 'Laki-laki',
                        'work_experience' => '3 Tahun',
                        'cover_letter' => 'Dengan pengalaman rekrutmen end-to-end, saya ingin meningkatkan efisiensi proses hiring di PT. Sat.Echno.',
                        'status' => 'interview_scheduled',
                    ]
                ]
            ],
            [
                'name' => 'Kartika Putri',
                'email' => 'kartika.p@gmail.com',
                'phone' => '081234567898',
                'address' => 'Jl. Sudirman No. 120, Palembang',
                'profile' => [
                    'education' => 'S1 Sistem Informasi - Universitas Sriwijaya',
                    'experience' => 'Fresh Graduate / Proyek magang kuliah',
                    'skills' => 'HTML, CSS, JavaScript dasar, PHP dasar, Git',
                    'portfolio_url' => 'https://github.com/kartikaputri',
                    'linkedin_url' => 'https://linkedin.com/in/kartikaputri',
                ],
                'applications' => [
                    [
                        'position' => 'Magang Software Developer',
                        'age' => 21,
                        'gender' => 'Perempuan',
                        'work_experience' => 'Fresh Graduate',
                        'cover_letter' => 'Saya bersemangat untuk belajar dan mengasah skill pemrograman web di lingkungan kerja profesional melalui program magang ini.',
                        'status' => 'pending',
                    ]
                ]
            ],
            [
                'name' => 'Lukman Hakim',
                'email' => 'lukman.h@gmail.com',
                'phone' => '081234567899',
                'address' => 'Jl. Riau No. 12, Pekanbaru',
                'profile' => [
                    'education' => 'S1 Teknik Elektro - Universitas Riau',
                    'experience' => '5 tahun sebagai IT Project Manager / Scrum Master',
                    'skills' => 'Project Management, Agile/Scrum, JIRA, Trello, Stakeholder Management',
                    'portfolio_url' => null,
                    'linkedin_url' => 'https://linkedin.com/in/lukmanhakim-pm',
                ],
                'applications' => [
                    [
                        'position' => 'Project Manager (Kontrak)',
                        'age' => 28,
                        'gender' => 'Laki-laki',
                        'work_experience' => '5 Tahun',
                        'cover_letter' => 'Berbekal sertifikasi Scrum Master dan 5 tahun pengalaman memimpin tim developer, saya siap mengelola proyek sistem informasi perusahaan.',
                        'status' => 'accepted',
                    ]
                ]
            ],
        ];

        // Ambil lowongan pekerjaan
        $jobs = JobListing::all()->keyBy('position');

        // Ambil kriteria evaluasi
        $criteria = DB::table('evaluation_criteria')->where('is_active', true)->get();

        // 2. Insert Registered Applicants & Profiles & Applications
        foreach ($applicantsData as $data) {
            // Create User
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'applicant',
                'phone' => $data['phone'],
                'address' => $data['address'],
                'email_verified_at' => now(),
            ]);

            // Create Profile
            ApplicantProfile::create(array_merge(
                ['user_id' => $user->id],
                $data['profile']
            ));

            // Create Applications
            foreach ($data['applications'] as $appData) {
                $job = $jobs->get($appData['position']);
                if ($job) {
                    $application = Application::create([
                        'user_id' => $user->id,
                        'job_id' => $job->id,
                        'applicant_name' => $user->name,
                        'applicant_email' => $user->email,
                        'applicant_phone' => $user->phone,
                        'applicant_age' => $appData['age'],
                        'applicant_gender' => $appData['gender'],
                        'work_experience' => $appData['work_experience'],
                        'cover_letter' => $appData['cover_letter'],
                        'resume_path' => 'resumes/fake_resume.pdf',
                        'status' => $appData['status'],
                        'interview_status' => $appData['status'] === 'interview_scheduled' ? 'scheduled' : 'not_scheduled',
                        'application_type' => 'registered',
                    ]);

                    // Seed Evaluations for reviewed, accepted, rejected, interview_scheduled
                    if (in_array($appData['status'], ['reviewed', 'accepted', 'rejected', 'interview_scheduled'])) {
                        $this->seedEvaluations($application->id, $criteria);
                    }

                    // Seed Interview Schedule if interview_scheduled
                    if ($appData['status'] === 'interview_scheduled') {
                        $this->seedInterviewSchedule($application->id);
                    }
                }
            }
        }

        // 3. Insert Quick Apply Applicants (without registered users)
        $quickApplicants = [
            [
                'name' => 'Maria Ulfah',
                'email' => 'maria.ulfah@gmail.com',
                'phone' => '081345678901',
                'position' => 'Frontend Developer',
                'age' => 26,
                'gender' => 'Perempuan',
                'work_experience' => '2 Tahun',
                'cover_letter' => 'Saya tertarik melamar lewat quick apply untuk posisi Frontend Developer.',
                'status' => 'reviewed',
            ],
            [
                'name' => 'Novianti',
                'email' => 'novianti@gmail.com',
                'phone' => '081345678902',
                'position' => 'Magang Software Developer',
                'age' => 20,
                'gender' => 'Perempuan',
                'work_experience' => 'Fresh Graduate',
                'cover_letter' => 'Melamar cepat untuk posisi magang software developer.',
                'status' => 'pending',
            ],
            [
                'name' => 'Rian Hidayat',
                'email' => 'rian.hidayat@gmail.com',
                'phone' => '081345678903',
                'position' => 'Software Engineer',
                'age' => 27,
                'gender' => 'Laki-laki',
                'work_experience' => '4 Tahun',
                'cover_letter' => 'Saya adalah backend engineer berpengalaman yang ingin bergabung dengan cepat.',
                'status' => 'interview_scheduled',
                'evaluations' => true,
            ]
        ];

        foreach ($quickApplicants as $qa) {
            $job = $jobs->get($qa['position']);
            if ($job) {
                $application = Application::create([
                    'user_id' => null,
                    'job_id' => $job->id,
                    'applicant_name' => $qa['name'],
                    'applicant_email' => $qa['email'],
                    'applicant_phone' => $qa['phone'],
                    'applicant_age' => $qa['age'],
                    'applicant_gender' => $qa['gender'],
                    'work_experience' => $qa['work_experience'],
                    'cover_letter' => $qa['cover_letter'],
                    'resume_path' => 'resumes/fake_resume.pdf',
                    'status' => $qa['status'],
                    'interview_status' => $qa['status'] === 'interview_scheduled' ? 'scheduled' : 'not_scheduled',
                    'application_type' => 'quick_apply',
                ]);

                // Seed Evaluations for reviewed, accepted, rejected, interview_scheduled
                if (in_array($qa['status'], ['reviewed', 'accepted', 'rejected', 'interview_scheduled'])) {
                    $this->seedEvaluations($application->id, $criteria);
                }

                // Seed Interview Schedule if interview_scheduled
                if ($qa['status'] === 'interview_scheduled') {
                    $this->seedInterviewSchedule($application->id);
                }
            }
        }

        // 4. Recalculate Rankings using SawRankingService
        $rankingService = new SawRankingService();
        foreach ($jobs as $job) {
            $rankingService->recalculateRankingsForJob($job->id);
        }

        $this->command?->info('Berhasil menambahkan data pelamar, profil, lamaran, evaluasi, peringkat SAW, dan jadwal wawancara.');
    }

    private function seedEvaluations(int $applicationId, $criteria): void
    {
        // Evaluator ID = 2 (HR Manager)
        foreach ($criteria as $criterion) {
            // Generate random score between min_value and max_value (usually 1 - 5)
            $score = rand($criterion->min_value, $criterion->max_value);
            
            DB::table('application_evaluations')->insert([
                'application_id' => $applicationId,
                'criteria_id' => $criterion->id,
                'score' => $score,
                'evaluator_id' => 2, // HR Manager
                'notes' => 'Evaluasi awal berkas oleh HRD',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedInterviewSchedule(int $applicationId): void
    {
        DB::table('interview_schedules')->insert([
            'application_id' => $applicationId,
            'interviewer_id' => 2, // HR Manager
            'interview_type' => 'video',
            'interview_date' => now()->addDays(rand(2, 7))->setTime(10, 0, 0),
            'timezone' => 'Asia/Jakarta',
            'duration_minutes' => 45,
            'meeting_link' => 'https://meet.google.com/abc-defg-hij',
            'meeting_room' => null,
            'status' => 'scheduled',
            'notes' => 'Wawancara kompetensi teknis dan kultur perusahaan.',
            'is_proposed' => false,
            'selection_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
