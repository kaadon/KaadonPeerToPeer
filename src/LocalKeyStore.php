<?php
/**
 *   +----------------------------------------------------------------------
 *   | PROJECT:   [ KaadonPeerToPeer ]
 *   +----------------------------------------------------------------------
 *   | 官方网站:   [ https://developer.kaadon.com ]
 *   +----------------------------------------------------------------------
 *   | Author:    [ kaadon.com <kaadon.com@gmail.com>]
 *   +----------------------------------------------------------------------
 *   | Tool:      [ PhpStorm ]
 *   +----------------------------------------------------------------------
 *   | Date:      [ 2025/9/24 ]
 *   +----------------------------------------------------------------------
 *   | 版权所有    [ 2020~2025 kaadon.com ]
 *   +----------------------------------------------------------------------
 **/

namespace Kaadon\PeerToPeer;

/**
 * 本地公钥存储管理类
 */
class LocalKeyStore
{
    private $keysFile;

    /**
     * 构造函数
     * @param string $keysFile 存储公钥的JSON文件路径
     */
    public function __construct(string $keysFile = '/tmp/local_keys.json')
    {
        $this->keysFile = $keysFile;
        if (!file_exists($this->keysFile)) {
            file_put_contents($this->keysFile, json_encode([]));
        }
    }

    /**
     * 保存远程公钥
     * @param string $name 用户名称
     * @param string $publicKey Base64编码的公钥
     */
    public function savePublicKey(string $name, string $publicKey)
    {
        $keys        = $this->loadKeys();
        $keys[$name] = $publicKey;
        $this->saveKeys($keys);
    }

    /**
     * 获取远程公钥
     * @param string $name 用户名称
     * @return string|null Base64编码的公钥
     */
    public function getPublicKey(string $name): ?string
    {
        $keys = $this->loadKeys();
        return $keys[$name] ?? null;
    }

    /**
     * 列出所有保存的公钥名称
     * @return array 公钥名称列表
     */
    public function listPublicKeys(): array
    {
        $keys = $this->loadKeys();
        return array_keys($keys);
    }

    /**
     * 删除公钥
     * @param string $name 用户名称
     */
    public function deletePublicKey(string $name)
    {
        $keys = $this->loadKeys();
        unset($keys[$name]);
        $this->saveKeys($keys);
    }

    /**
     * 检查公钥是否存在
     * @param string $name 用户名称
     * @return bool
     */
    public function hasPublicKey(string $name): bool
    {
        $keys = $this->loadKeys();
        return isset($keys[$name]);
    }

    private function loadKeys()
    {
        $content = file_get_contents($this->keysFile);
        return json_decode($content, true) ?: [];
    }

    private function saveKeys($keys)
    {
        file_put_contents($this->keysFile, json_encode($keys, JSON_PRETTY_PRINT));
    }
}