<?php

use Yeganehha\DigikalaSellerWebhook\Tests\PHPUnitUtil;

require_once __DIR__.'/vendor/autoload.php';

PHPUnitUtil::setToken(true);
PHPUnitUtil::setCONTENT(false , true);
PHPUnitUtil::updateAllVariantMockHandler();
digikala()
    ->setDiscordWebHook( "https://discord.com/api/webhooks/1022227160662020289/sVhlJSIxDYBFRx2HAfXZU65i0okiMpNyVcRtMAIztz5lY_Sz6c6uVO-xgKJplrhxdJE8")
    ->orders();