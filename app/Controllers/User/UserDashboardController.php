<?php

namespace App\Controllers\User;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Medoo\Medoo;

class UserDashboardController
{
    protected $view;
    protected $db;

    public function __construct(Twig $view, Medoo $db)
    {
        $this->view = $view;
        $this->db = $db;
    }

    public function index(Request $request, Response $response): Response
    {
        // Ambil data user dari session
        $user = $_SESSION['user'] ?? null;

        // Cek jika belum login
        if (!$user || !isset($user['jobseeker_id'])) {
            return $response->withHeader('Location', base_path() . '/login')->withStatus(302);
        }

        $jobseeker_id = $user['jobseeker_id'];

        // Hitung statistik lamaran
        $jumlahLamaran = $this->db->count('applications', [
            'jobseeker_id' => $jobseeker_id
        ]);

        $jumlahDiterima = $this->db->count('applications', [
            'jobseeker_id' => $jobseeker_id,
            'status' => 'accepted'
        ]);

        $jumlahDitolak = $this->db->count('applications', [
            'jobseeker_id' => $jobseeker_id,
            'status' => 'rejected'
        ]);

        $jumlahMenunggu = $this->db->count('applications', [
            'jobseeker_id' => $jobseeker_id,
            'status[!]' => ['accepted', 'rejected']
        ]);

        // Ambil 5 lamaran terbaru
        $lamaranTerbaru = $this->db->select('applications (a)', [
            '[>]jobs (j)' => ['a.job_id' => 'id'],
            '[>]companies (c)' => ['j.user_id' => 'user_id']
        ], [
            'a.tanggal_lamar(tanggal)',
            'a.status',
            'j.judul(posisi)',                     // ini perbaikannya
            'c.nama_perusahaan'
        ], [
            'a.jobseeker_id' => $jobseeker_id,
            'ORDER' => ['a.tanggal_lamar' => 'DESC'],
            'LIMIT' => 5
        ]);


        // Kirim data ke view
        return $this->view->render($response, 'user/dashboard.twig', [
            'lamaran'         => $jumlahLamaran,
            'diterima'        => $jumlahDiterima,
            'ditolak'         => $jumlahDitolak,
            'menunggu'        => $jumlahMenunggu,
            'lamaran_terbaru' => $lamaranTerbaru
        ]);
    }
}
