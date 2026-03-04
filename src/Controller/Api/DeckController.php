<?php

namespace App\Controller\Api;

use App\Entity\Deck;
use App\Entity\DeckCard;
use App\Repository\CardRepository;
use App\Repository\DeckRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class DeckController extends AbstractController
{
    #[Route('/decks', methods: ['GET'])]
    public function myDecks(DeckRepository $repo): JsonResponse
    {
        $user = $this->getUser();
        $decks = $repo->findBy(['usere' => $user]);

        $result = [];
        foreach ($decks as $deck) {
            $cards = [];
            foreach ($deck->getDeckCards() as $deckCard) {
                $card = $deckCard->getCard();
                $cards[] = [
                    'cardId' => $card->getId(),
                    'name' => $card->getName(),
                    'quantity' => $deckCard->getQuantity(),
                    'type' => $card->getType(),
                    'rarity' => $card->getRarity(),
                    'attack' => $card->getAttack(),
                    'defense' => $card->getDefense(),
                    'hp' => $card->getHp(),
                    'energyCost' => $card->getEnergyCost()
                ];
            }

            $result[] = [
                'deckId' => $deck->getId(),
                'name' => $deck->getName(),
                'cards' => $cards
            ];
        }

        return $this->json($result);
    }

    #[Route('/create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, CardRepository $cardRepo): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        $deck = new Deck();
        $deck->setUser($user);
        $deck->setName($data['name']);
        $em->persist($deck);

        // Ajouter les cartes au deck
        foreach ($data['cards'] as $c) {
            $card = $cardRepo->find($c['id']);
            if ($card) {
                $deckCard = new DeckCard();
                $deckCard->setDeck($deck);
                $deckCard->setCard($card);
                $deckCard->setQuantity($c['quantity']);
                $em->persist($deckCard);
            }
        }

        $em->flush();

        return $this->json([
            'message' => 'Deck created',
            'deckId' => $deck->getId()
        ]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Deck $deck, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        if ($deck->getUser() !== $user) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $em->remove($deck);
        $em->flush();

        return $this->json(['message' => 'Deck deleted']);
    }
}