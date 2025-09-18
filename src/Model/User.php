<?php

namespace ReferralSystem\Model;

class User implements UserComponent
{
    private int $id;

    private string $name;

    private int $points;

    private ?UserComponent $leftChild = null;

    private ?UserComponent $rightChild = null;

    public function __construct(int $id, string $name, int $points)
    {
        $this->id = $id;
        $this->name = $name;
        $this->points = $points;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLeftChild(): ?UserComponent
    {
        return $this->leftChild;
    }

    public function getRightChild(): ?UserComponent
    {
        return $this->rightChild;
    }

    public function setLeftChild(UserComponent $user): void
    {
        $this->leftChild = $user;
    }

    public function setRightChild(UserComponent $user): void
    {
        $this->rightChild = $user;
    }

    public function calculatePoints(): int
    {
        $totalPoints = $this->points;

        if ($this->leftChild !== null) {
            $totalPoints += $this->leftChild->calculatePoints();
        }

        if ($this->rightChild !== null) {
            $totalPoints += $this->rightChild->calculatePoints();
        }

        return $totalPoints;
    }
}
