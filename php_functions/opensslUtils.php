<?php 

function encrypt_password($plaintext_password, $passphrase) {
    $iv = openssl_random_pseudo_bytes(16);
    $encrypted_password = base64_encode(openssl_encrypt(
        $plaintext_password,
        'aes-256-cbc',
        $passphrase,
        OPENSSL_RAW_DATA,
        $iv
    ));
    return $encrypted_password . ":" . base64_encode($iv);
}

function decrypt_password($encrypted_password, $passphrase) {
    $parts = explode(":", $encrypted_password);
    $encrypted = $parts[0];
    $iv = base64_decode($parts[1]);
    $decrypted_password = openssl_decrypt(
        base64_decode($encrypted),
        'aes-256-cbc',
        $passphrase,
        OPENSSL_RAW_DATA,
        $iv
    );
    return $decrypted_password;
}
