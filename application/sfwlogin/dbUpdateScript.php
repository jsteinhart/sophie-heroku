<?php
require_once(BASE_PATH . '/contrib/Updates.php');
$updater = new Application_Contrib_Updates();
$updater->runUpdates();