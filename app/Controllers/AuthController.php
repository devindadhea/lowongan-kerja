<?php

namespace App\Controllers;

use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    protected $db;

    public function __construct(Medoo $db)
    {
        $this->db = $db;
    }

    // ========== HALAMAN REGISTER USER ==========
    public function showRegister(Request $request, Response $response)
    {
        return view($request, $response, 'auth/register.twig');
    }

    public function register(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        if (!$data['nama'] || !$data['email'] || !$data['password']) {
            return view($request, $response, 'auth/register.twig', ['error' => 'Semua field wajib diisi']);
        }

        $existing = $this->db->get('users', '*', ['email' => $data['email']]);
        if ($existing) {
            return view($request, $response, 'auth/register.twig', ['error' => 'Email sudah terdaftar']);
        }

        $this->db->insert('users', [
            'nama'     => $data['nama'],
            'email'    => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role'     => 'user'
        ]);

        $user = $this->db->get('users', '*', ['email' => $data['email']]);

        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'nama' => $user['nama'],
            'role' => 'user'
        ];

        return $response->withHeader('Location', base_path() . '/home')->withStatus(302);
    }

    // ========== HALAMAN LOGIN ==========
    public function showLogin(Request $request, Response $response)
    {
        return view($request, $response, 'auth/login.twig');
    }

    public function login(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        $user = $this->db->get('users', '*', ['email' => $data['email']]);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            return view($request, $response, 'auth/login.twig', ['error' => 'Email atau password salah']);
        }

        // Simpan default session
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        // Tambahan data sesuai role
        //admin
        if ($user['role'] === 'admin') {
            $_SESSION['user']['nama'] = $user['nama'];
            return $response->withHeader('Location', base_path() . '/dashboard')->withStatus(302);
            //users
        } elseif ($user['role'] === 'user') {
            $_SESSION['user']['nama'] = $user['nama'];
            return $response->withHeader('Location', base_path() . '/home')->withStatus(302);

            //company
        } elseif ($user['role'] === 'company') {
            $company = $this->db->get('companies', '*', ['user_id' => $user['id']]);

            $_SESSION['user'] = [
                'id'              => $user['id'],
                'email'           => $user['email'],
                'role'            => 'company',
                'nama_perusahaan' => $company['nama_perusahaan'] ?? $user['nama'],
                'alamat_perusahaan' => $company['alamat_perusahaan'] ?? '',
                'no_telp'         => $company['no_telp'] ?? '',
                'deskripsi'       => $company['deskripsi'] ?? '',
                'logo'            => $company['logo'] ?? null
            ];

            return $response->withHeader('Location', base_path() . '/company/dashboard')->withStatus(302);
        }



        // Fallback
        return $response->withHeader('Location', base_path() . '/')->withStatus(302);
    }

    // ========== LOGOUT ==========
    public function logout(Request $request, Response $response)
    {
        session_destroy();
        return $response->withHeader('Location', base_path() . '/login')->withStatus(302);
    }

    // ========== REGISTER PERUSAHAAN ==========
    public function showRegisterCompany(Request $request, Response $response)
    {
        return view($request, $response, 'auth/register_company.twig');
    }

    public function registerCompany(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        if (!$data['nama'] || !$data['email'] || !$data['password']) {
            return view($request, $response, 'auth/register_company.twig', ['error' => 'Semua field wajib diisi']);
        }

        $existing = $this->db->get('users', '*', ['email' => $data['email']]);
        if ($existing) {
            return view($request, $response, 'auth/register_company.twig', ['error' => 'Email sudah terdaftar']);
        }

        // Simpan user
        $this->db->insert('users', [
            'nama'     => $data['nama'],
            'email'    => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role'     => 'company'
        ]);
        $userId = $this->db->id();

        // Simpan ke companies
        $this->db->insert('companies', [
            'user_id' => $userId,
            'nama_perusahaan' => $data['nama']
        ]);

        $_SESSION['user'] = [
            'id' => $userId,
            'email' => $data['email'],
            'role' => 'company',
            'nama_perusahaan' => $data['nama'],
            'alamat' => '',
            'logo' => null
        ];

        return $response->withHeader('Location', base_path() . '/company/dashboard')->withStatus(302);
    }
}
