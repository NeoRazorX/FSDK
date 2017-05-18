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

require_once __DIR__ . '/../lib/xml_from_table.php';

class fsdk_tabla extends fs_controller {

   public $nombre_modelo;
   public $tabla;
   public $xml;
   public $modelo;
   public $controlador;
   private $tab = '   ';
   private $columns;

   private function modelname_from_table($table) {
      if (substr($table, -1) == 's') {
         switch (substr($table, -3)) {
            case "nes":
            case "res": {
                  $result = substr($table, 0, -2);
                  break;
               }

            default: {
                  $result = substr($table, 0, -1);
               }
         }
      } else
         $result = $table;

      return $result;
   }

   private function get_primarykeys($table) {
      $constrains = $this->db->get_constraints($table);
      $result = [];

      foreach ($constrains as $column) {
         if (($column['tipo'] == 'PRIMARY KEY') OR ( $column['tipo'] == 'p'))
            $result[] = $column['column_name'];
      }
      return $result;
   }

   private function fields_comma($columns) {
      $result = '';
      foreach ($columns as $col) {
         if ($result)
            $result .= ',';
         $result .= $col['column_name'];
      }
      return $result;
   }

   private function fields_list($columns, $prefix, $sufix) {
      $result = '';
      foreach ($columns as $col) {
         $result .= $prefix . $col['column_name'] . $sufix;
      }
      return $result;
   }

   private function fields_declare($columns) {
      $prefix = $this->tab . 'private $';
      $sufix = ";\n";

      return $this->fields_list($columns, $prefix, $sufix);
   }

   private function fields_clear($columns) {
      $prefix = $this->tab . $this->tab . '$this->';
      $sufix = " = '';\n";

      return $this->fields_list($columns, $prefix, $sufix);
   }

   private function fields_load($columns) {
      $prefix = $this->tab . $this->tab . '$this->';
      $result = '';
      foreach ($columns as $col) {
         $result .= $prefix . $col['column_name'] . ' = $data[\'' . $col['column_name'] . '\']' . ";\n";
      }
      return $result;
   }

   private function fields_keys($key_fields) {
      $prefix = $this->tab . $this->tab;
      $result = '';
      foreach ($key_fields as $fieldname) {
         $result .= $prefix . '$this->add_keyfield(\'' . $fieldname . '\');' . "\n";
      }
      return $result;
   }

   private function fields_columns($columns) {
      $result = '';
      $display = 'left';
      $prefix = $this->tab . $this->tab . $this->tab;
      $cont = 1;

      foreach ($columns as $col) {
         if ($cont > 6)
            $display = 'none';
         
         $result .= $prefix . "['label' => '" . ucfirst($col['column_name']) . "', 'field' => '" . $col['column_name']  . "', 'display' => '" . $display . "'],\n";
         $cont++;
      }

      return $result;
   }

   private function fields_orderby($columns) {
      $result = '';
      $prefix = $this->tab . $this->tab . $this->tab;
      $cont = 1;

      foreach ($columns as $col) {
         if ($cont > 3)
            break;
         
         $result .= $prefix . "'" . ucfirst($col['column_name']) . "' => '" . $col['column_name'] . " ASC',\n";
         $result .= $prefix . "'" . ucfirst($col['column_name']) . " Desc' => '" . $col['column_name'] . " DESC',\n";
                  
         $cont++;
      }
      
      return $result;      
   }
   
   /* -----------------
    * P R O T E C T E D
    * ----------------- */

   protected function private_core() {
      parent::private_core();

      $table = (string) filter_input(INPUT_GET, 'table');

      if ($this->db->table_exists($table)) {
         $this->page->title = 'Tabla ' . $table;
         $this->tabla = $table;
         $this->nombre_modelo = $this->modelname_from_table($table);

         $this->columns = $this->db->get_columns($table);

         $this->export_structure_xml($table);
         $this->generar_modelo($table);
         $this->generar_controlador($table);
      } else {
         $this->new_error_msg('Tabla desconocida.', 'error', FALSE, FALSE);
      }
   }

   protected function export_structure_xml($table) {
      // Create XML file
      $xml = new xml_from_table($this->db, $table);
      $xml->add_columns();
      $xml->add_constrains();

      // Set to view
      $this->xml = $xml->read();
   }

   protected function generar_modelo($table) {
      $key_fields = $this->get_primarykeys($table);

      // Load Model Template
      $template = file_get_contents(__DIR__ . '/../template/model.php');

      // Calculate template values
      $template_var = ['/*{TABLE_NAME}*/',
         '/*{MODEL}*/',
         '/*{FIELDS_DECLARATION}*/',
         '/*{FIELDS_KEYS}*/',
         '/*{FIELDS_CLEAR}*/',
         '/*{FIELDS_COMMASEPARATED}*/',
         '/*{FIELDS_LOAD}*/'];

      $template_values = [$table,
         $this->nombre_modelo,
         $this->fields_declare($this->columns),
         $this->fields_keys($key_fields),
         $this->fields_clear($this->columns),
         $this->fields_comma($this->columns),
         $this->fields_load($this->columns)];

      // Apply values to template
      $this->modelo = str_replace($template_var, $template_values, $template);
   }

   protected function generar_controlador($table) {
      // Load Model Template
      $template = file_get_contents(__DIR__ . '/../template/controller.php');

      // Calculate template values
      $template_var = ['/*{MODEL}*/',
         '/*{CONTROLLER}*/',
         '/*{FIELDS_COLUMNS}*/',
         '/*{FIELDS_ORDERBY}*/'];

      $template_values = [$this->nombre_modelo,
         $table,
         $this->fields_columns($this->columns),
         $this->fields_orderby($this->columns)];

      // Apply values to template
      $this->controlador = str_replace($template_var, $template_values, $template);
   }

   /* -----------
    * P U B L I C
    * ----------- */

   public function __construct() {
      parent::__construct(__CLASS__, 'Tabla', 'admin', FALSE, FALSE);
   }

   public function url() {
      if ($this->tabla) {
         return parent::url() . '&table=' . $this->tabla;
      } else {
         return parent::url();
      }
   }

}
