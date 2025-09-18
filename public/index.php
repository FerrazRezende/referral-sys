<?php

require_once __DIR__.'/../src/bootstrap.php';

use ReferralSystem\Service\UserService;
use ReferralSystem\Repository\UserRepository;

$service = new UserService();
$userRepository = new UserRepository();

$viewData = [
    'message' => '',
    'error' => '',
    'scoreboard' => ['user_name' => 'N/A', 'left_points' => 0, 'right_points' => 0],
    'allUsers' => []
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    try {
        $viewData['message'] = "User added successfully!";
        $service->addUser(
            $_POST['name'] ?? 'New user',
            (int) ($_POST['points'] ?? 0),
            (int) ($_POST['referrer_id'] ?? 0)
        );
    } catch (Exception $e) {
        $viewData['error'] = $e->getMessage();
    }
}

try {
    $viewData['scoreboard'] = $service->getScoreboard(1);
    $viewData['allUsers'] = $userRepository->findAll();

    $root = $userRepository->findTreeById(1);
    $serialize = function ($node) use (&$serialize) {
        if (!$node) { return null; }
        return [
            'name' => $node->getName(),
            'points' => $node->calculatePoints(),
            'left' => $node->getLeftChild() ? $serialize($node->getLeftChild()) : null,
            'right' => $node->getRightChild() ? $serialize($node->getRightChild()) : null,
        ];
    };
    $viewData['tree'] = $root ? $serialize($root) : null;
} catch(Exception $e) {
    $viewData['error'] = "Error loading data: " . $e->getMessage();
}

echo $twig->render('index.twig', $viewData);