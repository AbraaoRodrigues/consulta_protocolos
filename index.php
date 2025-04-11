<?php include 'conexao.php'; ?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Consulta de Protocolos</title>
  <link rel="stylesheet" href="style.css">
  <script src="scripts.js" defer></script>
</head>
<body>

<h1>Consulta de Protocolos</h1>

<div class="form-container">
<form method="POST">
  <div class="form-group">
    <label for="numero">Nº Protocolo:</label>
    <input type="text" name="numero" id="numero" value="<?= $_POST['numero'] ?? '' ?>">
  </div>

  <div class="form-group">
    <label for="ano">Ano:</label>
    <input type="text" name="ano" id="ano" value="<?= $_POST['ano'] ?? '' ?>">
  </div>

  <div class="form-group">
    <label for="interessado">Interessado:</label>
    <input type="text" name="interessado" id="interessado" value="<?= $_POST['interessado'] ?? '' ?>">
  </div>

  <div class="form-group">
    <label for="assunto">Assunto:</label>
    <input type="text" name="assunto" id="assunto" value="<?= $_POST['assunto'] ?? '' ?>">
  </div>

  <div class="form-group">
    <label for="resumo">Resumo:</label>
    <input type="text" name="resumo" id="resumo" value="<?= $_POST['resumo'] ?? '' ?>">
  </div>

  <div class="form-group">
    <label for="limite">Exibir:</label>
    <select name="limite" id="limite">
      <option value="20" <?= ($_POST['limite'] ?? '') == 20 ? 'selected' : '' ?>>20</option>
      <option value="50" <?= ($_POST['limite'] ?? '') == 50 ? 'selected' : '' ?>>50</option>
      <option value="100" <?= ($_POST['limite'] ?? '') == 100 ? 'selected' : '' ?>>100</option>
    </select>
  </div>

  <div class="form-group full-width button-group">
  <button type="submit">Buscar</button>
  <button type="button" onclick="window.location.href='index.php'" style="background:#888;">Limpar Filtros</button>
</div>
</form>
</div>

<div id="loader" class="loader">🔄 Carregando resultados, por favor aguarde...</div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $numero = $_POST['numero'] ?? null;
  $ano = $_POST['ano'] ?? null;
  $interessado = $_POST['interessado'] ?? null;
  $assunto = $_POST['assunto'] ?? null;
  $resumo = $_POST['resumo'] ?? null;
  $limite = (int) ($_POST['limite'] ?? 20);
  $pagina = (int) ($_POST['pagina'] ?? 1);
  $offset = ($pagina - 1) * $limite;

  $sql = "SELECT FIRST $limite SKIP $offset
              P.PRO_I_ID, 
              P.PRO_S_ANO, 
              P.PRO_I_NUM, 
              P.PRO_M_RES, 
              I.INT_A_NOM AS interessado, 
              A.ASS_A_DES AS assunto
          FROM PRTPROTO P
          LEFT JOIN TRIINT I ON P.PRO_A_INT = I.INT_I_COD
          LEFT JOIN PRTASSUN A ON TRIM(P.PRO_S_ASS) = TRIM(A.ASS_S_COD)
          WHERE 1=1";

  $countSql = "SELECT COUNT(*) FROM PRTPROTO P
                LEFT JOIN TRIINT I ON P.PRO_A_INT = I.INT_I_COD
                LEFT JOIN PRTASSUN A ON TRIM(P.PRO_S_ASS) = TRIM(A.ASS_S_COD)
                WHERE 1=1";

  $filtros = "";
  if ($numero) $filtros .= " AND P.PRO_I_NUM = " . (int)$numero;
  if ($ano) $filtros .= " AND P.PRO_S_ANO = '" . $ano . "'";
  if ($interessado) $filtros .= " AND I.INT_A_NOM STARTING WITH '" . strtoupper(str_replace("'", "''", $interessado)) . "'";
  if ($assunto) $filtros .= " AND A.ASS_A_DES STARTING WITH '" . strtoupper(str_replace("'", "''", $assunto)) . "'";
  if ($resumo) $filtros .= " AND P.PRO_M_RES CONTAINING '" . str_replace("'", "''", $resumo) . "'";

  $sql .= $filtros . " ORDER BY P.PRO_D_DTA DESC";
  $countSql .= $filtros;

  try {
    $stmt = $conn->query($sql);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total = $conn->query($countSql)->fetchColumn();
  } catch (PDOException $e) {
    echo "<p>Erro na consulta: " . $e->getMessage() . "</p>";
    exit;
  }

  if ($resultados) {
    echo "<div style='margin: 0 50px;'>";
    echo "<table>
            <tr>
              <th>Nº Protocolo</th>
              <th>Ano</th>
              <th>Interessado</th>
              <th>Assunto</th>
              <th>Ações</th>
            </tr>";
    foreach ($resultados as $row) {
      $nome = $row['INTERESSADO'] ?? iconv('utf-8', 'windows-1252', '[Interessado não encontrado]');
      $nome = iconv('windows-1252', 'utf-8', $nome);
      $assunto = iconv('windows-1252', 'utf-8', $row['ASSUNTO'] ?? '[Sem assunto]');
      echo "<tr>
              <td>{$row['PRO_I_NUM']}</td>
              <td>{$row['PRO_S_ANO']}</td>
              <td>{$nome}</td>
              <td>{$assunto}</td>
              <td><button class='btn-detalhes' onclick=\"abrirModal({$row['PRO_I_ID']})\">Ver Detalhes</button></td>
            </tr>";
    }
    echo "</table>";

    $totalPaginas = ceil($total / $limite);
    if ($totalPaginas > 1) {
      echo "<form method='POST'><div class='paginacao'>";
      foreach ($_POST as $k => $v) {
        if ($k != 'pagina') {
          echo "<input type='hidden' name='$k' value='" . htmlspecialchars($v) . "'>";
        }
      }
      if ($pagina > 1) {
        echo "<button name='pagina' value='" . ($pagina - 1) . "'>← Anterior</button> ";
      }
      echo " Página $pagina de $totalPaginas ";
      if ($pagina < $totalPaginas) {
        echo " <button name='pagina' value='" . ($pagina + 1) . "'>Próxima →</button>";
      }
      echo "</div></form>";
    }
    echo "</div>";
  } else {
    echo "<p style='text-align:center;'>Nenhum protocolo encontrado com os filtros aplicados.</p>";
  }
}
?>

<!-- Modal -->
<div id="detalhesModal" class="modal">
  <div class="modal-box">
  <button onclick="fecharModal()" class="fechar-modal">&times;</button>
    <div id="modalContent"></div>
  </div>
</div>

</body>
</html>
