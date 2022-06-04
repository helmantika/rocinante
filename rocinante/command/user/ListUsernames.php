<?php

namespace rocinante\command\user;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/view/ViewHelper.php';

/**
 * ListUsernames creates an HTML table that shows usernames of Rocinante users.
 */
class ListUsernames extends \rocinante\controller\Command
{

   /**
    * The XML localization file root.
    * @var \SimpleXMLElement
    */
   private $l10n;

   /**
    * Creates an HTML table that shows Rocinante usernames.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "user/ListUsernames")
      {
         $this->l10n = \rocinante\view\ViewHelper::instance()->getL10n();
         $tablel10n = $this->l10n->{"frontpage"}->{"tabs"}->{"admin"}->{"tabs"}->{"users"}->{"table"};
         $factory = new \rocinante\persistence\PersistenceFactory("User");
         $assembler = new \rocinante\persistence\DomainAssembler($factory);
         $userIdentity = new \rocinante\mapper\identity\Identity(array('Username' => 's', 'IsActive' => 'i'), "User");
         $userIdentity->field("IsActive")->eq(1)->orderByAsc("Username");
         $collection = $assembler->find($userIdentity);
         
         $html  = "<thead>\n";
         $html .= "<tr>\n";
         $html .= "<th>" . $tablel10n->{"username"} . "</th>\n";
         $html .= "</tr>\n";
         $html .= "</thead>\n";
         $html .= "<tbody>\n";
            
         $generator = $collection->getGenerator();
         foreach ($generator as $object)
         {
            $html .= "<tr>\n";
            $html .= "<td>" . $object->get('Username') . "</td>\n";
            $html .= "</tr>\n";
         }
         
         $html .= "</tbody>\n";
         
         $response = array("html" => $html);
         echo \json_encode($response);
      }
   }
   
}


            