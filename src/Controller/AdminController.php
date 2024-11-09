<?php
// src/Controller/AdminController.php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $unvalidatedUsers = $entityManager->getRepository(User::class)->findBy(['isValidated' => false]);

        return $this->render('admin/dashboard.html.twig', [
            'unvalidatedUsers' => $unvalidatedUsers,
        ]);
    }

    #[Route('/admin/validate/{id}', name: 'admin_validate_user')]
    public function validateUser(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setValidated(true);
        $entityManager->flush();

        $this->addFlash('success', 'User validated successfully.');
        return $this->redirectToRoute('admin_dashboard');
    }
}
