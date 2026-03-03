<?php

namespace App\Controller\Api;

use App\Entity\Card;
use App\Repository\CardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('IS_AUTHENTICATED_FULLY')]
class CardController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(CardRepository $cardRepository): JsonResponse
    {
        $cards = $cardRepository->findAll();

        // Retour JSON prêt pour Angular
        $result = [];
        foreach($cards as $card){
            $result[] = [
                'id' => $card->getId(),
                'name' => $card->getName(),
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

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id, CardRepository $cardRepository): JsonResponse
    {
        $card = $cardRepository->find($id);

        if (!$card) {
            return $this->json(['error' => 'Card not found'], 404);
        }

        $result = [
            'id' => $card->getId(),
            'name' => $card->getName(),
            'description' => $card->getDescription(),
            'type' => $card->getType(),
            'rarity' => $card->getRarity(),
            'attack' => $card->getAttack(),
            'defense' => $card->getDefense(),
            'hp' => $card->getHp(),
            'energyCost' => $card->getEnergyCost()
        ];

        return $this->json($result);
    }
}