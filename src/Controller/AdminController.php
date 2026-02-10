<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'admin_')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    // ==========================================
    // DASHBOARD
    // ==========================================

    #[Route('', name: 'dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    // ==========================================
    // GESTION UTILISATEURS (Thaïs)
    // ==========================================
    
    #[Route('/users', name: 'users', methods: ['GET'])]
    public function users(Request $request, ParticipantRepository $repo): Response
    {
        $q = trim((string) $request->query->get('q', ''));
        $actif = (string) $request->query->get('actif', '');
        $admin = (string) $request->query->get('admin', '');

        $users = $repo->searchUsers($q ?: null, $actif ?: null, $admin ?: null);

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
            'q' => $q,
            'actif' => $actif,
            'admin' => $admin,
        ]);
    }


    #[Route('/users/delete', name: 'users_delete', methods: ['POST'])]
    public function deleteUsers(
        Request $request,
        ParticipantRepository $participantRepository,
        EntityManagerInterface $em,
        CsrfTokenManagerInterface $csrfTokenManager
    ): RedirectResponse {

        // 1) Vérification du token CSRF (protection contre les requêtes forgées)
        $token = new CsrfToken('bulk_delete_users', (string) $request->request->get('_token'));
        if (!$csrfTokenManager->isTokenValid($token)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_users');
        }

        // 2) Récupération des IDs sélectionnés via les checkbox (ex: name="user_ids[]")
        $ids = $request->request->get('user_ids', []);
        if (!is_array($ids) || count($ids) === 0) {
            $this->addFlash('warning', 'Aucun utilisateur sélectionné.');
            return $this->redirectToRoute('admin_users');
        }

        // (Optionnel mais propre) on cast en int et on enlève les doublons / valeurs vides
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        // 3) On récupère les entités en une seule requête
        $users = $participantRepository->findByIds($ids);

        // 4) Si rien trouvé en base, on informe
        if (count($users) === 0) {
            $this->addFlash('warning', 'Aucun utilisateur correspondant trouvé.');
            return $this->redirectToRoute('admin_users');
        }

        // 5) Suppression en boucle, flush à la fin (plus performant)
        $deletedCount = 0;
        foreach ($users as $user) {
            // Ici c'est possible ajouter des règles :
            // - ne pas supprimer son propre compte
            // - ne pas supprimer un admin
            // - ne pas supprimer un user lié à des sorties, etc.

            $em->remove($user);
            $deletedCount++;
        }

        // 6) Exécution réelle des suppressions
        $em->flush();

        // 7) Feedback utilisateur
        $this->addFlash('success', sprintf('%d utilisateur(s) supprimé(s).', $deletedCount));

        return $this->redirectToRoute('admin_users');
    }

}