<?php


class testorphansCest {
    /** @var $modx modX */
    public $modx = null;

    public function _before(\AcceptanceTester $I) {
        $I->assertTrue(true);
        ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
        include "c:/xampp/htdocs/test/core/config/config.inc.php";
        include MODX_CORE_PATH . 'model/modx/modx.class.php';
        $this->modx = new modX();

        $modx =& $this->modx;
        $modx->initialize('mgr');

        $user = $modx->getObject('modUser', array('username' => 'JoeTester'));
        if (!$user) {
            $fields = array(
                'username' => 'JoeTester',
                'password' => 'testerPassword',
                'specifiedpassword' => 'testerPassword',
                'confirmpassword' => 'testerPassword',
                'email' => 'bobray99@gmail.com',
                'passwordnotifymethod' => 's',
                'passwordgenmethod' => 'x',
                'active' => '1',
            );

            $modx->runProcessor('security/user/create', $fields);
            $user = $modx->getObject('modUser', array('username' => 'JoeTester'));
            $user->joinGroup('Administrator');
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

            /* Get rid of leftover renamed objects */
            $oldObject = $modx->getObject('mod' . $object, array($nameField => 'aaOrphan.OrphansTest' . $object));
            if ($oldObject) {
                $oldObject->remove();
            }

            /* Create new objects unless they're already there */
            $newObject = $modx->getObject('mod' . $object, array($nameField => 'OrphansTest' . $object));
            if (!$newObject) {
                $newObject = $modx->newObject('mod' . $object);
                $newObject->set($nameField, 'OrphansTest' . $object);
                $newObject->set('description', $object . ' for Orphans test');
                $newObject->save();
            }
        }

        /* Create Category  */
        $cat = $modx->getObject('modCategory', array('category' => 'abOrphans'));
        if (!$cat) {
            $cat = $modx->newObject('modCategory');
            $cat->set('category', 'abOrphans');
            $cat->save();
        }

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

    // tests
    /* @throws \Exception */
    public function tryToTest(\AcceptanceTester $I) {
        /** @var $I AcceptanceTester */
        $I->amOnPage("manager/");

        /* Log In */
        try {
            $I->see("Login");
            $I->fillField('#modx-login-username', 'JoeTester');
            $I->fillField('#modx-login-password', 'testerPassword');
            $I->click('#modx-login-btn');
        } catch (Exception $e) {

        }

        /* Launch Orphans */
        $I->waitForElement('#limenu-components > a', 10);
        $I->moveMouseOver('#limenu-components > a');
        $I->see('Extras');
        $I->waitforElement('#orphans');
        $I->click('#orphans');

        /* Load objects in grid */
        $I->waitForElement("#orphans-chunks-reload", 5);
        $I->click("#orphans-chunks-reload");
        $I->waitForElement('//div[contains(., "OrphansTestChunk")]', 3);
        $I->see('OrphansTestChunk');

        /* Test Rename and UN-Rename*/

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

        /* Test Change Category */
        $I->clickWithRightButton("//div[text() = 'OrphansTestChunk']");
        $I->waitForElement("//span[text() = 'Change Category']");
        $I->see('Change Category');
        $I->click("//span[text() = 'Change Category']");
        $I->waitForElement("#orphans-chunk-category-combo");
        $I->click("#orphans-chunk-category-combo");
        $I->waitForElement("//div[contains(@class, 'x-combo-list-item') and text() = 'abOrphans']");
        $I->click("//div[contains(@class, 'x-combo-list-item') and text() = 'abOrphans']");
        $I->click("//button[contains(@class, 'x-btn-text') and text() = 'Save']");

        /* Test Delete */
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
}
