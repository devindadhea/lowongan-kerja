<?php
namespace App\Controllers\Admin;

use Medoo\Medoo;
use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DashboardController
{
    protected $view;
    protected $db;

    public function __construct(Twig $view, Medoo $db)
    {
        $this->view = $view;
        $this->db   = $db;
    }

    public function index(Request $request, Response $response): Response
    {
        // 1. Ambil statistik
        $totalLowongan = $this->db->count('jobs');
        $totalUser     = $this->db->count('users');
        $totalPelamar  = $this->db->count('applications');

        // 2. Ambil 5 lowongan terbaru (join kategori & perusahaan)
        $jobs = $this->db->select('jobs', [
            '[>]categories' => ['kategori_id' => 'id'],
            '[>]companies'  => ['user_id' => 'user_id']
        ], [
            'jobs.id',
            'jobs.judul',
            'jobs.lokasi',
            'jobs.gaji',
            'jobs.status',
            'jobs.created_at',
            'categories.nama_kategori',
            'companies.nama_perusahaan'
        ], [
            'ORDER' => ['jobs.created_at' => 'DESC'],
            'LIMIT' => 5
        ]);

        // 3. Kirim ke view
        return $this->view->render($response, 'admin/dashboard.twig', [
            'totalLowongan' => $totalLowongan,
            'totalUser'     => $totalUser,
            'totalPelamar'  => $totalPelamar,
            'jobs'          => $jobs,
            'session'       => $_SESSION
        ]);
    }
}
