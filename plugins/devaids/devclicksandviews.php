<?php

## generate lists
#var_dump($tables);
@ob_end_flush();

$count = array();

$req = Sql_Query(sprintf('select * from %s where viewed is null and status = "sent"',
  $tables['usermessage']));
while ($row = Sql_Fetch_Assoc($req)) {
  set_time_limit(60);
  if (rand(0,100) < 25) { ## 25% open rate
    Sql_Query(sprintf('update %s set viewed = date_add(entered,interval %d second) where messageid = %d and userid = %d',
      $tables['usermessage'],rand(0,86400),$row['messageid'],$row['userid']));
    $count['viewed']++;
    
    ## also add clicks
    $clickcnt = 0;
    $linksreq = Sql_Query(sprintf('select * from %s where messageid = %d',$tables['linktrack_ml'],$row['messageid']));
    while ($link = Sql_Fetch_Assoc($linksreq)) {
      $html = rand(1,10) > 2; ## 90% html

      if (rand(0,10) > 8) {
        $clickcnt ++;
        if (empty($link['firstclick'])) {
          Sql_Query(sprintf('update %s set firstclick = date_add("%s",interval %d second) where messageid = %d and forwardid = %d',
            $tables['linktrack_ml'],$row['entered'],rand(0,100),$link['messageid'],$link['forwardid']));
        }
        if ($html) {
          $clicktype = ' htmlclicked = htmlclicked + 1';
        } else {
          $clicktype = ' textclicked = textclicked + 1';
        }
        Sql_Query(sprintf('update %s set latestclick = date_add("%s",interval %d second),clicked = clicked + 1, %s where messageid = %d and forwardid = %d',
          $tables['linktrack_ml'],$row['entered'],rand(0,86400),$clicktype,$link['messageid'],$link['forwardid']));
        Sql_Query(sprintf('insert into %s set messageid = %d, userid = %d,forwardid = %d,firstclick = date_add("%s",interval %d second),latestclick = date_add("%s",interval %d second),clicked = clicked + 1, %s ',
          $tables['linktrack_uml_click'],$row['messageid'],$row['userid'],$link['forwardid'],$row['entered'],rand(0,300),$row['entered'],rand(200,86400),$clicktype,$link['messageid'],$link['forwardid']));

        ## one user generally only clicks once, at most twice
        if ($clickcnt > rand(1,2)) break;
      }
    }
  }
}

print PageLink2('main',s('Back to main'));
