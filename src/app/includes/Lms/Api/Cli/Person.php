<?php

class Lms_Api_Cli_Person {

    public static function add()
    {
        $opts = new Zend_Console_Getopt(
            array(
                'help|h'    => '�������� �������',
                'person-id|p=i' => 'ID ������������ ����������, ����� -u -n -i --photos ����� ���������������, ����� ����� ����� ������ � ����������� -m -r',
                
                'url|u=s' => '�������� ���������� �� url (kinopoisk, imdb, ozon, world-art), ����� -n -i --photos ����� ���������������',
                
                'name|n=s'    => '���[,������������� ���]',
                'info|i=s'    => '����������, ����� ����� ��������������� ���� ���������� ����� ������� � ���� ������ �� ����� ��� url',
                'photos=s'    => '���������� <url1[,url2[,url3...]]>, ����� ����� ��������������� ���� ���������� ����� ������� � ���� ������ �� ����� ��� url',
                
                'movie-id|m=i'    => '�������� ���������� � ������ � ������ ID',
                'role|r=s'    => '���� (��������/�����/..), ����� ������������ ������ � ���������� -m',
                'character|c=s'    => '��������, ����� ������������ ������ � ���������� -m',
            )
        );
        $opts->parse();
        
        if ($opts->getOption('h')) {
            Lms_Api_Cli::showUsageAndExit($opts, 0);
        }
        
        if ($personId = $opts->getOption('p')) {
            $personItem = Lms_Item::create('Person', $personId);
        } else {
            $names = array();
            if ($value = $opts->getOption('n')) {
                $names = preg_split("/(\s*,\s*)/", $value);
            }
            $url = $opts->getOption('u');


            $personItem = Lms_Item_Person::getByMisc($names, $url);
            if (!$personItem) {
                $personItem = Lms_Item_Person::getByMiscOrCreate($names, $url);
                if ($value = $opts->getOption('i')) {
                    $personItem->setInfo($value);
                }
                if ($value = $opts->getOption('photos')) {
                    $photos = preg_replace('{\s*,\s*}', "\n", $value);
                    $personItem->setPhotos($photos);
                }
                $personItem->save();
            }
        }
        if ($movieId = $opts->getOption('m')) {
            $movie = Lms_Item::create('Movie', $movieId);
            $role = $opts->getOption('r');
            if (!$role) {
                throw new Zend_Console_Getopt_Exception(
                    "�� ������� ���� ���������� (-r)",
                    $opts->getUsageMessage());
            }
            $roleItem = Lms_Item_Role::getByNameOrCreate($role);

            $item = Lms_Item::create('Participant');
            $item->setMovieId($movie->getId())
                 ->setRoleId($roleItem->getId())
                 ->setPersonId($personItem->getId());
            if ($value = $opts->getOption('�')) {
                $item->setCharacter($value);
            }
            $item->save();
        }
        
        return $personItem->getId();
    }
    
    public static function delete()
    {
        $opts = new Zend_Console_Getopt(
            array(
                'help|h'    => '�������� �������',
                'person-id|p=i' => 'ID ����������, ������� ����� �������',
            )
        );
        $opts->parse();
        
        if ($opts->getOption('h')) {
            Lms_Api_Cli::showUsageAndExit($opts, 0);
        }
        $personId = $opts->getOption('p');
        
        if (!$personId) {
            throw new Zend_Console_Getopt_Exception(
                "�� ������ ID ����������",
                $opts->getUsageMessage());
        }
        
        $person = Lms_Item::create('Person', $personId);

        $db = Lms_Db::get('main');
        $db->transaction();

        $person->delete();

        $db->commit();
    }
    
    public static function del()
    {
        return self::delete();
    }
}
