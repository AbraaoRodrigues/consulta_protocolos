<?php
include 'conexao.php';

if (!isset($_GET['id'])) {
    echo "Protocolo não especificado.";
    exit;
}

$id = (int) $_GET['id'];

// Consulta principal
$sql = "SELECT 
    P.PRO_I_ID, 
    P.PRO_S_ANO, 
    P.PRO_I_NUM, 
    P.PRO_D_DTA, 
    P.PRO_S_SIT, 
    P.PRO_A_INT,  
    P.PRO_M_RES,
    A.ASS_A_DES AS assunto,
    (SELECT FIRST 1 INT_A_NOM FROM TRIINT WHERE INT_I_COD = P.PRO_A_INT) AS INTERESSADO
FROM PRTPROTO P
LEFT JOIN PRTASSUN A ON TRIM(P.PRO_S_ASS) = TRIM(A.ASS_S_COD)
WHERE P.PRO_I_ID = :id";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$protocolo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$protocolo) {
    echo "Protocolo não encontrado.";
    exit;
}

$assuntoBruto = $protocolo['ASSUNTO'] ?? '';
$assunto = $assuntoBruto ? iconv('ISO-8859-1', 'utf-8', $assuntoBruto) : '[Sem descrição]';

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Detalhes do Protocolo</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Detalhes do Protocolo #<?= $protocolo['PRO_I_NUM'] ?>/<?= $protocolo['PRO_S_ANO'] ?></h1>

<p><strong>Data:</strong> <?= $protocolo['PRO_D_DTA'] ?></p>
<p><strong>Interessado:</strong> <?= $protocolo['INTERESSADO'] ?> (<?= $protocolo['PRO_A_INT'] ?>)</p>
<p><strong>Situação:</strong> <?= $protocolo['PRO_S_SIT'] ?></p>
<p><strong>Assunto:</strong> <?= $assunto ?></p>
<p><strong>Resumo:</strong><br><?= nl2br(iconv('windows-1252', 'utf-8', $protocolo['PRO_M_RES'])) ?></p>


<h2>Tramitações</h2>

<?php
// Busca tramitações na tabela PRTREMESSA
$sqlRemessa = "
    SELECT 
        R.REM_D_DEM AS DATA_ENVIO,
        R.REM_D_DREC AS DATA_RECEBIMENTO,
        O.LOC_A_DES AS ORIGEM,
        D.LOC_A_DES AS DESTINO,
        U.UCUSERNAME AS EMISSOR_NOME
    FROM PRTREMESSA R
    LEFT JOIN PRTLOCAL O ON R.REM_A_OND = O.LOC_S_COD
    LEFT JOIN PRTLOCAL D ON R.REM_A_PAR = D.LOC_S_COD
    LEFT JOIN ASP_TABUSERS U ON R.RES_S_CODO = U.UCIDUSER
    WHERE R.REM_I_COD = :numero AND R.REM_I_ANO = :ano
    ORDER BY R.REM_D_DEM
";

$stmtRemessa = $conn->prepare($sqlRemessa);
$stmtRemessa->bindParam(':numero', $protocolo['PRO_I_NUM'], PDO::PARAM_INT);
$stmtRemessa->bindParam(':ano', $protocolo['PRO_S_ANO'], PDO::PARAM_STR);
$stmtRemessa->execute();

$remessas = [];
$stmtRemessa->execute();

while ($row = $stmtRemessa->fetch(PDO::FETCH_ASSOC)) {
    try {
        // Força leitura e ignora registros problemáticos
        $remessas[] = $row;
    } catch (Exception $e) {
        // Apenas ignora o registro com erro de charset
        continue;
    }
}

if ($remessas) {
    echo "<table>
        <tr><th>Data Envio</th><th>Origem</th><th>Destino</th><th>Emissor</th><th>Receptor</th><th>Recebido</th></tr>";

        foreach ($remessas as $r) {
            echo "<tr>
                <td>{$r['DATA_ENVIO']}</td>
                <td>" . iconv('windows-1252', 'utf-8', $r['ORIGEM']) . "</td>
                <td>" . iconv('windows-1252', 'utf-8', $r['DESTINO']) . "</td>
                <td>" . iconv('windows-1252', 'utf-8', $r['EMISSOR_NOME']) . "</td>
                <td>" . ($r['DATA_RECEBIMENTO'] ? '✔️ Sim' : '❌ Não') . "</td>
            </tr>";
        }             

    echo "</table>";
} else {
    echo "<p>Sem tramitações registradas.</p>";
}
?>

</body>
</html>
