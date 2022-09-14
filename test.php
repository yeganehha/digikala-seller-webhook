<?php

use Yeganehha\DigikalaSellerWebhook\Tests\PHPUnitUtil;

require_once __DIR__.'/vendor/autoload.php';

PHPUnitUtil::setToken(true);
PHPUnitUtil::setCONTENT(false , true);
PHPUnitUtil::updateAllVariantMockHandler();
digikala_order();