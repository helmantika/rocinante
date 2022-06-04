<?php 

namespace rocinante\command\translation;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * UpdateEsoTable changes description and type of an ESO table.
 */
class UpdateEsoTable extends \rocinante\controller\Command
{

   /**
    * Specifies how to validate fields coming from a request.
    * @var array
    */
   private $validation = array('tablename'   => array('IsNonEmpty'),
                               'description' => array('IsNonEmpty', 'IsMaxLength(60)'),
                               'type'        => array('IsNumeric'));

   /**
    * The XML localization file root.
    * @var \SimpleXMLElement
    */
   private $l10n = null;

   /**
    * The persistence factory.
    * @var \rocinante\persistence\PersistenceFactory
    */
   private $factory = null;

   /**
    * The domain object assembler.
    * @var \rocinante\persistence\DomainAssembler
    */
   private $assembler = null;

   /**
    * Changes description of an ESO table.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "translation/UpdateEsoTable")
      {
         $this->l10n = \rocinante\view\ViewHelper::instance()->getL10n();

         // Validate request fields.
         $message = \rocinante\command\Validation::validate($this->validation, $this->request);

         // Retrieve the table by means of its name.
         $tablename = $this->request->getProperty('tablename')['value'];
         if (empty($message))
         {
            $this->factory = new \rocinante\persistence\PersistenceFactory("EsoTable");
            $this->assembler = new \rocinante\persistence\DomainAssembler($this->factory);
            $tableid = $this->readTableId($tablename);
            $esoTableIdentity = $this->factory->getIdentity();
            $esoTableIdentity->field("TableId")->eq($tableid);
            $collection = $this->assembler->find($esoTableIdentity);
            $table = $collection->first();
            if ($table !== null)
            {
               $sqlm = \rocinante\persistence\SqlManager::instance();
               $table->set('Description', $sqlm->escape($this->request->getProperty('description')['value']));
               $table->set('TypeId', \intval($sqlm->escape($this->request->getProperty('type')['value'])));
               $this->assembler->update($table);
            }
         }

         // Make a response.
         $array = null;
         if (empty($message))
         {
            $array["result"] = "ok";
            $array["html"] = \sprintf((string) $this->l10n->{"dialog"}->{"esotable"}->{"success-modification"}, $tablename);
         }
         else
         {
            $array["result"] = "null";
            $array["html"] = $this->l10n->{"validation"}->{"error"} . "<br />" . $message;
         }
         echo \json_encode($array);
      }
   }

   /**
    * Retrieves a table ID by means of its name.
    * @param string $tablename A name of an ESO table.
    * @return integer A number between 0 and 0xffffffff.
    */
   private function readTableId($tablename)
   {
      $tableId = null;
      if ($tablename === $this->l10n->{"frontpage"}->{"tabs"}->{"master-table"}->{"lua-table-id"})
      {
         $tableId = 0;
      }
      else
      {
         $tabletype = \substr($tablename, 0, 4);
         $tablenumber = \substr($tablename, 4);
         if ($tabletype === "meta")
         {
            $tableId = \intval($tablenumber);
         }
         else
         {
            $esoTableIdentity = $this->factory->getIdentity();
            $esoTableIdentity->field("Number")->eq(\intval($tablenumber));
            $collection = $this->assembler->find($esoTableIdentity);
            $table = $collection->first();
            if ($table !== null)
            {
               $tableId = $table->get("TableId");
            }
         }
      }
      return $tableId;
   }
}
