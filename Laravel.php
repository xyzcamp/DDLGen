<?php
require_once __DIR__ . '/BaseSql.php';

class Laravel extends BaseSql
{

    public function save_sql_file($path)
    {
		$this->output_file = $path;
		
		// write sql
		$wrap = "\r\n";
		
		$tables = $this->tables;

		$fp = fopen($this->output_file, 'w');

		fwrite($fp, "<?php$wrap");
		fwrite($fp, $wrap);
		fwrite($fp, "use Illuminate\Database\Schema\Blueprint;$wrap");
		fwrite($fp, "use Illuminate\Database\Migrations\Migration;$wrap");
		fwrite($fp, $wrap);
		fwrite($fp, "class CreateTables extends Migration$wrap");
		fwrite($fp, "{".$wrap);

		fwrite($fp, "\tpublic function up()$wrap");
		fwrite($fp, "\t{".$wrap);
			foreach ($tables as $name => $table) {
				fwrite($fp, "\t\tSchema::create('$table->table_name', function (Blueprint \$table) {".$wrap);
				
				foreach ($table->field as $f_idx => $field) {
					$field->data_type = strtolower($field->data_type);
					switch ($field->data_type) {
						case 'bigint unsigned':
							fwrite($fp, "\t\t\t\$table->unsignedBigInteger('$field->field_name')");
							break;
						case 'smallint':
							fwrite($fp, "\t\t\t\$table->smallInteger('$field->field_name')");
							break;
						case 'tinyint':
							fwrite($fp, "\t\t\t\$table->tinyInteger('$field->field_name')");
							break;
						case 'timestamp':
							fwrite($fp, "\t\t\t\$table->timestamp('$field->field_name')");
							break;
						case 'text':
							fwrite($fp, "\t\t\t\$table->text('$field->field_name')");
							break;
						case 'mediumtext':
							fwrite($fp, "\t\t\t\$table->mediumText('$field->field_name')");
							break;
						default:

							if (preg_match('/(\w*)\((\d*)\)/', $field->data_type, $matches)) {
								list($data_type, $type, $length) = $matches;
								if ($type == 'varchar') {
									fwrite($fp, "\t\t\t\$table->string('$field->field_name', $length)");
								} else if ($type == 'int') {
									fwrite($fp, "\t\t\t\$table->integer('$field->field_name')");
								} 
							} else if (preg_match('/(\w*)\((\d*),(\d*)\)/', $field->data_type, $matches)) {
								list($data_type, $type, $p, $s) = $matches;
								fwrite($fp, "\t\t\t\$table->decimal('$field->field_name', $p, $s)");
							}
							
							break;

					}

					if ($field->not_null === 'Y') {
						fwrite($fp, "->nullable(false)");
					} else {
						fwrite($fp, "->nullable()");
					}
					
					if ($field->default !== '') {

						if ($field->default == 'CURRENT_TIMESTAMP') {
							fwrite($fp, "->default(DB::raw('$field->default'))");
						} else {
							fwrite($fp, "->default($field->default)");
						}
						
					}

					if ($field->more !== '') {
						switch ($field->more) {
							case 'AUTO_INCREMENT':
								fwrite($fp, "->autoIncrement()");
								break;
							case 'ON UPDATE CURRENT_TIMESTAMP':
								//fwrite($fp, "->autoIncrement()");
								break;
						}
						//fwrite($fp, " $field->more");
					}

					if ($field->field_comments !== '') {
						fwrite($fp, "->comment('$field->field_comments')");
					}
					fwrite($fp, ";".$wrap);
					
	
				}

				if (is_array($table->pk)) {
					//fwrite($fp, "\t\t\t\$table->primary('" . implode("','", $table->pk) . "');".$wrap);
				}

				if (is_array($table->uk)) {
					ksort($table->uk);
					foreach ($table->uk as $uk_idx => $uk) {
						fwrite($fp, "\t\t\t\$table->unique(['" . implode("','", $uk) . "'],'". $table->table_name ."_uk".$uk_idx."');".$wrap);
					}
				}

				if (is_array($table->index)) {
					ksort($table->index);
					foreach ($table->index as $idx => $index) {
						fwrite($fp, "\t\t\t\$table->index(['" . implode("','", $index) . "'],'". $table->table_name ."_index".$idx."');".$wrap);
					}
				}
	


				fwrite($fp, "\t\t});".$wrap);

				fwrite($fp, "\t\tDB::statement(\"ALTER TABLE `$table->table_name` comment '$table->table_comments'\");".$wrap);



				fwrite($fp, $wrap);
				
			}

		fwrite($fp, "\t}".$wrap);

		fwrite($fp, $wrap);

		fwrite($fp, "\tpublic function down()$wrap");
		fwrite($fp, "\t{".$wrap);
			foreach ($tables as $name => $table) {
				fwrite($fp, "\t\tSchema::dropIfExists('$table->table_name');".$wrap);
				
			}

		fwrite($fp, "\t}".$wrap);

		fwrite($fp, "}".$wrap);

		fclose($fp);

    }
}
