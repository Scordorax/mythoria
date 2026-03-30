<?php

namespace App\Controller\Api;

use App\Entity\Card;
use App\Repository\BoosterRepository;
use App\Repository\CardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;


class CardController extends AbstractController
{
    #[Route('/cards', methods: ['GET'])]
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

    #[Route('/cards/{id}', methods: ['GET'])]
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

    #[Route('/cards/create/{boosterId}', methods: ['POST'])]
    public function create(
        int $boosterId,
        Request $request,
        EntityManagerInterface $em,
        BoosterRepository $boosterRepo
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        // 🔍 Récupérer le booster
        $booster = $boosterRepo->find($boosterId);

        if (!$booster) {
            return $this->json(['error' => 'Booster not found'], 404);
        }

        // 🃏 Créer la carte
        $card = new Card();

        $card->setName($data['name'] ?? '');
        $card->setDescription($data['description'] ?? '');
        $card->setType($data['type'] ?? '');
        $card->setRarity($data['rarity'] ?? '');
        $card->setAttack($data['attack'] ?? 0);
        $card->setDefense($data['defense'] ?? 0);
        $card->setHp($data['hp'] ?? 0);
        $card->setEnergyCost($data['energyCost'] ?? 0);
        $card->setCreatedAt(new \DateTimeImmutable());

        // 💾 Sauvegarde carte
        $em->persist($card);

        // 🔗 Association avec le booster
        $booster->addCard($card);

        $em->flush();

        return $this->json([
            'message' => 'Card created and linked to booster',
            'cardId' => $card->getId(),
            'boosterId' => $booster->getId()
        ]);
    }


}