<?php

## generate lists
#var_dump($tables);
  @ob_end_flush();

$max = 10;

$subs_count = Sql_Fetch_Row_Query(sprintf('select count(*) from %s',$tables['user']));
$total_subscribers = $subs_count[0];

$pref = substr(md5(time()),0,10);
for ($i = 1; $i <= $max; $i++) {
  Sql_Query(sprintf('insert into %s (name,description,entered,listorder,modified,active,owner,category)
    values("Test list %s","Test List %s",now(),%d,now(),1,"%s","%s")',
    $tables['list'],$pref.$i,$pref.$i,$i,$_SESSION['logindetails']['adminname'],'Category '.substr($i,0,1)));
  $listid = Sql_Insert_Id();

  ## add a random number of subscribers
  $subsAdd = rand(50,$total_subscribers);
  for ($j=1; $j<$subsAdd;$j++) {
    set_time_limit(60);
    Sql_Query(sprintf('insert into %s (userid,listid,entered,modified) values(%d,%d,now(),now())',
      $tables['listuser'],rand(1,$total_subscribers),$listid),1);
  }
  print '<br/>List '.$i.' -> '.$total_subscribers.'<br/>';
};

print $max.' lists created';
