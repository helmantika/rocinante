<?php

namespace rocinante\mapper\identity;

require_once 'rocinante/mapper/identity/Identity.php';

/**
 * TaskContentsIdentity encapsulates the conditional aspect of a database query that selects 
 * information from TaskContents database table.
 */
class TaskContentsIdentity extends Identity
{

   /**
    * An identity object can start off empty, or with a field.
    * @param string $field An field name to test.
    */
   public function __construct($field = null)
   {
      parent::__construct( array('TaskId' => 'i',
                                 'TableId' => 'i',
                                 'TextId' => 'i',
                                 'LuaTextId' => 's',
                                 'SeqId' => 'i',
                                 'Done' => 'i'), "TaskContents", $field);
   }

}
