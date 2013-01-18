<?php

class Lms_Api_Cli_Hit {

    public static function add()
    {
        $opts = new Zend_Console_Getopt(
            array(
                'help|h'    => '�������� �������',
                'movie-id|m=i'    => 'ID ������',
                'user-name|u=s'    => '��� ������������ (�����)',
                'uid=i'    => 'ID ������������, �������� -u ����� ��������������',
                'ip=s'    => 'IP ������������',
                'created-at|c=s'    => '���� � ������� yyyy-mm-dd hh:mm:ss',
                'force-update'    => '����������� ���������� ���������� ������ ����� (�� �������������), ��. ����� ������� "... hit update -h"',
            )
        );
        $opts->parse();
        
        if ($opts->getOption('h')) {
            Lms_Api_Cli::showUsageAndExit($opts, 0);
        }

        if ($value = $opts->getOption('uid')) {
            $userId = $value;
        } else {
            $username = $opts->getOption('u');
            if (!$username) {
                throw new Zend_Console_Getopt_Exception(
                    "�� ������ �������� -u ��� --uid",
                    $opts->getUsageMessage());
            }
            $user = Lms_Item_User::getByUserName($username);
            if (!$user) {
                throw new Zend_Console_Getopt_Exception(
                    "������������ '$username' �� ����������",
                    $opts->getUsageMessage());
            }
            $userId = $user->getId();
        }
        $movieId = $opts->getOption('m');
        if (!$movieId) {
            throw new Zend_Console_Getopt_Exception(
                "�� ������ �������� -m",
                $opts->getUsageMessage());
        }

        $ip = $opts->getOption('ip');
        
        $hit = Lms_Item_Hit::select($movieId, $userId, $ip);
        if (!$hit) {
            $hit = Lms_Item::create('Hit');
            $hit->setMovieId($movieId)
                ->setUserId($userId)
                ->setIp($opts->getOption('ip'));
        }
        $hit->setCreatedAt($opts->getOption('c'))
            ->save();
        
        if ($opts->getOption('force-update')) {
            $movie = Lms_Item::create('Movie', $movieId);
            $movie->updateHit()
                  ->save();
        }
        
        
        return $hit->getId();
    }
    
    public static function update()
    {
        $opts = new Zend_Console_Getopt(
            array(
                'help|h'    => '�������� �������',
                'all|a'    => '����������� ���������� ���������� ���� �������, �������� -m ����� ��������������',
                'movie-id|m=i'    => 'ID ������, ��� �������� ���������� ����������� ���������� ����������',
            )
        );
        $opts->parse();
        
        if ($opts->getOption('h')) {
            Lms_Api_Cli::showUsageAndExit($opts, 0);
        }
        
        if ($opts->getOption('a')) {
            Lms_Item_Hit::updateMoviesHit();
        } else if ($movieId = $opts->getOption('m')) {
            $movie = Lms_Item::create('Movie', $movieId);
            $movie->updateHit()
                  ->save();
        } else {
            throw new Zend_Console_Getopt_Exception(
                "���������� ������ �������� -m ��� -a",
                $opts->getUsageMessage());
        }
        return;
    }
    
}
