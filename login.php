<?php
require_once 'config.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senhaDigitada = $_POST['senha'] ?? '';

    if ($email === '' || $senhaDigitada === '') {
        $erro = 'Informe o e-mail e a senha.';
    } else {
        try {
            $stmt = $conn->prepare("
                SELECT id, nome, email, senha, ativo
                FROM usuarios
                WHERE email = ?
                LIMIT 1
            ");

            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                $erro = 'E-mail não cadastrado.';
            } elseif ((int)$usuario['ativo'] !== 1) {
                $erro = 'Este usuário está inativo.';
            } elseif ($senhaDigitada !== $usuario['senha']) {
                $erro = 'Senha incorreta.';
            } else {
                session_regenerate_id(true);

                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];

                header('Location: index.php');
                exit;
            }

        } catch (PDOException $e) {
            $erro = 'Não foi possível realizar a autenticação.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Indústria Estoque</title>

    <link rel="stylesheet" href="estilo.css">
</head>

<body class="login-body">

    <main class="login-card">

        <div class="brand-mark">
            IE
        </div>

        <h1>Indústria Estoque</h1>

        <p class="subtitle">
            Sistema de Controle de Estoque Industrial
        </p>

        <?php if ($erro): ?>

            <div class="alert alert-error">
                <?= e($erro) ?>
            </div>

        <?php endif; ?>

        <form method="post" class="form">

            <label for="email">
                E-mail
            </label>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="seu@email.com"
                value="<?= e($_POST['email'] ?? '') ?>"
                required
            >

            <label for="senha">
                Senha
            </label>

            <input
                type="password"
                id="senha"
                name="senha"
                placeholder="Digite sua senha"
                required
            >

            <button
                class="btn btn-primary btn-full"
                type="submit"
            >
                Entrar no sistema
            </button>

        </form>

    </main>

</body>

</html>