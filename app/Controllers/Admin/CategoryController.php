<?php
namespace App\Controllers\Admin;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Medoo\Medoo;

class CategoryController
{
    protected $view;
    protected $db;

    public function __construct(Twig $view, Medoo $db)
    {
        $this->view = $view;
        $this->db = $db;
    }

    // Tampilkan daftar kategori
    public function index(Request $request, Response $response): Response
    {
        $categories = $this->db->select('categories', '*');
        return $this->view->render($response, 'admin/categories/index.twig', [
            'categories' => $categories,
            'session' => $_SESSION,
        ]);
    }

// Tampilkan form tambah kategori
public function create(Request $request, Response $response): Response
{
    return $this->view->render($response, 'admin/categories/create.twig', [
        'session' => $_SESSION,
    ]);
}

// Simpan data kategori baru
public function store(Request $request, Response $response): Response
{
    $data = $request->getParsedBody();

    if (empty($data['nama_kategori'])) {
        $_SESSION['error'] = "Nama kategori wajib diisi.";
        return $response->withHeader('Location', '/lowongan-kerja/public/admin/categories/create')->withStatus(302);
    }

    $this->db->insert('categories', [
        'nama_kategori' => $data['nama_kategori'],
    ]);

    $_SESSION['success'] = "Kategori berhasil ditambahkan.";
    return $response->withHeader('Location', '/lowongan-kerja/public/admin/categories')->withStatus(302);
}


// Form edit kategori
public function edit(Request $request, Response $response, $args): Response
{
    $id = $args['id'];
    $category = $this->db->get('categories', '*', ['id' => $id]);

    if (!$category) {
        $_SESSION['error'] = "Kategori tidak ditemukan.";
        return $response->withHeader('Location', '/lowongan-kerja/public/admin/categories')->withStatus(302);
    }

    return $this->view->render($response, 'admin/categories/edit.twig', [
        'category' => $category,
        'session' => $_SESSION,
    ]);
}

// Update kategori
public function update(Request $request, Response $response, $args): Response
{
    $id = $args['id'];
    $data = $request->getParsedBody();

    if (empty($data['nama_kategori'])) {
        $_SESSION['error'] = "Nama kategori wajib diisi.";
        return $response->withHeader('Location', "/lowongan-kerja/public/admin/categories/edit/$id")->withStatus(302);
    }

    $this->db->update('categories', [
        'nama_kategori' => $data['nama_kategori'],
    ], ['id' => $id]);

    $_SESSION['success'] = "Kategori berhasil diupdate.";
    return $response->withHeader('Location', '/lowongan-kerja/public/admin/categories')->withStatus(302);
}

// Hapus kategori
public function delete(Request $request, Response $response, $args): Response
{
    $id = $args['id'];
    $this->db->delete('categories', ['id' => $id]);

    $_SESSION['success'] = "Kategori berhasil dihapus.";
    return $response->withHeader('Location', '/lowongan-kerja/public/admin/categories')->withStatus(302);
}

}
