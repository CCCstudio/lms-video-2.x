<?php

class Lms_Api_Cli_Rating {

    public static function add()
    {
        $opts = new Zend_Console_Getopt(
            array(
                'help|h'    => '�������� �������',
                'user-name|u=s'    => '��� ������������ (�����)',
                'uid=i'    => 'ID ������������, �������� -u ����� ��������������',
                'rating|r=i'    => '������� 1..10',
                'created-at|c=s'    => '���� �������� � ������� yyyy-mm-dd hh:mm:ss',
                'movie-id|m=i'    => 'ID ������',
                'force-update'    => '����������� ��������� ������� ������ ����� (�� �������������), ��. ����� ������� "... rating update -h"',
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

        $rating = $opts->getOption('r');
        if ($rating<1 || $rating>10) {
            throw new Zend_Console_Getopt_Exception(
                "�������� -r ������ ���� � �������� �� 1 �� 10",
                $opts->getUsageMessage());
        }
        
        $userRating = Lms_Item::create('MovieUserRating');
        $userRating->setMovieId($movieId)
                   ->setUserId($userId)
                   ->setRating($rating)
                   ->setCreatedAt($opts->getOption('c'))
                   ->save();

        if ($opts->getOption('force-update')) {
            Lms_Item_Movie::updateLocalRating($movieId);
        }
        
        
        return $userRating->getId();
    }
    
    public static function update()
    {
        $opts = new Zend_Console_Getopt(
            array(
                'help|h'    => '�������� �������',
                'all|a'    => '����������� ��������� �������� ���� �������, �������� -m ����� ��������������',
                'movie-id|m=i'    => 'ID ������, ��� �������� ���������� ����������� ��������� �������',
            )
        );
        $opts->parse();
        
        if ($opts->getOption('h')) {
            Lms_Api_Cli::showUsageAndExit($opts, 0);
        }
        
        if ($opts->getOption('a')) {
            Lms_Item_Rating::updateLocalRatings();
        } else if ($movieId = $opts->getOption('m')) {
            Lms_Item_Movie::updateLocalRating($movieId);
        } else {
            throw new Zend_Console_Getopt_Exception(
                "���������� ������ �������� -m ��� -a",
                $opts->getUsageMessage());
        }
        return;
        
    }
}
