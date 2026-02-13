<?php

namespace App\Controller;

use App\Entity\Participant;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ParticipantController extends AbstractController
{
    #[Route('/participant/{id}', name: 'participant_profile', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Participant $participant): Response
    {
        // Optionnel : si vous voulez réserver la consultation aux utilisateurs connectés,
        // décommente la ligne suivante.
        // $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('participant/profile.html.twig', [
            'participant' => $participant,
        ]);
    }
}


