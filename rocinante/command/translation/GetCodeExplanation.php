<?php

namespace rocinante\command\translation;

require_once 'rocinante/controller/Command.php';
require_once 'rocinante/command/Validation.php';
require_once 'rocinante/view/ViewHelper.php';

/**
 * Description of GetCodeExplanation
 *
 * @author jorge
 */
class GetCodeExplanation extends \rocinante\controller\Command
{
   /**
    * Specifies how to validate fields coming from a request.
    * @var array
    */
   private $validation = array('code' => array('IsNonEmpty'));
          
   /**
    * Reads a file for a given code and generates a simple HTML page.
    * @return string HTML code.
    */
   function doExecute()
   {
      if ($this->request->getProperty('cmd') === "translation/GetCodeExplanation")
      {
         // Validate request fields.
         $message = \rocinante\command\Validation::validate($this->validation, $this->request);
         if (empty($message))
         {
            $helper = \rocinante\view\ViewHelper::instance();
            $codes = $helper->readCodesFile();
            
            $code = html_entity_decode($this->request->getProperty('code')['value']);
            
            $matches = null;
            if (\preg_match("/<<(\\d+)>>/", $code, $matches) === 1)
            {
               $explanation = $codes['number'];
            }
            else if (\preg_match("/<<(\\w\\w?):\\d+>>/", $code, $matches) === 1)
            {
               $explanation = $codes[$matches[1]];
            }
            else if (\preg_match("/<<(\\w+)\{\\w+\/\\w+\}>>/", $code, $matches) === 1)
            {
               $explanation = $codes[$matches[1]];
            }
            else if (\preg_match("/<<(\\d+)\{\\w+\/\\w+\}>>/", $code, $matches) === 1)
            {
               $explanation = $codes['number-gender'];
            }
            else if (\preg_match("/<<(\\d+)\[(.*)\/(.*)(\/.*)?\]>>/", $code, $matches) === 1)
            {
               if (\strpos($matches[2], "/") === false)
               {
                  $explanation = $codes['numeric-two'];
               }
               else
               {
                  $explanation = $codes['numeric-three'];
               }
            }
            else if (\preg_match("/(\^.+$)/", $code, $matches) === 1)
            {
               $explanation = $codes['suffix'];
            }
            else
            {
               $explanation = "<p>missing code</p>";
            }
            echo $explanation;
         }
      }
   }
}
