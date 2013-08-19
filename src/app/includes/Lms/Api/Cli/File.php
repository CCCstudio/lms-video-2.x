<?php

class Lms_Api_Cli_File {

    public static function add()
    {
        $opts = new Zend_Console_Getopt(
            array(
                'help|h'    => '�������� �������',
                'movie-id|m=i'    => 'ID ������, � �������� ����� �������� ���� ��� �����',
                'path|p=s'    => '���� � ����� ��� ����� (��������� ����� ����� ����� ���������)',
                'size|s=s'    => '������ ����� (������������ ������ ��� ����������� ����� � ��������� --skip-errors)',
                'is-dir|d'    => '������� ���������� (������������ ������ ��� ����������� ����� � ��������� --skip-errors)',
                
                'quality|q=s'    => '�������� ����� (���� ���� ��� ����� ��� ����� ������������, �������� ��� ����� ����������)',
                'translation|t=s'    => '�������(�) <�������1[,�������2[,�������3...]]> (���� ���� ��� ����� ��� ����� ������������, �������� ��� ����� ����������)',
                'skip-errors' => '������������ ������',
            )
        );
        $opts->parse();
        
        if ($opts->getOption('h')) {
            Lms_Api_Cli::showUsageAndExit($opts, 0);
        }

        $movieId = $opts->getOption('m');
        
        $movie = Lms_Item::create('Movie', $movieId);

        $path = $opts->getOption('p');
        $path = Lms_Application::normalizePath($path);
        
        $db = Lms_Db::get('main');
        $db->transaction();
        
        if (!Lms_Ufs::file_exists($path)) {
            if ($opts->getOption('skip-errors')) {
                $isDir = $opts->getOption('d')? 1 : 0;
                $files = array(array(
                    'path' => $path,
                    'basename' => basename($path),
                    'size' => !$isDir? $opts->getOption('s') : null,
                    'is_dir' => $isDir
                ));
                $movie->updateFilesByStruct($files);
            } else {
                throw new Zend_Console_Getopt_Exception(
                    "���� $path �� ������",
                    $opts->getUsageMessage());
            }
        } else {
            $files = Lms_Item_File::parseFiles($path);
            $movie->updateFilesByStruct($files);
        }
        
        $quality = null;
        if ($opts->getOption('q')) {
            $quality = $opts->getOption('q');
        }
        $translation = array();
        if ($opts->getOption('t')) {
            $translation = preg_split("/(\s*,\s*)/", $opts->getOption('t'));
        }
        
        $filesIds = array();
        foreach ($movie->getFiles() as $file) {
            $filesIds[] = $file->getId();
            if ($file->getPath()==$path) {
                if ($quality = $opts->getOption('q')) {
                    $file->setQuality($quality);
                }
                if ($translation = $opts->getOption('t')) {
                    $translation = preg_split("/(\s*,\s*)/", $translation);
                    $file->setTranslation($translation);
                }
                $file->save();
            }
        }
        $db->commit();

        return implode(',', $filesIds);
    }
}
