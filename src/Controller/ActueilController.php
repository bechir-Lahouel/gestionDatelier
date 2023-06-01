<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActueilController extends AbstractController
{
    #[Route('/', name: 'app_actueil')]
    public function index(): Response
    {
        return $this->render('actueil/index.html.twig', [
            'controller_name' => 'ActueilController',
        ]);
    }


}
