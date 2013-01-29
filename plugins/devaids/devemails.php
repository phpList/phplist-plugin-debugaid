<?php

$mailinator_domains = array(
'supergreatmail.com',
'spamthisplease.com',
'letthemeatspam.com',
'chammy.info',
'devnullmail.com',
'bobmail.info',
'sendspamhere.com',
'spamherelots.com',
'sogetthis.com',
'mailinator.net',
'safetymail.info',
'binkmail.com',
'tradermail.info',
'thisisnotmyrealemail.com',
'veryrealemail.com',
'mailinator2.com',
'notmailinator.com',
'zippymail.info',
'suremail.info'
);


if (!empty($_POST['prefix'])) {
  
  
  @ob_end_flush();
  $count = 1;
  $ran = substr(time(),-5);
  $prefix = $_POST['prefix'];
  $prefix = preg_replace('/\W/','',$prefix);
  
  $req = Sql_Query(sprintf('select id,email from %s',$GLOBALS['tables']['user']));

  $total = Sql_Num_Rows($req);
  printf($GLOBALS['I18N']->get('%d to process').'<br/>',$total);
  
  while ($row = Sql_Fetch_Array($req)) {
    if (!empty($_POST['domain'])) {
      $domain = $_POST['domain'];
    } else {
      $domain = $mailinator_domains[rand(0,sizeof($mailinator_domains)-1)];
    }
    $domain = preg_replace('/[^\w\.]/','',$domain);
   
    $email = $prefix.'-'.$ran.$count.'@'.$domain;
    
    Sql_Query(sprintf('update %s set email = "%s" where id = %d',
      $GLOBALS['tables']['user'],$email,$row['id']));
    $count++;

    set_time_limit(60);

    if ($count % 100 == 0) {
      print "$count / $total<br/>";
      flush();
    }
  }
  print '<br/>'.$count.' emails changed';
  if (!empty($_POST['newcount'])) {
    $makenew = sprintf('%d',$_POST['newcount']);
    if ($makenew) {
      $count = 0;
      print "<h3>Generating $makenew new emails</h3>";
      
      while ($count <= $makenew) {
        $domain = $mailinator_domains[rand(0,sizeof($mailinator_domains)-1)];
        $email = $prefix.'-'.$ran.$count.'@'.$domain;
        
        Sql_Query(sprintf('insert ignore into %s (email,confirmed,htmlemail) values("%s",1,1)',
          $GLOBALS['tables']['user'],$email));
        $count++;
        set_time_limit(60);
        
        if ($count % 100 == 0) {
          print "$count / $makenew<br/>";
          flush();
        }
      }
      print '<br/>'.$count.' emails created';
    }
  }
  
  
} else {
  
  print '<form action="" method="post" >';
  print '<br/>Add new emails:';
  print '<input type="text" name="newcount" value="" />';
  print '<br/>Email prefix';
  print '<input type="text" name="prefix" value="" />';
  print '<br/>Email domain';
  print '<input type="text" name="domain" value="" />';
  print '<br/><input type="submit" name="go" value="Generate Emails" />';
  print '</form>';
}


