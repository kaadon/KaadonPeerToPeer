# Kaadon PeerToPeer

[![PHP Version](https://img.shields.io/badge/PHP-7.2+-blue.svg)](https://www.php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

一个基于 X25519 密钥交换和 AES-GCM 加密的 PHP 端到端加密库，提供安全的点对点通信解决方案。

## 特性

- 🔐 **端到端加密**: 使用 X25519 椭圆曲线密钥交换 + AES-256-GCM 加密
- 🔑 **密钥管理**: 自动生成密钥对，支持密钥导入导出
- 💾 **本地密钥存储**: 提供公钥的本地存储和管理功能
- 🛡️ **安全保障**: 基于 libsodium 加密库，军用级别的安全性
- 🎯 **简单易用**: 简洁的 API 设计，几行代码即可实现加密通信
- 🔧 **框架集成**: 支持 ThinkPHP 框架集成

## 前端
[本库适用于后端 PHP 环境，前端可以使用 @kaadon.com/peertopeer 结合]('https://github.com/kaadon/npm_plugins_peertopeer')

[前端库 npm]('https://www.npmjs.com/package/@kaadon.com/peertopeer')

```bash
npm i @kaadon.com/peertopeer
```

## 安装

通过 Composer 安装：

```bash
composer require kaadon/peertopeer
```

## 系统要求

- PHP >= 7.2
- ext-sodium 扩展
- ext-json 扩展

### 安装 Sodium 扩展

```bash
# Ubuntu/Debian
sudo apt-get install php-sodium

# CentOS/RHEL
sudo yum install php-sodium

# macOS (使用 Homebrew)
brew install libsodium

# 或者通过 PECL
pecl install libsodium
```

## 快速开始

### 基础用法

```php
<?php
require_once 'vendor/autoload.php';

use Kaadon\PeerToPeer\E2EEncryption;

// 创建两个用户实例
$alice = new E2EEncryption();
$bob = new E2EEncryption();

// 获取公钥（用于交换）
$alicePublicKey = $alice->getPublicKey();
$bobPublicKey = $bob->getPublicKey();

// Alice 发送加密消息给 Bob
$message = "Hello, Bob!";
$encrypted = $alice->encrypt($bobPublicKey, $message);

// Bob 解密消息
$decrypted = $bob->decrypt($alicePublicKey, $encrypted['iv'], $encrypted['ciphertext']);

echo "原始消息: $message\n";
echo "解密消息: $decrypted\n";
```

### 使用本地密钥存储

```php
<?php
use Kaadon\PeerToPeer\E2EEncryption;
use Kaadon\PeerToPeer\LocalKeyStore;

// 创建加密实例
$user = new E2EEncryption();

// 创建密钥存储
$keyStore = new LocalKeyStore('/path/to/keys.json');

// 保存朋友的公钥
$keyStore->savePublicKey('alice', 'Base64EncodedPublicKey');
$keyStore->savePublicKey('bob', 'AnotherBase64PublicKey');

// 发送加密消息
$friendPublicKey = $keyStore->getPublicKey('alice');
if ($friendPublicKey) {
    $encrypted = $user->encrypt($friendPublicKey, "机密消息");
    // 发送 $encrypted 到对方
}
```

## API 文档

### E2EEncryption 类

#### 构造函数

```php
public function __construct(string $privateKey = null)
```

- `$privateKey`: (可选) Base64 编码的私钥。如果不提供，将自动生成新的密钥对

#### 公共方法

##### getPublicKey()

```php
public function getPublicKey(): string
```

获取当前实例的公钥（Base64 编码）

##### getPrivateKey()

```php
public function getPrivateKey(): string
```

获取当前实例的私钥（Base64 编码）

##### encrypt()

```php
public function encrypt(string $remotePublicKeyB64, string $message): array
```

使用远程公钥加密消息

**参数:**
- `$remotePublicKeyB64`: Base64 编码的远程公钥
- `$message`: 要加密的明文消息

**返回:**
```php
[
    'iv' => 'Base64EncodedIV',
    'ciphertext' => 'Base64EncodedCiphertext'
]
```

##### decrypt()

```php
public function decrypt(string $remotePublicKeyB64, string $ivB64, string $ciphertextB64): string
```

使用远程公钥解密消息

**参数:**
- `$remotePublicKeyB64`: Base64 编码的远程公钥
- `$ivB64`: Base64 编码的初始化向量
- `$ciphertextB64`: Base64 编码的密文

**返回:** 解密后的明文消息

### LocalKeyStore 类

#### 构造函数

```php
public function __construct(string $keysFile = '/tmp/local_keys.json')
```

- `$keysFile`: 存储公钥的 JSON 文件路径

#### 公共方法

##### savePublicKey()

```php
public function savePublicKey(string $name, string $publicKey): void
```

保存远程用户的公钥

##### getPublicKey()

```php
public function getPublicKey(string $name): ?string
```

获取指定用户的公钥

##### listPublicKeys()

```php
public function listPublicKeys(): array
```

列出所有已保存的公钥名称

##### deletePublicKey()

```php
public function deletePublicKey(string $name): void
```

删除指定用户的公钥

##### hasPublicKey()

```php
public function hasPublicKey(string $name): bool
```

检查是否存在指定用户的公钥

## 使用示例

### 完整的通信示例

```php
<?php
require_once 'vendor/autoload.php';

use Kaadon\PeerToPeer\E2EEncryption;
use Kaadon\PeerToPeer\LocalKeyStore;

try {
    // 模拟两个用户
    $alice = new E2EEncryption();
    $bob = new E2EEncryption();

    // 创建密钥存储
    $aliceKeyStore = new LocalKeyStore('/tmp/alice_keys.json');
    $bobKeyStore = new LocalKeyStore('/tmp/bob_keys.json');

    // 交换公钥（实际场景中通过安全渠道交换）
    $aliceKeyStore->savePublicKey('bob', $bob->getPublicKey());
    $bobKeyStore->savePublicKey('alice', $alice->getPublicKey());

    // Alice 发送消息给 Bob
    $message = "这是一条机密消息！";
    $bobPublicKey = $aliceKeyStore->getPublicKey('bob');
    $encrypted = $alice->encrypt($bobPublicKey, $message);

    echo "Alice 发送的加密消息:\n";
    echo "IV: " . $encrypted['iv'] . "\n";
    echo "密文: " . $encrypted['ciphertext'] . "\n\n";

    // Bob 接收并解密消息
    $alicePublicKey = $bobKeyStore->getPublicKey('alice');
    $decrypted = $bob->decrypt(
        $alicePublicKey,
        $encrypted['iv'],
        $encrypted['ciphertext']
    );

    echo "Bob 解密后的消息: $decrypted\n";

} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}
```

### ThinkPHP 框架集成

本库支持 ThinkPHP 5.1 框架集成。安装后会自动注册配置文件。

在 ThinkPHP 中使用：

```php
<?php
namespace app\controller;

use Kaadon\PeerToPeer\E2EEncryption;
use think\Controller;

class ChatController extends Controller
{
    public function sendMessage()
    {
        $encryption = new E2EEncryption();

        // 从数据库获取对方公钥
        $friendPublicKey = $this->getUserPublicKey($friendId);

        // 加密消息
        $encrypted = $encryption->encrypt($friendPublicKey, $message);

        // 保存到数据库或发送到对方
        // ...
    }
}
```

## 安全注意事项

⚠️ **重要安全提示:**

1. **私钥保护**: 私钥必须妥善保管，绝不能泄露。建议使用安全的密钥管理系统。

2. **公钥验证**: 在实际应用中，必须通过安全渠道验证公钥的真实性，防止中间人攻击。

3. **密钥轮换**: 定期更换密钥对以提高安全性。

4. **安全存储**: 本地密钥存储文件应设置适当的文件权限（如 600）。

5. **随机数生成**: 确保系统有足够的熵源用于生成安全的随机数。

## 错误处理

库中定义了自定义异常类 `PeerToPeerException`：

```php
try {
    $encryption = new E2EEncryption();
    $result = $encryption->encrypt($publicKey, $message);
} catch (\Kaadon\PeerToPeer\PeerToPeerException $e) {
    echo "加密错误: " . $e->getMessage();
} catch (\Exception $e) {
    echo "系统错误: " . $e->getMessage();
}
```

## 测试

运行测试套件：

```bash
composer test
```

或者直接使用 PHPUnit：

```bash
vendor/bin/phpunit
```

## 性能注意事项

- X25519 密钥交换计算相对较重，建议缓存共享密钥
- AES-GCM 加密性能优异，适合大量数据加密
- 避免在循环中重复创建 E2EEncryption 实例

## 故障排除

### 常见问题

1. **缺少 sodium 扩展**
   ```
   错误: 需要安装 sodium 扩展：pecl install libsodium
   ```
   解决方案: 安装 php-sodium 扩展

2. **解密失败**
   ```
   错误: 解密失败
   ```
   可能原因：
   - 使用了错误的密钥对
   - IV 或密文被篡改
   - 密钥格式不正确

3. **文件权限错误**
   ```
   错误: Permission denied
   ```
   解决方案: 检查密钥存储文件的读写权限

## 更新日志

### v1.0.0
- 初始版本发布
- 支持 X25519 + AES-GCM 端到端加密
- 提供本地密钥存储功能
- 支持 ThinkPHP 框架集成

## 贡献

欢迎提交 Issue 和 Pull Request！

1. Fork 本项目
2. 创建特性分支 (`git checkout -b feature/amazing-feature`)
3. 提交更改 (`git commit -am 'Add amazing feature'`)
4. 推送到分支 (`git push origin feature/amazing-feature`)
5. 创建 Pull Request

## 许可证

本项目基于 MIT 许可证开源。详见 [LICENSE](LICENSE) 文件。

## 联系我们

- 官网: https://developer.kaadon.com
- 邮箱: kaadon.com@gmail.com

## 相关链接

- [libsodium 文档](https://doc.libsodium.org/)
- [X25519 密钥交换](https://tools.ietf.org/html/rfc7748)
- [AES-GCM 加密](https://tools.ietf.org/html/rfc5116)
