<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilePhotoType;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProfileController extends AbstractController
{
    /**
     * PAGE PROFIL (infos + mot de passe)
     * Route stable : /profil
     */
    #[Route('/profil', name: 'profil_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var Participant $user */
        $user = $this->getUser();

        // Form lié à l'utilisateur (modifie directement l'entité)
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Mot de passe : champ non mappé => on hash seulement s'il est rempli
            $plainPassword = $form->get('plainPassword')->getData();
            if (!empty($plainPassword)) {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $plainPassword)
                );
            }

            $em->flush();
            $this->addFlash('success', 'Profil mis à jour');

            return $this->redirectToRoute('profil_edit');
        }

        return $this->render('profil/edit.html.twig', [
            'profileForm' => $form->createView(),
        ]);
    }

    /**
     * PAGE PHOTO (upload uniquement)
     * Route existante conservée : /profil/photo
     * code Justine (slug + move + setPhotoFilename).
     */
    #[Route('/profil/photo', name: 'profil_photo_edit')]
    public function editPhoto(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var Participant $user */
        $user = $this->getUser();

        $form = $this->createForm(ProfilePhotoType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photo')->getData();

            if ($photoFile) {
                $originalFilename = pathinfo(
                    $photoFile->getClientOriginalName(),
                    PATHINFO_FILENAME
                );

                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                $photoFile->move(
                    $this->getParameter('profile_photos_dir'),
                    $newFilename
                );

                // Ton champ : photoFilename
                $user->setPhotoFilename($newFilename);
                $em->flush();

                $this->addFlash('success', 'Photo mise à jour ✅');

                // ✅ UX : retour au profil
                return $this->redirectToRoute('profil_edit');
            }
        }

        return $this->render('profil/photo.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
