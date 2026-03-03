<?php

namespace App\Controller\Api;

use App\Entity\Collectionne;
use App\Entity\Booster;
use App\Repository\BoosterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('IS_AUTHENTICATED_FULLY')]
class BoosterController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function list(BoosterRepository $repo): JsonResponse
    {
        $boosters = $repo->findAll();
        return $this->json($boosters);
    }

    #[Route('/open/{id}', methods: ['POST'])]
    public function open(int $id, BoosterRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        /** @var Booster|null $booster */
        $booster = $repo->find($id);
        if (!$booster) {
            return $this->json(['error' => 'Booster not found'], 404);
        }

        // Ajouter chaque carte du booster à la collection du joueur
        foreach ($booster->getCards() as $card) {
            $collection = $em->getRepository(Collectionne::class)->findOneBy([
                'user' => $user,
                'card' => $card
            ]);

            if ($collection) {
                // Si la carte existe déjà, incrémenter la quantité
                $collection->setQuantity($collection->getQuantity() + 1);
            } else {
                // Sinon créer une nouvelle entrée
                $collection = new Collectionne();
                $collection->setUser($user);
                $collection->setCard($card);
                $collection->setQuantity(1);
                $em->persist($collection);
            }
        }

        $em->flush();

        return $this->json([
            'message' => 'Booster opened',
            'cardsReceived' => count($booster->getCards())
        ]);
    }
}