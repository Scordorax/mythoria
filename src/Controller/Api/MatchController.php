<?php

namespace App\Controller\Api;

use App\Entity\GameMatch;
use App\Entity\MatchPlayer;
use App\Repository\DeckRepository;
use App\Repository\GameMatchRepository;
use App\Repository\UserRepository;
use App\Service\GameEngine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/match')]
class MatchController extends AbstractController
{
    public function __construct(private GameEngine $gameEngine)
    {
    }

    // =========================
    // 🎮 CREATE MATCH
    // =========================
    #[Route('/create/{userId}/{deckId}', methods: ['POST'])]
    public function createMatch(
        int $userId,
        int $deckId,
        EntityManagerInterface $em,
        UserRepository $userRepo,
        DeckRepository $deckRepo
    ): JsonResponse
    {
        $user = $userRepo->find($userId);
        $deck = $deckRepo->find($deckId);

        if (!$user || !$deck) {
            return $this->json(['error' => 'User or deck not found'], 404);
        }

        $match = new GameMatch();
        $match->setStatus('in_progress');
        $match->setStartedAt(new \DateTimeImmutable());
        $match->setCurrentTurn(1);

        // 👤 Joueur
        $player = new MatchPlayer();
        $player->setUsere($user);
        $player->setDeck($deck);
        $player->setLifePoints(100);
        $player->setEnergy(1000);
        $player->setMatch($match);

        // 🤖 IA
        $ai = new MatchPlayer();
        $ai->setUsere($userRepo->find(7));
        $ai->setDeck($deckRepo->find(11));
        $ai->setLifePoints(100);
        $ai->setEnergy(1000);
        $ai->setMatch($match);

        // 🎴 INIT
        $this->gameEngine->initGame($player, $this->extractCards($player));
        $this->gameEngine->initGame($ai, $this->extractCards($ai));

        $em->persist($match);
        $em->persist($player);
        $em->persist($ai);
        $em->flush();

        return $this->json([
            'matchId' => $match->getId()
        ]);
    }

    // =========================
    // 🃏 DRAW CARD
    // =========================
    #[Route('/{id}/draw-card', methods: ['POST'])]
    public function drawCard(
        Request $request,
        GameMatch $match,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userId = $data['userId'] ?? null;

        if (!$userId) {
            return $this->json(['error' => 'userId required'], 400);
        }

        $player = $em->getRepository(MatchPlayer::class)->findOneBy([
            'match' => $match,
            'usere' => $userId
        ]);

        if (!$player) {
            return $this->json(['error' => 'Player not found'], 400);
        }

        $card = $this->gameEngine->drawCard($player);

        if (!$card) {
            return $this->json(['error' => 'Deck empty'], 400);
        }

        $em->flush();

        return $this->json([
            'card' => $card
        ]);
    }

    // =========================
    // 🎴 PLAY CARD
    // =========================
    #[Route('/{id}/play-card', methods: ['POST'])]
    public function playCard(
        Request $request,
        GameMatch $match,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $userId = $data['userId'] ?? null;
        $cardId = $data['cardId'] ?? null;

        if (!$userId) {
            return $this->json(['error' => 'userId required'], 400);
        }

        // ⚠️ correction ici
        $user = $em->getRepository(MatchPlayer::class)->findOneBy([
            'match' => $match,
            'usere' => $userId
        ]);

        if (!$user) {
            return $this->json(['error' => 'Player not found'], 400);
        }

        $ai = $this->getOpponent($match, $user);

        if (!$ai) {
            return $this->json(['error' => 'Opponent not found'], 400);
        }

        $card = $this->gameEngine->playCard($user, $cardId);

        if (!$card) {
            return $this->json(['error' => 'Not enough energy or card not found'], 400);
        }

        // 🔥 attaque
        $result = $this->gameEngine->attack($user, $ai, $card);

        // 🤖 IA
        $aiResult = $this->gameEngine->aiTurn($ai, $user);

        $match->setCurrentTurn($match->getCurrentTurn() + 1);

        if ($user->getLifePoints() <= 0 || $ai->getLifePoints() <= 0) {
            $match->setStatus('finished');
            $match->setEndedAt(new \DateTimeImmutable());
        }

        $em->flush();

        return $this->json([
            'playerResult' => $result,
            'aiResult' => $aiResult
        ]);
    }

    // =========================
    // 📊 GET MATCH
    // =========================
    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id, GameMatchRepository $repo): JsonResponse
    {
        $match = $repo->find($id);

        if (!$match) {
            return $this->json(['error' => 'Match not found'], 404);
        }

        $players = [];

        foreach ($match->getMatchPlayers() as $p) {
            $players[] = [
                'userId' => $p->getUsere()->getId(),
                'username' => $p->getUsere()?->getUsername(),
                'lifePoints' => $p->getLifePoints(),
                'energy' => $p->getEnergy(),

                'hand' => $this->formatCards($p->getHand()),

                // ⚠️ IMPORTANT : toujours tableau
                'activeCards' => $this->formatCards($p->getActiveCard() ?? []),

                'deadCards' => $this->formatCards($p->getDeadCards() ?? []),

                'deckCount' => count($p->getDeckState()),
                'discard' => $this->formatCards($p->getDiscard())
            ];
        }

        return $this->json([
            'matchId' => $match->getId(),
            'status' => $match->getStatus(),
            'turn' => $match->getCurrentTurn(),
            'players' => $players
        ]);
    }

    // =========================
    // 🤖 OPPONENT
    // =========================
    private function getOpponent(GameMatch $match, MatchPlayer $player): ?MatchPlayer
    {
        foreach ($match->getMatchPlayers() as $p) {
            if ($p->getId() !== $player->getId()) {
                return $p;
            }
        }

        return null;
    }

    // =========================
    // 🎴 FORMAT
    // =========================
    private function formatCards(array $cards): array
    {
        return array_map(function ($card) {
            return [
                'id' => $card['id'] ?? null,
                'name' => $card['name'] ?? null,
                'attack' => $card['attack'] ?? null,
                'defense' => $card['defense'] ?? null,
                'hp' => $card['hp'] ?? null,
                'energyCost' => $card['energyCost'] ?? null
            ];
        }, $cards);
    }

    // =========================
    // 🎴 INIT DECK
    // =========================
    private function extractCards(MatchPlayer $player): array
    {
        $cards = [];

        foreach ($player->getDeck()->getDeckCards() as $dc) {
            $card = $dc->getCard();

            $cards[] = [
                'id' => $card->getId(),
                'name' => $card->getName(),
                'attack' => $card->getAttack(),
                'defense' => $card->getDefense(),
                'hp' => $card->getHp(),
                'energyCost' => $card->getEnergyCost()
            ];
        }

        return $cards;
    }
}