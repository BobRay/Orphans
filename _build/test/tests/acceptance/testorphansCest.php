<?php

use Page\Ovariables as Variables;

class testorphansCest
{
    /** @var $modx modX */
    public $modx = null;


    public function _before(\AcceptanceTester $I)
    {
        $I->wantToTest('Orphans');
        ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
        include "c:/xampp/htdocs/test/core/config/config.inc.php";
        include MODX_CORE_PATH . 'model/modx/modx.class.php';
        $this->modx = new modX();

        $modx =& $this->modx;
        $modx->initialize('mgr');

        $user = $modx->getObject('modUser', array('username' => 'JoeTester'));
        if (! $user) {
            $fields = array(
                'username' => 'JoeTester',
                'password' => 'testerPassword',
                'specifiedpassword' => 'testerPassword',
                'confirmpassword' => 'testerPassword',
                'email'=> 'bobray99@gmail.com',
                'passwordnotifymethod' => 's',
                'passwordgenmethod' => 'x',
                'active' => '1',
            );

            $modx->runProcessor('security/user/create', $fields);
            $user = $modx->getObject('modUser', array('username' => 'JoeTester'));
            /** @var $user modUser */
            $user->joinGroup('Administrator', 2);
        }


        $modx->user =& $user;

        $objects = array(
            'Chunk',
            'Snippet',
            'Template',
            'TemplateVar',
        );


        foreach ($objects as $object) {
            $nameField = $object == 'Template' ? 'templatename' : 'name';

            $I->wantTo('Clean up old renamed objects');
            $oldObject = $modx->getObject('mod' . $object, array($nameField => 'aaOrphan.OrphansTest' . $object));
            if ($oldObject) {
                $oldObject->remove();
            }

            $I->wantTo('Create new objects for tests');
            $newObject = $modx->getObject('mod' . $object, array($nameField => 'OrphansTest' . $object));
            if (! $newObject) {
                $newObject = $modx->newObject('mod' . $object);
                $newObject->set($nameField, 'OrphansTest' . $object);
                $newObject->set('description', $object . ' for Orphans test');
                $newObject->save();
            }
        }

        $I->wantTo('Create a category');
        $cat = $modx->getObject('modCategory', array('category' => 'abOrphans'));
        if (!$cat) {
            $cat = $modx->newObject('modCategory');
            $cat->set('category', 'abOrphans');
            $cat->save();
        }
    }

    // tests
    public function tryToTest(\AcceptanceTester $I)
    {
          /** @var $I AcceptanceTester */
      //   $I->amOnPage("manager/");

       $class = 'modChunk';
       $name = 'Chunk';
       $nameLower = strtolower($name);
       $namePlural = $name . 's';
       $namePluralLower = strtolower($namePlural);
       $I->amOnPage(Variables::$managerUrl);

        $I->wantTo('Log In');

        try {
            $I->see("Login");
            $I->fillField('#modx-login-username', 'JoeTester');
            $I->fillField('#modx-login-password', 'testerPassword');
            $I->click('#modx-login-btn');
        } catch (Exception $e) {

        }

        $I->wantTo('Launch Orphans');
        $I->waitForElement('#limenu-components > a',10);
        $I->moveMouseOver('#limenu-components > a');
        $I->wait(1);


        // $I->see('Extras');
        $I->waitforElement('#orphans');
        $I->click('#orphans');
        // span.x-tab-strip-text
        //"//div[contains(@class, 'x-combo-list-item') and text() = 'abOrphans']"
        $I->waitForElement("//span[contains(@class, 'x-tab-strip-text') and text() = '{$namePlural}']");
        $I->click("//span[contains(@class, 'x-tab-strip-text') and text() = '{$namePlural}']");
        $I->wantTo('Load objects in grid');
        $I->waitForElement("#orphans-{$nameLower}s-reload", 5);
        $I->click("#orphans-{$nameLower}s-reload");
        $I->waitForElement("//div[contains(., 'OrphansTest{$name}')]", 3);
        $I->see("OrphansTest{$name}");

        $I->wantToTest('Rename and UN-Rename');

        $I->clickWithRightButton("//div[text() = 'OrphansTest{$name}']");
        $I->waitForElement("//span[text() = 'Rename {$name}(s)']");
        $I->see("Rename {$name}(s)");

        $I->click("//span[text() = 'Rename {$name}(s)']");
        $I->waitForText("aaOrphan.OrphansTest{$name}", 3);

        $I->clickWithRightButton("//div[text() = 'aaOrphan.OrphansTest{$name}']");
        $I->waitForElement("//span[text() = 'UN-Rename {$name}(s)']", 4);

        $I->see("UN-Rename {$name}(s)");
              // Un-rename Chunks
        $I->click("//span[text() = 'UN-Rename {$name}(s)']");
        $I->wait(2);
        $I->dontSee("aaOrphan.OrphansTest{$name}");

        $I->wantToTest('Changing a category');
        $I->clickWithRightButton("//div[text() = 'OrphansTest{$name}']");
        $I->waitForElement("//span[text() = 'Change Category']");
        $I->see('Change Category');
        $I->click("//span[text() = 'Change Category']");
        $I->waitForElement("#orphans-{$nameLower}-category-combo");
        $I->click("#orphans-{$nameLower}-category-combo");
        $I->waitForElement("//div[contains(@class, 'x-combo-list-item') and text() = 'abOrphans']");
        $I->click("//div[contains(@class, 'x-combo-list-item') and text() = 'abOrphans']");
        $I->click("//button[contains(@class, 'x-btn-text') and text() = 'Save']");

        $I->wantToTest('Deleting an element');
        $I->wait(2);
        $I->waitForElement("//div[text() = 'OrphansTest{$name}']");
        $I->clickWithRightButton("//div[text() = 'OrphansTest{$name}']");
        $I->waitForElementVisible("//span[text() = 'Delete {$name}(s)']");
        $I->click("//span[text() = 'Delete {$name}(s)']");
        $I->waitForElement("//button[contains(@class, 'x-btn-text') and text() = 'Yes']");
        $I->click("//button[contains(@class, 'x-btn-text') and text() = 'Yes']");
        $I->wait(1);
        $I->dontSee("OrphansTest{$name}");

        $I->wantToTest('Add Element to Ignore List');

        $I->wait(5);
    }


    public function _after(\AcceptanceTester $I) {
        $objects = array(
            'Chunk',
            'Snippet',
            'Template',
            'TemplateVar',
        );


        foreach ($objects as $object) {
            $nameField = $object == 'Template' ? 'templatename' : 'name';

            /* Get rid of leftover renamed objects */
            $oldObject = $this->modx->getObject('mod' . $object, array($nameField => 'aaOrphan.OrphansTest' . $object));
            if ($oldObject) {
                $oldObject->remove();
            }

            /* delete objects */
            $newObject = $this->modx->getObject('mod' . $object, array($nameField => 'OrphansTest' . $object));
            if ($newObject) {
                $newObject->remove();
            }

            $cat = $this->modx->getObject('modCategory', array('category' => 'abOrphans'));
            if ($cat) {
                $cat->remove();
            }
        }
    }
}

