<?php
namespace App\Controllers\User;

use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomeController
{
    private Medoo $db;
    public function __construct(Medoo $db) { $this->db = $db; }

    public function index(Request $req, Response $res): Response
    {
        /* ------- Ambil querystring filter ---------------------------------- */
        $q        = trim($req->getQueryParams()['q']        ?? '');
        $cat      = trim($req->getQueryParams()['kategori'] ?? '');
        $lokasi   = trim($req->getQueryParams()['lokasi']   ?? '');

        /* ------- Build kondisi WHERE dinamis ------------------------------ */
        $where = ['jobs.status' => 'aktif'];
        if ($q !== '') {
            // cari di judul atau nama perusahaan
            $where['OR'] = [
                'jobs.judul[~]'            => $q,
                'companies.nama_perusahaan[~]' => $q
            ];
        }
        if ($lokasi !== '') $where['jobs.lokasi[~]'] = $lokasi;
        if ($cat   !== '') $where['jobs.kategori_id'] = $cat;

        /* ------- Query lowongan ------------------------------------------- */
        $jobs = $this->db->select('jobs', [
            '[>]categories' => ['kategori_id' => 'id'],
            '[>]companies'  => ['user_id'     => 'user_id']
        ], [
            'jobs.id',
            'jobs.judul',
            'jobs.deskripsi',
            'jobs.lokasi',
            'jobs.created_at',
            'companies.nama_perusahaan',
            'categories.nama_kategori'
        ], [
            'AND'   => $where,
            'ORDER' => ['jobs.created_at' => 'DESC'],
            'LIMIT' => 50
        ]);

        /* ------- Data kategori untuk dropdown ----------------------------- */
        $categories = $this->db->select('categories', ['id','nama_kategori']);

        /* ------- Kirim ke view ------------------------------------------- */
        return view($req, $res, 'user/home/index.twig', [
            'jobs'       => $jobs,
            'categories' => $categories,
            'filters'    => compact('q','cat','lokasi')
        ]);
    }
}
