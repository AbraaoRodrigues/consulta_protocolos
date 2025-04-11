<?php
try {
    $caminhoBanco = "C:/xampp/htdocs/consulta_protocolos/banco/protocolo.fb2";

    $conn = new PDO(
        "firebird:dbname=localhost:$caminhoBanco;charset=WIN1252",
        "SYSDBA",
        "masterkey"
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Erro na conexão com o banco Firebird: " . $e->getMessage());
}
?>
