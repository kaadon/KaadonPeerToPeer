<?php
/**
 * 端到端加密 PHP 类
 * 使用 X25519 密钥交换和 AES-GCM 加密
 * 
 * 使用方法：
 * $e2e = new E2EEncryption();
 * $encrypted = $e2e->encrypt($remotePublicKey, $message);
 * $decrypted = $e2e->decrypt($remotePublicKey, $iv, $ciphertext);
 */
namespace Kaadon\PeerToPeer;

class E2EEncryption {
    private $privateKey;
    private $publicKey;

    /**
     * 构造函数
     * @param string|null $privateKey Base64编码的私钥，如果为null则生成新密钥对
     * @throws \Kaadon\PeerToPeer\PeerToPeerException
     * @throws \Random\RandomException
     * @throws \SodiumException
     */
    public function __construct(string $privateKey = null) {
        if (!extension_loaded('sodium')) {
            throw new PeerToPeerException('需要安装 sodium 扩展：pecl install libsodium');
        }
        
        if ($privateKey) {
            $this->privateKey = base64_decode($privateKey);
            $this->publicKey = $this->derivePublicKey($this->privateKey);
        } else {
            $this->generateKeyPair();
        }
    }

    /**
     * 生成新的密钥对
     * @throws \Random\RandomException|\SodiumException
     */
    private function generateKeyPair() {
        $this->privateKey = random_bytes(32);
        $this->publicKey = $this->derivePublicKey($this->privateKey);
    }

    /**
     * 从私钥派生公钥
     * @throws \SodiumException
     */
    private function derivePublicKey($privateKey): string
    {
//        return sodium_crypto_scalarmult_base($privateKey);
        return sodium_crypto_box_publickey_from_secretkey($privateKey);
    }
    
    /**
     * 获取公钥（Base64编码）
     */
    public function getPublicKey(): string
    {
        return base64_encode($this->publicKey);
    }
    
    /**
     * 获取私钥（Base64编码）
     */
    public function getPrivateKey(): string
    {
        return base64_encode($this->privateKey);
    }

    /**
     * 使用远程公钥加密消息
     * @param string $remotePublicKeyB64 Base64编码的远程公钥
     * @param string $message 要加密的消息
     * @return array ['iv' => string, 'ciphertext' => string]
     * @throws \Random\RandomException
     * @throws \SodiumException
     */
    public function encrypt(string $remotePublicKeyB64, string $message): array
    {
        $remotePublicKey = base64_decode($remotePublicKeyB64);
        $sharedSecret = sodium_crypto_scalarmult($this->privateKey, $remotePublicKey);
        $iv = random_bytes(12);
        
        $ciphertext = sodium_crypto_aead_aes256gcm_encrypt(
            $message,
            '', // 附加数据
            $iv,
            $sharedSecret
        );
        
        return [
            'iv' => base64_encode($iv),
            'ciphertext' => base64_encode($ciphertext)
        ];
    }

    /**
     * 使用远程公钥解密消息
     * @param string $remotePublicKeyB64 Base64编码的远程公钥
     * @param string $ivB64 Base64编码的IV
     * @param string $ciphertextB64 Base64编码的密文
     * @return string 解密后的消息
     * @throws \SodiumException
     * @throws \Kaadon\PeerToPeer\PeerToPeerException
     */
    public function decrypt(string $remotePublicKeyB64, string $ivB64, string $ciphertextB64): string
    {
        $remotePublicKey = base64_decode($remotePublicKeyB64);
        $iv = base64_decode($ivB64);
        $ciphertext = base64_decode($ciphertextB64);
        
        $sharedSecret = sodium_crypto_scalarmult($this->privateKey, $remotePublicKey);
        
        $plaintext = sodium_crypto_aead_aes256gcm_decrypt(
            $ciphertext,
            '', // 附加数据
            $iv,
            $sharedSecret
        );
        
        if ($plaintext === false) {
            throw new PeerToPeerException('解密失败');
        }
        
        return $plaintext;
    }
}

