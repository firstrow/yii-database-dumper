      Yii::import('ext.yii-database-dumper.SDatabaseDumper');
      $dumper = new SDatabaseDumper;
      // Get path to backup file
      $file = Yii::getPathOfAlias('webroot.protected.backups').DIRECTORY_SEPARATOR.'dump_'.date('Y-m-d_H_i_s').'.sql';

      // Gzip dump
      if(function_exists('gzencode'))
          file_put_contents($file.'.gz', gzencode($dumper->getDump()));
      else
          file_put_contents($file, $dumper->getDump());
