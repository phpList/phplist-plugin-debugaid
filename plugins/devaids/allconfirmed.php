<?php

Sql_Query(sprintf('update %s set confirmed = 1',$GLOBALS['tables']['user']));
Sql_Query(sprintf('update %s set blacklisted = 0',$GLOBALS['tables']['user']));
Sql_Query(sprintf('delete from %s',$GLOBALS['tables']['user_blacklist']));

print s('Marked all subscribers confirmed and not blacklisted, and cleared the blacklist');

