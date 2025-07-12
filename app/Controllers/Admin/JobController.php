<?php

namespace App\Controllers\Admin;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Medoo\Medoo;

class JobController
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
            'ORDER' => ['jobs.created_at' => 'DESC']
        ]);

        return $this->view->render($response, 'admin/jobs/index.twig', [
            'jobs'    => $jobs,
            'session' => $_SESSION,
        ]);
    }

    // Tampil form tambah lowongan
public function create(Request $request, Response $response): Response
{
    $categories = $this->db->select('categories', ['id', 'nama_kategori']);
    return $this->view->render($response, 'admin/jobs/create.twig', ['categories' => $categories]);
}

public function store(Request $request, Response $response): Response
{
    $data = (array)$request->getParsedBody();
    $this->db->insert('jobs', [
        'judul' => $data['judul'],
        'lokasi' => $data['lokasi'],
        'gaji' => $data['gaji'],
        'status' => $data['status'],
        'kategori_id' => $data['kategori_id']
    ]);
    $_SESSION['success'] = 'Lowongan berhasil ditambahkan.';
    return $response->withHeader('Location', base_path() . '/admin/jobs')->withStatus(302);
}

public function edit(Request $request, Response $response, array $args): Response
{
    $job = $this->db->get('jobs', '*', ['id' => $args['id']]);
    $categories = $this->db->select('categories', ['id', 'nama_kategori']);
    return $this->view->render($response, 'admin/jobs/edit.twig', ['job' => $job, 'categories' => $categories]);
}

public function update(Request $request, Response $response, array $args): Response
{
    $data = (array)$request->getParsedBody();
    $this->db->update('jobs', [
        'judul' => $data['judul'],
        'lokasi' => $data['lokasi'],
        'gaji' => $data['gaji'],
        'status' => $data['status'],
        'kategori_id' => $data['kategori_id']
    ], ['id' => $args['id']]);
    $_SESSION['success'] = 'Lowongan berhasil diperbarui.';
    return $response->withHeader('Location', base_path() . '/admin/jobs')->withStatus(302);
}

public function delete(Request $request, Response $response, array $args): Response
{
    $this->db->delete('jobs', ['id' => $args['id']]);
    $_SESSION['success'] = 'Lowongan berhasil dihapus.';
    return $response->withHeader('Location', base_path() . '/admin/jobs')->withStatus(302);
}


}
