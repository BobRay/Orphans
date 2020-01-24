<?php
/**
 * Orphans class file for Orphans extra
 *
 * Copyright 2013-2019 Bob Ray <https://bobsguides.com>
 * Created on 04-13-2013
 *
 * Orphans is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * Orphans is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Orphans; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package orphans
 */
/**
 * @package orphans
 */

/* These are for LexiconHelper */
// include 'addtoignore.php'
// include 'changecategory.php'
// include 'delete.php'
// include 'rename.php'
// include 'unrename.php'
// include 'getlist.php'


if (! class_exists('OrphanObject')) {
    class OrphanObject {
        public $name = '';
        public $type = '';
        public $id = '';
        public $category = '';
        public $description = '';
        public $found = false;

        function __construct($fields, $type, $found = false) {
            $this->name = $fields['name'];
            $this->type = $type;
            $this->id = $fields['id'];
            $this->category = $fields['category'];
            $this->description = $fields['description'];
            $this->found = $found;
        }

        /**
         * If my name occurs in the $content and I'm not already found,
         * set me as found
         * @param $content - string
         */
        public function findMe($content) {
            /* Skip if I'm already found */
            if (!$this->found) {
                if (strpos($content, $this->name) !== false) {
                    $this->found = true;
                }
            }
        }
    }
}

if (! class_exists('Orphans')) {
    class Orphans {
        public $files = array();
        public $classFiles = array();
        public $output = '';
        public $included = array();
        public $scriptCode = '';
        public $allChunks = array();
        public $foundChunks = array();
        public $allObjects = array();
        public $orphans = array();
        public $objectTypes = array(
            'modTemplate',
            'modChunk',
            'modResource',
            'modTemplateVar',
            'modSnippet',
            'modPlugin'
        );
        /** @var $request modManagerRequest */
        public $request = null;
        public $classFileCount = 0;
        /* @var $modx modX */
        public $modx;

        function __construct(modX &$modx, array $config = array()) {
            $this->modx =& $modx;
            $corePath = $modx->getOption('orphans.core_path', null, $modx->getOption('core_path') . 'components/orphans/');
            $assetsUrl = $modx->getOption('orphans.assets_url', null, $modx->getOption('assets_url') . 'components/orphans/');

            $this->config = array_merge(array(
                'corePath' => $corePath,
                'chunksPath' => $corePath . 'elements/chunks/',
                'modelPath' => $corePath . 'model/',
                'processorsPath' => $corePath . 'processors/',
                'prefix' => $this->modx->getOption('orphans.prefix', null, 'aaOrphan.'),
                'assetsUrl' => $assetsUrl,
                'connectorUrl' => $assetsUrl . 'connector.php',
                'cssUrl' => $assetsUrl . 'css/',
                'jsUrl' => $assetsUrl . 'js/',
            ), $config);

            $this->modx->addPackage('orphans', $this->config['modelPath']);
            if ($this->modx->lexicon) {
                $this->modx->lexicon->load('orphans:default');
            }
        }

        function makeOrphan($fields, $type, $found = false) {
            $orphan = array();
            $orphan['name'] = $fields['name'];
            $orphan['type'] = $type;
            $orphan['id'] = $fields['id'];
            $orphan['category'] = $fields['category'];
            $orphan['description'] = $fields['description'];
            $orphan['found'] = $found;
            return $orphan;
        }


        /**
         * Initializes Orphans based on a specific context.
         *
         * @access public
         * @param string $ctx The context to initialize in.
         * @return string The processed content.
         */
        public function initialize($ctx = 'mgr') {
            @set_time_limit(0);
            @ini_set('memory_limit', '1024M');
            $output = '';
            switch ($ctx) {
                case 'mgr':
                    if (!$this->modx->loadClass('orphans.request.OrphansControllerRequest', $this->config['modelPath'], true, true)) {
                        return 'Could not load controller request handler.';
                    }
                    $this->request = new OrphansControllerRequest($this);
                    $output = $this->request->handleRequest();
                    break;
            }
            return $output;
        }

        public function addFile($dir, $file) {
            $this->files[$file] = $dir;
        }

        public function resetFiles() {
            $this->files = array();
        }

        public function getFiles() {
            return $this->files;
        }

        public function getOutput() {
            return $this->output;
        }


        public function getNameAlias($type) {
            switch ($type) {
                case 'modTemplate':
                    $nameAlias = 'templatename';
                    break;

                case 'modResource':
                    $nameAlias = 'pagetitle';
                    break;

                default:
                    $nameAlias = 'name';
                    break;
            }
            return $nameAlias;

        }

        /**
         * If my name occurs in the $content and I'm not already found,
         * set me as found
         * @param $content - string
         */
        public function findMe(&$orphan, $content) {
            /* Skip if I'm already found */
            if (strpos($content, $orphan['name']) !== false) {
                return true;
            }

            return false;
        }

        public function process($type) {

            if (empty ($this->classFiles)) {
                $this->getClassFiles();
            }
            // $this->classFileCount = count($this->classFiles);
            $objectTypes = $this->objectTypes;
            $c = $this->modx->newQuery($type);
            if ($type == 'modTemplate') {
                $c->select(array('id', 'templatename', 'category', 'description'));
                $c->sortby('templatename');
            } else {
                $c->select(array('id', 'name', 'category', 'description'));
                $c->sortby('name');
            }
            $c->limit(300);
            /* ToDo: Use getCollectionGraph here */
            $objects = $this->modx->getCollection($type, $c);

            /* get Ignore List */
            $ignoreChunk = $this->modx->getObject('modChunk', array('name' => 'OrphansIgnoreList'));
            $ignoreList = $ignoreChunk ? $ignoreChunk->getContent() : '';

            /* @var $object modElement */
            foreach ($objects as $object) {
                $fields = $object->toArray('', true, true);
                if ($type == 'modTemplate') {
                    $fields['name'] = $fields['templatename'];
                }
                /* check ignore list */
                if (strpos($ignoreList, $fields['name']) !== false) {
                    continue;
                }
                $cat = $this->modx->getObject('modCategory', $fields['category']);
                $fields['category'] = $cat ? $cat->get('category') : '';
                $obj = $this->makeOrphan($fields, $type);
                $this->allObjects[$fields['id']] = $obj;
                unset ($object);

            }
            unset($objects);

            /* Get each object again, one at a time, to save memory */

            /* Warning! Reading this section of code may be hazardous to your sanity */

            /* iterate through collections of resources, snippets, chunks, etc. */

            $this->orphans = array();
            foreach ($objectTypes as $objectType) {
                $c = $this->modx->newQuery($objectType);

                switch ($objectType) {
                    case 'modResource':
                        $c->select(array('id', 'content', 'template', 'class_key'));
                        break;
                    case 'modChunk';
                        $c->select(array('id', 'snippet', 'properties'));
                        break;
                    case 'modSnippet':
                        $c->select(array('id', 'snippet', 'properties'));
                        break;
                    case 'modPlugin':
                        $c->select(array('id', 'plugincode', 'properties'));
                        break;
                    case 'modTemplate':
                        $c->select(array('id', 'content', 'properties'));

                        break;
                    case 'modTemplateVar':
                        $c->select(array('id', 'default_text', 'input_properties', 'output_properties', 'properties'));
                        break;
                }

                $objects = $this->modx->getCollection($objectType, $c);
                foreach ($objects as $object) {
                    /* @var $orphanObject OrphanObject */
                    /* Get concatenated content + included files + properties, etc. */
                    $content = $this->getContent($object, $objectType);
                    /* look through all objects of this type for each orphan of any type.
                       findMe() sets its 'found' member if found anywhere */
                    foreach ($objectTypes as $orphanType) {
                        @set_time_limit(0);
                        /* get the orphan objects of one type */
                        $orphanObjects = $this->allObjects;
                        /* Check each one against the content of the current $object */
                        foreach ($orphanObjects as $orphanObject) {
                            /* skip if already found */
                            if ($orphanObject == null) {
                                continue;
                            }
                            $nameAlias = $this->getNameAlias($objectType);
                            /* don't look for myself in myself */
                            if (($orphanObject['type'] == $objectType) &&
                                ($orphanObject['name'] == $object->get($nameAlias))) {
                                continue;
                            }
                            if ($this->findMe($orphanObject, $content)) {
                                $this->allObjects[$orphanObject['id']] = null;
                            }

                            //$orphanObject->findMe($content);
                        }
                    }
                    unset($orphanObjects, $orphanObject);
                }
                unset($objects, $object);
            }
            return $this->prepareReturn($type);


        }

        public function prepareReturn($type) {
            $orphans = array();
            $objects = $this->allObjects;
            foreach ($objects as $id => $object) {
                if ($object !== null) {
                    unset ($object['found']);
                    unset ($object['type']);
                    if ($type == 'modTemplate') {
                        $object['templatename'] = $object['name'];
                        unset ($object['templateName']);
                    }
                    $orphans[] = $object;
                }
            }
            return $orphans;
        }

        public function getContent($object, $objectType) {
            /* @var $object xPDOObject */
            $fields = $object->toArray('', true, true);
            if (isset($fields['class_key'])
                && ($fields['class_key'] == 'modStaticResource')) {
                /* @var $object modResource */

                $fields['content'] = $object->getContent();
            }
            $content = implode(' ', $fields);
            switch ($objectType) {
                case 'modResource':
                    $templateId = $fields['template'];
                    /* append the template name */
                    $templateObj = $this->modx->getObject('modTemplate', $templateId);
                    if ($templateObj) {
                        $content .= ' ' . $templateObj->get('templatename');
                    }
                    break;

                case 'modSnippet':
                    $content .= $this->getOptions($content);
                    $this->scriptCode = '';
                    $this->resetFiles();
                    $this->getIncludes('', $content);
                    $content .= $this->scriptCode;
                    break;

                case 'modPlugin':
                    $content = $object->get('plugincode');
                    $content .= $this->getOptions($content);
                    $this->scriptCode = '';
                    $this->resetFiles();
                    $this->getIncludes('', $content);
                    $content .= $this->scriptCode;
                    break;

                default:
                    break;
            }
            return $content;

        }

        public function getOptions($content) {
            $extraContent = '';
            $matches = array();
            preg_match_all('#modx->getOption.*\(\'([a-zA-Z0-9_.-]+)\'#', $content, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $property) {
                    $property = trim($property);
                    $value = $this->modx->getOption($property);
                    if ($this->validate($value)) {
                        $extraContent .= ' ' . $value;
                    }
                }
            }
            return $extraContent;
        }

        /**
         * Recursively search directories for certain file types
         * Adapted from boen dot robot at gmail dot com's code on the scandir man page
         * @param $dir - dir to search (no trailing slash)
         * @param mixed $types - null for all files, or a comma-separated list of strings
         *                       the filename must include (e.g., '.php,.class')
         * @param bool $recursive - if false, only searches $dir, not it's descendants
         * @param string $baseDir - used internally -- do not send
         */
        public function dirWalk($dir, $types = null, $recursive = false, $baseDir = '') {

            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file === '.' || $file === '..') {
                        continue;
                    }
                    // $this->output .= "\n" , $dir;
                    //$this->output .= "\n", $file;
                    if (is_file($dir . '/' . $file)) {
                        if ($types !== null) {
                            $found = false;
                            $typeArray = explode(',', $types);
                            foreach ($typeArray as $type) {
                                if (strstr($file, $type)) {
                                    $found = true;
                                }
                            }
                            if (!$found) {
                                continue;
                            }
                        }
                        // $this->{$callback}($dir, $file);
                        $this->addFile($dir, $file);
                    } elseif ($recursive && is_dir($dir . '/' . $file)) {
                        $this->dirWalk($dir . '/' . $file, $types, $recursive, $baseDir . '/' . $file);
                    }
                }
                closedir($dh);
            }
        }

        public function getClassFiles() {
            $this->classFiles = array();
            $dir = MODX_CORE_PATH . 'components';
            if (is_dir($dir)) {
                $this->dirWalk($dir, '.php', true);
                $this->classFiles = $this->getFiles();
            }
        }


        /**
         * Searches for included .php files in code
         * and appends their content to $this->scriptCode var
         *
         * @param $file - string - path to code file(s)
         * @param $content string - if set, $file is ignored and content is used.
         */

        public function getIncludes($file, $content = '') {
            $lines = array();
            $matches = array();

            if (empty($content)) {
                if (empty($fp)) {
                    return;
                }
                $fp = fopen($file, "r");
                if ($fp) {
                    while (!feof($fp)) {
                        $lines[] = fgets($fp, 4096);
                    }
                    fclose($fp);
                } else {
                    $this->output .= "\n" . $this->modx->lexicon('orphans.file_nf') . ': ' . $file;
                    return;
                }
            } else {
                $lines = explode("\n", $content);
            }

            foreach ($lines as $line) {
                $fileName = 'x';
                if (strstr($line, 'include') || strstr($line, 'include_once') || strstr($line, 'require') || strstr($line, 'require_once')) {
                    //preg_match('#[0-9a-zA-Z_\-\s]+\.class\.php#', $line, $matches);
                    preg_match('#[0-9a-zA-Z_\-\s.]+\.php#', $line, $matches);

                    $fileName = isset($matches[0])
                        ? $matches[0]
                        : 'x';
                }

                /* check files included with getService() and loadClass() */
                if (strstr($line, 'modx->getService')) {
                    $pattern = "/modx\s*->\s*getService\s*\(\s*\'[^,]*,\s*'([^']+)/";
                    preg_match($pattern, $line, $matches);
                    if (isset($matches[1])) {
                        $s = strtolower($matches[1]);
                        if (strstr($s, '.')) {
                            $r = strrev($s);
                            $fileName = strrev(substr($r, 0, strpos($r, '.')));
                        } else {
                            $fileName = $s;
                        }
                    }
                }
                if (strstr($line, 'modx->loadClass')) {
                    $pattern = "/modx\s*->\s*loadClass\s*\(\s*\'([^']+)/";
                    preg_match($pattern, $line, $matches);
                    if (isset($matches[1])) {
                        $s = strtolower($matches[1]);
                        if (strstr($s, '.')) {
                            $r = strrev($s);
                            $fileName = strrev(substr($r, 0, strpos($r, '.')));
                        } else {
                            $fileName = $s;
                        }
                    }
                }
                $fileName = strstr($fileName, 'class.php')
                    ? $fileName
                    : $fileName . '.class.php';

                if (isset($this->classFiles[$fileName])) {

                    /* skip files we've already included */
                    if (!in_array($fileName, $this->included)) {
                        $this->scriptCode .= file_get_contents($this->classFiles[$fileName] . '/' . $fileName);
                        $this->included[] = $fileName;
                        $this->getIncludes($this->classFiles[$fileName] . '/' . $fileName);
                    }
                }
            }
        }


        public function validate($s) {
            if (empty($s) || ($s == '.') || is_numeric(substr($s, 0, 1)) || (preg_match('#[%,:\/<>\=\\+\[]#', $s))) {
                return false;
            }
            return true;
        }

    }
}