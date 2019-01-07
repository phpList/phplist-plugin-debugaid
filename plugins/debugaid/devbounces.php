<?php

## generate lists
#var_dump($tables);
@ob_end_flush();

$req = Sql_Query(sprintf('select * from %s where viewed is null and status = "sent"',
  $tables['usermessage']));
while ($row = Sql_Fetch_Assoc($req)) {
  set_time_limit(60);
  if (rand(0,100) < 5) { ## 5% bounce rate
    Sql_Query(sprintf('insert into %s (date,header,data,status,comment) values(now(),"Test bounce","Test bounce","","")',
      $tables['bounce']));
    $bounceid = Sql_Insert_Id();
    Sql_Query(sprintf('insert into %s set id = 0, user = %d, message = %d, bounce = %d, time = date_add("%s",interval %d second)',
      $tables['user_message_bounce'],$row['userid'],$row['messageid'],$bounceid,$row['entered'],rand(0,86400)));
    Sql_Query(sprintf('update %s set bouncecount = bouncecount + 1 where id = %d',
      $tables['message'],$row['messageid']));
    Sql_Query(sprintf('update %s set bouncecount = bouncecount + 1 where id = %d',
      $tables['user'],$row['userid']));
  }
}


