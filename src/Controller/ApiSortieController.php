<?php

namespace App\Controller;

use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ApiSortieController extends AbstractController
{
    #[Route('/api/sortie', name: 'api_sortie_list', methods: ['GET'])]
    public function list(SortieRepository $sortieRepository, SerializerInterface $serializer, Request $request): Response
    {
        $etatsExclure = ['En création', 'Terminée', 'Annulée', 'Historisée'];
        $etatsAutorises = ['Ouverte', 'Clôturée', 'En cours'];
        $qb = $sortieRepository->createQueryBuilder('s')
                                ->innerJoin('s.etat', 'e')
                                ->andWhere('e.libelle NOT IN (:etatsExclure)')
                                ->setParameter('etatsExclure', $etatsExclure);


        if($request->query->count() !== 0)
        {
            $etat = $request->query->get('etat');
            $date = $request->query->get('date');
            if ($etat && !in_array($etat, $etatsAutorises, true)) {
                return new JsonResponse([
                    'error' => 'Etat invalide',
                    'Etats valides' => $etatsAutorises], Response::HTTP_BAD_REQUEST);
            }


            if($etat){
                $qb->andWhere('e.libelle = :etat')
                    ->setParameter('etat', $etat);
            }

            if($date){
                $dateHeureDebut = \DateTime::createFromFormat('Y-m-d', $date);
                if (!$dateHeureDebut) {
                    return new JsonResponse([
                        'error' => 'Format de date invalide, veuillez entrer YYYY-mm-dd',
                        'exemple' => '2025-06-29'], Response::HTTP_BAD_REQUEST);
                }
                $errors = \DateTime::getLastErrors();
                if($errors){
                        return new JsonResponse([
                            'error' => 'Date non valide',
                        ], Response::HTTP_BAD_REQUEST);
                }

                $qb->andWhere('s.dateHeureDebut < :date')
                    ->setParameter('date', $dateHeureDebut);
            }

        }

        $qb->orderBy('s.dateHeureDebut', 'ASC');
        $sortieList = $qb->getQuery()->getResult();
        $sortieListJson = $serializer->serialize($sortieList, 'json', ['groups' => 'getSorties']);

        return new JsonResponse($sortieListJson, Response::HTTP_OK, [], true);
    }
}
