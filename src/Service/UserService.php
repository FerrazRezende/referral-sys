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

    public function addUser(string $name, int $points, int $referrerId): int
    {
        $parent = $this->userRepository->findById($referrerId);
        if (!$parent) {
            throw new Exception("Indicator with ID {$referrerId} not found.");
        }

        $position = $this->userRepository->findNextAvailablePosition($referrerId);
        if ($position === null) {
            throw new Exception("The {$parent['name']} indicator has no free positions.");
        }

        $this->userRepository->beginTransaction();
        try {
            $newUserId = $this->userRepository->create([
                'name' => $name, 'current_points' => $points,
            ]);

            $pdo = Connection::getInstance();

            $pdo->prepare("INSERT INTO binary_tree_structure (user_id, parent_id, position) VALUES (?, ?, ?)")
                ->execute([$newUserId, $referrerId, $position]);

            $pdo->prepare("INSERT INTO points_history (user_id, points, operation, description) VALUES (?, ?, 'set', 'Initial Points')")
                ->execute([$newUserId, $points]);

            $pdo->prepare("INSERT INTO referrals (referrer_id, referred_id) VALUES (?, ?)")
                ->execute([$referrerId, $newUserId]);

            $this->userRepository->commit();
            return $newUserId;
        } catch (Exception $e) {
            $this->userRepository->rollback();
            throw new Exception("Error adding user: " . $e->getMessage());
        }
    }

    public function getScoreboard(int $userId): array
    {
        $userTree = $this->userRepository->findTreeById($userId);
        if (!$userTree) {
            throw new Exception("User with ID {$userId} not found.");
        }

        $leftPoints = $userTree->getLeftChild() ? $userTree->getLeftChild()->calculatePoints() : 0;
        $rightPoints = $userTree->getRightChild() ? $userTree->getRightChild()->calculatePoints() : 0;

        return [
            'user_name' => $userTree->getName(),
            'left_points' => $leftPoints,
            'right_points' => $rightPoints,
        ];
    }
}