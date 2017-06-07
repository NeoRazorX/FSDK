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

class xml_from_table {

   private $database;
   private $table;
   private $xml;

   public function __construct($database, $table) {
      $this->database = $database;
      $this->table = $table;
      $this->xml = simplexml_load_string($this->xml_header());
   }

   public function add_columns() {
      $columns = $this->database->get_columns($this->table);
      foreach ($columns as $column) {
         $node = $this->xml->addChild('columna');
         $node->addChild('nombre', $column['name']);

         /// comprobamos si es auto_increment
         $auto_increment = (isset($column['extra']) AND ( $column['extra'] === 'auto_increment'));
         if ($auto_increment) {
            $this->add_serial_field($node, $column);
            continue;
         }

         /// comprobamos si es tipo serial
         if ($column['type'] == 'integer' AND $column['default'] == "nextval('" . $this->table . '_' . $column['name'] . "_seq'::regclass)") {
            $this->add_serial_field($node, $column);
            continue;
         }

         // añadimos campo estandar
         $this->add_field($node, $column);
      }
   }

   public function add_constrains() {
      $constrains = $this->database->get_constraints($this->table, TRUE);
      $primary_fields = array();
      $uniques = array();

      foreach ($constrains as $column) {
         switch ($column['type']) {
            case 'PRIMARY KEY':
               $primary_fields[] = $column['column_name'];
               break;

            case 'FOREIGN KEY':
               $fk = 'FOREIGN KEY (' . $column['column_name'] . ')'
                       . ' REFERENCES ' . $column['foreign_table_name'] . ' (' . $column['foreign_column_name'] . ')'
                       . ' ON DELETE ' . $column['on_delete']
                       . ' ON UPDATE ' . $column['on_update'];
               $this->add_constrain($column['name'], $fk);
               break;

            case 'UNIQUE':
               $uniques[$column['name']][] = $column['column_name'];
               break;

            default:
               $this->add_constrain($column['name'], '...');
               break;
         }
      }

      // Add Primary Keys
      if ($primary_fields) {
         $this->add_constrain($this->table . '_pkey', 'PRIMARY KEY (' . join(',', $primary_fields) . ')');
      }
      
      foreach($uniques as $key => $value) {
         $this->add_constrain($key, 'UNIQUE (' . join(',', $value) . ')');
      }
   }

   public function read() {
      // Formateamos XML para "Human-Readable"
      $doc = new DOMDocument();
      $doc->preserveWhiteSpace = false;
      $doc->formatOutput = true;
      $doc->loadXML($this->xml->asXML());

      // devolvemos el contenido del XML
      return $doc->saveXML();
   }

   private function xml_header() {
      return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
              . "<!--\n"
              . "   Document   : " . $this->table . ".xml\n"
              . "   Description:\n"
              . "        Estructura de la tabla " . $this->table . ".\n"
              . "-->\n"
              . "<tabla>\n"
              . "</tabla>\n";
   }

   private function add_field($node, $column) {
      if (substr($column['type'], 0, 3) == 'int') {
         $node->addChild('tipo', 'integer');
      } else if (substr($column['type'], 0, 7) == 'tinyint') {
         $node->addChild('tipo', 'boolean');
      } else {
         $node->addChild('tipo', $column['type']);
      }

      if ($column['is_nullable'] == 'YES') {
         $node->addChild('nulo', 'YES');
      } else {
         $node->addChild('nulo', 'NO');
      }

      if (isset($column['column_default'])) {
         $node->addChild('defecto', $column['column_default']);
      }
   }

   private function add_serial_field($node, $column) {
      $node->addChild('tipo', 'serial');
      $node->addChild('nulo', 'NO');
      $node->addChild('defecto', "nextval('" . $this->table . '_' . $column['name'] . "_seq'::regclass)");
   }

   private function add_constrain($name, $value) {
      $node = $this->xml->addChild('restriccion');
      $node->addChild('nombre', $name);
      $node->addChild('consulta', $value);
   }

}
