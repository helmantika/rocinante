<?php

namespace rocinante\domain\model;

require_once 'rocinante/domain/model/Domain.php';

/**
 * Pupil is a domain class that stores the relationship between an advisor and a pupil.
 * @param int $relationid A univocal identifier for the relationship or null whether it is a new one.
 */
class Pupil extends Domain
{

   /**
    * Creates a new relationship advisor-pupil.
    */
   public function __construct($relationid = null)
   {
      $fields = array('RelationId' => 'i',
                      'AdvisorId' => 'i',
                      'PupilId' => 'i');
      parent::__construct($fields);
      if ($relationid !== null)
      {
         parent::set('RelationId', $relationid);
      }
   }

   /**
    * Sets a univocal ID for this object generated by the database.
    * @param int $value A number.
    */
   public function setLastInsertId($value)
   {
      if ($this->get('RelationId') === null)
      {
         $this->set('RelationId', $value);
      }
   }
}
