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

use Kaadon\PeerToPeer\E2EEncryption;

class E2E extends E2EEncryption
{
    public function __construct(string $privateKey = null)
    {
        parent::__construct($privateKey);
    }

}