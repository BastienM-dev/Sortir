<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilePhotoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProfileController extends AbstractController
{
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

                $user->setPhotoFilename($newFilename);
                $em->flush();

                $this->addFlash('success', 'Photo mise à jour ✅');

                return $this->redirectToRoute('participant_show', [
                    'id' => $user->getId(),
                ]);
            }
        }

        return $this->render('profil/photo.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
