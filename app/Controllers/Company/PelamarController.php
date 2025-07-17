<?php
namespace App\Controllers\Company;

use Medoo\Medoo;
use Slim\Psr7\Stream;
use Slim\Routing\RouteContext;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PelamarController
{
    private Medoo $db;
    public function __construct(Medoo $db) { $this->db = $db; }

    /* ========== 1. Tampilkan daftar pelamar ========== */
    public function index(Request $req, Response $res): Response
    {
        $companyId = $_SESSION['user']['id'];
        $jobIds    = $this->db->select('jobs', 'id', ['user_id' => $companyId]);
        $pelamar   = $jobIds ? $this->fetchApplicants($jobIds) : [];

        /* —— ambil + hapus flash (sekali tampil) —— */
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_success']);

        return view($req, $res, 'company/pelamar/index.twig', [
            'pelamar'       => $pelamar,
            'active'        => 'pelamar',
            'flash_success' => $flashSuccess
        ]);
    }

    /* ========== 2. Ubah status lamaran ========== */
    public function updateStatus(Request $req, Response $res): Response
    {
        $data      = $req->getParsedBody();
        $appId     = (int) ($data['application_id'] ?? 0);
        $newStatus = $data['status'] ?? '';
        $companyId = $_SESSION['user']['id'];

        if (!in_array($newStatus, ['accepted', 'rejected'], true)) {
            return $res->withStatus(400)->write('Status tidak valid');
        }

        /* cek kepemilikan */
        $ownerId = $this->db->get(
            'applications',
            ['[>]jobs' => ['job_id' => 'id']],
            'jobs.user_id',
            ['applications.id' => $appId]
        );

        if ((int)$ownerId !== $companyId) {
            return $res->withStatus(403)->write('Akses ditolak');
        }

        $this->db->update('applications', ['status' => $newStatus], ['id' => $appId]);
        $_SESSION['flash_success'] = 'Status lamaran diperbarui.';

        $url = RouteContext::fromRequest($req)
                ->getRouteParser()->urlFor('company.pelamar');

        return $res->withHeader('Location', $url)->withStatus(302);
    }

    /* ========== 3. Download CV ========== */
    public function downloadCV(Request $req, Response $res, array $args): Response
    {
        $filename = $args['file'];
        $filepath = __DIR__ . '/../../../public/uploads/cv/' . $filename;

        if (!file_exists($filepath)) {
            return $res->withStatus(404)->write('File tidak ditemukan.');
        }

        $stream = fopen($filepath, 'rb');
        return $res
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withBody(new Stream($stream));
    }

    /* ========== 4. Helper ambil pelamar ========== */
    private function fetchApplicants(array $jobIds): array
    {
        return $this->db->select(
            'applications',
            [
                '[>]jobs'        => ['job_id'       => 'id'],
                '[>]job_seekers' => ['jobseeker_id' => 'user_id'],
                '[>]users'       => ['job_seekers.user_id' => 'id']
            ],
            [
                'applications.id(application_id)',
                'applications.tanggal_lamar',
                'applications.status',
                'jobs.judul',
                'users.nama',
                'users.email',
                'job_seekers.cv'
            ],
            [
                'applications.job_id' => $jobIds,
                'ORDER'               => ['applications.tanggal_lamar' => 'DESC']
            ]
        );
    }
}
