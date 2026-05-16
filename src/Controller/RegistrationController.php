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
                $config = \Brevo\Client\Configuration::getDefaultConfiguration()
                    ->setApiKey('api-key', $_ENV['BREVO_API_KEY']);

                $apiInstance = new \Brevo\Client\Api\TransactionalEmailsApi(
                    new \GuzzleHttp\Client(),
                    $config
                );

                $sendEmail = new \Brevo\Client\Model\SendSmtpEmail([
                    'sender' => ['email' => 'ab6828001@smtp-brevo.com', 'name' => 'Vite & Gourmand'],
                    'to' => [['email' => $user->getEmail()]],
                    'subject' => 'Bienvenue chez Vite & Gourmand !',
                    'htmlContent' => $this->renderView('registration/confirmation_email.html.twig'),
                ]);

                $apiInstance->sendTransacEmail($sendEmail);

            } catch (\Exception $e) {
                error_log('BREVO API ERROR: ' . $e->getMessage());
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
