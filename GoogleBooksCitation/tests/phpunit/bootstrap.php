<?php
// SPDX-License-Identifier: GPL-2.0-only

/**
 * PHPUnit bootstrap for GoogleBooksCitation extension
 */

if (getenv('MW_INSTALL_PATH') !== false) {
    $IP = getenv('MW_INSTALL_PATH');
} else {
    $IP = dirname(__DIR__, 3);
}

require_once "$IP/tests/common/TestsAutoLoader.php";
