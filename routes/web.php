<?php

use App\Middleware\RoleMiddleware;
use App\Controllers\AuthController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\ApplicantController;
use App\Controllers\Admin\AdminProfileController;
use App\Controllers\Admin\JobController;
use App\Controllers\Admin\CategoryController;
use App\Controllers\Company\DashboardController as CompanyDashboardController;
use App\Controllers\Company\CompanyJobController;
use App\Controllers\Company\CompanyProfileController;

//LANDING
$app->get('/', function ($request, $response) {
    return view($request, $response, 'landing.twig');
});

//REGISTER
$app->get('/register', [AuthController::class, 'showRegister']);
$app->post('/register', [AuthController::class, 'register']);
//LOGIN
$app->get('/login', [AuthController::class, 'showLogin']);
$app->post('/login', [AuthController::class, 'login']);
$app->get('/logout', [AuthController::class, 'logout']);

//REGISTRASI PERUSAHAAN 
$app->get('/register-company', [AuthController::class, 'showRegisterCompany']);
$app->post('/register-company', [AuthController::class, 'registerCompany']);


// Halaman utama untuk user
$app->get('/home', function ($request, $response) {
    return view($request, $response, 'user/home.twig');
});

// DASHBOARD ADMIN
$app->get('/dashboard', DashboardController::class . ':index')->add(new RoleMiddleware('admin'));

// PROFILE ADMIN
$app->group('/admin', function ($group) {
    $group->get('/profile', \App\Controllers\Admin\AdminProfileController::class . ':index');
    $group->post('/profile', \App\Controllers\Admin\AdminProfileController::class . ':update');

    // KATEGORI
    $group->get('/categories', CategoryController::class . ':index');
    $group->get('/categories/create', CategoryController::class . ':create');
    $group->post('/categories/create', CategoryController::class . ':store');
    $group->get('/categories/edit/{id}', CategoryController::class . ':edit');
    $group->post('/categories/edit/{id}', CategoryController::class . ':update');
    $group->post('/categories/delete/{id}', CategoryController::class . ':delete');

    // LOWONGAN
    $group->get('/jobs', [JobController::class, 'index']);
    $group->get('/jobs/create', [JobController::class, 'create']);
    $group->post('/jobs/store', [JobController::class, 'store']);
    $group->get('/jobs/edit/{id}', [JobController::class, 'edit']);
    $group->post('/jobs/update/{id}', [JobController::class, 'update']);
    $group->post('/jobs/delete/{id}', [JobController::class, 'delete']);

    // PELAMAR
    $group->get('/pelamar', [ApplicantController::class, 'allApplicants']);
    $group->get('/applicants/{job_id}', [ApplicantController::class, 'index']);
    $group->post('/applicants/status', [ApplicantController::class, 'updateStatus']);
})->add(new RoleMiddleware('admin'));


// DASHBOARD PERUSAHAAN
$app->group('/company', function ($group) {
    $group->get('/dashboard', [CompanyDashboardController::class, 'index']);

    // CRUD LOWONGAN
    $group->get('/jobs', [CompanyJobController::class, 'index']);
    $group->get('/jobs/create', [CompanyJobController::class, 'create']);
    $group->post('/jobs/store', [CompanyJobController::class, 'store']);
    $group->get('/jobs/edit/{id}', [CompanyJobController::class, 'edit']);
    $group->post('/jobs/update/{id}', [CompanyJobController::class, 'update']);
    $group->get('/jobs/delete/{id}', [CompanyJobController::class, 'delete']);

    // PROFILE
 $group->get('/profileCompany', CompanyProfileController::class . ':index');
$group->post('/profileCompany', CompanyProfileController::class . ':update');

})->add(new RoleMiddleware('company'));