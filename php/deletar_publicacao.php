<?php
    $stmt = $conn->prepare("DELETE FROM publicacao WHERE id_usuario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
?>