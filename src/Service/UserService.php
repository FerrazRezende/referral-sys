<?php

namespace ReferralSystem\Service;

use Exception;
use ReferralSystem\Database\Connection;
use ReferralSystem\Repository\UserRepository;

class UserService
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function addUser(string $name, int $points, ?int $referrerId = null): int
    {
        $totalUsers = $this->userRepository->countUsers();
        if ($totalUsers >= 3) {
            throw new Exception("Máximo de 3 usuários permitidos!");
        }

        $this->userRepository->beginTransaction();
        try {
            $newUserId = $this->userRepository->create([
                'name' => $name, 'current_points' => $points,
            ]);

            $pdo = Connection::getInstance();

            if ($totalUsers === 0) {
                $pdo->prepare("INSERT INTO binary_tree_structure (user_id, parent_id, position) VALUES (?, NULL, 'root')")
                    ->execute([$newUserId]);
            } else {
                $parent = $this->userRepository->findById($referrerId);
                $position = $this->userRepository->findNextAvailablePosition($referrerId);

                $pdo->prepare("INSERT INTO binary_tree_structure (user_id, parent_id, position) VALUES (?, ?, ?)")
                    ->execute([$newUserId, $referrerId, $position]);
                $pdo->prepare("INSERT INTO referrals (referrer_id, referred_id) VALUES (?, ?)")
                    ->execute([$referrerId, $newUserId]);
            }

            $pdo->prepare("INSERT INTO points_history (user_id, points, operation, description) VALUES (?, ?, 'set', 'Initial Points')")
                ->execute([$newUserId, $points]);

            $this->userRepository->commit();
            return $newUserId;
        } catch (Exception $e) {
            $this->userRepository->rollback();
            throw new Exception("Erro ao adicionar um usuário:" . $e->getMessage());
        }
    }

    public function updateUserPoints(int $userId, int $newPoints): bool
    {
        $user = $this->userRepository->findById($userId);

        $this->userRepository->beginTransaction();
        try {
            $pdo = Connection::getInstance();
            
            $pdo->prepare("UPDATE users SET current_points = ? WHERE id = ?")
                ->execute([$newPoints, $userId]);

            $pdo->prepare("INSERT INTO points_history (user_id, points, operation, description) VALUES (?, ?, 'set', 'Points updated')")
                ->execute([$userId, $newPoints]);

            $this->userRepository->commit();
            return true;
        } catch (Exception $e) {
            $this->userRepository->rollback();
            throw new Exception("Erro ao atualizar os pontos:" . $e->getMessage());
        }
    }

    public function resetSystem(): bool
    {
        try {
            $pdo = Connection::getInstance();
            
            $pdo->prepare("DELETE FROM referrals")->execute();
            $pdo->prepare("DELETE FROM points_history")->execute();
            $pdo->prepare("DELETE FROM binary_tree_structure")->execute();
            $pdo->prepare("DELETE FROM users")->execute();
            $pdo->prepare("ALTER TABLE users AUTO_INCREMENT = 1")->execute();

            return true;
        } catch (Exception $e) {
            throw new Exception("Erro ao resetar o sistema: " . $e->getMessage());
        }
    }

    public function getScoreboard(int $userId): array
    {
        $userTree = $this->userRepository->findTreeById($userId);

        $leftPoints = $userTree->getLeftChild() ? $userTree->getLeftChild()->calculatePoints() : 0;
        $rightPoints = $userTree->getRightChild() ? $userTree->getRightChild()->calculatePoints() : 0;

        return [
            'user_name' => $userTree->getName(),
            'left_points' => $leftPoints,
            'right_points' => $rightPoints,
        ];
    }
}
