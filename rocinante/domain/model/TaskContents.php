<?php

namespace rocinante\domain\model;

require_once 'rocinante/domain/model/Domain.php';

/**
 * TaskContents is a domain class that stores a translation unit bound to a task.
 */
class TaskContents extends Domain
{

   /**
    * Creates a new translation unit for a task.
    */
   public function __construct()
   {
      $fields = array('TaskId'    => 'i',
                      'TableId'   => 'i',
                      'TextId'    => 'i',
                      'LuaTextId' => 's',
                      'SeqId'     => 'i',
                      'Done'      => 'i');
      parent::__construct($fields);
   }

}
