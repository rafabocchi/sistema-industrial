<?php
require_once 'config.php';
verificarLogin();

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produto_id = (int)($_POST['produto'] ?? 0);
    $tipo = (int)($_POST['tipo'] ?? 0); // 1 = Entrada, 2 = Saída (Consumo)
    $quantidade = (int)($_POST['quantidade'] ?? 0);
    $data_operacao = $_POST['data_operacao'] ?? date('Y-m-d');

    $stmt = $conn->prepare("SELECT estoque_atual FROM produtos WHERE id = ?");
    $stmt->execute([$produto_id]);
    $item = $stmt->fetch();

    if (!$item || $quantidade <= 0 || !in_array($tipo, [1, 2])) {
        $erro = 'Preencha todos os campos corretamente.';
    } else {
        $saldo_anterior = (int)$item['estoque_atual'];
        $novo = ($tipo === 1) ? ($saldo_anterior + $quantidade) : ($saldo_anterior - $quantidade);

        if ($tipo === 2 && $novo < 0) {
            $erro = 'Estoque insuficiente para realizar esta saída.';
        } else {
            $conn->beginTransaction();

            try {
                // 1. Atualiza estoque no produto
                $stmtUp = $conn->prepare("UPDATE produtos SET estoque_atual = ? WHERE id = ?");
                $stmtUp->execute([$novo, $produto_id]);

                // 2. Registra no histórico de movimentações
                $stmtMov = $conn->prepare("
                    INSERT INTO movimentacoes (tipo, data, quantidade, saldo_anterior, usuarios_id, produtos_id)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmtMov->execute([$tipo, $data_operacao, $quantidade, $saldo_anterior, $_SESSION['usuario_id'], $produto_id]);

                $conn->commit();
                $sucesso = 'Movimentação registrada com sucesso!';
            } catch (Exception $e) {
                $conn->rollBack();
                $erro = 'Erro ao registrar movimentação: ' . $e->getMessage();
            }
        }
    }
}

$produtos = $conn->query("
    SELECT id, codigo, nome, estoque_atual, estoque_minimo
    FROM produtos
    WHERE ativo = 1
    ORDER BY nome
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Movimentações de Estoque</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
<main class="container">
    <h1>Gestão de Movimentações de Estoque</h1>
    <p><a href="index.php">Voltar ao Menu Principal</a></p>

    <?php if ($sucesso): ?><div class="alert"><?= e($sucesso) ?></div><?php endif; ?>
    <?php if ($erro): ?><div class="alert"><?= e($erro) ?></div><?php endif; ?>

    <section class="panel">
        <h2>Registrar Movimentação</h2>
        <form method="post" style="max-width: 300px;">
            <label>Produtos</label>
            <select name="produto" required style="width: 100%;">
                <option value="">Selecione um produto...</option>
                <?php foreach ($produtos as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= e($p['nome']) ?></option>
                <?php endforeach; ?>
            </select>

            <label style="margin-top: 10px; display:block;">Tipo de Movimentação:</label>
            <select name="tipo" required style="width: 100%;">
                <option value="1">1 - Entrada</option>
                <option value="2">2 - Saída / Consumo</option>
            </select>

            <label style="margin-top: 10px; display:block;">Quantidade:</label>
            <input type="number" name="quantidade" min="1" required style="width: 100%;">

            <label style="margin-top: 10px; display:block;">Data da Operação:</label>
            <input type="date" name="data_operacao" value="<?= date('Y-m-d') ?>" required style="width: 100%;">

            <button type="submit" style="margin-top: 15px;">Confirmar Movimentação</button>
        </form>
    </section>

    <section class="panel" style="margin-top: 20px;">
        <h2>Saldos de Estoque</h2>
        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Estoque Atual</th>
                    <th>Estoque Mínimo</th>
                    <th>Situação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $p): ?>
                    <?php $critico = $p['estoque_atual'] <= $p['estoque_minimo']; ?>
                    <tr>
                        <td><?= e($p['nome']) ?></td>
                        <td><?= (int)$p['estoque_atual'] ?></td>
                        <td><?= (int)$p['estoque_minimo'] ?></td>
                        <td>
                            <?php if ($critico): ?>
                                <strong style="color: red;">ESTOQUE CRÍTICO</strong>
                            <?php else: ?>
                                <strong>OK</strong>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>