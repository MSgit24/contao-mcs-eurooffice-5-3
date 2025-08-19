:: Run easy-coding-standard (ecs) via this batch file inside your IDE e.g. PhpStorm (Windows only)
:: Install inside PhpStorm the  "Batch Script Support" plugin
cd..
cd..
cd..
cd..
cd..
cd..
php vendor\bin\ecs check vendor/mcs/contao-mcs-eurooffice/src --fix --config vendor/mcs/contao-mcs-eurooffice/tools/ecs/config.php
php vendor\bin\ecs check vendor/mcs/contao-mcs-eurooffice/contao --fix --config vendor/mcs/contao-mcs-eurooffice/tools/ecs/config.php
php vendor\bin\ecs check vendor/mcs/contao-mcs-eurooffice/config --fix --config vendor/mcs/contao-mcs-eurooffice/tools/ecs/config.php
php vendor\bin\ecs check vendor/mcs/contao-mcs-eurooffice/templates --fix --config vendor/mcs/contao-mcs-eurooffice/tools/ecs/config.php
php vendor\bin\ecs check vendor/mcs/contao-mcs-eurooffice/tests --fix --config vendor/mcs/contao-mcs-eurooffice/tools/ecs/config.php
