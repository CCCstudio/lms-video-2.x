<?php

class Lms_Api_Cli_User {

    public static function add()
    {
        $opts = new Zend_Console_Getopt(
            array(
                'help|h'    => '�������� �������',
                'user-name|u=s'    => '��� ������������ (�����)',
                'password|p=s'    => '������, �������� --password-hash ����� ��������������',
                'password-hash=s'    => 'MD5-��� ������',
                'email|e=s'    => 'email',
                'ip=s'    => 'IP ����������� ��� �����������',
                'user-group|g=i'    => '������ ������������ (1 - ������������, 2 - ���������, 3 - �������������), ��-��������� 1',
                'register-at|r=s'    => '���� ����������� � ������� yyyy-mm-dd hh:mm:ss',
                'enabled=i'    => '���� ���������� (��-��������� 1)',
            )
        );
        $opts->parse();
        
        if ($opts->getOption('h')) {
            Lms_Api_Cli::showUsageAndExit($opts, 0);
        }

        if ($value = $opts->getOption('p')) {
            $passmd5 = md5($value);
        } else {
            $passmd5 = $opts->getOption('password-hash');
        }
        
        $userName = $opts->getOption('u');
        if (!Lms_Item_User::loginIsFree($userName)) {
            throw new Zend_Console_Getopt_Exception(
                "������������ '$userName' ��� ����������",
                $opts->getUsageMessage());
        }
        $newUser = Lms_Item::create('User');
        $newUser->setLogin($userName)
                ->setPassword($passmd5)
                ->setEmail($opts->getOption('e'))
                ->setUserGroup($opts->getOption('g')?: Lms_Item_User::USER_GROUP_USER)
                ->setBalans(1)
                ->setIp($opts->getOption('ip'))
                ->setsetRegisterDate($opts->getOption('r'))
                ->setEnabled($opts->getOption('enabled')?: 1)
                ->setPreferences('')
                ->save();

        return $newUser->getId();
    }
}
