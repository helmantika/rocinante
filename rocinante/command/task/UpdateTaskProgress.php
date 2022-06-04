<?php

namespace rocinante\command\task;

require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';

/**
 * UpdateTaskProgress updates progress of a given task by means of a lang string ID.
 */
class UpdateTaskProgress
{
   /**
    * The TaskContents persistence factory.
    * @var \rocinante\persistence\PersistenceFactory 
    */
   private $taskContentsFactory = null;
   
   /**
    * The TaskContents assembler.
    * @var \rocinante\persistence\DomainAssembler 
    */
   private $taskContentsAssembler = null;
   
   /**
    * The Task persistence factory.
    * @var \rocinante\persistence\PersistenceFactory 
    */
   private $taskFactory = null;
   
   /**
    * The Task assembler.
    * @var \rocinante\persistence\DomainAssembler 
    */
   private $taskAssembler = null;
   
   /**
    * Updates the number of string translated.
    * @param int $tableid A table ID.
    * @param mixed $textid A number for lang tables or a string for Lua table.
    * @param int $seqid A sequence ID.
    * @param string $action TRANSLATION, REVISION, or SPECIAL (i.e. GLOSSARY/UPDATING).
    * @param bool addition Defines whether the progress is bigger or smaller.
    */
   public function execute($tableid, $textid, $seqid, $action, $addition = true)
   {
      // Find the language string.
      $this->taskContentsFactory = new \rocinante\persistence\PersistenceFactory("TaskContents");
      $this->taskContentsAssembler = new \rocinante\persistence\DomainAssembler($this->taskContentsFactory);
      $taskContentsIdentity = $this->taskContentsFactory->getIdentity();
      $taskContentsIdentity->field("TableId")->eq($tableid)->iand()->field("SeqId")->eq($seqid)->iand();
      if (\is_numeric($textid))
      {
         $taskContentsIdentity->field("TextId")->eq($textid);
      }
      else
      {
         $taskContentsIdentity->field("LuaTextId")->eq($textid);
      }
      $taskContentsCollection = $this->taskContentsAssembler->find($taskContentsIdentity);
      $generator = $taskContentsCollection->getGenerator();
      foreach ($generator as $taskContents)
      {
         // Find the task the string belongs to.
         $this->taskFactory = new \rocinante\persistence\PersistenceFactory("Task");
         $this->taskAssembler = new \rocinante\persistence\DomainAssembler($this->taskFactory);
         $taskIdentity = $this->taskFactory->getIdentity();
         $taskIdentity->field("TaskId")->eq($taskContents->get('TaskId'));
         $taskCollection = $this->taskAssembler->find($taskIdentity);
         $task = $taskCollection->first();
         if ($task !== null)
         {
            if ($action === "TRANSLATION" || $action === "REVISION" || $action === "SPECIAL")
            {
               $taskContents->set('Done', $addition);
               $this->taskContentsAssembler->update($taskContents);
               
               // Update task progress.
               $finished = $this->readFinishedStrings($taskContents->get('TaskId'));
               $progress = $finished * 100.0 / $task->get('Size');
               $task->set('Progress', $progress);
               $this->taskAssembler->update($task);
            }
         }
      }
   }
   
   /**
    * Returns how many string of a given task are finished.
    * @param int $taskid A task ID.
    * @return int A number of finished strings.
    */
   private function readFinishedStrings($taskid)
   {
      $taskContentsIdentity = new \rocinante\mapper\identity\Identity(array('TaskId' => 'i', 'Done' => 'i'), "TaskContents");
      $taskContentsIdentity->field('TaskId')->eq($taskid)->iand()->field('Done')->eq(1);
      $taskContentsCollection = $this->taskContentsAssembler->find($taskContentsIdentity);
      return $taskContentsCollection->size();
   }
}
