<?php

use Page\Ovariables as oPageVariables;
use Page\Login as LoginPage;
use \Helper\Acceptance;
use \Codeception\Lib\Interfaces\SessionSnapshot;


/**
 * Class testorphansCest
 */
class testorphansCest
{
    /** @var $modx modX */
    public $modx = null;
    /** @var \AcceptanceTester $I */
    public $I;



    public function _before(\AcceptanceTester $I)
    {
        $I->wantToTest('Orphans');
        ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
        if (!class_exists('modX')) {
            include "c:/xampp/htdocs/test/core/config/config.inc.php";
            include MODX_CORE_PATH . 'model/modx/modx.class.php';
        }

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
            'TV',
        );


        foreach ($objects as $object) {
            $nameField = $object == 'Template' ? 'templatename' : 'name';
            $class = 'mod' . (($object == 'TV')? 'TemplateVar' : $object);
            /* *************** */
            $I->wantTo('Clean up old renamed objects');
            $oldObject = $modx->getObject($class, array($nameField => 'aaOrphan.' . oPageVariables::$namePrefix . $object));
            if ($oldObject) {
                $oldObject->remove();
            }

            /* *************** */
            $I->wantTo('Create new objects for tests');
            $newObject = $modx->getObject($class, array($nameField => oPageVariables::$namePrefix . $object));
            if (! $newObject) {
                $newObject = $modx->newObject($class);
                $newObject->set($nameField, oPageVariables::$namePrefix . $object);
                $newObject->set('description', $object . ' for Orphans test');
                $success = $newObject->save();
                $I->assertNotFalse($success);
            }
        }
        /* Empty Ignore chunk */
        $obj = $this->modx->getObject('modChunk', array('name' => 'OrphansIgnoreList'));
        $I->assertNotEmpty($obj);
        $obj->setContent("OrphansIgnoreList\n");
        $obj->save();
        /* *************** */
        $I->wantTo('Create a category');
        $cat = $modx->getObject('modCategory', array('category' => oPageVariables::$category));
        if (!$cat) {
            $cat = $modx->newObject('modCategory');
            $cat->set('category', oPageVariables::$category);
            $cat->save();
        }
    }
/** @method void waitForElement($element, $delay = null, $tag = null) */


    /**
     * @param $I \AcceptanceTester
     * @throws \Exception
     */
    public function tryToTest(\AcceptanceTester $I)
    {

        $types = array(
            'Chunk',
            'Snippet',
            'Template',
            'TV'
        );
        /* *************** */
        $I->wantTo('Log In');
        $loginPage = new LoginPage($I);
        $loginPage->login();

        /* *************** */
        $I->wantTo('Launch Orphans');
        $I->amOnPage(oPageVariables::$orphansPage);

        foreach ($types as $type) {
            $I->seeMyVar($type); // log to console using helper
            $name = $type;
            $nameLower = strtolower($name);
            $namePlural = $name . 's';
            $namePluralLower = strtolower($namePlural);

            $tabHeading = ($name == 'TV') ? 'Template Variables' : $namePlural;
            $I->waitForElementVisible("//span[contains(@class, 'x-tab-strip-text') and text() = '{$tabHeading}']");
            $I->click("//span[contains(@class, 'x-tab-strip-text') and text() = '{$tabHeading}']");

            /* *************** */
            $I->wantTo('Load objects in grid');
            $element = "#orphans-{$nameLower}s-reload";
            // $I->performOn($element, array('click' => $element), 5);
            $I->waitForElementVisible($element, 15);
            // $I->see($element);  // Crashes run
            $I->click($element);

            $I->waitForElement("//div[contains(., '" . oPageVariables::$namePrefix . $name . "')]", 10);

            $I->see(oPageVariables::$namePrefix . $name);

            /* *************** */
            $I->wantToTest('Rename and UN-Rename');

            $I->clickWithRightButton("//div[text() = '" . oPageVariables::$namePrefix . $name . "']");
            $I->waitForElement("//span[text() = 'Rename {$name}(s)']");
            $I->see("Rename {$name}(s)");

            $I->click("//span[text() = 'Rename {$name}(s)']");
            $I->waitForText("aaOrphan." . oPageVariables::$namePrefix . $name, 3);

            $I->clickWithRightButton("//div[text() = 'aaOrphan." . oPageVariables::$namePrefix  . $name . "']");
            $I->waitForElement("//span[text() = 'UN-Rename {$name}(s)']", 4);

            $I->see("UN-Rename {$name}(s)");

            /* Un-rename Chunks */
            $I->click("//span[text() = 'UN-Rename {$name}(s)']");
            $I->wait(2);
            $I->dontSee("aaOrphan." . oPageVariables::$namePrefix . $name);

            /* *************** */
            $I->wantToTest('Changing a category');
            $I->clickWithRightButton("//div[text() = '" . oPageVariables::$namePrefix . $name . "']");
            $element = "//span[contains(@class, 'x-menu-item-text') and text() = 'Change {$name} Category']";
            $I->waitForElement($element);

          //   $I->see($element);
            $I->click($element);
            $I->waitForElement("#orphans-{$nameLower}-category-combo");
            $I->click("#orphans-{$nameLower}-category-combo");
            $category = oPageVariables::$category;
            $I->waitForElement("//div[contains(@class, 'x-combo-list-item') and text() = '{$category}']");
            // $I->wait(3);
            $I->click("//div[contains(@class, 'x-combo-list-item') and text() = '{$category}']");
            $I->click(oPageVariables::$changeCategorySaveButton);

            /* *************** */
            $I->wantToTest('Add Element to Ignore List');
            $I->waitForElement("//div[text() = '" . oPageVariables::$namePrefix . $name . "']");
            $I->wait(1);
            $I->clickWithRightButton("//div[text() = '" .  oPageVariables::$namePrefix . $name . "']");
            $I->waitForElementVisible(oPageVariables::$addToIgnoreListContextOption);
            $I->click(oPageVariables::$addToIgnoreListContextOption);
            $I->wait(2);
            $I->dontSee( oPageVariables::$namePrefix . $name);
            $obj = $this->modx->getObject('modChunk', array('name' => oPageVariables::$ignoreChunk));
            $I->assertNotEmpty($obj);
            $c = $obj->getContent();
            $I->assertContains(oPageVariables::$namePrefix . $name, $c);

            /* Empty Ignore list so element will reappear  */
            $obj->setContent(oPageVariables::$ignoreChunkText);
            $obj->save();

            /* Reload objects */
            $I->click("#orphans-{$nameLower}s-reload");
            $I->waitForText(oPageVariables::$namePrefix . $name, 20);
            $I->see("OrphansTest{$name}");
  //          $I->wait(1);
            $I->see(oPageVariables::$namePrefix . $name);

            /* *************** */
            $I->wantToTest('Deleting an element');
//            $I->wait(2);
            $I->waitForElement("//div[text() = '" . oPageVariables::$namePrefix . $name . "']");
            $I->clickWithRightButton("//div[text() = '" . oPageVariables::$namePrefix . $name . "']");

            $I->waitForElementVisible("//span[text() = 'Delete {$name}(s)']", 2);
            $I->click("//span[text() = 'Delete {$name}(s)']");
            /* Confirm Delete  */
            $I->waitForElement(oPageVariables::$deleteYesButton);
            $I->click(oPageVariables::$deleteYesButton);
            $I->wait(1);
            $I->dontSee(oPageVariables::$namePrefix . $name);
            $I->reloadPage();
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
            $nameSuffix = ($object == 'TemplateVar') ? 'TV' : $object;
            /* Get rid of leftover renamed objects */
            $oldObject = $this->modx->getObject('mod' . $object, array($nameField => 'aaOrphan.' . oPageVariables::$namePrefix . $nameSuffix));
            if ($oldObject) {
                $oldObject->remove();
            }

            /* delete objects */
            $newObject = $this->modx->getObject('mod' . $object, array($nameField => oPageVariables::$namePrefix . $nameSuffix));
            if ($newObject) {
                $newObject->remove();
            }

            $cat = $this->modx->getObject('modCategory', array('category' => oPageVariables::$category));
            if ($cat) {
                $cat->remove();
            }
        }
    }
}

