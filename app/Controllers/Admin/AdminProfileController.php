<?php

namespace App\Controllers\Admin;

use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminProfileController
{
    protected $db;

    public function __construct(Medoo $db)
    {
        $this->db = $db;
    }

    public function index(Request $request, Response $response)
    {
        $admin = $this->db->get('users', '*', [
            'id' => $_SESSION['user']['id'],
            'role' => 'admin'
        ]);

        return view($request, $response, 'admin/profile/index.twig', [
            'admin' => $admin,
            'session' => $_SESSION
        ]);
    }

    public function update(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        $this->db->update('users', [
            'nama' => $data['nama'],
            'email' => $data['email']
        ], [
            'id' => $_SESSION['user']['id'],
            'role' => 'admin'
        ]);

        $_SESSION['user']['nama'] = $data['nama'];
        $_SESSION['user']['email'] = $data['email'];
        $_SESSION['success'] = 'Profil berhasil diperbarui.';

        return $response->withHeader('Location', base_path() . '/admin/profile')->withStatus(302);
    }
}
