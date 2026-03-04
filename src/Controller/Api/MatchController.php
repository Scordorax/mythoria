<?php

namespace App\Controller\Api;

use App\Entity\GameMatch;
use App\Repository\GameMatchRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class MatchController extends AbstractController
{
    #[Route('/match', methods: ['GET'])]
    public function myMatches(GameMatchRepository $repo): JsonResponse
    {
        $user = $this->getUser();
        $matches = $repo->findByUser($user); // Custom repository qui récupère tous les matchs du joueur

        $result = [];
        foreach ($matches as $match) {
            $players = [];
            foreach ($match->getMatchPlayers() as $p) {
                $players[] = [
                    'userId' => $p->getUser()->getId(),
                    'username' => $p->getUser()->getUsername(),
                    'deckId' => $p->getDeck()->getId()
                ];
            }
            $result[] = [
                'matchId' => $match->getId(),
                'status' => $match->getStatus(),
                'createdAt' => $match->getCreatedAt()->format('Y-m-d H:i:s'),
                'players' => $players
            ];
        }

        return $this->json($result);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(GameMatch $match): JsonResponse
    {
        $user = $this->getUser();

        $isParticipant = false;
        foreach ($match->getMatchPlayers() as $p) {
            if ($p->getUser() === $user) {
                $isParticipant = true;
                break;
            }
        }

        if (!$isParticipant) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $players = [];
        foreach ($match->getMatchPlayers() as $p) {
            $players[] = [
                'userId' => $p->getUser()->getId(),
                'username' => $p->getUser()->getUsername(),
                'deckId' => $p->getDeck()->getId()
            ];
        }

        $result = [
            'matchId' => $match->getId(),
            'status' => $match->getStatus(),
            'createdAt' => $match->getCreatedAt()->format('Y-m-d H:i:s'),
            'players' => $players
        ];

        return $this->json($result);
    }

    #[Route('/{id}/play-card', methods: ['POST'])]
    public function playCard(Request $request, GameMatch $match, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        $player = null;
        foreach ($match->getMatchPlayers() as $p) {
            if ($p->getUser() === $user) {
                $player = $p;
                break;
            }
        }

        if (!$player) {
            return $this->json(['error' => 'Not participant'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $cardId = $data['cardId'] ?? null;

        if (!$cardId) {
            return $this->json(['error' => 'No card specified'], 400);
        }

        // Ici, tu appelles le service GameEngine pour appliquer la carte dans le match
        // Exemple :
        // $this->gameEngine->playCard($player, $cardId);

        return $this->json([
            'message' => 'Card played',
            'cardId' => $cardId
        ]);
    }
}