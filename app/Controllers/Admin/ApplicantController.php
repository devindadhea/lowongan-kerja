<?php

namespace App\Controllers\Admin;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Medoo\Medoo;

class ApplicantController
{
    protected $view;
    protected $db;

    public function __construct(Twig $view, Medoo $db)
    {
        $this->view = $view;
        $this->db = $db;
    }

    public function allApplicants(Request $request, Response $response)
    {
        $pelamar = $this->db->select('applications', [
            '[>]users' => ['jobseeker_id' => 'id'],
            '[>]jobs' => ['job_id' => 'id']
        ], [
            'applications.id',
            'applications.status',
            'users.nama',
            'users.email',
            'jobs.judul(job_judul)'
        ]);

        return $this->view->render($response, 'admin/pelamar_all.twig', [
            'pelamarList' => $pelamar,
            'session' => $_SESSION
        ]);
    }
}
