<?php

namespace Kaadon\PeerToPeer\Tests;

use Kaadon\PeerToPeer\LocalKeyStore;
use PHPUnit\Framework\TestCase;

class LocalKeyStoreTest extends TestCase
{
    private $testKeysFile;
    private $keyStore;

    protected function setUp(): void
    {
        $this->testKeysFile = sys_get_temp_dir() . '/test_keys_' . uniqid() . '.json';
        $this->keyStore = new LocalKeyStore($this->testKeysFile);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testKeysFile)) {
            unlink($this->testKeysFile);
        }
    }

    public function testConstructorCreatesFileIfNotExists()
    {
        $this->assertFileExists($this->testKeysFile);

        $content = file_get_contents($this->testKeysFile);
        $this->assertEquals('[]', $content);
    }

    public function testConstructorWithExistingFile()
    {
        $existingData = ['alice' => 'alice_public_key'];
        file_put_contents($this->testKeysFile, json_encode($existingData));

        $keyStore = new LocalKeyStore($this->testKeysFile);

        $this->assertEquals('alice_public_key', $keyStore->getPublicKey('alice'));
    }

    public function testSaveAndGetPublicKey()
    {
        $name = 'alice';
        $publicKey = 'alice_public_key_base64';

        $this->keyStore->savePublicKey($name, $publicKey);
        $retrievedKey = $this->keyStore->getPublicKey($name);

        $this->assertEquals($publicKey, $retrievedKey);
    }

    public function testGetNonExistentPublicKey()
    {
        $result = $this->keyStore->getPublicKey('nonexistent');

        $this->assertNull($result);
    }

    public function testHasPublicKey()
    {
        $name = 'alice';
        $publicKey = 'alice_public_key_base64';

        $this->assertFalse($this->keyStore->hasPublicKey($name));

        $this->keyStore->savePublicKey($name, $publicKey);

        $this->assertTrue($this->keyStore->hasPublicKey($name));
    }

    public function testListPublicKeys()
    {
        $this->assertEquals([], $this->keyStore->listPublicKeys());

        $this->keyStore->savePublicKey('alice', 'alice_key');
        $this->keyStore->savePublicKey('bob', 'bob_key');
        $this->keyStore->savePublicKey('charlie', 'charlie_key');

        $keys = $this->keyStore->listPublicKeys();
        sort($keys);

        $this->assertEquals(['alice', 'bob', 'charlie'], $keys);
    }

    public function testDeletePublicKey()
    {
        $name = 'alice';
        $publicKey = 'alice_public_key_base64';

        $this->keyStore->savePublicKey($name, $publicKey);
        $this->assertTrue($this->keyStore->hasPublicKey($name));

        $this->keyStore->deletePublicKey($name);

        $this->assertFalse($this->keyStore->hasPublicKey($name));
        $this->assertNull($this->keyStore->getPublicKey($name));
    }

    public function testDeleteNonExistentKey()
    {
        $this->keyStore->deletePublicKey('nonexistent');

        $this->assertEquals([], $this->keyStore->listPublicKeys());
    }

    public function testUpdateExistingKey()
    {
        $name = 'alice';
        $originalKey = 'alice_original_key';
        $updatedKey = 'alice_updated_key';

        $this->keyStore->savePublicKey($name, $originalKey);
        $this->assertEquals($originalKey, $this->keyStore->getPublicKey($name));

        $this->keyStore->savePublicKey($name, $updatedKey);
        $this->assertEquals($updatedKey, $this->keyStore->getPublicKey($name));

        // 确保只有一个alice条目
        $this->assertEquals([$name], $this->keyStore->listPublicKeys());
    }

    public function testMultipleOperations()
    {
        // 保存多个密钥
        $this->keyStore->savePublicKey('alice', 'alice_key');
        $this->keyStore->savePublicKey('bob', 'bob_key');
        $this->keyStore->savePublicKey('charlie', 'charlie_key');

        // 验证所有密钥都存在
        $this->assertTrue($this->keyStore->hasPublicKey('alice'));
        $this->assertTrue($this->keyStore->hasPublicKey('bob'));
        $this->assertTrue($this->keyStore->hasPublicKey('charlie'));

        // 删除一个密钥
        $this->keyStore->deletePublicKey('bob');

        // 验证删除后的状态
        $this->assertTrue($this->keyStore->hasPublicKey('alice'));
        $this->assertFalse($this->keyStore->hasPublicKey('bob'));
        $this->assertTrue($this->keyStore->hasPublicKey('charlie'));

        $keys = $this->keyStore->listPublicKeys();
        sort($keys);
        $this->assertEquals(['alice', 'charlie'], $keys);
    }

    public function testFileContentFormat()
    {
        $this->keyStore->savePublicKey('alice', 'alice_key');
        $this->keyStore->savePublicKey('bob', 'bob_key');

        $content = file_get_contents($this->testKeysFile);
        $data = json_decode($content, true);

        $this->assertIsArray($data);
        $this->assertEquals('alice_key', $data['alice']);
        $this->assertEquals('bob_key', $data['bob']);
    }

    public function testEmptyNameHandling()
    {
        $this->keyStore->savePublicKey('', 'empty_name_key');

        $this->assertTrue($this->keyStore->hasPublicKey(''));
        $this->assertEquals('empty_name_key', $this->keyStore->getPublicKey(''));
        $this->assertContains('', $this->keyStore->listPublicKeys());
    }

    public function testSpecialCharactersInName()
    {
        $specialName = 'user@example.com';
        $publicKey = 'special_user_key';

        $this->keyStore->savePublicKey($specialName, $publicKey);

        $this->assertTrue($this->keyStore->hasPublicKey($specialName));
        $this->assertEquals($publicKey, $this->keyStore->getPublicKey($specialName));
    }

    public function testUnicodeInNameAndKey()
    {
        $unicodeName = '用户名';
        $unicodeKey = 'key_with_unicode_你好';

        $this->keyStore->savePublicKey($unicodeName, $unicodeKey);

        $this->assertTrue($this->keyStore->hasPublicKey($unicodeName));
        $this->assertEquals($unicodeKey, $this->keyStore->getPublicKey($unicodeName));
    }
}