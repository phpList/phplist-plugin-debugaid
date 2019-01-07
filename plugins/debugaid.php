<?php
class debugaid extends phplistPlugin {
  public $name = "Develop Aids";
  public $coderoot = "debugaid/";
  public $version= "0.0.1";
  public $authors= "Bas Ovink";

    public $commandlinePluginPages = array(
        'resetpwd','sanitise', 'devclicksandviews'
    );
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
    'sanitise' => array('category' => 'develop'),
//      'dbadmin' => array('category'=> 'develop')
  );
  
  public $pageTitles = array(
    "deletesent" => "Delete all table entries that mark messages as sent to a user",
    "devemails" => "Generate development subscribers",
    "devattributes" => "Generate development attributes",
    "allconfirmed" => "Mark all confirmed",
    "createlists" => "Generate Lists",
    "devmessages" => "Generate Campaigns",
    "devclicksandviews" => "Randomly fill clicks and views",
    "devbounces" => "Randomly bounce some messages",
    'resetstats' => 'Reset click statistics',
    'sanitise' => 'Sanitise the database'
  );

  function debugaid() {
    parent::phplistplugin();
    $this->coderoot = dirname(__FILE__).'/debugaid/';
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
