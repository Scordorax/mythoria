<?php

namespace App\Controller\Api;

use App\Entity\Collectionne;
use App\Entity\Booster;
use App\Repository\BoosterRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class BoosterController extends AbstractController
{
    #[Route('/boosters', methods: ['GET'])]
    public function list(BoosterRepository $repo): JsonResponse
    {
        $boosters = $repo->findAll();

        // Transformer chaque booster en tableau simple
        $data = array_map(fn($b) => [
            'id' => $b->getId(),
            'name' => $b->getName(),
            'price' => $b->getPrice(),
            'created_at' => $b->getCreatedAt()->format('Y-m-d H:i:s')
        ], $boosters);

        return $this->json($data);
    }

    #[Route('/boosters/open/{userId}/{boosterId}', methods: ['POST'])]
    public function open(int $userId, int $boosterId, UserRepository $userRepo, BoosterRepository $boosterRepo, EntityManagerInterface $em): JsonResponse
    {
        // Récupérer l'utilisateur par son ID
        $user = $userRepo->find($userId);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        // Récupérer le booster par son ID
        $booster = $boosterRepo->find($boosterId);
        if (!$booster) {
            return $this->json(['error' => 'Booster not found'], 404);
        }

        // Ajouter chaque carte du booster à la collection de l'utilisateur
        foreach ($booster->getCards() as $card) {
            $collection = $em->getRepository(Collectionne::class)->findOneBy([
                'usere' => $user,
                'card' => $card
            ]);

            if ($collection) {
                $collection->setQuantity($collection->getQuantity() + 1);
            } else {
                $collection = new Collectionne();
                $collection->setUsere($user);
                $collection->setCard($card);
                $collection->setQuantity(1);
                $em->persist($collection);
            }
        }

        $em->flush();

        return $this->json([
            'message' => 'Booster opened',
            'userId' => $user->getId(),
            'boosterId' => $booster->getId(),
            'cardsReceived' => count($booster->getCards())
        ]);
    }
}