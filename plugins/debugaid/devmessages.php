<?php

## generate lists
#var_dump($tables);

$lists = array();
$listreq = Sql_Query(sprintf('select id from %s',$tables['list']));
while ($row = Sql_Fetch_Row($listreq)) {
  $lists[] = $row[0];
}
 
@ob_end_flush();
$pref = substr(md5(time()),0,10);
for ($i = 1; $i < 6; $i++) {
  Sql_Query(sprintf('insert into %s (subject,fromfield,message,footer,entered,status,embargo,modified,htmlformatted)
    values("Test Message %s","Test@phplist.com","[URL:http://www.mailinator.com]","Message footer",now(),"submitted",now(),now(),1)',
    $tables['message'],$pref.$i));
  $msgid = Sql_Insert_Id();

  ## add message to a random number of lists
  foreach ($lists as $listid) {
    $add = rand(0,100) < 25;
    if ($add) {
      Sql_Query(sprintf('insert into %s (messageid,listid,entered,modified) values(%d,%d,now(),now())',
        $tables['listmessage'],$msgid,$listid),1);
    }
  }
  print '<br/>Message '.$i.'<br/>';
};


print PageLink2('main',s('Back to main'));
