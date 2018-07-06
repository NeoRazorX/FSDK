<?php
/*
 * This file is part of FacturaScripts
 * Copyright (C) 2013-2018  Carlos Garcia Gomez <neorazorx@gmail.com>
 * Copyright (C) 2017       Artex Trading sa    <jcuello@artextrading.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of fs_standard_model
 *
 * @author Artex Trading sa <jcuello@artextrading.com>
 */
abstract class fs_standard_model extends \fs_model
{

    protected $key_fields;
    protected $required_fields;

    public function __construct($name = '')
    {
        parent::__construct($name);

        $this->fields_key = array();
        $this->required_fields = array();
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function exists()
    {
        $result = FALSE;
        if ($this->test_keyfields()) {
            $sql = 'SELECT 1 FROM ' . $this->table_name . ' WHERE 1 = 1';
            foreach ($this->key_fields as $key_field) {
                $sql .= ' AND ' . $key_field . ' = ' . $this->var2str($this->$key_field);
            }
            $result = $this->db->select($sql . ' LIMIT 1');
        }
        return $result;
    }

    public function save()
    {
        if ($this->test()) {
            $this->clean_cache();

            if ($this->exists()) {
                return $this->update();
            } else {
                return $this->insert();
            }
        } else {
            return FALSE;
        }
    }

    public function delete()
    {
        if (!empty($this->key_fields)) {
            $sql = 'DELETE FROM ' . $this->table_name . ' WHERE ';
            foreach ($this->key_fields as $key_field) {
                $sql .= $key_field . ' = ' . $this->var2str($this->$key_field);
            }

            $this->db->exec($sql);
        }
    }

    abstract protected function update();

    abstract protected function insert();

    abstract public function load_from_data($data);

    abstract public function clear();

    private function test_keyfields()
    {
        $result = TRUE;
        foreach ($this->key_fields as $key_field) {
            if (empty($this->$key_field)) {
                $result = FALSE;
                break;
            }
        }

        return $result;
    }

    private function test_requiredfields()
    {
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

    protected function add_keyfield($fieldname)
    {
        return array_push($this->key_fields, $fieldname);
    }

    protected function add_requiredfield($fieldname)
    {
        return array_push($this->required_fields, $fieldname);
    }

    protected function test()
    {
        return $this->test_requiredfields();
    }
}

/**
 * TEST: To compare with template model generated
 */
class test_template extends fs_standard_model
{

    private $field1;
    private $field2;

    public function __construct($data = FALSE)
    {
        parent::__construct('test_template');

        $this->add_keyfield('field1');

        if ($data)
            $this->load_from_data($data);
        else
            $this->clear();
    }

    protected function test()
    {
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

    protected function update()
    {
        $sql = '';
        return $this->db->exec($sql);
    }

    protected function insert()
    {
        $sql = '';
        return $this->db->exec($sql);
    }

    public function clear()
    {
        $this->field1 = '';
        $this->field2 = '';
    }

    public function load_from_data($data)
    {
        $this->field1 = $data['field1'];
        $this->field2 = $data['field2'];
    }

    public function exists()
    {
        return parent::exists();
    }

    public function install()
    {
        return '';
    }
}
