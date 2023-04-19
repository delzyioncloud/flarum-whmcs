<?php

/*
 * This file is part of delzyioncloud/flarum-whmcs.
 *
 * Copyright (c) 2023 Micorksen.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace DelzyionCloud\FlarumWhmcs;

use Flarum\Extend;

return [
    (new Extend\Locales(__DIR__.'/locale')),
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),
    (new Extend\Routes('forum'))
        ->get('/auth/whmcs', 'auth.whmcs', Controllers\WhmcsAuthController::class),
];
