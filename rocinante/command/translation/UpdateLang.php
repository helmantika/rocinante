<?php

namespace rocinante\command\translation;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/command/SessionRegistry.php';
require_once 'rocinante/command/translation/UpdateStats.php';
require_once 'rocinante/command/translation/UpdateStatus.php';
require_once 'rocinante/command/translation/UpdateEsoTablePercentages.php';
require_once 'rocinante/command/task/UpdateTaskProgress.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/command/translation/MetaTable.php';

/**
 * UpdateLang updates a translation and its status.
 */
class UpdateLang extends \rocinante\controller\Command
{
   
   use \rocinante\command\translation\MetaTable
   {
      readTables as protected;
   }
   
   /**
    * Specifies how to validate fields coming from a request.
    * @var array
    */
   private $validation = array('tableid' => array('IsNumeric'),
                               'textid'  => array('IsNonEmpty'),
                               'seqid'   => array('IsNumeric'));
   
   /**
    * Changes a translation string and its status.
    */
   public function doExecute()
   {
      // Resumes the current session.
      $session = \rocinante\command\SessionRegistry::instance();
      $session->resume();
      
      $result = false;
      if ($this->request->getProperty('cmd') === "translation/UpdateLang")
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
               $decoded1 = \str_replace(array("<strong>", "</strong>"), "", $textValue);
               $decoded2 = \str_replace(array("<div><br></div>", "<div><br /></div>", "<div><br/></div>"), "<br>", $decoded1);
               $decoded3 = \str_replace(array("<br>", "<br />", "<br/>"), "\n", \str_replace("\n", "", $decoded2));
               $decoded4 = \str_replace(array("<div>", "</div>"), array("\n", ""), $decoded3);
               $decoded5 = \str_replace("&nbsp;", " ", \htmlspecialchars_decode($decoded4, ENT_COMPAT | ENT_HTML5));
               $text = \trim(\preg_replace(array('/[ ]+/', '/\n /'), array(" ", "\n"), $decoded5));
               if ($text === "")
               {
                  $text = "NULL";
               }
            }
            $isUpdatedValue = $this->request->getProperty('isUpdated')['value'];
            $isUpdated = (\strlen($isUpdatedValue) === 0 ? null : \intval($isUpdatedValue));
            $isTranslatedValue = $this->request->getProperty('isTranslated')['value'];
            $isTranslated = (\strlen($isTranslatedValue) === 0 ? null : \intval($isTranslatedValue));
            $isRevisedValue = $this->request->getProperty('isRevised')['value'];
            $isRevised = (\strlen($isRevisedValue) === 0 ? null : \intval($isRevisedValue));
            $isLockedValue = $this->request->getProperty('isLocked')['value'];
            $isLocked = (\strlen($isLockedValue) === 0 ? null : \intval($isLockedValue));
            $isDisputedValue = $this->request->getProperty('isDisputed')['value'];
            $isDisputed = (\strlen($isDisputedValue) === 0 ? null : \intval($isDisputedValue));
            
            if ($tableid === 0 && !is_string($textid))
            {
               throw new Exception("Illegal data");
            }
            
            if ($tableid === 0)
            {
               $result = $this->changeLuaStringStatus(\str_replace(' ', '_', $textid), $text, $isUpdated, $isTranslated, $isRevised, $isLocked, $isDisputed);
            }
            else
            {
               $result = $this->changeLangStringStatus($tableid, \intval($textid), $seqid, $text, $isUpdated, $isTranslated, $isRevised, $isLocked, $isDisputed);
            }
         }
      }
      
      echo $text === "NULL" || $text === "" ? json_encode("OK") : json_encode(\nl2br(\htmlspecialchars($text, ENT_COMPAT | ENT_HTML5, "UTF-8")));      
   }
   
   /**
    * Changes translation string editing status of the Lua table.
    * @param string $textid Text ID.
    * @param bool $isUpdated Whether a text has been updated because of glossary task or updating task.
    * @param bool $isTranslated Whether a text has been translated or not.
    * @param bool $isRevised Whether a text has been revised or not.
    * @param bool $isLocked Whether a text has been locked or not.
    * @param bool $isDisputed Whether a text has been locked because of a dispute or not.
    */
   private function changeLuaStringStatus($textid, $text, $isUpdated, $isTranslated, $isRevised, $isLocked, $isDisputed)
   {
      $stats = array("translated" => 0, "revised" => 0, "updated" => 0);
      $factory = new \rocinante\persistence\PersistenceFactory("Lua");
      $assembler = new \rocinante\persistence\DomainAssembler($factory);
      $luaIdentity = $factory->getIdentity();
      $luaIdentity->field("TextId")->eq($textid);
      $collection = $assembler->find($luaIdentity);
      $table = $collection->first();
      if ($table !== null)
      {
         $table->set('Es', $text);
         if ($isTranslated !== null)
         {
            $wasTranslated = $table->get('IsTranslated');
            $table->set('IsTranslated', $isTranslated);
            if ($wasTranslated === 0 && $isTranslated === 1)
            {
               $stats["translated"] = 1;
            }
            else if ($wasTranslated === 1 && $isTranslated === 0)
            {
               $stats["translated"] = -1;
            }
         }
         if ($isRevised !== null)
         {
            $wasRevised = $table->get('IsRevised');
            $table->set('IsRevised', $isRevised);
            if ($wasRevised === 0 && $isRevised === 1)
            {
               $stats["revised"] = 1;
            }
            else if ($wasRevised === 1 && $isRevised === 0)
            {
               $stats["revised"] = -1;
            }
         }
         if ($isLocked !== null)
         {
            $table->set('IsLocked', $isLocked);
         }
         if ($isDisputed !== null)
         {
            $table->set('IsDisputed', $isDisputed);
         }
         $result = $assembler->update($table);
      }
      
      // Update user stats.
      $updateStats = new \rocinante\command\translation\UpdateStats();
      $updateStats->execute($stats["translated"], $stats["revised"], $stats["updated"]);
      
      // Update ESO table status.
      $request = new \rocinante\controller\Request();
      $request->setProperty('cmd', 'translation/UpdateEsoTablePercentages');
      $request->setProperty('tableid', 0);
      $updateEsoTable = new \rocinante\command\translation\UpdateEsoTablePercentages();
      $updateEsoTable->execute($request);
      
      // Update Rocinante status.
      $updateStatus = new \rocinante\command\translation\UpdateStatus();
      $updateStatus->execute($stats["translated"]);
      
      // Update task progress.
      $taskProgress = new \rocinante\command\task\UpdateTaskProgress();
      if ($isUpdated !== null)
      {
         $action = "SPECIAL";
         $addition = $isUpdated === 1;
         $taskProgress->execute(0, $textid, 0, $action, $addition);
      }
      if ($isTranslated !== null && $stats["translated"] !== 0)
      {
         $action = "TRANSLATION";
         $addition = $stats["translated"] === 1;
         $taskProgress->execute(0, $textid, 0, $action, $addition);
      }
      if ($isRevised !== null && $stats["revised"] !== 0)
      {
         $action = "REVISION";
         $addition = $stats["revised"] === 1;
         $taskProgress->execute(0, $textid, 0, $action, $addition);
      }
      
      return $result;
   }
   
   /**
    * Changes translation string editing status of a Lang table.
    * @param int $tableid Table ID.
    * @param int $textid Text ID.
    * @param int $seqid Sequence ID.
    * @param bool $isUpdated Whether a text has been updated because of glossary task or updating task.
    * @param bool $isTranslated Whether a text has been translated or not.
    * @param bool $isRevised Whether a text has been revised or not.
    * @param bool $isLocked Whether a text has been locked or not.
    * @param bool $isDisputed Whether a text has been locked because of a dispute or not.
    */
   private function changeLangStringStatus($tableid, $textid, $seqid, $text, $isUpdated, $isTranslated, $isRevised, $isLocked, $isDisputed)
   {
      $stats = array("translated" => 0, "revised" => 0, "updated" => 0);
      $factory = new \rocinante\persistence\PersistenceFactory("Lang");
      $assembler = new \rocinante\persistence\DomainAssembler($factory);
      $langIdentity = $factory->getIdentity();
      $langIdentity->field("TableId")->eq($tableid)->iand()->field("TextId")->eq($textid)->iand()->field("SeqId")->eq($seqid);
      $collection = $assembler->find($langIdentity);
      $table = $collection->first();
      if ($table !== null)
      {
         $table->set('Es', $text);
         if ($isTranslated !== null)
         {
            $wasTranslated = $table->get('IsTranslated');
            $table->set('IsTranslated', $isTranslated);
            if ($wasTranslated === 0 && $isTranslated === 1)
            {
               $stats["translated"] = 1;
            }
            else if ($wasTranslated === 1 && $isTranslated === 0)
            {
               $stats["translated"] = -1;
            }
         }
         if ($isRevised !== null)
         {
            $wasRevised = $table->get('IsRevised');
            $table->set('IsRevised', $isRevised);
            if ($wasRevised === 0 && $isRevised === 1)
            {
               $stats["revised"] = 1;
            }
            else if ($wasRevised === 1 && $isRevised === 0)
            {
               $stats["revised"] = -1;
            }
         }
         if ($isLocked !== null)
         {
            $table->set('IsLocked', $isLocked);
         }
         if ($isDisputed !== null)
         {
            $table->set('IsDisputed', $isDisputed);
         }
         $result = $assembler->update($table);
      }
      
      // Update user stats.
      $updateStats = new \rocinante\command\translation\UpdateStats();
      $updateStats->execute($stats["translated"], $stats["revised"], $stats["updated"]);
      
      // Update ESO table status.
      $request = new \rocinante\controller\Request();
      $request->setProperty('cmd', 'translation/UpdateEsoTablePercentages');
      $metatableid = $this->getMetaTable($tableid);
      $request->setProperty('tableid', $metatableid !== 0 ? $metatableid : $tableid);
      $updateEsoTable = new \rocinante\command\translation\UpdateEsoTablePercentages();
      $updateEsoTable->execute($request);
      
      // Update Rocinante status.
      $updateStatus = new \rocinante\command\translation\UpdateStatus();
      $updateStatus->execute($stats["translated"]);
      
      // Update task progress.
      $taskProgress = new \rocinante\command\task\UpdateTaskProgress();
      if ($isUpdated !== null)
      {
         $action = "SPECIAL";
         $addition = $isUpdated === 1;
         $taskProgress->execute($tableid, $textid, $seqid, $action, $addition);
      }
      if ($isTranslated !== null && $stats["translated"] !== 0)
      {
         $action = "TRANSLATION";
         $addition = $stats["translated"] === 1;
         $taskProgress->execute($tableid, $textid, $seqid, $action, $addition);
      }
      if ($isRevised !== null && $stats["revised"] !== 0)
      {
         $action = "REVISION";
         $addition = $stats["revised"] === 1;
         $taskProgress->execute($tableid, $textid, $seqid, $action, $addition);
      }
      
      return $result;
   }
}
