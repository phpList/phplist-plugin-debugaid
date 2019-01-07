<?php

## generate dev attributes
@ob_end_flush();
$count = 1;
 
$attributes = array();
$attreq = Sql_Query(sprintf('select * from %s where type ="textline"',$GLOBALS['tables']['attribute']));
while ($att = Sql_Fetch_Assoc($attreq)) {
  $attributes[$att['id']] = $att;
}
//var_dump($attributes);

$userreq = Sql_Query(sprintf('select id from %s',$GLOBALS['tables']['user']));
$total = Sql_Num_Rows($userreq);
while ($user = Sql_Fetch_Assoc($userreq)) {
  foreach ($attributes as $attrid => $attribute) {
    switch ($attribute['type']) {
      ## for now we just do textlines for testing
      ## when adding ones, make sure to update the query above
      case 'textline':
        Sql_Query(sprintf('update %s set value = "%s" where userid = %d  and attributeid = %d',
          $GLOBALS['tables']['user_attribute'],$attribute['name'].' '.$user['id'],$user['id'],$attrid));
        break;
    }
  }
  $count++;

  set_time_limit(60);

  if ($count % 100 == 0) {
    print "$count / $total<br/>";
    flush();
  }
  
}
