<?php

namespace ReferralSystem\Repository;

use PDO;
use ReferralSystem\Database\BaseRepository;
use ReferralSystem\Model\User;
use ReferralSystem\Model\UserComponent;

class UserRepository extends BaseRepository
{
    protected function getTable(): string
    {
        return 'users';
    }

    public function findTreeById(int $userId): ?UserComponent
    {
        $sql = 'SELECT u.id, u.name, u.current_points 
                FROM users u 
                WHERE u.id = :id';
        $userData = $this->queryOne($sql, ['id' => $userId]);

        return $userData ? $this->buildTreeFromData($userData) : null;
    }

    private function buildTreeFromData(array $userData): UserComponent
    {
        $user = new User($userData['id'], $userData['name'], $userData['current_points']);

        $sql = 'SELECT u.id, u.name, u.current_points, bts.position
                FROM users u
                JOIN binary_tree_structure bts ON u.id = bts.user_id
                WHERE bts.parent_id = :parent_id';
        $children = $this->query($sql, ['parent_id' => $user->getId()]);

        foreach ($children as $childData) {
            $childNode = $this->buildTreeFromData($childData);
            if ($childData['position'] === 'left') {
                $user->setLeftChild($childNode);
            } elseif ($childData['position'] === 'right') {
                $user->setRightChild($childNode);
            }
        }

        return $user;
    }

    public function findNextAvailablePosition(int $parentId): ?string
    {
        $sql = 'SELECT position FROM binary_tree_structure WHERE parent_id = :parent_id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['parent_id' => $parentId]);
        $positions = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (! in_array('left', $positions)) {
            return 'left';
        }
        if (! in_array('right', $positions)) {
            return 'right';
        }

        return null;
    }

    public function countUsers(): int
    {
        $sql = 'SELECT COUNT(*) FROM users';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}
