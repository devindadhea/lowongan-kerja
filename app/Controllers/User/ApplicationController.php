<?php
namespace App\Controllers\User;

use Medoo\Medoo;
use Slim\Psr7\UploadedFile;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ApplicationController
{
    private Medoo $db;
    public function __construct(Medoo $db) { $this->db = $db; }

    /* ---------- 1. FORM /user/jobs/apply/{id} ------------------------------ */
    public function form(Request $req, Response $res, array $args): Response
    {
        $jobId = (int)$args['id'];

        $job = $this->db->get('jobs', [
            '[>]companies' => ['user_id' => 'user_id']
        ], [
            'jobs.id',
            'jobs.judul',
            'jobs.lokasi',
            'companies.nama_perusahaan'
        ], ['jobs.id' => $jobId]);

        if (!$job) {
            $_SESSION['error'] = 'Lowongan tidak ditemukan.';
            return $this->redirect($res, '/user/home');
        }

        return view($req, $res, 'user/jobs/apply.twig', [
            'job'     => $job,
            'error'   => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ]);
    }

    /* ---------- 2. SUBMIT /user/jobs/apply/{id} --------------------------- */
    public function submit(Request $req, Response $res, array $args): Response
    {
        $jobId        = (int)$args['id'];
        $jobseekerId  = $_SESSION['user']['jobseeker_id'] ?? null;   // FK di tabel applications
        $redirectPath = "/user/jobs/apply/$jobId";

        if (!$jobseekerId) {                         // sanity‑check
            $_SESSION['error'] = 'Silakan login ulang.';
            return $this->redirect($res, '/login');
        }

        /* 1. Cek duplikat lamaran */
        if ($this->db->has('applications', [
            'job_id'       => $jobId,
            'jobseeker_id' => $jobseekerId
        ])) {
            $_SESSION['error'] = 'Kamu sudah melamar lowongan ini.';
            return $this->redirect($res, $redirectPath);
        }

        /* 2. Validasi file */
        /** @var UploadedFile|null $file */
        $file = $req->getUploadedFiles()['cv'] ?? null;
        if (!$file || $file->getError() !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Gagal upload CV.';
            return $this->redirect($res, $redirectPath);
        }

        $ext = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'doc', 'docx'])) {
            $_SESSION['error'] = 'Format CV harus PDF, DOC, atau DOCX.';
            return $this->redirect($res, $redirectPath);
        }

        /* 3. Simpan file CV */
        $dir = __DIR__ . '/../../../public/uploads/cv';
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $newName = 'cv_' . $jobseekerId . '_' . time() . '.' . $ext;
        $file->moveTo($dir . '/' . $newName);

        /* 4. Update kolom cv di job_seekers (opsional) */
        $this->db->update('job_seekers', ['cv' => $newName], ['user_id' => $jobseekerId]);

        /* 5. Insert ke tabel applications */
        $this->db->insert('applications', [
            'job_id'        => $jobId,
            'jobseeker_id'  => $jobseekerId,
            'cv_file'       => $newName,
            'status'        => 'pending',
            'tanggal_lamar' => date('Y-m-d H:i:s')
        ]);

        $_SESSION['success'] = 'Lamaran berhasil dikirim.';
        return $this->redirect($res, '/user/home');
    }

    /* ---------- Helper redirect ------------------------------------------ */
    private function redirect(Response $res, string $path): Response
    {
        return $res->withHeader('Location', base_path() . $path)->withStatus(302);
    }
}
