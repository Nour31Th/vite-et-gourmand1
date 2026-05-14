<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setRoles(['ROLE_USER']);
            $user->setActif(true);
            $user->setIsVerified(true);

            $entityManager->persist($user);
            $entityManager->flush();

            try {
                $email = (new Email())
                    ->from(new Address('noreply@viteetgourmand.fr', 'Vite & Gourmand'))
                    ->to((string) $user->getEmail())
                    ->subject('Bienvenue chez Vite & Gourmand !')
                    ->html($this->renderView('registration/confirmation_email.html.twig'));

                $mailer->send($email);
            } catch (\Exception $e) {
                // Si l'email échoue, on continue quand même
            }

            $this->addFlash('success', 'Votre compte a été créé avec succès ! Bienvenue chez Vite & Gourmand.');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
