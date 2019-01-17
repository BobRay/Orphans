<?php 


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
        $I->amOnPage("manager/");

       // $I->amOnPage($managerUrl);

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

        $I->wantTo('Load objects in grid');
        $I->waitForElement("#orphans-chunks-reload", 5);
        $I->click("#orphans-chunks-reload");
        $I->waitForElement('//div[contains(., "OrphansTestChunk")]', 3);
        $I->see('OrphansTestChunk');

        $I->wantToTest('Rename and UN-Rename');

        $I->clickWithRightButton("//div[text() = 'OrphansTestChunk']");
        $I->waitForElement("//span[text() = 'Rename Chunk(s)']");
        $I->see('Rename Chunk(s)');

        $I->click("//span[text() = 'Rename Chunk(s)']");
        $I->waitForText('aaOrphan.OrphansTestChunk', 3);

        $I->clickWithRightButton("//div[text() = 'aaOrphan.OrphansTestChunk']");
        $I->waitForElement("//span[text() = 'UN-Rename Chunk(s)']", 4);

        $I->see('UN-Rename Chunk(s)');
              // Un-rename Chunks
        $I->click("//span[text() = 'UN-Rename Chunk(s)']");
        $I->wait(2);
        $I->dontSee('aaOrphan.OrphansTestChunk');

        $I->wantToTest('Changing a category');
        $I->clickWithRightButton("//div[text() = 'OrphansTestChunk']");
        $I->waitForElement("//span[text() = 'Change Category']");
        $I->see('Change Category');
        $I->click("//span[text() = 'Change Category']");
        $I->waitForElement("#orphans-chunk-category-combo");
        $I->click("#orphans-chunk-category-combo");
        $I->waitForElement("//div[contains(@class, 'x-combo-list-item') and text() = 'abOrphans']");
        $I->click("//div[contains(@class, 'x-combo-list-item') and text() = 'abOrphans']");
        $I->click("//button[contains(@class, 'x-btn-text') and text() = 'Save']");

        $I->wantToTest('Deleting an element');
        $I->wait(2);
        $I->waitForElement("//div[text() = 'OrphansTestChunk']");
        $I->clickWithRightButton("//div[text() = 'OrphansTestChunk']");
        $I->waitForElementVisible("//span[text() = 'Delete Chunk(s)']");
        $I->click("//span[text() = 'Delete Chunk(s)']");
        $I->waitForElement("//button[contains(@class, 'x-btn-text') and text() = 'Yes']");
        $I->click("//button[contains(@class, 'x-btn-text') and text() = 'Yes']");
        $I->wait(1);
        $I->dontSee('OrphansTestChunk');

        $I->wait(5);
    }

    /*protected function testObject(\AcceptanceTester $I, $class, $name) {
        $I->assertTrue(true);
        $I->see('Nothing');
        return true;
    }*/

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

