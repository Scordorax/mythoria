<?php

namespace App\Controller\Api;

use App\Entity\GameMatch;
use App\Entity\MatchPlayer;
use App\Entity\MatchAction;
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
    private GameEngine $gameEngine;

    public function __construct(GameEngine $gameEngine)
    {
        $this->gameEngine = $gameEngine;
    }

    /**
     * 📋 Liste des matchs
     */
    #[Route('/user/{userId}', methods: ['GET'])]
    public function myMatches(int $userId, GameMatchRepository $repo, UserRepository $userRepo): JsonResponse
    {
        $user = $userRepo->find($userId);

        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $matches = $repo->findByUser($user);

        $result = [];

        foreach ($matches as $match) {
            $players = [];

            foreach ($match->getMatchPlayers() as $p) {
                $players[] = [
                    'username' => $p->getUsere()?->getUsername() ?? 'AI',
                    'lifePoints' => $p->getLifePoints(),
                    'energy' => $p->getEnergy(),
                ];
            }

            $result[] = [
                'matchId' => $match->getId(),
                'status' => $match->getStatus(),
                'turn' => $match->getCurrentTurn(),
                'players' => $players
            ];
        }

        return $this->json($result);
    }

    /**
     * 🎮 Créer un match
     */
    #[Route('/create/{userId}/{deckId}', methods: ['POST'])]
    public function createMatch(
        int $userId,
        int $deckId,
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepo,
        DeckRepository $deckRepo
    ): JsonResponse {

        $user = $userRepo->find($userId);
        if (!$user) {
            return $this->json([
                'error' => 'User not found in DB',
                'userId' => $userId
            ], 404);
        }

        $deck = $deckRepo->find($deckId);
        if (!$deck) {
            return $this->json(['error' => 'Deck not found'], 404);
        }

        $match = new GameMatch();
        $match->setStatus('in_progress');
        $match->setStartedAt(new \DateTimeImmutable());
        $match->setCurrentTurn(1);

        // 👤 PLAYER
        $player = new MatchPlayer();
        $player->setUsere($user);
        $player->setDeck($deck);
        $player->setLifePoints(100);
        $player->setEnergy(0);
        $player->setMatch($match);

        // 🤖 IA
        $aiUser = $userRepo->find(7);
        $aiDeck = $deckRepo->find(11);

        if (!$aiUser || !$aiDeck) {
            return $this->json([
                'error' => 'AI user or deck not found',
                'aiUser' => $aiUser ? $aiUser->getId() : null,
                'aiDeck' => $aiDeck ? $aiDeck->getId() : null
            ], 500);
        }

        $ai = new MatchPlayer();
        $ai->setUsere($aiUser);
        $ai->setDeck($aiDeck);
        $ai->setLifePoints(100);
        $ai->setEnergy(0);
        $ai->setMatch($match);

        // 🎴 Deck joueur
        $deckCards = [];

        foreach ($player->getDeck()?->getDeckCards() ?? [] as $dc) {
            $card = $dc->getCard();

            $deckCards[] = [
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

        // 🎴 Init
        $this->gameEngine->initGame($player, $deckCards);
        $this->gameEngine->initGame($ai, $deckCards);

        $em->persist($match);
        $em->persist($player);
        $em->persist($ai);

        $em->flush();

        return $this->json([
            'matchId' => $match->getId(),
            'message' => 'Match initialisé'
        ]);
    }

    /**
     * 🔍 Voir un match
     */
    #[Route('/{id}', methods: ['GET'])]
    public function show(GameMatch $match): JsonResponse
    {
        $players = [];

        foreach ($match->getMatchPlayers() as $p) {
            $players[] = [
                'username' => $p->getUsere()?->getUsername() ?? 'AI',
                'lifePoints' => $p->getLifePoints(),
                'energy' => $p->getEnergy(),
                'hand' => $p->getHand(),
                'activeCard' => $p->getActiveCard(),
                'deckCount' => count($p->getDeckState()),
                'discard' => $p->getDiscard()
            ];
        }

        return $this->json([
            'matchId' => $match->getId(),
            'turn' => $match->getCurrentTurn(),
            'status' => $match->getStatus(),
            'players' => $players
        ]);
    }

    /**
     * 🃏 Jouer une carte
     */
    #[Route('/{id}/play-card', methods: ['POST'])]
    public function playCard(
        Request $request,
        GameMatch $match,
        EntityManagerInterface $em
    ): JsonResponse {

        $user = $this->getUser();

        $player = null;
        $ai = null;

        foreach ($match->getMatchPlayers() as $p) {
            if ($p->getUsere() === $user) {
                $player = $p;
            } else {
                $ai = $p;
            }
        }

        if (!$player || !$ai) {
            return $this->json(['error' => 'Invalid match'], 400);
        }

        $data = json_decode($request->getContent(), true);
        $cardId = $data['cardId'] ?? null;

        $this->gameEngine->playerTurn($player);

        $card = $this->gameEngine->playCard($player, $cardId);

        if (!$card) {
            return $this->json(['error' => 'Not enough energy'], 400);
        }

        $result = $this->gameEngine->attack($player, $ai, $card);

        // LOG
        $action = new MatchAction();
        $action->setActionType('PLAYER_ATTACK');
        $action->setPayload([
            'card' => $card['name'],
            'damage' => $result['damage'],
            'ko' => $result['ko']
        ]);
        $action->setCreatedAt(new \DateTimeImmutable());
        $action->setPlayer($player);
        $action->setMatch($match);

        $em->persist($action);

        // IA
        $aiResult = $this->gameEngine->aiTurn($ai, $player);

        $aiAction = new MatchAction();
        $aiAction->setActionType('AI_TURN');
        $aiAction->setPayload($aiResult);
        $aiAction->setCreatedAt(new \DateTimeImmutable());
        $aiAction->setPlayer($ai);
        $aiAction->setMatch($match);

        $em->persist($aiAction);

        $match->setCurrentTurn($match->getCurrentTurn() + 1);

        if ($player->getLifePoints() <= 0 || $ai->getLifePoints() <= 0) {
            $match->setStatus('finished');
            $match->setEndedAt(new \DateTimeImmutable());
        }

        $em->flush();

        return $this->json([
            'message' => 'Tour joué',
            'playerResult' => $result,
            'aiResult' => $aiResult
        ]);
    }

    /**
     * 📊 Historique
     */
    #[Route('/{id}/actions', methods: ['GET'])]
    public function actions(GameMatch $match): JsonResponse
    {
        $actions = [];

        foreach ($match->getMatchActions() as $action) {
            $actions[] = [
                'type' => $action->getActionType(),
                'player' => $action->getPlayer()->getUsere()?->getUsername() ?? 'AI',
                'payload' => $action->getPayload(),
                'date' => $action->getCreatedAt()->format('H:i:s')
            ];
        }

        return $this->json($actions);
    }

    /**
     * 🏁 Finir match
     */
    #[Route('/{id}/end', methods: ['POST'])]
    public function end(GameMatch $match, EntityManagerInterface $em): JsonResponse
    {
        $match->setStatus('finished');
        $match->setEndedAt(new \DateTimeImmutable());

        $em->flush();

        return $this->json(['message' => 'Match terminé']);
    }
}