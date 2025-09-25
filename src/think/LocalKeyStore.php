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

namespace Kaadon\PeerToPeer\think;

use think\facade\Config;

class LocalKeyStore extends \Kaadon\PeerToPeer\LocalKeyStore
{

    public function __construct(?string $keysFile = null)
    {
        if (is_null($keysFile) || !is_file($keysFile)) {
            $keysFile = Config::get('local_keys_file', '/tmp/kaadon_peer_to_peer_local_keys.json');
        }
        parent::__construct($keysFile);
    }
}