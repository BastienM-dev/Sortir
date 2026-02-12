<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfileType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProfileController extends AbstractController
{
    #[Route('/profil', name: 'profil_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        ParticipantRepository $participantRepository,
        UserPasswordHasherInterface $passwordHasher,
        SluggerInterface $slugger
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var Participant|null $user */
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Utilisateur non trouvé.');
        }

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // 1) Mot de passe optionnel
            $plainPassword = $form->get('plainPassword')->get('first')->getData();
            if ($plainPassword !== null && trim($plainPassword) !== '') {
                if (mb_strlen($plainPassword) < 6) {
                    $this->addFlash('error', 'Le mot de passe doit contenir au moins 6 caractères.');
                    return $this->redirectToRoute('profil_edit');
                }
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }

            // 2) Photo optionnelle
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                try {
                    $uploadDir = $this->getParameter('uploads_photos');

                    if (!is_dir($uploadDir)) {
                        throw new \RuntimeException("Le dossier d'upload n'existe pas : " . $uploadDir);
                    }
                    if (!is_writable($uploadDir)) {
                        throw new \RuntimeException("Le dossier d'upload n'est pas accessible en écriture : " . $uploadDir);
                    }

                    $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);

                    $ext = $photoFile->guessExtension() ?: ($photoFile->getClientOriginalExtension() ?: 'bin');
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $ext;

                    $photoFile->move($uploadDir, $newFilename);

                    $user->setPhotoFilename($newFilename);
                    $this->addFlash('success', "L'image a été bien chargée.");
                } catch (\Throwable $e) {
                    $this->addFlash('error', "Erreur lors de l'upload : " . $e->getMessage());
                }
            }

            $em->flush();
            $this->addFlash('success', 'Profil mis à jour.');

            return $this->redirectToRoute('profil_edit');
        }

        // user "fresh" (toujours à jour, même si app.user est en retard)
        $freshUser = $participantRepository->find($user->getId());

        return $this->render('profil/edit.html.twig', [
            'profileForm' => $form->createView(),
            'user' => $freshUser ?? $user,
        ]);
    }
}
