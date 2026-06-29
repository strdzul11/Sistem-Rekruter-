<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Users
        DB::table('users')->insert([
            [
                'name' => 'Administrator',
                'email' => 'admin@clarajob.co.id',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // 'password'
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'HR Manager',
                'email' => 'hr@clarajob.co.id',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // 'password'
                'role' => 'hrd',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 2. Seed Evaluation Criteria
        DB::table('evaluation_criteria')->insert([
            [
                'name' => 'Pendidikan',
                'description' => 'Tingkat pendidikan dan relevansi dengan posisi',
                'weight' => 20.00,
                'type' => 'benefit',
                'min_value' => 1,
                'max_value' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pengalaman Kerja',
                'description' => 'Lama dan relevansi pengalaman kerja',
                'weight' => 25.00,
                'type' => 'benefit',
                'min_value' => 1,
                'max_value' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Keterampilan Teknis',
                'description' => 'Kemampuan teknis sesuai dengan kebutuhan posisi',
                'weight' => 30.00,
                'type' => 'benefit',
                'min_value' => 1,
                'max_value' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Komunikasi',
                'description' => 'Kemampuan komunikasi dan presentasi',
                'weight' => 15.00,
                'type' => 'benefit',
                'min_value' => 1,
                'max_value' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kepribadian',
                'description' => 'Kesesuaian kepribadian dengan budaya perusahaan',
                'weight' => 10.00,
                'type' => 'benefit',
                'min_value' => 1,
                'max_value' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 3. Seed System Settings
        DB::table('system_settings')->insert([
            [
                'key' => 'company_name',
                'value' => 'PT. Sat.Echno',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'company_logo',
                'value' => 'logo.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'landing_hero_title',
                'value' => 'Bergabunglah dengan Tim Kami',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'landing_hero_subtitle',
                'value' => '{company_name} mencari talenta terbaik untuk bergabung dengan tim kami',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'landing_about_title',
                'value' => 'Tentang {company_name}',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'landing_about_description',
                'value' => '{company_name} adalah perusahaan teknologi yang berfokus pada pengembangan solusi digital inovatif. Kami percaya bahwa talenta terbaik adalah kunci kesuksesan, dan kami berkomitmen menciptakan lingkungan kerja yang kolaboratif, dinamis, dan penuh peluang berkembang.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 4. Seed Job Listings
        $this->call(JobListingSeeder::class);

        // 5. Seed Interview Questions
        DB::table('interview_questions')->insert([
            [
                'question_text' => 'Ceritakan tentang diri Anda dan pengalaman kerja Anda',
                'question_type' => 'general',
                'difficulty_level' => 'easy',
                'job_position' => null,
                'is_active' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question_text' => 'Mengapa Anda tertarik dengan posisi ini?',
                'question_type' => 'general',
                'difficulty_level' => 'easy',
                'job_position' => null,
                'is_active' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question_text' => 'Apa kelebihan dan kekurangan Anda?',
                'question_type' => 'behavioral',
                'difficulty_level' => 'medium',
                'job_position' => null,
                'is_active' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question_text' => 'Bagaimana Anda menangani konflik di tempat kerja?',
                'question_type' => 'behavioral',
                'difficulty_level' => 'medium',
                'job_position' => null,
                'is_active' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question_text' => 'Ceritakan tentang proyek terbesar yang pernah Anda kerjakan',
                'question_type' => 'behavioral',
                'difficulty_level' => 'medium',
                'job_position' => null,
                'is_active' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question_text' => 'Bagaimana Anda mengatasi deadline yang ketat?',
                'question_type' => 'situational',
                'difficulty_level' => 'medium',
                'job_position' => null,
                'is_active' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question_text' => 'Apa yang Anda ketahui tentang perusahaan kami?',
                'question_type' => 'cultural',
                'difficulty_level' => 'easy',
                'job_position' => null,
                'is_active' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question_text' => 'Di mana Anda melihat diri Anda dalam 5 tahun ke depan?',
                'question_type' => 'general',
                'difficulty_level' => 'medium',
                'job_position' => null,
                'is_active' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question_text' => 'Apa motivasi terbesar Anda dalam bekerja?',
                'question_type' => 'cultural',
                'difficulty_level' => 'easy',
                'job_position' => null,
                'is_active' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question_text' => 'Bagaimana Anda bekerja dalam tim?',
                'question_type' => 'behavioral',
                'difficulty_level' => 'medium',
                'job_position' => null,
                'is_active' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 6. Seed Letter Templates
        DB::table('letter_templates')->insert([
            [
                'type' => 'acceptance',
                'name' => 'Surat Penerimaan Resmi (Acceptance Letter)',
                'subject' => 'Selamat! Lamaran Anda di {{company_name}} Diterima',
                'body' => '<p>Dear <strong>{{applicant_name}}</strong>,</p><p>Kami dengan senang hati menginformasikan bahwa berdasarkan hasil evaluasi seleksi, Anda dinyatakan <strong>DITERIMA</strong> untuk bergabung bersama <strong>{{company_name}}</strong> sebagai <strong>{{position}}</strong>.</p><p>Kami sangat terkesan dengan kualifikasi dan potensi yang Anda tunjukkan selama proses seleksi. Kami yakin kontribusi Anda akan membawa dampak positif yang besar bagi pertumbuhan tim dan perusahaan kami.</p><p>Perwakilan dari tim HRD kami akan segera menghubungi Anda kembali dalam waktu dekat untuk membahas rincian penawaran kerja (Offering Letter), penentuan kompensasi, serta langkah onboarding selanjutnya.</p><p>Selamat bergabung di {{company_name}}!</p><p>Hormat kami,</p>',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'rejection',
                'name' => 'Surat Penolakan Sopan (Rejection Letter)',
                'subject' => 'Pemberitahuan Hasil Seleksi Lamaran di {{company_name}}',
                'body' => '<p>Dear <strong>{{applicant_name}}</strong>,</p><p>Terima kasih banyak atas waktu dan ketertarikan Anda untuk melamar posisi <strong>{{position}}</strong> di <strong>{{company_name}}</strong>.</p><p>Proses seleksi kali ini berjalan sangat kompetitif karena banyaknya kandidat berbakat yang melamar. Setelah melalui evaluasi yang sangat matang dan mendalam, kami menyesal harus menginformasikan bahwa saat ini kami belum dapat melanjutkan lamaran Anda ke tahap berikutnya.</p><p>Keputusan ini tidak mengurangi penghargaan kami terhadap kualifikasi dan antusiasme yang Anda tunjukkan. Profil Anda akan tetap tersimpan di dalam database rekrutmen kami untuk peluang karir di masa mendatang yang lebih sesuai.</p><p>Kami mendoakan kesuksesan yang terbaik untuk perjalanan karir profesional Anda.</p><p>Hormat kami,</p>',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 7. Seed Applicants
        $this->call(ApplicantSeeder::class);
    }
}

