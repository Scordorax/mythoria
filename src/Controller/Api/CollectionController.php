<?php

namespace App\Controller\Api;

use App\Entity\Collectionne;
use App\Repository\CollectionneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CollectionController extends AbstractController
{
    #[Route('/collections', methods: ['GET'])]
    public function myCollection(CollectionneRepository $repo): JsonResponse
    {
        $user = $this->getUser();

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