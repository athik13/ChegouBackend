<?php

function get_admin_nav($menu_items, $lavel = 0) {
    global $script;
    $li_html = "";
    $has_active_menu = false;

    if (is_array($menu_items)) {
        foreach ($menu_items as $key => $menu_item) {
            if (isset($menu_item['visible']) && $menu_item['visible'] == false) {
                continue;
            }

            $has_child = (isset($menu_item['children']) && count($menu_item['children']) > 0);
            $class = [];

            if ($has_child) {
                $class[] = "treeview";
            }
            $newTabEnable = $jsFunctionCall = "";
            if (isset($menu_item['target'])) {
                $newTabEnable = "target='_blank'";
            }
            if (isset($menu_item['active'])) {
                if ($script == $menu_item['active']) {
                    $has_active_menu = true;
                    $class[] = "active";
                }
            } else if (isset($menu_item['url']) && is_page($menu_item['url'])) {
                $has_active_menu = true;
                $class[] = "active";
            }
            if (strtolower($menu_item['url']) == "create_request.php") {
                $jsFunctionCall = "onClick=checkHotelAddress(this);";
            }
            $data = [];
            if ($has_child) {
                if (is_callable($menu_item['children'])) {
                    $func = $menu_item['children'];
                    $children = $func();
                } else {
                    $children = $menu_item['children'];
                }
                $data = get_admin_nav($children, $lavel + 1);
                if ($data['active']) {
                    $has_active_menu = true;
                    $class[] = "active";
                } else if (isset($menu_item['url']) && is_page($menu_item['url'])) {
                    $has_active_menu = true;
                    $class[] = "active";
                }
                $inner_html = "<a href='javascript:' $jsFunctionCall class='expand' $newTabEnable>";
            } else {
                $inner_html = "<a href='{$menu_item['url']}' $jsFunctionCall $newTabEnable>";
            }

            if (is_array($menu_item['icon'])) {
                $inner_html .= "<i class='{$menu_item['icon']['class']}' aria-hidden='true'><img src='{$menu_item['icon']['url']}' /></i>";
            } else {
                $inner_html .= "<i class='{$menu_item['icon']}' aria-hidden='true'></i>";
            }

            if ($lavel == 0) {
                $inner_html .= "<span>{$menu_item['title']}</span>";
            } else {
                $inner_html .= $menu_item['title'];
            }

            $inner_html .= "</a>";

            if (isset($data['html'])) {
                $inner_html .= $data['html'];
            }
            $attr_string = "";
            if (isset($menu_item['li_attr'])) {
                foreach ($menu_item['li_attr'] as $key => $value) {
                    $attr_string .= "{$key}='{$value}'";
                }
            }
            $li_html .= "<li {$attr_string} class='" . implode(" ", array_unique($class)) . "'>{$inner_html}</li>";
        }
    }

    $ul_class = $lavel == 0 ? 'sidebar-menu' : 'treeview-menu menu_drop_down';

    if ($lavel > 0) {
        $style = ($has_active_menu) ? "display:block" : "display:none";
    } else {
        $style = "";
    }
    $html = "<ul class='{$ul_class}' style='{$style}'>{$li_html}</ul>";

    if ($lavel > 0) {
        return ['html' => $html, 'active' => $has_active_menu];
    } else {
        return $html;
    }
}

function is_page($name) {
    if (!is_array($name)) {
        $name = [$name];
    }
    return in_array(basename($_SERVER['REQUEST_URI']), $name);
}

function sheet_to_array($objWorksheet, $header = true) {
    if ($header) {
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $headingsArray = $objWorksheet->rangeToArray('A1:' . $highestColumn . '1', null, true, true, true);
        $headingsArray = $headingsArray[1];
        $r = -1;
        $namedDataArray = array();
        for ($row = 2; $row <= $highestRow; ++$row) {
            $dataRow = $objWorksheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, true, true);
            if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
                ++$r;
                foreach ($headingsArray as $columnKey => $columnHeading) {
                    $columnHeading = str_replace([" ", "-"], '_', strtolower($columnHeading));
                    $namedDataArray[$r][$columnHeading] = $dataRow[$row][$columnKey];
                }
            }
        }
    } else {
        //excel sheet with no header
        $namedDataArray = $objWorksheet->toArray(null, true, true, true);
    }

    return $namedDataArray;
}

function ArrayToExcelSheet(&$sheet, $array) {
    foreach ($array as $i => $rows) {
        $row_key = $i + 1;
        foreach ($rows as $column_key => $value) {
            if (is_numeric($column_key)) {
                $sheet->setCellValueByColumnAndRow($column_key, $row_key, $value);
            } else {
                $sheet->setCellValue($column_key, $row_key, $value);
            }
        }
    }
}

function downloadExcel($doc) {
    ob_clean();
    $filename = 'not_imported_data.xls';
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");
    header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary ");
    $objWriter = new PHPExcel_Writer_Excel2007($doc);
    $objWriter->setOffice2003Compatibility(true);
    $objWriter->save('php://output');
    exit();
}

function hasMessage($key = "all") {
    return count($_SESSION['messages'][$key]) > 0;
}

function getMessage($key = "all") {
    $html = "";
    foreach ($_SESSION['messages'][$key] as $value) {
        $html .= "<div class='alert alert-{$value['type']}'>{$value['message']}</div>";
    }
    unset($_SESSION['messages'][$key]);
    return $html;
}

function setMessage($message, $type = "success", $key = "all") {
    $_SESSION['messages'][$key][] = compact('message', 'type');
}

function startSql() {
    DB::enableQueryLog();
}

function getSql() {
    $ary = DB::getQueryLog();
    $new_ary = [];
    foreach ($ary as $key => $data) {
        $query = $data['query'];
        $bindings = $data['bindings'];
        $time = $data['time'];

        // Format binding data for sql insertion
        foreach ($bindings as $i => $binding) {
            if ($binding instanceof \DateTime) {
                $bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
            } else if (is_string($binding)) {
                $bindings[$i] = "'$binding'";
            }
        }
        $query = str_replace(array('%', '?'), array('%%', '%s'), $query);
        $new_ary[] = vsprintf($query, $bindings);
    }
    return $new_ary;
}

function getMultiSelect($options, $name = "", $attrs = [], $value = null) {
    $attrs['multiple'] = true;
    return getSelect($options, $name, $value, $attrs);
}

function getSelect($options, $name = "", $attrs = [], $value = null) {
    if ($value == null) {
        $value = $_REQUEST[$name];
    }

    if ($attrs['multiple'] == true) {
        $name = $name . "[]";
    }
    $attrs['name'] = $name;

    $data = [
        'options' => $options,
        'value' => $value,
        'attrs' => $attrs,
    ];
    return getField('select', $data);
}

function getField($type, $data = []) {
    $html = "";

    if (!isset($data['value'])) {
        $data['value'] = "";
    }
    if (!isset($data['attrs'])) {
        $data['attrs'] = [];
    }


    $attrs = "";
    foreach ($data['attrs'] as $key => $value) {
        $attrs .= " {$key}='{$value}'";
    }

    switch ($type) {
        case 'select':
            $options = "";
            if (isset($data['options'])) {
                foreach ($data['options'] as $key => $value) {
                    $options .= "<option value='{$key}' " . selected($key, $data['value'], false) . ">{$value}</option>";
                }
            }
            $html .= "<select {$attrs} >{$options}</select>";
            break;

        default:
            break;
    }

    return $html;
}

function selected($value, $selected = "", $echo = true) {
    $is_selected = false;
    if (is_array($selected)) {
        $is_selected = in_array($value, $selected);
    } else {
        $is_selected = ($value == $selected);
    }

    if ($echo) {
        echo $is_selected ? "selected" : "";
    } else {
        return $is_selected ? "selected" : "";
    }
}

function checked($value, $checked = "", $echo = true) {
    $is_checked = false;
    if (is_array($checked)) {
        $is_checked = in_array($value, $checked);
    } else {
        $is_checked = ($value == $checked);
    }

    if ($echo) {
        echo $is_checked ? "checked" : "";
    } else {
        return $is_checked ? "checked" : "";
    }
}
