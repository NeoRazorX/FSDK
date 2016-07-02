<?php

/*
 * @author XXXX      xxx@xxx.xx
 * @copyright 2016, XXXX. All Rights Reserved.
 */


/**
 * Description of holamundo
 *
 * @author xxx
 */
class holamundo extends fs_controller
{
   public $text;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'holamundo', 'admin');
   }
   
   protected function private_core()
   {
      $this->text = 'hola mundo';
   }
}
