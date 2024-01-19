<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    #[Route('/login', name: 'app_front_login')]
    public function login(): Response
    {
        return $this->render('auth/login.html.twig', [
            
        ]);
    }

    #[Route('/register', name: 'app_front_register')]
    public function register(): Response
    {
        return $this->render('auth/register.html.twig', [
            
        ]);
    }

    #[Route('/dashboard', name: 'app_front_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            
        ]);
    }
}
