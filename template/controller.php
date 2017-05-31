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
/*{FIELDS_COLUMNS}*/             // ['label' => 'Titulo', 'field' => 'campo', 'display' => '[none/left/center/right]'],
      ];

      /* define orders fields. It's required to define at least one sort field.
       * Array of Array ('key' => [icon, label, order])
       */
/*
      $this->add_orderby('field name');
 */
      
      /* define filters:
       * Array of Array ('key' => [type, value, options[]])
       * type (select, checkbox)
       * Use the following functions for simplicity
       */
/*      
      $this->add_filter_select('key or fieldname', 'table name');
      $this->add_filter_checkbox('key', 'label', 'field name', invert[TRUE/FALSE]);
*/      
      // run standard entry point
      parent::__construct(__CLASS__, '/*{CONTROLLER}*/', '(...)');   // PUT HERE MENU OPTION WHERE INSTALL CONTROLLER 
   }

   protected function get_where() {
      $result = parent::get_where();

      /* Mount clause where based on the list of fields where you want to search */
      if ($this->get_value("query")) {
         $query = "LOWER('%" . $_REQUEST["query"] . "%')";
         
         // PUT HERE YOUR FIELDS WHERE APPLY FILTER
         
         /* EXAMPLE:
         $result .= " AND (field1 LIKE " . $query
             . " OR LOWER(field2) LIKE " . $query
             . " OR LOWER(field3) LIKE " . $query
             . " OR LOWER(field4) LIKE " . $query
             . ")";
          */
      }

      return $result;
   }
   
   protected function get_params() {
      $result = parent::get_params();                  
      return $result;
   }
   
   protected function private_core() {
      // configure delete action
      $this->allow_delete = $this->user->allow_delete_on(__CLASS__);

      // Prepare and get parameters
      parent::private_core();      
      
      // Load data with estructure data
      $where = $this->get_where();
      $order = $this->orderby[$this->selected_orderby]['order'];
      $model = new /*{MODEL}*/;
      $this->count = $model->all($this->cursor, $where, $order, $this->offset);
   }
}