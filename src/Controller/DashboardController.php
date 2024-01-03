<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class DashboardController extends AbstractController
{

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome dans le controller de votre api!',
            'path' => 'src/Controller/DashboardController.php',
        ]);
    }
}

