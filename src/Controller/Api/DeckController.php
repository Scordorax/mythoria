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
    #[Route('/decks/user/{userId}', methods: ['GET'])]
    public function decksByUser(int $userId, DeckRepository $repo, \App\Repository\UserRepository $userRepo): JsonResponse
    {
        // Récupérer l'utilisateur
        $user = $userRepo->find($userId);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur introuvable'], 404);
        }

        // Récupérer les decks de cet utilisateur
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

    #[Route('/decks/create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, CardRepository $cardRepo, \App\Repository\UserRepository $userRepo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // ⚡ Récupérer l'utilisateur depuis userId envoyé par Angular
        $userId = $data['userId'] ?? null;
        if (!$userId) {
            return $this->json(['error' => 'userId manquant'], 400);
        }

        $user = $userRepo->find($userId);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur introuvable'], 404);
        }

        $deck = new Deck();
        $deck->setUsere($user);  // maintenant non-null
        $deck->setName($data['name']);
        $deck->setCreatedAt(new \DateTimeImmutable());

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
            'message' => 'Deck créé',
            'deckId' => $deck->getId()
        ]);
    }

    #[Route('/decks/{userId}/{id}', methods: ['DELETE'])]
    public function delete(
        int $userId,
        Deck $deck,
        EntityManagerInterface $em
    ): JsonResponse
    {

        if ($deck->getUsere()->getId() !== $userId) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        // Supprimer les cartes du deck
        foreach ($deck->getDeckCards() as $deckCard) {
            $em->remove($deckCard);
        }

        $em->remove($deck);
        $em->flush();

        return $this->json([
            'message' => 'Deck deleted'
        ]);
    }

    #[Route('/decks/{userId}/{id}', methods: ['PUT'])]
    public function update(
        int                    $userId,
        Deck                   $deck,
        Request                $request,
        EntityManagerInterface $em,
        CardRepository         $cardRepo
    ): JsonResponse
    {
        if ($deck->getUsere()->getId() !== $userId) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $deck->setName($data['name']);
        }

        // On ne touche aux cartes que si elles sont fournies dans la requête
        if (isset($data['cards']) && is_array($data['cards'])) {

            foreach ($deck->getDeckCards() as $deckCard) {
                $em->remove($deckCard);
            }

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
        }

        $em->flush();

        return $this->json([
            'message' => 'Deck updated',
            'deckId' => $deck->getId()
        ]);
    }

    #[Route('/decks/{userId}/{id}', methods: ['GET'])]
    public function getDeckDetail(
        int $userId,
        Deck $deck
    ): JsonResponse
    {

        // Vérifie que le deck appartient bien à l'utilisateur
        if ($deck->getUsere()->getId() !== $userId) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

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

        return $this->json([
            'deckId' => $deck->getId(),
            'name' => $deck->getName(),
            'cards' => $cards
        ]);
    }


}