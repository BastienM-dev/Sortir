<?php

namespace App\Controller;

use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class ApiSortieController extends AbstractController
{
    #[Route('/api/sortie', name: 'api_sortie_list', methods: ['GET', 'POST'])]
    public function list(SortieRepository $sortieRepository, SerializerInterface $serializer, Request $request): Response
    {
        $etatsExclure = ['En création', 'Terminée', 'Annulée', 'Historisée'];
        $qb = $sortieRepository->createQueryBuilder('s')
                                ->innerJoin('s.etat', 'e')
                                ->andWhere('s.etat NOT IN (:etatsExclure)')
                                ->setParameter('etatsExclure', $etatsExclure);


        if($request->query->count() !== 0)
        {
            $etat = $request->query->get('etat');
            $date = $request->query->get('date');


            if($etat){
                $qb->andWhere('s.etat = :etat')
                    ->setParameter('etat', $etat);
            }

            if($date){
                $dateHeureDebut = \DateTime::createFromFormat('Y-m-d', $date);
                $qb->andWhere('s.date < :date')
                    ->setParameter('date', $dateHeureDebut);
            }

        }

        $qb->orderBy('s.dateHeureDebut', 'ASC');
        $sortieList = $qb->getQuery()->getResult();
        $sortieListJson = $serializer->serialize($sortieList, 'json', ['groups' => 'getSorties']);

        return new JsonResponse($sortieListJson, Response::HTTP_OK, [], true);
    }
}
