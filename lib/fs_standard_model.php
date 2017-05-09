<?php

/*
 * This file is part of FacturaScripts
 * Copyright (C) 2016-2017  Carlos Garcia Gomez  neorazorx@gmail.com
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

abstract class fs_standard_model extends \fs_model {
    protected $key_fields;
    protected $required_fields;

    abstract protected function update();
    abstract protected function insert();
    abstract public function load_from_data($data);
    abstract public function clear();
    
    private function test_keyfields(): bool {
        $result = TRUE;
        foreach ($this->key_fields as $key_field) {
            if (empty($this->$key_field)) {
                $result = FALSE;
                break;
            }
        }
        
        return $result;        
    }

    private function test_requiredfields(): bool {
        $result = $this->test_keyfields();
        if ($result)
            foreach ($this->required_fields as $field) {
                if (empty($this->$field)) {
                    $result = FALSE;
                    break;
                }
            }
        
        return $result;        
    }
    
    protected function add_keyfield($fieldname): int {
        return array_push($this->key_fields, $fieldname);
    }

    protected function add_requiredfield($fieldname): int {
        return array_push($this->required_fields, $fieldname);
    }
    
    protected function test(): bool {
        return $this->test_requiredfields();
    }
    
    public function __construct($name = '') {
        parent::__construct($name);
        
        $this->fields_key = [];
        $this->required_fields = [];
    }

    public function __get($name) {
        return $this->$name;
    }

    public function __set($name, $value) {
        $this->$name = $value;
    }

    public function exists(): bool {
        $result = FALSE;
        if ($this->test_keyfields()) {
            $sql = 'SELECT 1 FROM ' . $this->table_name . ' WHERE 1 = 1';
            foreach ($this->key_fields as $key_field) {
                $sql .=  ' AND ' . $key_field . ' = ' . $this->var2str($this->$key_field);
            }
            $result = $this->db->select($sql .' LIMIT 1');
        }
        return $result;
    }
    
    public function save(): bool {
        if ($this->test()) {
            $this->clean_cache();

            if ($this->exists())
                return $this->update();
            else
                return $this->insert();
        } else
            return FALSE;
    }
}

/* TEST: To compare with template model generated */
class test_template extends fs_standard_model {
    private $field1;
    private $field2;

   /* -----------------
    * P R O T E C T E D
    * ----------------- */

    protected function test(): bool {
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
      $sql = '';
      return $this->db->exec($sql);
    }

    protected function insert() {
      $sql = '';
      return $this->db->exec($sql);
    }

   /* -----------
    * P U B L I C
    * ----------- */

    public function __construct($data = FALSE) {
        parent::__construct('test_template');
        
        $this->add_keyfield('field1');
        
        if ($data)
          $this->load_from_data($data);
        else
          $this->clear();
    }

    public function clear() {
        $this->field1 = '';
        $this->field2 = '';
    }

    public function load_from_data($data) {
        $this->field1 = $data['field1'];
        $this->field2 = $data['field2'];
    }

    public function exists(): bool {
        return parent::exists();
    }

    public function install() {
        return '';
    }

    public function delete() {

    }
}
