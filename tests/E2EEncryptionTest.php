<?php

namespace Kaadon\PeerToPeer\Tests;

use Kaadon\PeerToPeer\E2EEncryption;
use Kaadon\PeerToPeer\PeerToPeerException;
use PHPUnit\Framework\TestCase;

class E2EEncryptionTest extends TestCase
{
    public function testConstructorWithoutPrivateKey()
    {
        $e2e = new E2EEncryption();

        $this->assertNotEmpty($e2e->getPublicKey());
        $this->assertNotEmpty($e2e->getPrivateKey());

        // Base64编码的密钥应该不为空且格式正确
        $publicKey = base64_decode($e2e->getPublicKey(), true);
        $privateKey = base64_decode($e2e->getPrivateKey(), true);

        $this->assertNotFalse($publicKey);
        $this->assertNotFalse($privateKey);
        //输出公钥
        $this->assertEquals(32, strlen($publicKey));
        $this->assertEquals(32, strlen($privateKey));
    }

    public function testConstructorWithPrivateKey()
    {
        $originalE2e = new E2EEncryption();
        $privateKey = $originalE2e->getPrivateKey();

        $e2e = new E2EEncryption($privateKey);

        $this->assertEquals($privateKey, $e2e->getPrivateKey());
    }

    public function testEncryptDecryptRoundTrip()
    {
        $alice = new E2EEncryption();
        $bob = new E2EEncryption();

        $message = "Hello, Bob! This is a secret message from Alice.";

        // Alice用Bob的公钥加密消息
        $encrypted = $alice->encrypt($bob->getPublicKey(), $message);

        $this->assertArrayHasKey('iv', $encrypted);
        $this->assertArrayHasKey('ciphertext', $encrypted);
        $this->assertNotEmpty($encrypted['iv']);
        $this->assertNotEmpty($encrypted['ciphertext']);

        // Bob用Alice的公钥解密消息
        $decrypted = $bob->decrypt($alice->getPublicKey(), $encrypted['iv'], $encrypted['ciphertext']);

        $this->assertEquals($message, $decrypted);
    }

    public function testEncryptDecryptWithEmptyMessage()
    {
        $alice = new E2EEncryption();
        $bob = new E2EEncryption();

        $message = "你好，世界！";

        $encrypted = $alice->encrypt($bob->getPublicKey(), $message);
        var_dump($encrypted);
        $decrypted = $bob->decrypt($alice->getPublicKey(), $encrypted['iv'], $encrypted['ciphertext']);
        var_dump($decrypted);

        $this->assertEquals($message, $decrypted);
    }

    public function testEncryptDecryptWithLongMessage()
    {
        $alice = new E2EEncryption();
        $bob = new E2EEncryption();

        $message = str_repeat("This is a long message. ", 1000);

        $encrypted = $alice->encrypt($bob->getPublicKey(), $message);
        var_dump($encrypted);
        $decrypted = $bob->decrypt($alice->getPublicKey(), $encrypted['iv'], $encrypted['ciphertext']);
        var_dump($decrypted);

        $this->assertEquals($message, $decrypted);
    }

    public function testEncryptDecryptWithUnicodeMessage()
    {
        $alice = new E2EEncryption();
        $bob = new E2EEncryption();

        $message = "你好，世界！🌍 Hello, мир! مرحبا بالعالم!";

        $encrypted = $alice->encrypt($bob->getPublicKey(), $message);
        $decrypted = $bob->decrypt($alice->getPublicKey(), $encrypted['iv'], $encrypted['ciphertext']);

        $this->assertEquals($message, $decrypted);
    }

    public function testDecryptWithInvalidCiphertext()
    {
        $this->expectException(PeerToPeerException::class);
        $this->expectExceptionMessage('解密失败');

        $alice = new E2EEncryption();
        $bob = new E2EEncryption();

        $encrypted = $alice->encrypt($bob->getPublicKey(), "test message");

        // 篡改密文
        $tamperedCiphertext = base64_encode("invalid ciphertext");

        $bob->decrypt($alice->getPublicKey(), $encrypted['iv'], $tamperedCiphertext);
    }

    public function testDecryptWithInvalidIV()
    {
        $this->expectException(PeerToPeerException::class);
        $this->expectExceptionMessage('解密失败');

        $alice = new E2EEncryption();
        $bob = new E2EEncryption();

        $encrypted = $alice->encrypt($bob->getPublicKey(), "test message");

        // 篡改IV
        $tamperedIV = base64_encode(str_repeat("\x00", 12));

        $bob->decrypt($alice->getPublicKey(), $tamperedIV, $encrypted['ciphertext']);
    }

    public function testEncryptWithInvalidPublicKey()
    {
        $this->expectException(\SodiumException::class);

        $alice = new E2EEncryption();

        // 无效的公钥
        $invalidPublicKey = base64_encode("invalid key");

        $alice->encrypt($invalidPublicKey, "test message");
    }

    public function testDecryptWithInvalidPublicKey()
    {
        $this->expectException(\SodiumException::class);

        $alice = new E2EEncryption();
        $bob = new E2EEncryption();

        $encrypted = $alice->encrypt($bob->getPublicKey(), "test message");

        // 无效的公钥
        $invalidPublicKey = base64_encode("invalid key");

        $bob->decrypt($invalidPublicKey, $encrypted['iv'], $encrypted['ciphertext']);
    }

    public function testConstructorWithInvalidPrivateKey()
    {
        $this->expectException(\SodiumException::class);

        // 无效的私钥
        $invalidPrivateKey = base64_encode("invalid private key");

        new E2EEncryption($invalidPrivateKey);
    }

    public function testPublicKeyConsistency()
    {
        $e2e = new E2EEncryption();
        $publicKey1 = $e2e->getPublicKey();
        $publicKey2 = $e2e->getPublicKey();

        $this->assertEquals($publicKey1, $publicKey2);
    }

    public function testPrivateKeyConsistency()
    {
        $e2e = new E2EEncryption();
        $privateKey1 = $e2e->getPrivateKey();
        $privateKey2 = $e2e->getPrivateKey();

        $this->assertEquals($privateKey1, $privateKey2);
    }

    public function testDifferentInstancesHaveDifferentKeys()
    {
        $e2e1 = new E2EEncryption();
        $e2e2 = new E2EEncryption();

        $this->assertNotEquals($e2e1->getPublicKey(), $e2e2->getPublicKey());
        $this->assertNotEquals($e2e1->getPrivateKey(), $e2e2->getPrivateKey());
    }
}