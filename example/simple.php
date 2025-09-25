<?php
// 引入端到端加密类

require_once '../vendor/autoload.php';

use Kaadon\PeerToPeer\E2EEncryption;
use Kaadon\PeerToPeer\LocalKeyStore;

// 简单使用示例
try {
    // 创建两个用户
    $alice = new E2EEncryption('LYNrtUvRa8WiNpWHDuez3uWOQlJvvgHyI281mjQ+wB4=');

    echo "=== 端到端加密示例 ===\n";
    echo "Alice 公钥: " . $alice->getPublicKey() . "\n";
    echo "Alice 私钥: " . $alice->getPrivateKey() . "\n\n";
    
    // 创建本地密钥存储
    $keyStore = new LocalKeyStore();
    
    // 保存公钥到本地
    $keyStore->savePublicKey('alice', $alice->getPublicKey());

    // Alice 发送消息给 Bob
    $message = "你好 Bob！";
    echo "Alice 发送消息: $message\n";
    
    $bobPubKey = 'OzaWJHe6AT8v846t3pf+9ecty0FIsQnqNqw9Gik4AQ8=';
    $encrypted = $alice->encrypt($bobPubKey, $message);
    
    echo "加密结果:\n";
    echo "IV: " . $encrypted['iv'] . "\n";
    echo "密文: " . $encrypted['ciphertext'] . "\n\n";
    
    $data = $alice->decrypt( $bobPubKey,'/6h+Pe75QLLQYit6','W7VCaNi1Bny4MpkZCG4c5JvmGQFYg1McWL5FZsL5uoQcLH96PvhJy2Ef51D24drMHPlNXmp8E/bEPTaSkncoduI1vhePNUA6ug+ySnRTLgQE3ohi3V3Vsg==');
    var_dump($data);

} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}
?> 