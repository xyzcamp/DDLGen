<?php
require_once __DIR__ . '/BaseSql.php';

class Mysql extends BaseSql
{

    public function save_sql_file($path)
    {
		$this->output_file = $path;
		
		// write sql
		$wrap = "\r\n";
		
		$tables = $this->tables;

		$fp = fopen($this->output_file, 'w');

		fwrite($fp, "/*$wrap");
		fwrite($fp, " * Generated by DDLGen tool.$wrap");
		fwrite($fp, " */$wrap");
		fwrite($fp, $wrap);

		foreach ($tables as $name => $table) {
			echo $table->table_name . "\n";
			fwrite($fp, "-- Table structure for table `$table->table_name`$wrap");
			fwrite($fp, "DROP TABLE IF EXISTS `$table->table_name`;$wrap");
			fwrite($fp, $wrap);
			fwrite($fp, "CREATE TABLE `$table->table_name` ( $wrap");

			foreach ($table->field as $f_idx => $field) {

				if ($f_idx !== 0) {
					fwrite($fp, "," . $wrap);
				}

				fwrite($fp, "\t`$field->field_name`");
				fwrite($fp, " $field->data_type");
				if ($field->not_null === 'Y') {
					fwrite($fp, " NOT NULL");
				}

				if ($field->default !== '') {
					fwrite($fp, " DEFAULT $field->default");
				}

				if ($field->more !== '') {
					fwrite($fp, " $field->more");
				}
				
				if ($field->field_comments !== '') {
					fwrite($fp, " COMMENT '$field->field_comments'");
				}

			}

			if (is_array($table->pk)) {
				fwrite($fp, ",$wrap\tPRIMARY KEY (`" . implode('`,`', $table->pk) . "`)");
			}

			if (is_array($table->uk)) {
				ksort($table->uk);
				foreach ($table->uk as $uk_idx => $uk) {
					fwrite($fp, ",$wrap\tUNIQUE KEY `" . $table->table_name . "_uk$uk_idx` (`" . implode('`,`', $uk) . "`)");
				}
			}

			if (is_array($table->index)) {
				ksort($table->index);
				foreach ($table->index as $idx => $index) {
					fwrite($fp, ",$wrap\tINDEX `" . $table->table_name . "_index$idx` (`" . implode('`,`', $index) . "`)");
				}
			}
			fwrite($fp, "$wrap) COMMENT='$table->table_comments';$wrap");
			fwrite($fp, $wrap);
			fwrite($fp, "ALTER TABLE `$table->table_name` AUTO_INCREMENT=10001;$wrap");
			fwrite($fp, $wrap);
		}

		fclose($fp);

    }
}
