<?php

class Lms_Api_Cli_File {

    public static function add()
    {
        $opts = new Zend_Console_Getopt(
            array(
                'help|h'    => '�������� �������',
                'movie-id|m=i'    => 'ID ������, � �������� ����� �������� ���� ��� �����',
                'path|p=s'    => '���� � ����� ��� ����� (��������� ����� ����� ����� ���������)',
                
                'quality|q=s'    => '�������� ����� (���� ���� ��� ����� ��� ����� ������������, �������� ��� ����� ����������)',
                'translation|t=s'    => '�������(�) <�������1[,�������2[,�������3...]]> (���� ���� ��� ����� ��� ����� ������������, �������� ��� ����� ����������)',
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
        
        if (!Lms_Ufs::file_exists($path)) {
            throw new Zend_Console_Getopt_Exception(
                "���� $path �� ������",
                $opts->getUsageMessage());
        }

        $db = Lms_Db::get('main');
        $db->transaction();

        $files = Lms_Item_File::parseFiles($path);
        
        Lms_Debug::debug($files);
        
        $movie->updateFilesByStruct($files);
        
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
