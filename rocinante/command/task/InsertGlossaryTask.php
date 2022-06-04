<?php

namespace rocinante\command\task;

require_once 'rocinante/view/ViewHelper.php';
require_once 'rocinante/persistence/DomainAssembler.php';
require_once 'rocinante/mapper/identity/Identity.php';
require_once 'rocinante/command/SessionRegistry.php';

/**
 * InsertGlossaryTask creates a new task for updating strings that contain a modified glossary 
 * term.
 */
class InsertGlossaryTask
{

   /**
    * Sets the mandatory arguments for executing this command.
    * @param string $term A modified term.
    * @param \rocinante\controller\Request $request A request.
    */
   public function __construct($term, $request)
   {
      $this->term = $term;
      $this->request = $request;
   }

   /**
    * Creates a new task for updating strings that contain a modified glossary term.
    */
   public function execute()
   {
      if ($this->request->getProperty('cmd') === "translation/UpdateTerm")
      {
         // Get current user.
         $session = \rocinante\command\SessionRegistry::instance();
         $session->resume();
         $userid = $session->getUserId();

         // Build the query for Lang.
         $langFactory = new \rocinante\persistence\PersistenceFactory("Lang");
         $langAssembler = new \rocinante\persistence\DomainAssembler($langFactory);
         $langIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 'TextId' => 'i', 'SeqId' => 'i', 'En' => 's', 'IsTranslated' => 'i'), "Lang");
         $langIdentity->field('IsTranslated')->eq(1)->iand()->field('En')->regexp("'[[:<:]]{$this->term}[[:>:]]'");
         $langCollection = $langAssembler->find($langIdentity);
         $langSize = $langCollection->size();

         // Build the query for Lua.
         $luaFactory = new \rocinante\persistence\PersistenceFactory("Lua");
         $luaAssembler = new \rocinante\persistence\DomainAssembler($luaFactory);
         $luaIdentity = new \rocinante\mapper\identity\Identity(array('TableId' => 'i', 'TextId' => 'i', 'En' => 's', 'IsTranslated' => 'i'), "Lua");
         $luaIdentity->field('IsTranslated')->eq(1)->iand()->field('En')->regexp("'[[:<:]]{$this->term}[[:>:]]'");
         $luaCollection = $luaAssembler->find($luaIdentity);
         $luaSize = $luaCollection->size();
         
         // If there are affected rows then create a new task.
         if ($langSize + $luaSize > 0)
         {
            $taskFactory = new \rocinante\persistence\PersistenceFactory("Task");
            $taskAssembler = new \rocinante\persistence\DomainAssembler($taskFactory);
            $taskContentsFactory = new \rocinante\persistence\PersistenceFactory("TaskContents");
            $taskContentsAssembler = new \rocinante\persistence\DomainAssembler($taskContentsFactory);
         }
         
         // Create a new task for Lang.
         if ($langSize > 0)
         {
            // Insert a new task.
            $newTask = $taskFactory->getDomainFactory()->createObject(array());
            $newTask->set('UserId', $userid);
            $newTask->set('AssignerId', $userid);
            $newTask->set('Date', \date('Y-m-d'));
            $newTask->set('Type', "GLOSSARY");
            $newTask->set('Term', $this->term);
            $newTask->set('Size', $langSize);
            $taskAssembler->insert($newTask);
            $taskid = $newTask->get('TaskId');

            // Insert task contents.
            $langGenerator = $langCollection->getGenerator();
            foreach ($langGenerator as $object)
            {
               $newTaskContents = $taskContentsFactory->getDomainFactory()->createObject(array());
               $newTaskContents->set('TaskId', $taskid);
               $newTaskContents->set('TableId', $object->get('TableId'));
               $newTaskContents->set('TextId', $object->get('TextId'));
               $newTaskContents->set('SeqId', $object->get('SeqId'));
               $taskContentsAssembler->insert($newTaskContents);
            }
         }
         
         // Create a new task for Lua.
         if ($luaSize > 0)
         {
            // Insert a new task.
            $newTask = $taskFactory->getDomainFactory()->createObject(array());
            $newTask->set('TableId', 0);
            $newTask->set('UserId', $userid);
            $newTask->set('AssignerId', $userid);
            $newTask->set('Date', \date('Y-m-d'));
            $newTask->set('Type', "GLOSSARY");
            $newTask->set('Size', $luaSize);
            $taskAssembler->insert($newTask);
            $taskid = $newTask->get('TaskId');

            // Insert task contents.
            $luaGenerator = $luaCollection->getGenerator();
            foreach ($luaGenerator as $object)
            {
               $newTaskContents = $taskContentsFactory->getDomainFactory()->createObject(array());
               $newTaskContents->set('TaskId', $taskid);
               $newTaskContents->set('TableId', $object->get('TableId'));
               $newTaskContents->set('LuaTextId', $object->get('TextId'));
               $taskContentsAssembler->insert($newTaskContents);
            }
         }
      }
   }

}
