<?php
require 'vendor/autoload.php';
require __DIR__ . '/Table.php';
require __DIR__ . '/Field.php';
require __DIR__ . '/Mysql.php';

// default parameter
$input_file = 'TableSchema.xls';
$output_file = 'TableSchema.sql';
$sql_type = 'mysql';

// input parameter
// php DDLGen.php [-i TableSchema.xls] [-o TableSchema.sql] [-t mysql]
foreach ($argv as $idx => $value) {
    switch ($value) {
        case '-i':
            if (isset($argv[$idx + 1]) && $argv[$idx + 1] !== '') {
                $input_file = $argv[$idx + 1];
            }
            break;
        case '-o':
            if (isset($argv[$idx + 1]) && $argv[$idx + 1] !== '') {
                $output_file = $argv[$idx + 1];
            }
            break;
        case '-t':
            if (isset($argv[$idx + 1]) && $argv[$idx + 1] !== '') {
                $sql_type = $argv[$idx + 1];
            }
            break;
    }
}

// read excel
$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
$reader->setLoadSheetsOnly(array("tables", "fields"));
$spreadsheet = $reader->load($input_file);

// get tables sheet
$worksheet = $spreadsheet->getSheetByName("tables");
$highestRow = $worksheet->getHighestRow(); // e.g. 10
$highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
$highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5

// get header(row=1)
$table_key = [];
for ($col = 1; $col <= $highestColumnIndex; $col++) {
    $value = $worksheet->getCellByColumnAndRow($col, 1)->getFormattedValue();
    $table_key[$value] = $col;
}

// get data
for ($row = 2; $row <= $highestRow; $row++) {
    $table_name = $worksheet->getCellByColumnAndRow($table_key['Table Name'], $row)->getFormattedValue();

    if (!empty($table_name)) {
        $tb = new Table();
        $tb->table_name = $table_name;
        $tb->table_comments = $worksheet->getCellByColumnAndRow($table_key['Table Comments'], $row)->getFormattedValue();
        $tables[$tb->table_name] = $tb;
    }

}

// get fields sheet
$worksheet = $spreadsheet->getSheetByName("fields");
$highestRow = $worksheet->getHighestRow(); // e.g. 10
$highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
$highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5

// get header(row=1)
$field_key = [];
for ($col = 1; $col <= $highestColumnIndex; $col++) {
    $value = $worksheet->getCellByColumnAndRow($col, 1)->getValue();
    $field_key[$value] = $col;
}

// get data
for ($row = 2; $row <= $highestRow; $row++) {
    $table_name = $worksheet->getCellByColumnAndRow($field_key['Table Name'], $row)->getFormattedValue();

    if (!empty($table_name)) {
        $fd = new Field();
        $fd->table_name = $table_name;

        foreach ($field_key as $key => $value) {
            switch ($key) {
                case 'Table Name':
                case 'Field Name':
                case 'Data Type':
                case 'Field Comments':
                case 'Not Null':
                case 'Default':
                case 'More':
                case 'PK':
                    $property = strtolower(str_replace(' ', '_', $key));
                    $fd->$property = $worksheet->getCellByColumnAndRow($value, $row)->getFormattedValue();
                    break;
                default: //UK_1,UK_2,INDEX_1,INDEX_2...
                    $cell_value = $worksheet->getCellByColumnAndRow($value, $row)->getFormattedValue();
                    if (!empty($cell_value)) {
                        list($property, $idx) = explode('_', strtolower($key));
                        $fd->$property[$idx] = $cell_value;
                        $tables[$fd->table_name]->$property[$idx][$cell_value] = $fd->field_name;
                    }
                    break;
            }
        }

        if (!empty($fd->pk)) {
            $tables[$fd->table_name]->pk[$fd->pk] = $fd->field_name;
        }

        $tables[$fd->table_name]->field[] = $fd;
    }
}

// export sql file
if ($sql_type == 'mysql') {
    $mysql = new Mysql;
    $mysql->set_output_file($output_file)->set_tables($tables)->save_sql_file();
    echo "The sql file is saved to " . $mysql->get_output_file();
}
