<?php
namespace App\Controllers\User;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Medoo\Medoo;

class UserProfileController
{
    protected $view;
    protected $db;

    public function __construct(Twig $view, Medoo $db)
    {
        $this->view = $view;
        $this->db = $db;
    }

    public function showProfile(Request $request, Response $response): Response
    {
        $user = $_SESSION['user'] ?? null;

        if (!$user || $user['role'] !== 'user') {
            return $response
                ->withHeader('Location', base_path() . '/login')
                ->withStatus(302);
        }

        $jobSeeker = $this->db->get('job_seekers', '*', [
            'user_id' => $user['id']
        ]);

        return $this->view->render($response, 'user/profile/profile.twig', [
            'nama'        => $user['nama'],          // dari session
            'email'       => $user['email'],         // dari session
            'job_seeker'  => $jobSeeker              // dari tabel job_seekers
        ]);
    }

    public function updateProfile(Request $request, Response $response): Response
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user || $user['role'] !== 'user') {
            return $response
                ->withHeader('Location', base_path() . '/login')
                ->withStatus(302);
        }

        $data = $request->getParsedBody();

        // Simpan ke tabel job_seekers
        $this->db->update('job_seekers', [
            'nama_lengkap'  => $data['nama_lengkap'],
            'lokasi'        => $data['lokasi'],
            'no_telp'       => $data['no_telp'],
            'tanggal_lahir' => $data['tanggal_lahir'],
            'keahlian'      => $data['keahlian'],
            'bio'           => $data['bio'],
        ], [
            'user_id' => $user['id']
        ]);


        // Update juga nama ke tabel users (jika diedit)
if (!empty($data['nama_lengkap'])) {
    $this->db->update('users', [
        'nama' => $data['nama_lengkap']
    ], [
        'id' => $user['id']
    ]);

    $_SESSION['user']['nama'] = $data['nama_lengkap'];
}


        return $response
            ->withHeader('Location', base_path() . '/user/profile')
            ->withStatus(302);
    
        return $response->withHeader('Location', base_path() . '/user/profile')->withStatus(302);
    }
}
