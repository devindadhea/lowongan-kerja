<?php
namespace App\Controllers;

use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    private Medoo $db;
    public function __construct(Medoo $db) { $this->db = $db; }

    /* ===== REGISTER USER ================================================= */
    public function showRegister(Request $r, Response $s) { return view($r,$s,'auth/register.twig'); }

    public function register(Request $r, Response $s): Response
    {
        $d = $r->getParsedBody();

        /* validasi sederhana */
        if (!$d['nama'] || !$d['email'] || !$d['password']) {
            return view($r,$s,'auth/register.twig',['error'=>'Semua field wajib diisi']);
        }
        if ($this->db->has('users',['email'=>$d['email']])) {
            return view($r,$s,'auth/register.twig',['error'=>'Email sudah terdaftar']);
        }

        /* 1. simpan ke users */
        $this->db->insert('users', [
            'nama'     => $d['nama'],
            'email'    => $d['email'],
            'password' => password_hash($d['password'],PASSWORD_DEFAULT),
            'role'     => 'user'
        ]);
        $userId = $this->db->id();

        /* 2. pastikan ada baris di job_seekers */
        $this->db->insert('job_seekers', ['user_id'=>$userId]);

        /* 3. set session */
        $_SESSION['user'] = [
            'id'            => $userId,
            'email'         => $d['email'],
            'nama'          => $d['nama'],
            'role'          => 'user',
            'jobseeker_id'  => $userId        // FK di tabel applications
        ];

        return $s->withHeader('Location', base_path().'/user/home')->withStatus(302);
    }

    /* ===== LOGIN ========================================================= */
    public function showLogin(Request $r, Response $s) { return view($r,$s,'auth/login.twig'); }

   public function login(Request $r, Response $s): Response
{
    $d    = $r->getParsedBody();
    $user = $this->db->get('users','*',['email'=>$d['email']]);

    if (!$user || !password_verify($d['password'],$user['password'])) {
        return view($r,$s,'auth/login.twig',['error'=>'Email atau password salah']);
    }

    /* ---- route per-role ---- */
    if ($user['role'] == 'admin') {
        $_SESSION['user'] = [
            'id'    => $user['id'],
            'email' => $user['email'],
            'nama'  => $user['nama'],
            'role'  => 'admin'
        ];

        return $s->withHeader('Location', base_path().'/dashboard')->withStatus(302);

    } elseif ($user['role'] == 'company') {
        $c = $this->db->get('companies','*',['user_id' => $user['id']]);

        if (!$c) {
            return view($r, $s, 'auth/login.twig', ['error' => 'Data perusahaan tidak ditemukan.']);
        }

        $_SESSION['user'] = [
            'id'                => $user['id'],
            'email'             => $user['email'],
            'role'              => 'company',
            'nama_perusahaan'   => $c['nama_perusahaan'] ?? $user['nama'],
            'alamat_perusahaan' => $c['alamat_perusahaan'] ?? '',
            'no_telp'           => $c['no_telp'] ?? '',
            'deskripsi'         => $c['deskripsi'] ?? '',
            'logo'              => $c['logo'] ?? null
        ];

        return $s->withHeader('Location', base_path().'/company/dashboard')->withStatus(302);

    } else {
        // pastikan baris di job_seekers ada, kalau belum buat
        if (!$this->db->has('job_seekers',['user_id' => $user['id']])) {
            $this->db->insert('job_seekers',['user_id' => $user['id']]);
        }

        // ambil id dari tabel job_seekers
        $jobSeeker = $this->db->get('job_seekers', '*', ['user_id' => $user['id']]);

        $_SESSION['user'] = [
            'id'            => $user['id'],
            'email'         => $user['email'],
            'nama'          => $user['nama'],
            'role'          => 'user',
            'jobseeker_id'  => $jobSeeker['id'] ?? $user['id']
        ];

        return $s->withHeader('Location', base_path().'/user/home')->withStatus(302);
    }

    }

    /* ===== LOGOUT ======================================================== */
    public function logout(Request $r, Response $s): Response
    {
        session_unset();
        session_destroy();

        return $s->withHeader('Location',base_path().'/')->withStatus(302);
    }

    /* ===== REGISTER COMPANY (tidak diubah) =============================== */
    public function showRegisterCompany(Request $r, Response $s) { return view($r,$s,'auth/register_company.twig'); }

    public function registerCompany(Request $r, Response $s): Response
    {
        $d = $r->getParsedBody();
        if (!$d['nama'] || !$d['email'] || !$d['password']) {
            return view($r,$s,'auth/register_company.twig',['error'=>'Semua field wajib diisi']);
        }
        if ($this->db->has('users',['email'=>$d['email']])) {
            return view($r,$s,'auth/register_company.twig',['error'=>'Email sudah terdaftar']);
        }

        $this->db->insert('users', [
            'nama'     => $d['nama'],
            'email'    => $d['email'],
            'password' => password_hash($d['password'],PASSWORD_DEFAULT),
            'role'     => 'company'
        ]);
        $userId = $this->db->id();

        $this->db->insert('companies', [
            'user_id'          => $userId,
            'nama_perusahaan'  => $d['nama']
        ]);

        $_SESSION['user'] = [
            'id'               => $userId,
            'email'            => $d['email'],
            'role'             => 'company',
            'nama_perusahaan'  => $d['nama'],
            'alamat_perusahaan'=> '',
            'logo'             => null
        ];
        return $s->withHeader('Location',base_path().'/company/dashboard')->withStatus(302);
    }
}
