<?php

## sanitise the DB
function output($message)
{
    if ($GLOBALS['commandline']) {
        @ob_end_clean();
        echo strip_tags($message)."\n";
        ob_start();
    } else {
        echo $message;
        flush();
        @ob_end_flush();
    }
    flush();
}

$footer = '--
  
    <div class="footer" style="text-align:left; font-size: 75%;">
      <p>This message was sent to [EMAIL] by [FROMEMAIL]</p>
      <p>To forward this message, please do not use the forward button of your email application, because this message was made specifically for you only. Instead use the <a href="[FORWARDURL]">forward page</a> in our newsletter system.<br/>
      To change your details and to choose which lists to be subscribed to, visit your personal <a href="[PREFERENCESURL]">preferences page</a><br/>
      Or you can <a href="[UNSUBSCRIBEURL]">opt-out completely</a> from all future mailings.</p>
    </div>

  ';

output(s('Sanitise Subscribers'));
Sql_query('drop table if exists ph_allowed_from');

output('Sanitising USER Uniqid and UUID');
// scramble the uniqid and wipe the uuid so that it is re-generated
Sql_Query(sprintf('update %s set uniqid = sha2(uniqid, 256), uuid = "", foreignkey = ""',$GLOBALS['tables']['user']));

output(s('Sanitise Campaign'));
Sql_Query(sprintf('update %s set uuid = "", 
    fromfield = "From Field <sanitised@phplist-testing.com>", 
    message = concat("Test Campaign Content",id), 
    textmessage = concat("Test Text Campaign",id), 
    subject = concat("Test Campaign Subject",id), 
    footer = "%s"',
    $GLOBALS['tables']['message'], sql_escape($footer)));
Sql_Query(sprintf('delete from %s where name in ("notify_start","notify_end","followupto","sendurl", "testtarget")',$GLOBALS['tables']['messagedata']));
Sql_Query(sprintf('update %s set data = concat("Test Campaign",id) where name = "campaigntitle"',$GLOBALS['tables']['messagedata']));
Sql_Query(sprintf('update %s set data = concat("Test Campaign Subject",id) where name = "subject"',$GLOBALS['tables']['messagedata']));
Sql_Query(sprintf('update %s set data = concat("Test Campaign",id) where name = "message"',$GLOBALS['tables']['messagedata']));
Sql_Query(sprintf('update %s set data = concat("Test Text Campaign",id) where name = "textmessage"',$GLOBALS['tables']['messagedata']));
Sql_Query(sprintf('update %s set data = "%s" where name = "footer"',$GLOBALS['tables']['messagedata'],sql_escape($footer)));
Sql_Query(sprintf('update %s set data = "From Field <sanitised@phplist-testing.com>" where name = "from" or name = "fromfield"',$GLOBALS['tables']['messagedata']));

output('Sanitising List names');
// rename the lists
Sql_Query(sprintf('update %s set name = concat("List " , id), Description = concat("Sign up to this list to receive information about List ", id)',$GLOBALS['tables']['list']));

output('Sanitising Passwords');
Sql_Query(sprintf('update %s set password = "" where password != ""', $GLOBALS['tables']['user']));

output('Sanitising Admins');
Sql_Query(sprintf('delete from %s where loginname != "admin"', $GLOBALS['tables']['admin']));

output('Sanitising blacklisted email addresses');
Sql_Query(sprintf('update %s bl join %s bld on bl.email = bld.email join %s u on bl.email = u.email 
  set u.email = concat("sanitised+",substr(md5(u.email),1,15), "@blacklistedemail.phplist.com"),
      bl.email = concat("sanitised+",substr(md5(u.email),1,15), "@blacklistedemail.phplist.com"), 
      bld.email = concat("sanitised+",substr(md5(u.email),1,15), "@blacklistedemail.phplist.com")',$GLOBALS['tables']['user_blacklist'],$GLOBALS['tables']['user_blacklist_data'],$GLOBALS['tables']['user']));

Sql_Query(sprintf('update %s bl join %s bld on bl.email = bld.email
  set bl.email = concat("sanitised+",substr(md5(bl.email),1,15), "@blacklistedemail.phplist.com"), 
      bld.email = concat("sanitised+",substr(md5(bl.email),1,15), "@blacklistedemail.phplist.com") where bl.email not like "sanitised%%"',$GLOBALS['tables']['user_blacklist'],$GLOBALS['tables']['user_blacklist_data'],$GLOBALS['tables']['user']));

## obfusscate those with few emais per domain
//$req = Sql_Query(sprintf('select
//      lcase(substring_index(email,"@",-1)) as domain,
//      lcase(substring_index(email,".",-1)) as tld,
//      count(email) as total
//      from %s
//      where email not like "sanitised%%"
//      group by domain ', $GLOBALS['tables']['user']));
//
//output('Sanitising Other Email addresses');
//while ($entry = Sql_Fetch_Assoc($req)) {
//    if (!empty($entry['domain']) && !in_array($entry['domain'], array('blacklistedemail.phplist.com','phplist.com'))) {
//        // for enries with less than 100 per domain, we obfuscate the domain as well
//        if ($entry['total'] <= 100) {
//#            $obfuscatedDomain = substr(md5($entry['domain']),1,20).'.'.$entry['tld'];
//#            output('<h1>' . $entry['domain'] . ' -> ' . $entry['total'] . '</h1>');
//#            Sql_Query(sprintf('update %s set email = concat("sanitised+",substr(md5(email),1,15),"@%s") where email like "%%@%s"', $GLOBALS['tables']['user'], $obfuscatedDomain, $entry['domain']),1);
//        } else {
//            output( '<h1>' . $entry['domain'] . ' ' . $entry['total'] . '</h1>');
//            Sql_Query(sprintf('update %s set email = concat("sanitised+",substr(md5(email),1,15),"@%s") where email like "%%@%s"', $GLOBALS['tables']['user'], $entry['domain'], $entry['domain']),1);
//        }
//    }
//}
//
output('Sanitising Other Email addresses');
Sql_Query(sprintf('update %s set email = concat("sanitised+",substr(cast(md5(email) as char character set utf8),1,15),"@",substr(cast(md5(substring_index(email,"@",-1)) as char character set utf8),1,25),".",substring_index(cast(email  as char character set utf8),".",-1)) where email not like "sanitised%%"', $GLOBALS['tables']['user']),1);
output('Deleting failed sanitisations');
$req = Sql_Query(sprintf('select id from %s where email not like "sanitised%%"',$GLOBALS['tables']['user']));
$num = Sql_Affected_Rows();
output($num.' to do ');
while ($row = Sql_Fetch_Row($req)) {
    deleteUser($row[0]);
}

output('Sanitising attribute values');
Sql_Query(sprintf('update %s set name = concat("Attribute ",id)',$GLOBALS['tables']['attribute']));
$attrReq = Sql_Query(sprintf('select id,type from %s where type = "textline" or type = "textarea" or type = "hidden"',$GLOBALS['tables']['attribute']));
while ($attr = Sql_Fetch_Assoc($attrReq)) {
    switch ($attr['type']) {
        case 'textline':
            Sql_Query(sprintf('update %s set value = concat("sanitised text line value for subscriber ",userid) where attributeid = %d',$GLOBALS['tables']['user_attribute'],$attr['id']));
            break;
        case 'textarea':
            Sql_Query(sprintf('update %s set value = concat("sanitised text area value for subscriber ",userid) where attributeid = %d',$GLOBALS['tables']['user_attribute'],$attr['id']));
            break;
    }
}
$attrReq = Sql_Query(sprintf('select id,type,tablename from %s ',$GLOBALS['tables']['attribute']));
while ($attr = Sql_Fetch_Assoc($attrReq)) {
    if (Sql_Table_Exists($GLOBALS['table_prefix'].'listattr_'.$attr['tablename'])) {
        Sql_Query(sprintf('update %s set name = concat("Attribute value ",id)',$GLOBALS['table_prefix'].'listattr_'.$attr['tablename']));
    }
}

output('Sanitising forwards');
Sql_Query(sprintf('update %s set forward = concat(md5(forward),"@forwarded.phplist.com")',$GLOBALS['tables']['user_message_forward']));

output('Sanitising links - zap uuids');
Sql_Query(sprintf('update %s set uuid = ""',$GLOBALS['tables']['linktrack_forward']));
output('Sanitising links - non personalised');
Sql_Query(sprintf('update %s set url = concat("https://sanitised-links.phplist.com/",md5(url)) where personalise = 0 and url not like "https://sanitised-links.phplist.com%%"',$GLOBALS['tables']['linktrack_forward']));

output('Sanitising links - personalised');
$req = Sql_Query(sprintf('select * from %s where personalise and url not like "https://sanitised-links.phplist.com%%"',$GLOBALS['tables']['linktrack_forward']));
while ($row = Sql_Fetch_Array($req)) {
    $url = $row['url'];
    if (preg_match('/https?:\/\/[^\/]+/',$url,$match)) {
  #      output($url.' '.$match[0]);
        Sql_Query(sprintf('update %s set url = "%s" where url = "%s"',$GLOBALS['tables']['linktrack_forward'],str_replace($match[0],'https://sanitised-links.phplist.com',$url),$url));
    }
}
Sql_Query(sprintf('update %s set url = concat("https://sanitised-links.phplist.com/?",md5(url)) where url not like "https://sanitised-links.phplist.com%%"',$GLOBALS['tables']['linktrack_forward']));

output('Sanitising user history');
Sql_Query(sprintf('update %s set ip = "127.0.0.1",detail = "https://sanitised-urls.phplist.com/lists/admin/", systeminfo = "HTTP_USER_AGENT = Mozilla/5.0 (Windows NT 6.2; WOW64; rv:46.0) Gecko/20100101 Firefox/46.0
HTTP_REFERER = https://sanitised-urls.phplist.com/lists/admin/
REMOTE_ADDR = 127.0.1.2
REQUEST_URI = /lists/admin/?page=pageaction&action=import2"',$GLOBALS['tables']['user_history']));


output('Wiping config');
Sql_Query(sprintf('delete from %s where item in ("xormask","hmackey","%s")',$GLOBALS['tables']['config'],join('","',array_keys($default_config))));

output('Deleting bounces');
Sql_Query(sprintf('delete from %s',$GLOBALS['tables']['bounce']));
output('Deleting log');
Sql_Query(sprintf('delete from %s',$GLOBALS['tables']['eventlog']));
Sql_Query(sprintf('delete from %s',$GLOBALS['tables']['admin_attribute']));
Sql_Query(sprintf('delete from %s',$GLOBALS['tables']['admin_password_request']));

$req = Sql_Query(sprintf('select id from %s where uuid is NULL or uuid = ""', $GLOBALS['tables']['user']));
$num = Sql_Affected_Rows();
if ($num) {
    cl_output(s('Giving a UUID to %d subscribers, this may take a while', $num));
    while ($row = Sql_Fetch_Row($req)) {
        Sql_query(sprintf('update %s set uuid = "%s" where id = %d', $GLOBALS['tables']['user'], (string) uuid::generate(4), $row[0]));
    }
}
$req = Sql_Query(sprintf('select id from %s where uuid is NULL or uuid = ""', $GLOBALS['tables']['message']));
$num = Sql_Affected_Rows();
if ($num) {
    cl_output(s('Giving a UUID to %d campaigns', $num));
    while ($row = Sql_Fetch_Row($req)) {
        Sql_query(sprintf('update %s set uuid = "%s" where id = %d', $GLOBALS['tables']['message'], (string) uuid::generate(4), $row[0]));
    }
}
$req = Sql_Query(sprintf('select id from %s where uuid is NULL or uuid = ""', $GLOBALS['tables']['linktrack_forward']));
$num = Sql_Affected_Rows();
if ($num) {
    cl_output(s('Giving a UUID to %d links, this may take a while', $num));
    while ($row = Sql_Fetch_Row($req)) {
        Sql_query(sprintf('update %s set uuid = "%s" where id = %d', $GLOBALS['tables']['linktrack_forward'], (string) uuid::generate(4), $row[0]));
    }
}
