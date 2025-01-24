<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;

final class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_registration')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // MDP hashé
            $hashedPassword = $userPasswordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            );
            $user->setPassword($hashedPassword);

            // Avatar
            $avatar = $form->get('avatar')->getData();
            if ($avatar) {
                $this->saveAvatarAsWebp($avatar, $user->getId());
                $user->setAvatar('uploads/avatars/' . $user->getId() . '.webp');
            }

            // Enregistrement
            $entityManager->persist($user);
            $entityManager->flush();

            // Redirection
            return $this->redirectToRoute('app_home');
        }
        return $this->render('registration/index.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    private function saveAvatarAsWebp($file, $userId): void
    {
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/avatars/';

        // Créer le dossier si il n'existe pas
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Conversion en webp
        $image = imagecreatefromstring(file_get_contents($file->getPathname()));

        $width = imagesx($image);
        $height = imagesy($image);
        $newWidth = 300;
        $newHeight = ($height / $width) * $newWidth;
        $resizedImage = imagescale($image, $newWidth, $newHeight);
        $filename = $userId . '.webp';
        imagewebp($resizedImage, $uploadDir . $filename);
        imagedestroy($image);
        imagedestroy($resizedImage);
    }
}
