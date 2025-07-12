<?php
namespace App\Controllers\Company;

use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DashboardController
{
    protected $db;

    public function __construct(Medoo $db)
    {
        $this->db = $db;
    }

    public function index(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user']['id'];

        /* -------------------------------------------------
         * 1) Pastikan nama_perusahaan ada di session
         * -------------------------------------------------*/
        if (empty($_SESSION['user']['nama_perusahaan'])) {
            $company = $this->db->get('companies', [
                'nama_perusahaan',
                'alamat_perusahaan',
                'no_telp',
                'deskripsi',
                'logo'
            ], ['user_id' => $userId]);

            if ($company) {
                $_SESSION['user']['nama_perusahaan']   = $company['nama_perusahaan'];
                $_SESSION['user']['alamat_perusahaan'] = $company['alamat_perusahaan'] ?? '';
                $_SESSION['user']['no_telp']           = $company['no_telp'] ?? '';
                $_SESSION['user']['deskripsi']         = $company['deskripsi'] ?? '';
                $_SESSION['user']['logo']              = $company['logo'] ?? null;
            }
        }

        /* -------------------------------------------------
         * 2) Hitung statistik
         * -------------------------------------------------*/
        $totalLowongan = $this->db->count('jobs', ['user_id' => $userId]);

        $totalPelamar  = $this->db->query("
            SELECT COUNT(*) AS jumlah
            FROM applications
            INNER JOIN jobs ON applications.job_id = jobs.id
            WHERE jobs.user_id = :uid
        ", ['uid' => $userId])->fetch()['jumlah'] ?? 0;

        $lamaranDiterima = $this->db->query("
            SELECT COUNT(*) AS jumlah
            FROM applications
            INNER JOIN jobs ON applications.job_id = jobs.id
            WHERE jobs.user_id = :uid AND applications.status = 'diterima'
        ", ['uid' => $userId])->fetch()['jumlah'] ?? 0;

        /* -------------------------------------------------
         * 3) Ambil 5 lowongan terbaru (opsional)
         * -------------------------------------------------*/
        $recentJobs = $this->db->select('jobs', '*', [
            'user_id' => $userId,
            'ORDER'   => ['created_at' => 'DESC'],
            'LIMIT'   => 5
        ]);

        /* -------------------------------------------------
         * 4) Kirim ke view
         * -------------------------------------------------*/
        return view($request, $response, 'company/dashboard.twig', [
            'totalLowongan'   => $totalLowongan,
            'totalPelamar'    => $totalPelamar,
            'lamaranDiterima' => $lamaranDiterima,
            'recentJobs'      => $recentJobs,
            // aktifkan tab sidebar
            'active'          => 'dashboard'
        ]);
    }
}
