<?php

require __DIR__ . '/Mysql.php';
require __DIR__ . '/Laravel.php';
require __DIR__ . '/Db2.php';

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



// export sql file
switch($sql_type) {
    case'mysql':
        $Sql = new Mysql;
        break;
    case'laravel':
        $Sql = new Laravel;
        break;
    case'db2':
        $Sql = new Db2;
        break;
}

$Sql->load_excel($input_file)->save_sql_file($output_file);
echo "The sql file is saved to " . $Sql->get_output_file();