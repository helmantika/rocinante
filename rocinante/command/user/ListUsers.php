<?php

namespace rocinante\command\user;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/view/ViewHelper.php';

/**
 * ListUsers creates an HTML table that shows Rocinante users.
 */
class ListUsers extends \rocinante\controller\Command
{

   /**
    * The XML localization file root.
    * @var \SimpleXMLElement
    */
   private $l10n;

   /**
    * Creates an HTML table that shows Rocinante users.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "user/ListUsers")
      {
         // Sorting column index.
         $columns = array("Username", "Type", "FirstName", "Email", "Since");
         $index = \intval($this->request->getProperty('column'));
         $column = $index === -1 ? null : $columns[$index];
         
         $this->l10n = \rocinante\view\ViewHelper::instance()->getL10n();
         $tablel10n = $this->l10n->{"frontpage"}->{"tabs"}->{"admin"}->{"tabs"}->{"users"}->{"table"};
         $factory = new \rocinante\persistence\PersistenceFactory("User");
         $assembler = new \rocinante\persistence\DomainAssembler($factory);
         $userCounter = new \rocinante\mapper\identity\Identity(array('UserId' => 'i'), "User");
         $userCounter->count("UserId");
         $object = $assembler->find($userCounter)->first();
         $totalRows = \intval($object->get('COUNT(UserId)'));
         $rpp = \intval($this->request->getProperty('rpp'));
         $totalPages = (int) \ceil($totalRows / $rpp);
         $page = \intval($this->request->getProperty('page'));
         $userIdentity = new \rocinante\mapper\identity\Identity(array('Username' => 's',
                                                                       'FirstName' => 's',
                                                                       'Gender' => 's',
                                                                       'Email' => 's',
                                                                       'Type' => 's',
                                                                       'Since' => 's',
                                                                       'IsActive' => 'i'), "User");
         $userIdentity->field("IsActive")->eq(1);
         if ($column !== null)
         {
            if ($this->request->getProperty('asc') === "true")
            {
               $userIdentity->orderByAsc($column);
            }
            else
            {
               $userIdentity->orderByDesc($column);
            }
         }
         else
         {
            $userIdentity->orderByAsc("Username");
         }
         $userIdentity->limit(($page - 1) * $rpp, $rpp);
         $collection = $assembler->find($userIdentity);
         
         $html  = "<thead>\n";
         $html .= "<tr>\n";
         $html .= "<th style='width: 20%'>" . $tablel10n->{"username"} . "</th>\n";
         $html .= "<th style='width: 20%'>" . $tablel10n->{"type"} . "</th>\n";
         $html .= "<th style='width: 20%'>" . $tablel10n->{"name"} . "</th>\n";
         $html .= "<th style='width: 30%'>" . $tablel10n->{"email"} . "</th>\n";
         $html .= "<th style='width: 10%'>" . $tablel10n->{"since"} . "</th>\n";
         $html .= "</tr>\n";
         $html .= "</thead>\n";
         $html .= "<tbody>\n";
            
         $generator = $collection->getGenerator();
         foreach ($generator as $object)
         {
            $html .= "<tr>\n";
            $html .= "<td>" . $object->get('Username') . "</td>\n";
            $html .= "<td>" . $this->getCategoryCaption($object->get('Gender'), $object->get('Type')) . "</td>\n";
            $html .= "<td>" . $object->get('FirstName') . "</td>\n";
            $html .= "<td>" . $object->get('Email') . "</td>\n";
            $date = \DateTime::createFromFormat('Y-n-j', $object->get('Since'));
            $html .= "<td>" . $date->format($this->l10n->{"format"}->{"date-format"}) . "</td>\n";
            $html .= "</tr>\n";
         }
         
         $html .= "</tbody>\n";
         
         $response = array("page" => $page, "total" => $totalPages, "html" => $html);
         echo \json_encode($response);
      }
   }
   
   /**
    * Composes a localized string that contains the type of user with gender treating. This treating
    * doesn't make sense for English, but it does for many other languages.
    * @param string $gender MALE or FEMALE.
    * @param string $category ADMIN, ADVISOR, or TRANSLATOR.
    * @return string A localized string.
    */
   private function getCategoryCaption($gender, $category)
   {
      $label = $this->l10n->{"user-type"};
      $captions = array('ADMIN' => $gender === 'MALE' ? $label->{"male-admin"} : $label->{"female-admin"},
                        'ADVISOR' => $gender === 'MALE' ? $label->{"male-advisor"} : $label->{"female-advisor"},
                        'TRANSLATOR' => $gender === 'MALE' ? $label->{"male-translator"} : $label->{"female-translator"});
      return $captions[$category];
   }
}


            