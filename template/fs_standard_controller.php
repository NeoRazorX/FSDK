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
 * Description of fs_standard_controller
 *
 * @author Artex Trading sa <jcuello@artextrading.com>
 */
class fs_standard_controller extends fs_controller
{

    public $icon;
    public $title;

    /**
     * Determines whether the user can delete data from the controller
     * @var boolean
     */
    public $allow_delete;

    /**
     * Cursor to data
     * @var type 
     */
    public $cursor;

    /**
     * List of fields that are loaded in the cursor and its parameterization
     * @var array of array("label" => "Field Label", "field" => "Field Name", "display" => "left/center/right/none")
     */
    public $fields;

    /**
     * Clause from of the select statement
     * @var string
     */
    public $from;

    /**
     * List of fields to select order by
     * @var array("item text" => "fieldname with alias ASC/DESC")
     */
    public $orderby;

    /**
     * List of fields filters that are loaded in the cursor and its parameterization
     * @var array of array("label" => "Field Label", "field" => "Field Name", "display" => "left/right/none")
     */
    public $filters;                                // TODO: Create user custom filter system

    /**
     * First record to select from database
     * @var int
     */
    public $offset;

    /**
     * Total number of records reader
     * @var int
     */
    public $count;

    /**
     * Item selected into order by list
     * @var string
     */
    public $selected_orderby;

    /* -----------------
     * P R O T E C T E D
     * ----------------- */

    /**
     * Fields name list separated by ','
     * @return string
     */
    protected function get_fields()
    {
        $result = "";

        foreach ($this->fields as $item) {
            if ($result != "")
                $result .= ",";

            $result .= $item['field'];
        }
        return $result;
    }

    /**
     * Check if the parameter is informed in the url and returns its value
     * @param string $field : Param name to check into url
     * @return variant
     */
    protected function get_value($field)
    {
        $result = FALSE;
        if (isset($_REQUEST[$field]))
            if ($_REQUEST[$field] != "")
                $result = $_REQUEST[$field];

        return $result;
    }

    /**
     * Set a Where clause to the selection of records
     * @return string
     */
    protected function get_where()
    {
        return "1 = 1";
    }

    /**
     * Make a string with params into url source
     * @return string
     */
    protected function get_params()
    {
        $result = "";
        if ($this->get_value("query"))
            $result = "&query=" . $_REQUEST["query"];

        return $result;
    }

    /**
     * Load Array list from database table
     * @param string $field : Field name to load
     * @param string $table : Table name from load
     * @param string $where : Where filter
     * @return array
     */
    protected function optionlist($field, $table, $where)
    {
        $result = [];
        if ($this->db->table_exists($table)) {
            $sql = "SELECT DISTINCT " . $field . " FROM " . $table;

            if ($where != "") {
                $sql .= " WHERE " . $where;
            }
            $sql .= " ORDER BY " . $field . " ASC;";

            $data = $this->db->select($sql);
            foreach ($data as $item) {
                $value = $item[$field];
                if ($value != "") {
                    $result[mb_strtolower($value, "UTF8")] = $value;
                }
            }
        }
        return $result;
    }
    /*
     * FacturaScript entry point
     */

    protected function private_core()
    {
        parent::private_core();

        // Set standard template
        $this->template = "fs_standard_view";

        // Set pagination values
        if (isset($_GET["offset"]))
            $this->offset = intval($_GET["offset"]);
        else
            $this->offset = 0;

        // Set order by selected
        $this->selected_orderby = $this->get_value("order");
        if (empty($this->selected_orderby) and count($this->orderby) > 0)
            $this->selected_orderby = array_values($this->orderby)[0];

        // Calculate all / search data
        $sql_count = "SELECT COUNT(*) as total FROM " . $this->from . " WHERE " . $this->get_where();
        $this->count = intval($this->db->select($sql_count)[0]["total"]);

        if ($this->count > 0) {
            $sql = "SELECT " . $this->get_fields()
                . " FROM " . $this->from
                . " WHERE " . $this->get_where()
                . " ORDER BY " . $this->selected_orderby;

            $this->cursor = $this->db->select_limit($sql, FS_ITEM_LIMIT, $this->offset);
        }
    }
    /* -----------
     * P U B L I C
     * ----------- */

    /**
     * Constructor inherited from parent controller
     */
    public function __construct($name = '', $title = 'home', $folder = '', $admin = FALSE, $shmenu = TRUE, $important = FALSE)
    {
        $this->allow_delete = FALSE;
        $this->offset = 0;
        $this->count = 0;

        parent::__construct($name, $title, $folder, $admin, $shmenu, $important);
    }
    /*
     * Calculate footer pagination jumper
     * @return array of array(
     *      url    => link to jump when user click
     *      icon   => shows the specified bootstrap icon instead of the page number
     *      page   => page number
     *      active => if item is active item
     */

    public function pagination()
    {
        $result = [];
        $url = $this->url() . $this->get_params();
        $page_margin = 3;
        $index = 0;

        $record_min = $this->offset - (FS_ITEM_LIMIT * $page_margin);
        if ($record_min < 0) {
            $record_min = 0;
        }

        $record_max = $this->offset + (FS_ITEM_LIMIT * ($page_margin + 1));
        if ($record_max > $this->count) {
            $record_max = $this->count;
        }

        // Add first page, if not included in pag_margin
        if ($this->offset > (FS_ITEM_LIMIT * $page_margin)) {
            $result[$index] = [
                'url' => $url . "&offset=0",
                'icon' => "glyphicon-step-backward",
                'page' => 1,
                'active' => FALSE
            ];
            $index++;
        }

        // Add middle left page, if offset is greater than page_margin
        $record_middle = ($record_min > FS_ITEM_LIMIT) ? ($this->offset / 2) : $record_min;
        if ($record_middle < $record_min) {
            $page = floor($record_middle / FS_ITEM_LIMIT);
            $result[$index] = [
                'url' => $url . "&offset=" . ($page * FS_ITEM_LIMIT),
                'icon' => "glyphicon-backward",
                'page' => ($page + 1),
                'active' => FALSE
            ];
            $index++;
        }

        // Add -pagination / offset / +pagination
        for ($record = $record_min; $record < $record_max; $record += FS_ITEM_LIMIT) {
            if (($record >= $record_min AND $record <= $this->offset) OR ( $record <= $record_max AND $record >= $this->offset)) {
                $page = ($record / FS_ITEM_LIMIT) + 1;
                $result[$index] = [
                    'url' => $url . "&offset=" . $record,
                    'icon' => FALSE,
                    'page' => $page,
                    'active' => ($record == $this->offset)
                ];

                $index++;
            }
        }

        // Add middle right page, if offset is lesser than page_margin   
        $record_middle = $this->offset + (($this->count - $this->offset) / 2);
        if ($record_middle > $record_max) {
            $page = floor($record_middle / FS_ITEM_LIMIT);
            $result[$index] = [
                'url' => $url . "&offset=" . ($page * FS_ITEM_LIMIT),
                'icon' => "glyphicon-forward",
                'page' => ($page + 1),
                'active' => FALSE
            ];
            $index++;
        }

        // Add last page, if not include in pag_margin
        if ($record_max < $this->count) {
            $page_max = floor($this->count / FS_ITEM_LIMIT);
            $result[$index] = [
                'url' => $url . "&offset=" . ($page_max * FS_ITEM_LIMIT),
                'icon' => "glyphicon-step-forward",
                'page' => ($page_max + 1),
                'active' => FALSE
            ];
        }

        return $result;
    }
}
