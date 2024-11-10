<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Driver;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        // Créer un utilisateur de base
        $user = new User();
        
        // Créer le formulaire associé à l'utilisateur
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // Vérifier si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer le type d'utilisateur (passenger ou driver) à partir du formulaire
            $userType = $form->get('userType')->getData();

            // Si l'utilisateur est un conducteur, créer une instance de Driver au lieu de User
            if ($userType === 'driver') {
                $user = new Driver();  // Crée un nouvel objet Driver
                $form = $this->createForm(RegistrationFormType::class, $user);  // Recréation du formulaire pour le driver
                $form->handleRequest($request);
            }

            // Récupérer et encoder le mot de passe
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setValidated(false); // L'utilisateur est initialement non validé

            // Persister l'utilisateur dans la base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Message de confirmation
            $this->addFlash('info', 'Your registration is pending approval from an administrator.');

            // Rediriger vers la page de connexion
            return $this->redirectToRoute('app_login');
        }

        // Rendre la vue avec le formulaire
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
