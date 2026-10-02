<?php
require_once 'config.php';
verificarLogin();

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}

$totalProdutos = (int)$conn->query(
    "SELECT COUNT(*) FROM produtos WHERE ativo = 1"
)->fetchColumn();

$estoqueBaixo = (int)$conn->query(
    "SELECT COUNT(*) FROM produtos 
     WHERE ativo = 1 
     AND estoque_atual <= estoque_minimo"
)->fetchColumn();

$totalMovimentacoes = (int)$conn->query(
    "SELECT COUNT(*) FROM movimentacoes"
)->fetchColumn();

/*
 * O banco atual não possui a coluna custo_unitario.
 * Por isso, o valor é iniciado em zero para evitar erro.
 */
$valorEstoque = 0;

$baixos = $conn->query(
    "SELECT nome, codigo, estoque_atual, estoque_minimo 
     FROM produtos 
     WHERE ativo = 1 
     AND estoque_atual <= estoque_minimo 
     ORDER BY estoque_atual ASC, nome ASC 
     LIMIT 5"
)->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Painel | Indústria Estoque</title>

    <link rel="stylesheet" href="estilo.css">
</head>

<body>

<header class="topbar">

    <div>
        <div class="brand">
            Indústria Estoque
        </div>

        <span class="topbar-sub">
            Controle de almoxarifado
        </span>
    </div>

    <div class="user-area">

        <span>
            Olá,
            <strong>
                <?= e($_SESSION['usuario_nome']) ?>
            </strong>
        </span>

        <a
            class="btn btn-outline"
            href="index.php?logout=1"
        >
            Sair
        </a>

    </div>

</header>


<main class="container">

    <div class="page-title">

        <div>

            <h1>
                Painel principal
            </h1>

            <p>
                Visão geral do estoque industrial.
            </p>

        </div>

    </div>


    <!-- CARDS DO PAINEL -->

    <section class="cards">

        <div class="stat-card">

            <span class="stat-label">
                Insumos ativos
            </span>

            <strong>
                <?= $totalProdutos ?>
            </strong>

            <span class="stat-info">
                cadastrados
            </span>

        </div>


        <div class="stat-card <?= $estoqueBaixo > 0 ? 'stat-warning' : '' ?>">

            <span class="stat-label">
                Estoque em alerta
            </span>

            <strong>
                <?= $estoqueBaixo ?>
            </strong>

            <span class="stat-info">
                no mínimo ou abaixo
            </span>

        </div>


        <div class="stat-card">

            <span class="stat-label">
                Movimentações
            </span>

            <strong>
                <?= $totalMovimentacoes ?>
            </strong>

            <span class="stat-info">
                registradas
            </span>

        </div>


        <div class="stat-card">

            <span class="stat-label">
                Valor em estoque
            </span>

            <strong>
                R$ <?= number_format($valorEstoque, 2, ',', '.') ?>
            </strong>

            <span class="stat-info">
                custo não informado
            </span>

        </div>

    </section>


    <!-- MENU PRINCIPAL -->

    <section class="menu-grid">

        <a
            class="menu-card"
            href="produtos.php"
        >

            <span class="menu-icon">
                ▦
            </span>

            <h2>
                Cadastro de Insumos
            </h2>

            <p>
                Cadastrar, pesquisar, editar e excluir
                matérias-primas e componentes.
            </p>

        </a>


        <a
            class="menu-card"
            href="estoque.php"
        >

            <span class="menu-icon">
                ↕
            </span>

            <h2>
                Gestão de Estoque
            </h2>

            <p>
                Registrar entradas e saídas,
                consultar saldos e acompanhar alertas.
            </p>

        </a>

    </section>


    <!-- ALERTAS DE ESTOQUE -->

    <?php if ($baixos): ?>

        <section class="panel">

            <div class="panel-header">

                <div>

                    <h2>
                        Alertas de estoque
                    </h2>

                    <p>
                        Insumos que precisam de atenção.
                    </p>

                </div>


                <a
                    href="estoque.php"
                    class="btn btn-secondary"
                >
                    Ver estoque
                </a>

            </div>


            <div class="alert-list">

                <?php foreach ($baixos as $item): ?>

                    <div class="stock-alert">

                        <div>

                            <strong>
                                <?= e($item['nome']) ?>
                            </strong>

                            <span>
                                Código:
                                <?= e($item['codigo']) ?>
                            </span>

                        </div>


                        <div class="stock-values">

                            <span>
                                Atual:
                                <b>
                                    <?= (int)$item['estoque_atual'] ?>
                                </b>
                            </span>

                            <span>
                                Mínimo:
                                <b>
                                    <?= (int)$item['estoque_minimo'] ?>
                                </b>
                            </span>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        </section>

    <?php endif; ?>


</main>

</body>
</html>