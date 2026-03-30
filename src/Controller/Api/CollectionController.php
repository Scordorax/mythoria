<?php

namespace App\Controller\Api;

use App\Entity\Collectionne;
use App\Repository\CollectionneRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CollectionController extends AbstractController
{
    #[Route('/collections/{userId}', methods: ['GET'])]
    public function myCollection(string $userId,CollectionneRepository $repo, UserRepository $userRepo): JsonResponse
    {
        $user = $userRepo->find($userId);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $collections = $repo->findBy(['usere' => $user]);

        $result = [];
        foreach ($collections as $c) {
            $card = $c->getCard();
            $result[] = [
                'collectionId' => $c->getId(),
                'cardId' => $card->getId(),
                'cardName' => $card->getName(),
                'quantity' => $c->getQuantity(),
                'description' => $card->getDescription(),
                'type' => $card->getType(),
                'rarity' => $card->getRarity(),
                'attack' => $card->getAttack(),
                'defense' => $card->getDefense(),
                'hp' => $card->getHp(),
                'energyCost' => $card->getEnergyCost()
            ];
        }

        return $this->json($result);
    }
}