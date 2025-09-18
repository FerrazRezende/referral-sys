<?php

require_once __DIR__.'/../src/bootstrap.php';

use ReferralSystem\Service\ReferralService;
use ReferralSystem\Repository\UserRepository;

$referralService = new ReferralService();
$userRepository = new UserRepository();

$viewData = [
    'message' => '',
    'error' => '',
    'scoreboard' => ['user_name' => 'N/A', 'left_points' => 0, 'right_points' => 0],
    'allUsers' => []
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    try {
        $viewData['message'] = "Usuário adicionado com sucesso!";
        $referralService->addUser(
            $_POST['name'] ?? 'Novo Usuário',
            (int) ($_POST['points'] ?? 0),
            (int) ($_POST['referrer_id'] ?? 0)
        );
    } catch (Exception $e) {
        $viewData['error'] = $e->getMessage();
    }
}

try {
    $viewData['scoreboard'] = $referralService->getScoreboard(1);
    $viewData['allUsers'] = $userRepository->findAll();
} catch(Exception $e) {
    $viewData['error'] = "Erro ao carregar dados: " . $e->getMessage();
}

echo $twig->render('index.twig', $viewData);