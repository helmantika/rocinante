<?php

namespace rocinante\command\translation;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * UpdateLang updates a translation comment.
 */
class UpdateLangNotes extends \rocinante\controller\Command
{
   /**
    * Specifies how to validate fields coming from a request.
    * @var array
    */
   private $validation = array('tableid' => array('IsNumeric'),
                               'textid'  => array('IsNonEmpty'),
                               'seqid'   => array('IsNumeric'));
   
   /**
    * Changes the comment of a translation string.
    */
   public function doExecute()
   {
      $result = false;
      
      if ($this->request->getProperty('cmd') === "translation/UpdateLangNotes")
      {
         // Validate request fields.
         $message = \rocinante\command\Validation::validate($this->validation, $this->request);
         if (empty($message))
         {
            $tableid = \intval($this->request->getProperty('tableid')['value']);
            $textid = $this->request->getProperty('textid')['value'];
            $seqid = \intval($this->request->getProperty('seqid')['value']);
            $textValue = $this->request->getProperty('text')['value'];
            $text = "NULL";
            if (!empty($textValue))
            {
               $decoded1 = \str_replace(array("<div><br></div>", "<div><br /></div>", "<div><br/></div>"), "<br>", $textValue);
               $decoded2 = \str_replace(array("<br>", "<br />", "<br/>"), "\n", \str_replace("\n", "", $decoded1));
               $decoded3 = \str_replace(array("<div>", "</div>"), array("\n", ""), $decoded2);
               $text = \str_replace("&nbsp;", "", \htmlspecialchars_decode($decoded3, ENT_COMPAT | ENT_HTML5));
            }
            
            if ($tableid === 0 && !is_string($textid))
            {
               throw new Exception("Illegal data");
            }
            
            if ($tableid === 0)
            {
               $result = $this->changeLuaEditing(\str_replace(" ", "_", $textid), $text);
            }
            else
            {
               $result = $this->changeLangEditing($tableid, \intval($textid), $seqid, $text);
            }
         }
      }
      
      echo json_encode("OK");
   }
   
   /**
    * Changes translation string editing status of the Lua table.
    * @param string $textid Text ID.
    * @param string $text A comment.
    */
   private function changeLuaEditing($textid, $text)
   {
      $factory = new \rocinante\persistence\PersistenceFactory("Lua");
      $assembler = new \rocinante\persistence\DomainAssembler($factory);
      $luaIdentity = $factory->getIdentity();
      $luaIdentity->field("TextId")->eq($textid);
      $collection = $assembler->find($luaIdentity);
      $table = $collection->first();
      if ($table !== null)
      {
         $table->set('Notes', $text);
         $result = $assembler->update($table);
      }
      
      return $result;
   }
   
   /**
    * Changes translation string editing status of a Lang table.
    * @param int $tableid Table ID.
    * @param int $textid Text ID.
    * @param int $seqid Sequence ID.
    * @param string $text A comment.
    */
   private function changeLangEditing($tableid, $textid, $seqid, $text)
   {
      $factory = new \rocinante\persistence\PersistenceFactory("Lang");
      $assembler = new \rocinante\persistence\DomainAssembler($factory);
      $langIdentity = $factory->getIdentity();
      $langIdentity->field("TableId")->eq($tableid)->iand()->field("TextId")->eq($textid)->iand()->field("SeqId")->eq($seqid);
      $collection = $assembler->find($langIdentity);
      $table = $collection->first();
      if ($table !== null)
      {
         $table->set('Notes', $text);
         $result = $assembler->update($table);
      }
      
      return $result;
   }
}
