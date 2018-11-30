<?php
require_once __DIR__ . '/BaseSql.php';

class Db2 extends BaseSql
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
			fwrite($fp, "-- Table structure for table $table->table_name $wrap");
			//fwrite($fp, "DROP TABLE IF EXISTS $table->table_name;$wrap");
			fwrite($fp, $wrap);
			fwrite($fp, "CREATE TABLE $table->table_name ( $wrap");

			foreach ($table->field as $f_idx => $field) {

				if ($f_idx !== 0) {
					fwrite($fp, "," . $wrap);
				}

				fwrite($fp, "\t$field->field_name");
				fwrite($fp, " $field->data_type");
				if ($field->not_null === 'Y') {
					fwrite($fp, " NOT NULL");
				}

				if ($field->default !== '') {
					fwrite($fp, " DEFAULT $field->default");
				}

				if ($field->more !== '') {
					if ($field->more == 'AUTO_INCREMENT') {
						fwrite($fp, " GENERATED ALWAYS AS IDENTITY");
					} else {
						fwrite($fp, " $field->more");
					}
					
				}
				
				// if ($field->field_comments !== '') {
				// 	fwrite($fp, " COMMENT '$field->field_comments'");
				// }

			}

			if (is_array($table->pk)) {
				fwrite($fp, ",$wrap\tCONSTRAINT " . "PK_" . $table->table_name . " PRIMARY KEY (" . implode(',', $table->pk) . ")");
			}

			if (is_array($table->uk)) {
				ksort($table->uk);
				foreach ($table->uk as $uk_idx => $uk) {
					fwrite($fp, ",$wrap\tCONSTRAINT " . "U$uk_idx" . "_" . $table->table_name . " UNIQUE (" . implode(',', $uk) . ")");
				}
			}

			if (is_array($table->index)) {
				ksort($table->index);
				foreach ($table->index as $idx => $index) {
					fwrite($fp, ",$wrap\tCONSTRAINT " . "X$idx" . "_" . $table->table_name . " INDEX (" . implode(',', $index) . ")");
				}
			}
			fwrite($fp, "$wrap);$wrap");
			fwrite($fp, $wrap);

			// comment of table
			fwrite($fp, "COMMENT ON TABLE $table->table_name IS '$table->table_comments';$wrap");

			// comment of column
			foreach ($table->field as $f_idx => $field) {
				fwrite($fp, "COMMENT ON COLUMN $table->table_name.$field->field_name IS '$field->field_comments';$wrap");
			}

			fwrite($fp, $wrap);
		}

		fclose($fp);

    }
}