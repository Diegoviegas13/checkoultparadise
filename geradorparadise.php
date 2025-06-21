<?php
// =================== CONFIGURAÇÕES ====================
$offer_hash = 'wcll6mpm55'; // go.paradisepagbr.com/(seucodigodaoferta)
$produto_hash = 'vejtlb5bvs'; // encontrado na aba do seu produto
$access_token = 'OWssbYMXZOUvkFe39U1EFQ2FHQwI6duLYCAXuy8nyPDP5eRWQLk1KHmkLZdD';
$api_url = 'https://api.paradisepagbr.com/api/public/v1/transactions?api_token=' . $access_token;
$postback_url = '/webhook/pix_webhook.php';

$data = json_decode(file_get_contents("php://input"), true);

$nome = $data['nome'] ?? 'Lucas Souza';
$email = $data['email'] ?? 'lucas@email.com';
$cpf = $data['cpf'] ?? '12345678900';
$telefone = $data['telefone'] ?? '11999999999';
$quantidade = $dados['quantidade'] ?? 990;
$utm = $data['utm'] ?? [];

$carga útil = [
    "quantidade" => $quantidade,
    "offer_hash" => $offer_hash,
    "método_de_pagamento" => "pix",
    "cliente" => [
        "nome" => $nome,
        "e-mail" => $e-mail,
        "número_de_telefone" => $telefone,
        "documento" => $cpf,
        "street_name" => "Rua Exemplo",
        "número" => "123",
        "complemento" => "Ap 101",
        "bairro" => "Centro",
        "cidade" => "São Paulo",
        "estado" => "SP",
        "CEP" => "01001000"
    ],
    "carrinho" => [[
        "product_hash" => $product_hash,
        "title" => "Produto Teste",
        "preço" => $valor,
        "quantidade" => 1,
        "tipo_de_operação" => 1,
        "tangível" => falso
    ]],
    "parcelas" => 1,
    "expire_in_days" => 1,
    "postback_url" => $postback_url,
    "rastreamento" => [
        "utm_source" => $utm['utm_source'] ?? '',
        "utm_medium" => $utm['utm_medium'] ?? '',
        "utm_campaign"=> $utm['utm_campaign']?? '',
        "utm_term" => $utm['utm_term'] ?? '',
        "utm_content" => $utm['utm_content'] ?? ''
    ]
];

// ========== ENVIA TRANSAÇÃO ==========
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, verdadeiro);
curl_setopt($ch, CURLOPT_POST, verdadeiro);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Tipo de conteúdo: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$resposta = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// ========== VERIFICA RESPOSTA ==========
se ($http_code === 200 || $http_code === 201) {
    $res = json_decode($response, true);
    $transactionId = $res['transação'] ?? nulo;
    $qrCodeText = $res['pix']['pix_qr_code'] ?? '';

    // Tenta encontrar o hash na lista
    $transaction_hash = nulo;

    se ($transactionId) {
        $list_url = 'https://api.paradisepagbr.com/api/public/v1/transactions?api_token=' . $access_token;
        $ch_list = curl_init($list_url);
        curl_setopt($ch_list, CURLOPT_RETURNTRANSFER, verdadeiro);
        $list_response = curl_exec($ch_list);
        curl_close($ch_list);

        $transações = json_decode($list_response, true);
        se (isset($transactions['dados']) && is_array($transactions['dados'])) {
            foreach ($transactions['dados'] como $tx) {
                se (isset($tx['transação']) && $tx['transação'] === $transactionId) {
                    $transaction_hash = $tx['hash'] ?? nulo;
                    quebrar;
                }
            }
        }
    }

    eco json_encode([
        'sucesso' => verdadeiro,
        'pix_data' => [
            'qrCode' => 'https://quickchart.io/qr?text=' . urlencode($qrCodeText),
            'qrCodeText' => $qrCodeText
        ],
        'transaction_id' => $transactionId,
        'transaction_hash' => $transaction_hash,
        'quantidade' => $quantidade
    ]);
} outro {
    eco json_encode([
        'sucesso' => falso,
        'error' => "Erro ao gerar pagamento. HTTP: $http_code",
        'depurar' => json_decode($resposta, verdadeiro)
    ]);
}
