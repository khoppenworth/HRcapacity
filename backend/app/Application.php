<?php

namespace App;

use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InstallationController;
use App\Services\AssessmentScoringService;
use App\Services\SystemCheckService;
use App\Support\Container;
use App\Support\Exceptions\HttpException;
use App\Support\Http\JsonResponse;
use App\Support\Http\Request;
use App\Support\Http\Router;

class Application
{
    private Router $router;

    public function __construct()
    {
        $this->router = new Router();
        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        $install = new InstallationController(new SystemCheckService());
        $auth = new AuthController();
        $assessments = new AssessmentController(new AssessmentScoringService());

        $this->router->add('GET', '/api/v1/install/status', fn (Request $request) => $install->status());
        $this->router->add('POST', '/api/v1/install', fn (Request $request) => $install->install($request));
        $this->router->add('POST', '/api/v1/auth/login', fn (Request $request) => $auth->login($request), ['tenant' => true]);
        $this->router->add('POST', '/api/v1/my/assessments', fn (Request $request) => $assessments->create($request), ['tenant' => true, 'auth' => true]);
        $this->router->add('PUT', '/api/v1/my/assessments/{id}/submit', fn (Request $request, string $id) => $assessments->submit($request, $id), ['tenant' => true, 'auth' => true]);
    }

    public function handle(Request $request): JsonResponse
    {
        try {
            return $this->router->dispatch($request);
        } catch (HttpException $e) {
            return new JsonResponse(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }
}
