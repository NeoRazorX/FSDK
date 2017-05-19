<?php
/*
 * This file is part of FacturaScripts
 * Copyright (C) 2013-2017  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of ______________
 *
 * @author ________
 */

require_model('/*{MODEL}*/.php');

class /*{CONTROLLER}*/ extends fs_standard_controller {

   public function __construct() {
      $this->icon = "fa-address-card";               // PUT HERE YOUR CUSTOM ICON
      $this->title = "/*{CONTROLLER}*/";             // PUT HERE YOUR CUSTOM TITLE

      /* define fields list:
       * Array of Array(label, field name, display [none, left, center, right]
       */
      $this->fields = [
/*{FIELDS_COLUMNS}*/ //          ['label' => 'Titulo', 'field' => 'campo', 'display' => '[none/left/center/right]'],
      ];

      /* define orders fields:
       * Array(title => field)
       */
      $this->orderby = [
/*{FIELDS_ORDERBY}*/ //          'Texto' => 'campo ASC',
      ];

      // define tables condition from extract data
      $this->from = "/*{CONTROLLER}*/";                // PUT HERE YOUR CUSTOM FROM CLAUSULE

      // run standard entry point
      parent::__construct(__CLASS__, '/*{CONTROLLER}*/', '(...)');   // PUT HERE MENU OPTION WHERE INSTALL CONTROLLER 
   }

   protected function get_where() {
      $result = parent::get_where();

      /* Mount clause where based on the list of fields where you want to search */
      if ($this->get_value("query")) {
         $query = "LOWER('%" . $_REQUEST["query"] . "%')";
         
         // PUT HERE YOUR FIELDS WHERE APPLY FILTER
         (...)
         
         /* EXAMPLE:
         $result .= " AND (t1.codcliente LIKE " . $query
             . " OR LOWER(t2.nombre) LIKE " . $query
             . " OR LOWER(t1.provincia) LIKE " . $query
             . " OR LOWER(t1.ciudad) LIKE " . $query
             . ")";
          */
      }

      return $result;
   }

   /* Custom fields list. Override parent get_fields()
    * Example:
   protected function get_fields() {
      $result = "";
  
      foreach ($this->fields as $item) {
         if ($result != "")
            $result .= ",";

         switch ($item['field']) {
            case "nombre":
            case "razonsocial":
               $result .= "t2." . $item['field'];
               break;

            default:
               $result .= "t1." . $item['field'];
               break;
         }
      }
      return $result;
   }
   */
   
   protected function private_core() {
      // configure delete action
      $this->allow_delete = $this->user->allow_delete_on(__CLASS__);

      parent::private_core();                // Load data with estructure data
   }

}