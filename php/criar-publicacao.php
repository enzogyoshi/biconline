<?php
$texto_publicacao = isset($_POST["texto"]) ? $_POST["texto"] : "";
$arquivo_midia = isset($_FILES["midia"]) ? $_FILES["midia"] : "";
$limite_tamanho_foto = 5242880;

//Area de arquivos/fotos
if ($arquivo_midia["error"] == 0) {
    if (
        $arquivo_midia["type"] == "image/png" ||
        $arquivo_midia["type"] == "image/jpeg" ||
        $arquivo_midia["type"] == "image/gif" ||
        $arquivo_midia["type"] == "image/webp"
    ) {
        if ($arquivo_midia["size"] <= $limite_tamanho_foto) {
            move_uploaded_file(
                $arquivo_midia["tmp_name"],
                "../img/" . $arquivo_midia["name"]
            );
        } else {
            echo "tamanho de arquivo maior que 5MB";
        }
    } else {
        echo "tipo de arquivo não permitido";
    }
} elseif ($arquivo_midia["error"] == 4) {
    echo "nenhum arquivo selecionado";
} else {
    echo "erro";
}
?>