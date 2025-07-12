<?php

namespace App\Controllers\Company;

use Medoo\Medoo;
use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CompanyProfileController
{
    
    protected $view;
    protected $db;

    public function __construct(Twig $view, Medoo $db)
    {
        
        $this->view = $view;
        $this->db = $db;
    }

    // Menampilkan profil perusahaan
    public function index(Request $request, Response $response): Response
    {
        $user_id = $_SESSION['user']['id'];

        $company = $this->db->get('companies', '*', [
            'user_id' => $user_id
        ]);

        return $this->view->render($response, 'company/profile/profile.twig', [
            'company' => $company,
            'session' => $_SESSION
        ]);
    }

    // Update atau insert data profil perusahaan
    public function update(Request $request, Response $response): Response
    {
        $user_id = $_SESSION['user']['id'];
        $data = $request->getParsedBody();

        $existing = $this->db->get('companies', '*', ['user_id' => $user_id]);

       $payload = [
    'user_id' => $user_id,
    'nama_perusahaan' => $data['nama_perusahaan'],
    'alamat_perusahaan' => $data['alamat_perusahaan'],
    'no_telp' => $data['no_telp'],
    'deskripsi' => $data['deskripsi']
];


        if ($existing) {
            $this->db->update('companies', $payload, ['user_id' => $user_id]);
            $_SESSION['success'] = "Profil perusahaan berhasil diperbarui.";
        } else {
            $this->db->insert('companies', $payload);
            $_SESSION['success'] = "Profil perusahaan berhasil disimpan.";
        }

        return $response->withHeader('Location', base_path() . '/company/profile')->withStatus(302);
    }
}
