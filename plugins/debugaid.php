<?php
class debugaid extends phplistPlugin {
  public $name = "Develop Aids";
  public $coderoot = "devaids/";
  public $version= "0.0.1";
  public $authors= "Bas Ovink";
  
  public $topMenuLinks = array(
    'deletesent' => array('category' => 'develop'),
    'devemails' => array('category' => 'develop'),
    'devattributes' => array('category' => 'develop'),
    'allconfirmed' => array('category' => 'develop'),
    'createlists' => array('category' => 'develop'),
    'devmessages' => array('category' => 'develop'),
    'devclicksandviews' => array('category' => 'develop'),
    'devbounces' => array('category' => 'develop'),
    'resetstats' => array('category' => 'develop'),
  );
  
  public $pageTitles = array(
    "deletesent" => "Delete all table entries that mark messages as sent to a user",
    "devemails" => "Generate development emails",
    "devattributes" => "Generate development attributes",
    "allconfirmed" => "Mark all confirmed",
    "createlists" => "Generate Lists",
    "devmessages" => "Generate Messages",
    "devclicksandviews" => "Randomly fill clicks and views",
    "devbounces" => "Randomly bounce some messages",
    'resetstats' => 'Reset click statistics',
  );

  function debugaid() {
    parent::phplistplugin();
    $this->coderoot = dirname(__FILE__).'/devaids/';
  }
  
  function adminmenu() {
    return $this->pageTitles;
  }

  function processQueueStart () {
    //Sql_Query(sprintf('delete from phplist_usermessage where messageid = 2'));
    //Sql_Query(sprintf('update phplist_message set status = "submitted" where id = 2'));
    //Sql_Query(sprintf('delete from phplist_rssitem_user'));
    //Sql_Query(sprintf('delete from phplist_user_rss'));

    //cl_output('processQueueStart');
  }

}
