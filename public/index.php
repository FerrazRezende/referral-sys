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
        $allUsers = $userRepository->findAll();
        $referrerId = count($allUsers) > 0 ? 1 : null;
        
        $service->addUser(
            $_POST['name'] ?? 'Usuário novo',
            (int) ($_POST['points'] ?? 0),
            $referrerId
        );
        $viewData['message'] = "Usuário adicionado com sucesso!";
    } catch (Exception $e) {
        $viewData['error'] = $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_points'])) {
    try {
        $service->updateUserPoints(
            (int) $_POST['user_id'],
            (int) $_POST['new_points']
        );
        $viewData['message'] = "Pontos atualizados com sucesso!";
    } catch (Exception $e) {
        $viewData['error'] = $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_system'])) {
    try {
        $service->resetSystem();
        $viewData['message'] = "Sistema resetado com sucesso!";
    } catch (Exception $e) {
        $viewData['error'] = $e->getMessage();
    }
}

try {
    $viewData['allUsers'] = $userRepository->findAll();
    
    if (count($viewData['allUsers']) > 0) {
        $viewData['scoreboard'] = $service->getScoreboard(1);
        
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
    } else {
        $viewData['tree'] = null;
    }
} catch(Exception $e) {
    $viewData['error'] = "Erro ao carregar os dados:" . $e->getMessage();
}

echo $twig->render('index.twig', $viewData);
