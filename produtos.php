<?php
require_once 'config.php';
verificarLogin();

$mensagem = '';
$erro = '';

// Processa o cadastro do insumo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? '');
    $nome = trim($_POST['nome'] ?? '');
    $fabricante = trim($_POST['fabricante'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $preco = (float)($_POST['preco'] ?? 0);
    $estoque = (int)($_POST['estoque'] ?? 0);
    $minimo = (int)($_POST['estoque_minimo'] ?? 0);
    $material = trim($_POST['material'] ?? '');

    if ($codigo === '' || $nome === '' || $categoria === '') {
        $erro = 'Preencha os campos obrigatórios (Código, Nome e Categoria).';
    } else {
        try {
            $sql = "INSERT INTO produtos 
                    (codigo, nome, fabricante, categoria, preco, estoque_atual, estoque_minimo, material_fabricacao, ativo)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$codigo, $nome, $fabricante, $categoria, $preco, $estoque, $minimo, $material]);
            $mensagem = 'Insumo cadastrado com sucesso!';
        } catch (PDOException $e) {
            $erro = 'Erro ao cadastrar insumo: ' . $e->getMessage();
        }
    }
}

// Filtro de busca
$busca = trim($_GET['busca'] ?? '');
if ($busca !== '') {
    $stmt = $conn->prepare("
        SELECT * FROM produtos 
        WHERE ativo = 1 AND (codigo LIKE ? OR nome LIKE ? OR categoria LIKE ?)
        ORDER BY nome
    ");
    $stmt->execute(["%$busca%", "%$busca%", "%$busca%"]);
    $produtos = $stmt->fetchAll();
} else {
    $produtos = $conn->query("
        SELECT * FROM produtos 
        WHERE ativo = 1 
        ORDER BY nome
    ")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro e Consulta de Recursos</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
<main class="container">
    <h1>Cadastro e Consulta de Recursos</h1>
    <p><a href="index.php">Voltar</a></p>

    <!-- Form de busca -->
    <form method="get" style="margin-bottom: 20px;">
        <input type="text" name="busca" placeholder="Buscar por código, nome ou..." value="<?= e($busca) ?>">
        <button type="submit">Buscar</button>
    </form>

    <?php if ($mensagem): ?><div class="alert"><?= e($mensagem) ?></div><?php endif; ?>
    <?php if ($erro): ?><div class="alert"><?= e($erro) ?></div><?php endif; ?>

    <!-- Form de cadastro em linha -->
    <h2>Novo Recurso</h2>
    <form method="post" style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-bottom: 30px;">
        <input type="text" name="codigo" placeholder="Código (ex: MAT-001)" required>
        <input type="text" name="nome" placeholder="Nome do Insumo" required>
        <input type="text" name="fabricante" placeholder="Fabricante / Fornecedor">
        <input type="text" name="categoria" placeholder="Categoria (ex: Chapas, Fix...)" required>
        <input type="number" step="0.01" name="preco" placeholder="Preço (R$)">
        <input type="number" name="estoque" placeholder="Estoque Inicial" value="0">
        <input type="number" name="estoque_minimo" placeholder="Estoque Mínimo" value="0">
        <input type="text" name="material" placeholder="Material (ex: Aço Carbono)">
        <button type="submit">Salvar Insumo</button>
    </form>

    <!-- Tabela de recursos -->
    <h2>Lista de Recursos Cadastrados</h2>
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Nome</th>
                <th>Fabricante</th>
                <th>Categoria</th>
                <th>Material</th>
                <th>Preço</th>
                <th>Estoque Atual</th>
                <th>Estoque Mínimo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produtos as $p): ?>
                <tr>
                    <td><?= e($p['codigo']) ?></td>
                    <td><?= e($p['nome']) ?></td>
                    <td><?= e($p['fabricante']) ?></td>
                    <td><?= e($p['categoria']) ?></td>
                    <td><?= e($p['material_fabricacao']) ?></td>
                    <td>R$ <?= number_format((float)$p['preco'], 2, ',', '.') ?></td>
                    <td><?= (int)$p['estoque_atual'] ?></td>
                    <td><?= (int)$p['estoque_minimo'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>
</html>