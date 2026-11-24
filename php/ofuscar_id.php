<?php
define('FATOR_ID', 87217);
define('SOMA_ID', 6439);

function esconderId(int $id): string {
    $embaralhado = ($id * FATOR_ID) + SOMA_ID;
    return base_convert((string)$embaralhado, 10, 36);
}

function revelarId(string $codigo): ?int {
    if (!ctype_alnum($codigo)) {
        return null;
    }

    $embaralhado = (int)base_convert($codigo, 36, 10);
    $calculo = $embaralhado - SOMA_ID;

    if ($calculo <= 0 || $calculo % FATOR_ID !== 0) {
        return null;
    }

    return intdiv($calculo, FATOR_ID);
}