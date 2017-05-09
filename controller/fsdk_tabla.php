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
    private $tab = '    ';

    /* -------------
     * P R I V A T E
     * ------------- */

    private function modelname_from_table($table): string {
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

    private function get_primarykeys($table): array {
        $constrains = $this->db->get_constraints($table);
        $result = [];

        foreach ($constrains as $column) {
            if (($column['tipo'] == 'PRIMARY KEY') OR ($column['tipo'] == 'p'))
                array_push($result, $column['column_name']);
        }
        return $result;
    }
    
    private function fields_comma($columns): string {
        $result = '';
        foreach ($columns as $col) {
            if ($result)
                $result .= ',';
            $result .= $col['column_name'];
        }
        return $result;
    }

    private function fields_list($columns, $prefix, $sufix): string {
        $result = '';
        foreach ($columns as $col) {
            $result .= $prefix . $col['column_name'] . $sufix;
        }
        return $result;
    }

    private function fields_declare($columns): string {
        $prefix = $this->tab . 'private $';
        $sufix = ";\n";

        return $this->fields_list($columns, $prefix, $sufix);
    }

    private function fields_clear($columns): string {
        $prefix = $this->tab . $this->tab . '$this->';
        $sufix = " = '';\n";

        return $this->fields_list($columns, $prefix, $sufix);
    }

    private function fields_load($columns): string {
        $prefix = $this->tab . $this->tab . '$this->';
        $result = '';
        foreach ($columns as $col) {
            $result .= $prefix . $col['column_name'] . ' = $data[\'' . $col['column_name'] . '\']' . ";\n";
        }
        return $result;
    }

    private function fields_keys($key_fields): string {
        $prefix = $this->tab . $this->tab;
        $result = '';
        foreach ($key_fields as $fieldname) {
            $result .= $prefix . '$this->add_keyfield(\''. $fieldname . '\');' . "\n";
        }          
        return $result;
    }
    
    /* -----------------
     * P R O T E C T E D
     * ----------------- */

    protected function private_core() {
        $table = (string) filter_input(INPUT_GET, 'table');

        if ($this->db->table_exists($table)) {
            $this->page->title = 'Tabla ' . $table;
            $this->tabla = $table;
            $this->nombre_modelo = $this->modelname_from_table($table);

            $this->export_structure_xml($table);
            $this->generar_modelo($table);
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
        $modelname = $this->modelname_from_table($table);
        $columns = $this->db->get_columns($table);
        $key_fields = $this->get_primarykeys($table);

        // Load Model Template
        $template = file_get_contents(__DIR__ . '/../lib/template_model.txt');

        // Calculate template values
        $template_var = array('//{TABLE_NAME}//',
            '//{MODEL}//',
            '//{FIELDS_DECLARATION}//',
            '//{FIELDS_KEYS}//',
            '//{FIELDS_CLEAR}//',
            '//{FIELDS_COMMASEPARATED}//',
            '//{FIELDS_LOAD}//');

        $template_values = array($table,
            $modelname,
            $this->fields_declare($columns),
            $this->fields_keys($key_fields),
            $this->fields_clear($columns),
            $this->fields_comma($columns),
            $this->fields_load($columns));

        // Apply values to template
        $this->modelo = str_replace($template_var, $template_values, $template);
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
