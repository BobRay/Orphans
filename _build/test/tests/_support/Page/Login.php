<?php
namespace Page;

use Codeception\Lib\Interfaces\SessionSnapshot;
use PHPUnit\Runner\Exception;

class Login {

    public static $managerUrl = 'manager/';
    public static $usernameField = '#modx-login-username';
    public static $passwordField = '#modx-login-password';
    public static $username = 'JoeTester';
    public static $password = 'testerPassword';
    public static $loginButton = '#modx-login-btn';

    /**
     * @var \AcceptanceTester $tester
     */
    protected $tester;


    public function __construct(\AcceptanceTester $I) {
        $this->tester = $I;
    }

    /* @throws Exception */
    public function login($username = '', $password = '') {
        /* @var \AcceptanceTester $I */
        $username = empty($username) ? self::$username : $username;
        $password = empty($password) ? self::$password : $password;
        $I = $this->tester;
       /* if ($I->loadSessionSnapshot('login')) {
            return;
        }*/


        $I->amOnPage(self::$managerUrl);
        $I->fillField(self::$usernameField, $username);
        $I->fillField(self::$passwordField, $password);
        $I->click(self::$loginButton);

        $I->saveSessionSnapshot('login');

        return $this;
    }
}
