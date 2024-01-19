<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\User;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\UserRepository;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(#[CurrentUser] ?User $user, EntityManagerInterface $entity_manager): JsonResponse
    {
        // No user found with credentials
        if (null === $user) {
            return $this->json([
                'message' => 'Missing credentials.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // If there's no token, generate a new one
        if (null === $user->getToken()) {
            $user->setToken($this->generateToken());
            $entity_manager->flush();
        }

        return $this->json([
            'user'  => $user->getUserIdentifier(),
            'token' => $user->getToken(),
        ]);
    }

    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(Request $request, UserRepository $user_repository, EntityManagerInterface $entity_manager): JsonResponse
    {
        $token = $request->headers->get('X-AUTH-TOKEN');

        // Token validation
        $user = $user_repository->findOneByToken($token);

        if (!$user) {
            return $this->json([
                'message' => 'Invalid token.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Remove token for logout
        $user->setToken(null);
        $entity_manager->flush();

        return $this->json([
            'token' => $token
        ]);
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, UserRepository $user_repository, EntityManagerInterface $entity_manager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $request_body = json_decode($request->getContent(), true);

        // Validate
        $user = $user_repository->findOneByEmail($request_body['email']);

        if ($user || $request_body['password'] != $request_body['password_confirmation']) {
            return $this->json([
                'message' => 'Errors in validation.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Creating
        $user = new User();
        $user->setEmail($request_body['email']);
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                $request_body['password']
            )
        );
        $user->setToken($this->generateToken());

        // Saving
        $entity_manager->persist($user);
        $entity_manager->flush();

        return $this->json(['token' => $user->getToken()], status: Response::HTTP_CREATED);
    }

    public function generateToken()
    {
        return bin2hex(random_bytes(18));
    }
}
