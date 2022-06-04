<?php

namespace rocinante\command;

require_once 'rocinante/validator/ValidatorResolver.php';

/**
 * Validation checks fields and values of a request basing on defined validators.
 */
class Validation
{

   /**
    * Validates a request content.
    * @param array $fields Field names and validators that check field correctness.
    * @param \rocinante\controller\Request $request A request.
    * @return string A message with found errors, or null whether everything is right.
    */
   public static function validate(array $fields, \rocinante\controller\Request $request)
   {
      $resolver = new \rocinante\validator\ValidatorResolver();
      $message = null;

      foreach ($fields as $field => $validators)
      {
         $data = $request->getProperty($field);
         foreach ($validators as $name)
         {
            $validator = $resolver->getValidator($name);
            if ($validator !== null)
            {
               // Extract validator name. It can have a param.
               $param = \preg_filter('/([A-Za-z]+)(\(([0-9]+)\))?/', '$3', $name);
               if ($param !== null && !empty($param))
               {
                  $validator->setParam(\intval($param));
               }
               // Validate.
               $result = $validator->validate($data['field'], $data['value']);
               if ($result !== true)
               {
                  $message .= "> " . $result . "<br />";
               }
            } 
            else
            {
               $message = "Critical error: $validator is not a validator";
               break 2;
            }
         }
      }

      return $message;
   }

}
