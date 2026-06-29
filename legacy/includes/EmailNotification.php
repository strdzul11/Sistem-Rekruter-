<?php
namespace App;

class EmailNotification {
    
    public static function sendApplicationConfirmation($applicantEmail, $applicantName, $jobTitle, $companyName, $applicationId = null) {
        // Simpan notifikasi ke file log atau database
        $logMessage = date('Y-m-d H:i:s') . " - Konfirmasi aplikasi untuk " . $applicantName . " (" . $applicantEmail . ") - Posisi: " . $jobTitle . " di " . $companyName;
        if($applicationId) {
            $logMessage .= " - ID Aplikasi: " . $applicationId;
        }
        $logMessage .= "\n";
        file_put_contents(__DIR__ . '/../logs/applications.log', $logMessage, FILE_APPEND | LOCK_EX);
        
        // Return true untuk menunjukkan "email berhasil dikirim"
        return true;
    }
    
    public static function sendHRNotification($jobTitle, $companyName, $applicantName, $applicantEmail, $applicantPhone, $coverLetter) {
        // Simpan notifikasi ke file log atau database
        $logMessage = date('Y-m-d H:i:s') . " - NOTIFIKASI HR: Aplikasi baru dari " . $applicantName . " (" . $applicantEmail . ") - Posisi: " . $jobTitle . " di " . $companyName . " - Telp: " . $applicantPhone . "\n";
        file_put_contents(__DIR__ . '/../logs/applications.log', $logMessage, FILE_APPEND | LOCK_EX);
        
        // Return true untuk menunjukkan "email berhasil dikirim"
        return true;
    }
    
    // Fungsi untuk mendapatkan template email konfirmasi (untuk ditampilkan di halaman)
    public static function getConfirmationMessage($applicantName, $jobTitle, $companyName, $applicantEmail, $applicationId = null) {
        return "
        <div class='bg-green-50 border border-green-200 rounded-lg p-6 mb-6'>
            <div class='flex items-center mb-4'>
                <div class='w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3'>
                    <i class='fas fa-check text-green-600'></i>
                </div>
                <h3 class='text-lg font-semibold text-green-800'>Konfirmasi Aplikasi</h3>
            </div>
            
            <div class='text-green-700 space-y-3'>
                <p>Halo <strong>" . htmlspecialchars($applicantName) . "</strong>!</p>
                
                <p>Terima kasih telah melamar untuk posisi <strong>" . htmlspecialchars($jobTitle) . "</strong> di <strong>" . htmlspecialchars($companyName) . "</strong>.</p>
                
                " . ($applicationId ? "<div class='bg-white border border-green-300 rounded-lg p-4 my-4'>
                    <p class='font-semibold text-green-800 mb-2'>📋 ID Aplikasi Anda: <span class='text-xl font-bold'>#" . $applicationId . "</span></p>
                    <p class='text-sm text-green-600'>Simpan ID ini untuk mengecek status aplikasi Anda di: <a href='check_application.php' class='underline font-medium'>Halaman Cek Status</a></p>
                </div>" : "") . "
                
                <p>Aplikasi Anda telah berhasil diterima dan sedang dalam proses review. Tim HR akan menghubungi Anda dalam 1-2 hari kerja melalui:</p>
                
                <ul class='list-disc list-inside ml-4 space-y-1'>
                    <li>📧 Email: " . htmlspecialchars($applicantEmail) . "</li>
                    <li>📱 WhatsApp: Pastikan nomor Anda aktif</li>
                </ul>
                
                <p><strong>💡 Cara Cek Status Aplikasi:</strong></p>
                <ul class='list-disc list-inside ml-4 space-y-1'>
                    <li>Kunjungi: <a href='check_application.php' class='underline font-medium text-blue-600'>Halaman Cek Status Aplikasi</a></li>
                    <li>Masukkan email dan ID aplikasi Anda</li>
                    <li>Lihat status dan hasil penilaian (jika tersedia)</li>
                </ul>
                
                <p>Jika Anda memiliki pertanyaan, silakan hubungi kami di:</p>
                <ul class='list-disc list-inside ml-4 space-y-1'>
                    <li>📞 Telepon: +62 21 1234 5678</li>
                    <li>📧 Email: info@clarajob.co.id</li>
                    <li>💬 WhatsApp: +62 812 3456 7890</li>
                </ul>
                
                <p class='font-semibold'>Terima kasih dan semoga sukses!</p>
                <p><strong>Tim PT. Clarajob</strong></p>
            </div>
        </div>
        ";
    }
    
    // Fungsi untuk mengirim notifikasi status update
    public static function sendStatusUpdateNotification($applicantEmail, $applicantName, $jobTitle, $companyName, $newStatus, $applicationId) {
        $statusMessages = [
            'menunggu' => 'sedang menunggu review',
            'ditinjau' => 'sedang ditinjau oleh tim HR',
            'diterima' => 'DITERIMA! Selamat, Anda akan dihubungi untuk tahap selanjutnya',
            'ditolak' => 'tidak dapat dilanjutkan. Terima kasih atas minat Anda'
        ];
        
        $statusMessage = $statusMessages[$newStatus] ?? 'diupdate';
        
        // Simpan notifikasi ke file log
        $logMessage = date('Y-m-d H:i:s') . " - UPDATE STATUS untuk " . $applicantName . " (" . $applicantEmail . ") - Posisi: " . $jobTitle . " - Status: " . $newStatus . " - ID: " . $applicationId . "\n";
        file_put_contents(__DIR__ . '/../logs/applications.log', $logMessage, FILE_APPEND | LOCK_EX);
        
        return true;
    }
    
    // Fungsi untuk mengirim notifikasi hasil penilaian
    public static function sendEvaluationResultNotification($applicantEmail, $applicantName, $jobTitle, $sawScore, $ranking, $totalApplicants, $applicationId) {
        // Simpan notifikasi ke file log
        $logMessage = date('Y-m-d H:i:s') . " - HASIL PENILAIAN untuk " . $applicantName . " (" . $applicantEmail . ") - Posisi: " . $jobTitle . " - Skor SAW: " . number_format($sawScore * 100, 1) . "% - Ranking: #" . $ranking . "/" . $totalApplicants . " - ID: " . $applicationId . "\n";
        file_put_contents(__DIR__ . '/../logs/applications.log', $logMessage, FILE_APPEND | LOCK_EX);
        
        return true;
    }
    
    // Fungsi untuk mendapatkan informasi aplikasi untuk HR
    public static function getHRNotificationInfo($jobTitle, $companyName, $applicantName, $applicantEmail, $applicantPhone, $coverLetter) {
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'job_title' => $jobTitle,
            'company' => $companyName,
            'applicant_name' => $applicantName,
            'applicant_email' => $applicantEmail,
            'applicant_phone' => $applicantPhone,
            'cover_letter' => $coverLetter,
            'status' => 'pending'
        ];
    }
}
