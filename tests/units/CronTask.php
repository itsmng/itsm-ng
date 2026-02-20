<?php

/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2022 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
*/

namespace tests\units;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/* Test for inc/crontask.class.php */

class Crontask extends \GLPITestCase
{
    public function testCronTemp()
    {
        $tmp_dir = GLPI_TMP_DIR . '/cron_temp_' . uniqid('', true);

        //create some files
        $Data = [
           [
              'name'    => $tmp_dir . '/recent_file.txt',
              'content' => 'content1',
           ],
           [
              'name'    => $tmp_dir . '/file1.txt',
              'content' => 'content1',
           ],
           [
              'name'    => $tmp_dir . '/file2.txt',
              'content' => 'content2',
           ],
           [
              'name'    => $tmp_dir . '/auto_orient/file3.txt',
              'content' => 'content3',
           ],
           [
              'name'    => $tmp_dir . '/auto_orient/file4.txt',
              'content' => 'content4',
           ]
        ];

        //create auto_orient directory
        if (!file_exists($tmp_dir . '/auto_orient/')) {
            mkdir($tmp_dir . '/auto_orient/', 0755, true);
        }

        foreach ($Data as $Row) {
            $file = fopen($Row['name'], 'c');
            fwrite($file, $Row['content']);
            fclose($file);

            //change filemtime (except recent_file.txt)
            if ($Row['name'] != $tmp_dir . '/recent_file.txt') {
                touch($Row['name'], time() - (HOUR_TIMESTAMP * 2));
            }

        }

        // launch Cron for cleaning _tmp directory
        $mode = - \CronTask::MODE_EXTERNAL; // force
        \CronTask::launch($mode, 5, 'temp');

        $this->boolean(file_exists($tmp_dir . '/recent_file.txt'))->isTrue();
        $this->boolean(file_exists($tmp_dir . '/file1.txt'))->isFalse();
        $this->boolean(file_exists($tmp_dir . '/file2.txt'))->isFalse();
        $this->boolean(file_exists($tmp_dir . '/auto_orient/file3.txt'))->isFalse();
        $this->boolean(file_exists($tmp_dir . '/auto_orient/file4.txt'))->isFalse();
        $this->boolean(is_dir($tmp_dir . '/auto_orient'))->isFalse();

        unlink($tmp_dir . '/recent_file.txt');
        rmdir($tmp_dir);
    }


    public function getFileCountRecursively($path)
    {

        $dir = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator(
            $dir,
            RecursiveIteratorIterator::CHILD_FIRST
        );
        return iterator_count($files);
    }
}
