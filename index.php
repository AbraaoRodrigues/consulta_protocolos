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
<form method="GET">
  <div class="form-group">
    <label for="numero">Nº Protocolo:</label>
    <input type="text" name="numero" id="numero" value="<?= $_GET['numero'] ?? '' ?>">
  </div>

  <div class="form-group">
    <label for="ano">Ano:</label>
    <input type="text" name="ano" id="ano" value="<?= $_GET['ano'] ?? '' ?>">
  </div>

  <div class="form-group">
    <label for="interessado">Interessado:</label>
    <input type="text" name="interessado" id="interessado" value="<?= $_GET['interessado'] ?? '' ?>">
  </div>

  <div class="form-group">
    <label for="assunto">Assunto:</label>
    <input type="text" name="assunto" id="assunto" value="<?= $_GET['assunto'] ?? '' ?>">
  </div>

  <div class="form-group">
    <label for="resumo">Resumo:</label>
    <input type="text" name="resumo" id="resumo" value="<?= $_GET['resumo'] ?? '' ?>">
  </div>

  <div class="form-group">
    <label for="limite">Exibir:</label>
    <select name="limite" id="limite">
      <option value="20" <?= ($_GET['limite'] ?? '') == 20 ? 'selected' : '' ?>>20</option>
      <option value="50" <?= ($_GET['limite'] ?? '') == 50 ? 'selected' : '' ?>>50</option>
      <option value="100" <?= ($_GET['limite'] ?? '') == 100 ? 'selected' : '' ?>>100</option>
    </select>
  </div>

  <div class="form-group full-width">
    <button type="submit">Buscar</button>
  </div>
</form>
</div>

<?php
// Exibir resultados só se houver filtro
if (!empty($_GET)) {
  $numero = $_GET['numero'] ?? null;
  $ano = $_GET['ano'] ?? null;
  $interessado = $_GET['interessado'] ?? null;
  $assunto = $_GET['assunto'] ?? null;
  $resumo = $_GET['resumo'] ?? null;
  $limite = (int) ($_GET['limite'] ?? 20);

  $sql = "SELECT FIRST $limite 
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

  if ($numero) $sql .= " AND P.PRO_I_NUM = " . (int)$numero;
  if ($ano) $sql .= " AND P.PRO_S_ANO = '" . $ano . "'";
  if ($interessado) $sql .= " AND I.INT_A_NOM CONTAINING '" . $interessado . "'";
  if ($assunto) $sql .= " AND A.ASS_A_DES CONTAINING '" . $assunto . "'";
  if ($resumo) $sql .= " AND P.PRO_M_RES CONTAINING '" . $resumo . "'";

  $sql .= " ORDER BY P.PRO_D_DTA DESC";

  try {
    $stmt = $conn->query($sql);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
              <td><a href='detalhes.php?id={$row['PRO_I_ID']}'>Ver Detalhes</a></td>
            </tr>";
    }
    echo "</table></div>";
  } else {
    echo "<p style='text-align:center;'>Nenhum protocolo encontrado com os filtros aplicados.</p>";
  }
}
?>

</body>
</html>
