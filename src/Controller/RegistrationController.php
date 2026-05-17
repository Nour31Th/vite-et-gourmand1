<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
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
                $apiKey = $_ENV['BREVO_API_KEY'];
                $htmlContent = $this->renderView('registration/confirmation_email.html.twig');

                $data = json_encode([
                    'sender' => ['email' => 'thualmiora.31@gmail.com', 'name' => 'Vite & Gourmand'],
                    'to' => [['email' => $user->getEmail()]],
                    'subject' => 'Bienvenue chez Vite & Gourmand !',
                    'htmlContent' => $htmlContent,
                ]);

                $ch = curl_init('https://api.brevo.com/v3/smtp/email');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'api-key: ' . $apiKey,
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode !== 201) {
                    error_log('BREVO ERROR: ' . $response);
                    $this->addFlash('warning', 'Email non envoyé : ' . $response);
                }

            } catch (\Exception $e) {
                error_log('BREVO EXCEPTION: ' . $e->getMessage());
                $this->addFlash('warning', 'Email non envoyé : ' . $e->getMessage());
            }

            $this->addFlash('success', 'Votre compte a été créé avec succès ! Bienvenue chez Vite & Gourmand.');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}