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
class /*{MODEL}*/ extends fs_standard_model {

/*{FIELDS_DECLARATION}*/
   public function __construct($data = FALSE) {
      parent::__construct('/*{TABLE_NAME}*/');

/*{FIELDS_KEYS}*/
      if ($data) {
         $this->load_from_data($data);
      } else {
         $this->clear();
      }
   }

   /* ONLY NECESARY FOR CALCULATE FIELDS OR SPECIAL PROCESS
   public function __get($name) {
      return parent::__get($name);
      
      // Calculate Runtime Field Example. Can delete if don't needed
      $result = NULL;
      switch ($name) {
         case "calculate_field":
            $result = $this->field1 + $this->field2;
            break;
         
         default:
            $result = parent::__get($name);
            break;
      }
      return $result;
      // END
   }
    */

   /* ONLY NECESARY FOR CALCULATE FIELDS OR SPECIAL PROCESS
   public function __set($name, $value) {
      parent::__set($name, $value);
      
      // Calculate Database Field Example. Can delete if don't needed
      switch ($name) {
         case "field1":
         case "field2":
            $this->calculate = $this->field1 * $this->field2;
            break;

         default:
            break;
      }
      // END
   }
    */

   public function exists() {
      return parent::exists();
   }

   public function clear() {
/*{FIELDS_CLEAR}*/   }

   public function load_from_data($data) {
/*{FIELDS_LOAD}*/   }

   public function install() {
      return '';
   }

   protected function test() {
      /*
        PUT HERE MODEL DATA VALIDATIONS
        EXAMPLE:
        if($this->field_Numeric == 0) {
        $this->new_error_msg('Must be inform a code value');
        return FALSE;
        }
        return TRUE;
       */
      return parent::test();
   }

   protected function update() {
      $sql = 'UPDATE /*{TABLE_NAME}*/ SET '
              . '  field1 = value1'
              . ', fieldN = valueN'
              . ' WHERE field_key1 = key_value1;';

      return $this->db->exec($sql);
   }

   protected function insert() {
      $sql = 'INSERT INTO /*{TABLE_NAME}*/ '
              . '(/*{FIELDS_COMMASEPARATED}*/)'
              . ' VALUES '
              . '(...);';

      return $this->db->exec($sql);
   }

}
