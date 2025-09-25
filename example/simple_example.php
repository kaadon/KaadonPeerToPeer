<?php


require_once '../vendor/autoload.php';

// 引入端到端加密类
use Kaadon\PeerToPeer\E2EEncryption;
use Kaadon\PeerToPeer\LocalKeyStore;
// 简单使用示例
try {
    // 创建两个用户
    $alice = new E2EEncryption();
    $bob = new E2EEncryption();
    
    echo "=== 端到端加密示例 ===\n";
    echo "Alice 公钥: " . $alice->getPublicKey() . "\n";
    echo "Bob 公钥: " . $bob->getPublicKey() . "\n\n";
    
    // 创建本地密钥存储
    $keyStore = new LocalKeyStore();
    
    // 保存公钥到本地
    $keyStore->savePublicKey('alice', $alice->getPublicKey());
    $keyStore->savePublicKey('bob', $bob->getPublicKey());
    
    // Alice 发送消息给 Bob
    $message = "你好 Bob！";
    echo "Alice 发送消息: $message\n";
    
    $bobPubKey = $keyStore->getPublicKey('bob');
    $encrypted = $alice->encrypt($bobPubKey, $message);
    
    echo "加密结果:\n";
    echo "IV: " . $encrypted['iv'] . "\n";
    echo "密文: " . $encrypted['ciphertext'] . "\n\n";
    
    // Bob 解密消息
    $alicePubKey = $keyStore->getPublicKey('alice');
    $decrypted = $bob->decrypt($alicePubKey, $encrypted['iv'], $encrypted['ciphertext']);
    
    echo "Bob 解密结果: $decrypted\n";
    
    // 列出本地存储的公钥
    echo "\n本地存储的公钥: " . implode(', ', $keyStore->listPublicKeys()) . "\n";
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}
?> 