<?php

namespace App\Controller;

use App\Form\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function index(): Response
    {
        $loginForm = $this->createForm(LoginType::class);

        return $this->render('Login/index.html.twig',
            [
                'loginForm' => $loginForm->createView(),
            ]
        );
    }
}
