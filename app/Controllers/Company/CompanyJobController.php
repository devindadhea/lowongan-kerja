<?php

namespace App\Controllers\Company;

use Medoo\Medoo;
use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CompanyJobController
{
    protected $view;
    protected $db;

    public function __construct(Twig $view, Medoo $db)
    {
        $this->view = $view;
        $this->db = $db;
    }

    // Tampilkan semua lowongan milik perusahaan
// CompanyJobController.php
public function index(Request $request, Response $response): Response
{
    $user_id = $_SESSION['user']['id'];

    /* JOIN jobs -> categories */
    $jobs = $this->db->select('jobs', [
        '[>]categories' => ['kategori_id' => 'id']   // foreign‑key JOIN
    ], [
        'jobs.id',
        'jobs.judul',
        'jobs.deskripsi',
        'jobs.lokasi',
        'jobs.gaji',
        'jobs.status',
        'categories.nama_kategori(kategori)'
    ], [
        'jobs.user_id' => $_SESSION['user']['id'],
    'ORDER' => ['jobs.created_at' => 'DESC']
    ]);

    return $this->view->render($response, 'company/jobs/index.twig', [
        'jobs'    => $jobs,
        'session' => $_SESSION
    ]);
}


    // Tampilkan form tambah lowongan
public function create(Request $request, Response $response): Response
{
    $categories = $this->db->select('categories', '*');

    return view($request, $response, 'company/jobs/create.twig', [
        'categories' => $categories,
        'session' => $_SESSION
    ]);
}


// ------------- STORE (tambah lowongan) -------------
public function store(Request $request, Response $response): Response
{
    $data = $request->getParsedBody();

    // ⬇⬇ Ubah string kosong menjadi NULL agar FK tidak error
    $kategori = $data['kategori_id'] ?? null;
    if ($kategori === '') {
        $kategori = null;
    }

    $this->db->insert('jobs', [
        'user_id'     => $_SESSION['user']['id'],
        'judul'       => $data['judul'],
        'deskripsi'   => $data['deskripsi'],
        'lokasi'      => $data['lokasi'],
        'gaji'        => $data['gaji'],
        'kategori_id' => $kategori
    ]);

    return $response->withHeader('Location', base_path() . '/company/jobs')
                    ->withStatus(302);
}

    // Tampilkan form edit lowongan
    public function edit(Request $request, Response $response, $args): Response
    {
        $job = $this->db->get('jobs', '*', [
            'id'      => $args['id'],
            'user_id' => $_SESSION['user']['id']
        ]);

        $categories = $this->db->select('categories', '*');

        return $this->view->render($response, 'company/jobs/edit.twig', [
            'job'        => $job,
            'categories' => $categories,
            'session'    => $_SESSION
        ]);
    }

// ------------- UPDATE (edit lowongan) -------------
public function update(Request $request, Response $response, $args): Response
{
    $data = $request->getParsedBody();

    // ⬇⬇ Ubah string kosong menjadi NULL
    $kategori = $data['kategori_id'] ?? null;
    if ($kategori === '') {
        $kategori = null;
    }

    $this->db->update('jobs', [
        'judul'       => $data['judul'],
        'deskripsi'   => $data['deskripsi'],
        'lokasi'      => $data['lokasi'],
        'gaji'        => $data['gaji'],
        'kategori_id' => $kategori
    ], [
        'id'      => $args['id'],
        'user_id' => $_SESSION['user']['id']
    ]);

    return $response->withHeader('Location', base_path() . '/company/jobs')
                    ->withStatus(302);
}

    // Hapus lowongan
    public function delete(Request $request, Response $response, $args): Response
    {
        $this->db->delete('jobs', [
            'id'      => $args['id'],
            'user_id' => $_SESSION['user']['id']
        ]);

        return $response->withHeader('Location', base_path() . '/company/jobs')->withStatus(302);
    }
}
