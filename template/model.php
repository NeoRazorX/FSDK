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
   /* -------------
    * P R I V A T E
    * ------------- */

/*{FIELDS_DECLARATION}*/

   /* -----------------
    * P R O T E C T E D
    * ----------------- */

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
         . 'WHERE field_key1 = key_value1;';

      return $this->db->exec($sql);
   }

   protected function insert() {
      $sql = 'INSERT INTO /*{TABLE_NAME}*/ (/*{FIELDS_COMMASEPARATED}*/) VALUES (...);';
      return $this->db->exec($sql);
   }

   /* -----------
    * P U B L I C
    * ----------- */

   public function __construct($data = FALSE) {
      parent::__construct('/*{TABLE_NAME}*/');

/*{FIELDS_KEYS}*/
      if ($data)
         $this->load_from_data($data);
      else
         $this->clear();
   }

   public function __get($name) {
      switch ($name) {
         // Calculate Field Example. Can delete if don't needed         
         case "calculate_field_name": {
            $result = $this->field1 + $this->field2;
            break;
         }

         default: {
            if (property_exists(__CLASS__, $name))
               $result = $this->$name;
            else
               $result = NULL;
            break;
         }
      }
      return $result;
   }

   public function __set($name, $value) {
      switch ($name) {
         // Calculate Database Field Example. Can delete if don't needed
         case "field1":
         case "field2": {
            // $this->calculate = $this->field1 * $this->field2;
            break;
         }

         default: {
            if (property_exists(__CLASS__, $name))
               $this->$name = $value;
         }
      }
   }

   public function exists() {
      return parent::exists();
   }

   public function clear() {
/*{FIELDS_CLEAR}*/
   }

   public function load_from_data($data) {
/*{FIELDS_LOAD}*/
   }

   public function install() {
      return '';
   }

   public function all(&$records, $where, $order, $offset = 0, $limit = FS_ITEM_LIMIT) {
      // MODIFY SQL COUNT for count all data when there are data from multiples tables
      // Count total records
      $sql = "SELECT COUNT(*) as total FROM /*{TABLE_NAME}*/";
      if (!empty($where))
         $sql .= " WHERE " . $where;

      $result = intval($this->db->select($sql)[0]["total"]);
      $records = [];

      // If there are data to read
      if ($result > 0) {
         // MODIFY SQL SELECT for read all data when need data from more than one table
         $sql = "SELECT * FROM /*{TABLE_NAME}*/";

         if (!empty($where))
            $sql .= " WHERE " . $where;

         if (!empty($order))
            $sql .= " ORDER BY " . $order;

         $cursor = $this->db->select_limit($sql, $limit, $offset);
         foreach ($cursor as $data) {
            $model = new /*{MODEL}*/($data);
            $records[] = $model;
         }
      }
      return $result;
   }
}
