<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    // Création de compte public
    #[Route('/register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['username']) || empty($data['password'])) {
            return $this->json(['error' => 'Email, username and password required'], 400);
        }

        // Vérifie si email déjà utilisé
        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->json(['error' => 'Email already used'], 400);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setUsername($data['username']);
        $user->setPassword($hasher->hashPassword($user, $data['password']));
        $user->setRoles(['ROLE_USER']);
        $user->setEloRating(1000);
        $user->setCreatedAt(new \DateTimeImmutable());

        $em->persist($user);
        $em->flush();

        return $this->json([
            'message' => 'User created',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'elo_rating' => $user->getEloRating(),
                'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s')
            ]
        ]);
    }

    // Connexion simple sans JWT
    #[Route('/login', methods: ['POST'])]
    public function login(
        Request                     $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface      $em
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['password'])) {
            return $this->json(['error' => 'Email and password required'], 400);
        }

        $user = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if (!$user || !$hasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['error' => 'Invalid credentials'], 401);
        }

        // Génère un token maison
        $token = TokenGenerator::createToken([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'roles' => $user->getRoles()
        ]);

        return $this->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'elo_rating' => $user->getEloRating(),
                'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s')
            ]
        ]);
    }

    #[Route('/me', methods: ['GET'])]
    public function me(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->json(['error' => 'Token missing'], 401);
        }

        $token = substr($authHeader, 7); // enlève "Bearer "
        if (!TokenGenerator::isValid($token)) {
            return $this->json(['error' => 'Token invalide ou expiré'], 401);
        }

        $payload = TokenGenerator::decode($token);
        if (!$payload || !isset($payload['sub'])) {
            return $this->json(['error' => 'Payload invalide'], 401);
        }

        // On récupère l'utilisateur en base via l'ID stocké dans le token
        $user = $em->getRepository(User::class)->find($payload['sub']);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        return $this->json([
            'message' => 'User authenticated',
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'elo_rating' => $user->getEloRating(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s')

        ]);
    }
}