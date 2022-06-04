<?php

namespace rocinante\command\task;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/view/ViewHelper.php';

/**
 * SelectTaskStatus makes a report when a task is going to be deleted. It shows task progress and
 * finds out how many task string are revised.
 */
class SelectTaskStatus extends \rocinante\controller\Command
{

   /**
    * Specifies how to validate fields coming from a request.
    * @var array
    */
   private $validation = array('taskid' => array('IsNumeric'));

   /**
    * The XML localization file root.
    * @var \SimpleXMLElement
    */
   private $l10n;

   /**
    * Creates an HTML table that shows strings to translate  for a given task.
    */
   public function doExecute()
   {
      if ($this->request->getProperty('cmd') === "task/SelectTaskStatus")
      {
         // Validate request fields.
         $message = \rocinante\command\Validation::validate($this->validation, $this->request);
         if (empty($message))
         {
            $this->l10n = \rocinante\view\ViewHelper::instance()->getL10n();
            $decimalMark = (string) $this->l10n->format->{"decimal-mark"};
            $thousandsMark = (string) $this->l10n->format->{"thousands-mark"};
            $taskid = \intval($this->request->getProperty('taskid')['value']);

            $taskFactory = new \rocinante\persistence\PersistenceFactory("Task");
            $taskAssembler = new \rocinante\persistence\DomainAssembler($taskFactory);
            $identity = new \rocinante\mapper\identity\Identity(array('TaskId' => 'i', 'TableId' => 'i', 'Type' => 's', 'Size' => 'i', 'Progress' => 'd'), "Task");
            $identity->field("TaskId")->eq($taskid);
            $task = $taskAssembler->find($identity)->first();
            $tableid = \intval($task->get('TableId'));
            $type = $task->get('Type');
            $size = $task->get('Size');
            $progress = $task->get('Progress');

            if ($tableid == 0)
            {
               $count = $this->countRevisedStringFromLua($taskid);
            } else
            {
               $count = $this->countRevisedStringFromLang($taskid);
            }

            // Build the response.
            if ($type === "TRANSLATION" || $type === "REVISION")
            {
               $message .= \sprintf((string) $this->l10n->{"dialog"}->{"task"}->{"task-progress"}, $taskid, \number_format($progress, 2, $decimalMark, $thousandsMark));
               if ($count === $size)
               {
                  $message .= (string) $this->l10n->{"dialog"}->{"task"}->{"string-revised-all"};
               }
               else if ($count === 0)
               {
                  $message .= (string) $this->l10n->{"dialog"}->{"task"}->{"string-revised-zero"};
               }
               else if ($count === 1)
               {
                  $message .= \sprintf((string) $this->l10n->{"dialog"}->{"task"}->{"string-revised-one"}, \number_format($size, 0, $decimalMark, $thousandsMark));
               }
               else
               {
                  $message .= \sprintf((string) $this->l10n->{"dialog"}->{"task"}->{"string-revised-many"}, \number_format($size, 0, $decimalMark, $thousandsMark), \number_format($count, 0, $decimalMark, $thousandsMark));
               }
               $message .= (string) $this->l10n->{"dialog"}->{"task"}->{"confirm-deletion"};
            }
            else
            {
               $message .= \sprintf((string) $this->l10n->{"dialog"}->{"task"}->{"confirm-deletion-simple"}, $taskid, \number_format($progress, 2, $decimalMark, $thousandsMark));
            }

            $response = array("message" => $message);
            echo \json_encode($response);
         }
      }
   }

   /**
    * Counts the number of revised strings, that are bound to given task, from Lua table.
    * @param int $taskid A task ID.
    * @return int The number of revised strings.
    */
   private function countRevisedStringFromLua($taskid)
   {
      $luaFactory = new \rocinante\persistence\PersistenceFactory("Lua");
      $assembler = new \rocinante\persistence\DomainAssembler($luaFactory);

      $taskContentsFactory = new \rocinante\persistence\PersistenceFactory("TaskContents");
      $taskContentsIdentity = $taskContentsFactory->getIdentity();
      $taskContentsIdentity->field("TaskId")->eq($taskid);

      $luaIdentity = $luaFactory->getIdentity();
      $luaIdentity->field("IsRevised")->eq(1);
      $luaIdentity->join($taskContentsIdentity, "TextId", "LuaTextId");

      $collection = $assembler->find($luaIdentity);
      return $collection->size();
   }

   /**
    * Counts the number of revised strings, that are bound to given task, from a table or metatable.
    * @param int $taskid A task ID.
    * @return int The number of revised strings.
    */
   private function countRevisedStringFromLang($taskid)
   {
      $langFactory = new \rocinante\persistence\PersistenceFactory("Lang");
      $assembler = new \rocinante\persistence\DomainAssembler($langFactory);

      $taskContentsFactory = new \rocinante\persistence\PersistenceFactory("TaskContents");
      $taskContentsIdentity = $taskContentsFactory->getIdentity();
      $taskContentsIdentity->field("TaskId")->eq($taskid);

      $fields = array("TableId", "TextId", "SeqId");
      $langIdentity = $langFactory->getIdentity();
      $langIdentity->field("IsRevised")->eq(1);
      $langIdentity->join($taskContentsIdentity, $fields, $fields);

      $collection = $assembler->find($langIdentity);
      return $collection->size();
   }

}
