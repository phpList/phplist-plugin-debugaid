<?php

## need to ask for confirmation here

#return;

if (DEVVERSION) {
    Sql_Verbose_Query("delete from {$tables["usermessage"]}");
    foreach ($GLOBALS['plugins'] as $pluginName => $plugin) {
      $plugin->deleteSent();
    }
} else {
    print s('Only available in DEV versions');
}


