<?php

/**
 * Creates DB dump.
 *
 * Usage:
 * <pre>
 *      Yii::import('ext.yii-database-dumper.SDatabaseDumper');
 *      $dumper = new SDatabaseDumper;
 *      // Get path to backup file
 *      $file = Yii::getPathOfAlias('webroot.protected.backups').DIRECTORY_SEPARATOR.'dump_'.date('Y-m-d_H_i_s').'.sql';
 *
 *      // Gzip dump
 *      if(function_exists('gzencode'))
 *          file_put_contents($file.'.gz', gzencode($dumper->getDump()));
 *      else
 *          file_put_contents($file, $dumper->getDump());
 * </pre>
 */
class SDatabaseDumper
{

	/**
	 * Dump all tables
	 * @return string sql structure and data
	 */
	public function getDump()
	{
		ob_start();
		echo 'SET FOREIGN_KEY_CHECKS = 0;'.PHP_EOL;
		foreach($this->getTables() as $key=>$val)
			$this->dumpTable($key);
		echo 'SET FOREIGN_KEY_CHECKS = 1;'.PHP_EOL;
		$result=ob_get_contents();
		ob_end_clean();
		return $result;
	}

	/**
	 * Create table dump
	 * @param $tableName
	 * @return mixed
	 */
	public function dumpTable($tableName)
	{
		$db = Yii::app()->db;
		$pdo = $db->getPdoInstance();

		echo '
--
-- Structure for table `'.$tableName.'`
--
'.PHP_EOL;
		echo 'DROP TABLE IF EXISTS '.$db->quoteTableName($tableName).';'.PHP_EOL;

		$q = $db->createCommand('SHOW CREATE TABLE '.$db->quoteTableName($tableName).';')->queryRow();
		echo $q['Create Table'].';'.PHP_EOL.PHP_EOL;

		$rows = $db->createCommand('SELECT * FROM '.$db->quoteTableName($tableName).';')->queryAll();

		if(empty($rows))
			return;

		echo '
--
-- Data for table `'.$tableName.'`
--
'.PHP_EOL;

		$attrs = array_map(array($db, 'quoteColumnName'), array_keys($rows[0]));
		echo 'INSERT INTO '.$db->quoteTableName($tableName).''." (", implode(', ', $attrs), ') VALUES'.PHP_EOL;
		$i=0;
		$rowsCount = count($rows);
		foreach($rows AS $row)
		{
			// Process row
			foreach($row AS $key => $value)
			{
				if($value === null)
					$row[$key] = 'NULL';
				else
					$row[$key] = $pdo->quote($value);
			}

			echo " (", implode(', ', $row), ')';
			if($i<$rowsCount-1)
				echo ',';
			else
				echo ';';
			echo PHP_EOL;
			$i++;
		}
		echo PHP_EOL;
		echo PHP_EOL;
	}

	/**
	 * Get mysql tables list
	 * @return array
	 */
	public function getTables()
	{
		$db = Yii::app()->db;
		return $db->getSchema()->getTables();
	}
}
