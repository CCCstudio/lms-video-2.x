<?php

class Lms_Api_Cli_Comment {

    public static function add()
    {
        $opts = new Zend_Console_Getopt(
            array(
                'help|h'    => '�������� �������',
                'user-name|u=s'    => '��� ������������ (�����)',
                'uid=i'    => 'ID ������������, �������� -u ����� ��������������',
                'text|t=s'    => '����� ����������� (� ������� plain text)',
                'created-at|c=s'    => '���� �������� � ������� yyyy-mm-dd hh:mm:ss',
                'ip=s'    => 'IP ������',
                'movie-id|m=i'    => 'ID ������',
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
        $movie = Lms_Item::create('Movie', $movieId);

        $db = Lms_Db::get('main');
        $db->transaction();

        $comment = Lms_Item::create('Comment');
        $comment->setText($opts->getOption('t'))
                ->setUserId($userId)
                ->setCreatedAt($opts->getOption('c'))
                ->setIp($opts->getOption('ip'));
        
        $movie->add($comment);
        
        $db->commit();

        return $comment->getId();
    }
}
