<?php

namespace rocinante\command\login;

/**
 * Subject defines the common interface for Rocinante and Proxy so that a Proxy can be used
 * anywhere Rocinante is expected.
 */
interface Subject
{

   /**
    * Client submits request through this interface to the Proxy, but access to Rocinante is
    * possible only if a request first goes to the Proxy.
    */
   public function request();
}
