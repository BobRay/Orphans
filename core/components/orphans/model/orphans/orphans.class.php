<?php
/**
 * Orphans class file for Orphans extra
 *
 * Copyright 2013 by Bob Ray <http://bobsguides.com>
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


class OrphanObject {
    public $name = '';
    public $type = '';
    public $found = false;

    function __construct($name, $type, $found = false) {
        $this->name = trim($name);
        $this->type = $type;
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

class Orphans {
    public $files = array();
    public $classFiles = array();
    public $output = '';
    public $included = array();
    public $scriptCode = '';
    public $allChunks = array();
    public $foundChunks = array();
    public $allObjects = array();
    public $objectTypes = array(
        'modTemplate',
        'modChunk',
        'modResource',
        'modTemplateVar',
        'modSnippet',
        'modPlugin'
    );
    public $classFileCount = 0;
    /* @var $modx modX */
    public $modx;

    function __construct(&$modx) {
        $this->modx =& $modx;

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

    public function getObjects($type) {
        return $this->allObjects[$type];
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

    public function process() {
        $this->getClassFiles();
        $this->classFileCount = count($this->classFiles);
        $objectTypes = $this->objectTypes;
        foreach ($objectTypes as $objectType) {
            $objects = $this->modx->getCollection($objectType);

            /* @var $object modElement */
            foreach ($objects as $object) {
                $obj = new OrphanObject($object->get($this->getNameAlias($objectType)), $objectType);
                $this->allObjects[$objectType][] = $obj;
                unset ($object);

            }
            unset($objects);
        }
        /* Get each object again, one at a time, to save memory */

        /* Warning! Reading this section of code may be hazardous to your sanity */

        /* iterate through collections of resources, snippets, chunks, etc. */
        foreach ($objectTypes as $objectType) {
            $objects = $this->modx->getCollection($objectType);
            foreach ($objects as $object) {
                /* @var $orphanObject OrphanObject */
                $content = $this->getContent($object, $objectType);
                /* look through all object of this type for each orphan of any type.
                   findMe() sets its 'found' member if found anywhere */
                foreach ($objectTypes as $orphanType) {
                    /* get the orphan objects of one type */
                    $orphanObjects = $this->getObjects($orphanType);
                    /* Check each one against the content of the current $object */
                    foreach ($orphanObjects as $orphanObject) {
                        $nameAlias = $this->getNameAlias($objectType);
                        /* don't look for myself in myself */
                        if (($orphanObject->type == $objectType) &&
                            ($orphanObject->name == $object->get($nameAlias))
                        ) {
                            continue;
                        }
                        $orphanObject->findMe($content);
                    }
                }
                unset($orphanObjects, $orphanObject);
            }
            unset($objects, $object);
        }
        $this->displayOutput();

    }

    public function displayOutput() {
        $cli = (php_sapi_name() == 'cli')
            ? true
            : false;

        $this->output .= $cli? '' : "\n" . '<div id="orphans-div">' ;
        $msg = $this->modx->lexicon('orphans.header_message~~Processing all resource and element objects looking for unused elements. The following objects may, or may not, be unused.');
        $this->output .= $cli
            ? "\n" . $msg
            : "\n" . '<h2>Orphans</h2>' . "\n" . '<p class="orphans">' . $msg . '</p>';
        $msg = $this->modx->lexicon('orphans.found~~Found');
        $msg2 = $this->modx->lexicon('orphans.php_files~~PHP files in core/components.');
        $this->output .= $cli
            ? "\n" . $msg . ' ' . $this->classFileCount . ' ' . $msg2
            : "\n" . '<p class="orphans">' . $msg . ' ' .  $this->classFileCount . ' ' . $msg2 . '</p>';
        $objectTypes = $this->objectTypes;
        $totalObjects = 0;
        $totalObjects += count($this->getObjects('modResource'));
        $totalObjects += count ($this->getObjects('modPlugin'));
        unset($objectTypes[array_search('modPlugin', $objectTypes)]);
        unset($objectTypes[array_search('modResource', $objectTypes)]);

        foreach ($objectTypes as $objectType) {
            $objectName = substr($objectType, 3) . 's';
            $objects = $this->getObjects($objectType);
            $total = count($objects);
            $totalObjects += $total;
            $this->output .= $cli
                ? "\n\n" . strtoupper($objectName)
                : "\n" . '<h3 class="orphans">' . $objectName . '</h3>';
            $msg = $this->modx->lexicon('orphans.total~~Total');
            $this->output .= $cli
                ? "\n\n" . $msg . " " . $objectName . ': ' . $total
                : "\n" . '<p class="orphans">' . $msg . ' ' .  $objectName . ': ' . $total . '</p>';
            $orphanCount = 0;
            $orphans = array();
            foreach ($objects as $object) {
                if (!$object->found) {
                    $orphans[] = $object->name;
                    $orphanCount++;
                }
            }

            $msg = $this->modx->lexicon('orphans.Orphan~~Orphan');
            $this->output .= $cli
                ? "\n" . $msg . ' ' . $objectName . ': ' . $orphanCount
                : "\n" . '<p class="orphans">' . $msg . ' ' . $objectName . ': ' . $orphanCount . '</p>';
            natcasesort($orphans);
            $this->output .= $cli
                ? ''
                : "\n" . '<ul class="orphans">';
            foreach ($orphans as $orphan) {
                $this->output .= $cli
                    ? "\n     " . $orphan
                    : "\n    " . '<li class="orphans">' . $orphan . '</li>';
            }
            $this->output .= $cli
                ? ''
                : "\n</ul>";
        }
        $msg = $this->modx->lexicon('orphans.total_processed~~Total Objects Processed');
        $this->output .= $cli
            ? "\n\n" . $msg . ": " . $totalObjects
            : "\n<br />" . '<p class="orphans">' . $msg . ': ' . $totalObjects;
        $this->output .= $cli ? '' : "\n</div>";
    }

    public function getContent($object, $objectType) {
        /* @var $object xPDOObject */
        $content = 'xxx'; /* make sure nobody gets an empty one */
        switch ($objectType) {
            case 'modTemplate':
                $content = $object->get('content');
                $content = $object->get('content');
                break;

            case 'modChunk':
                $content = $object->get('snippet');
                break;

            case 'modResource':
                $content = $object->get('content');
                $templateId = $object->get('template');
                /* append the template name */
                $templateObj = $this->modx->getObject('modTemplate', $templateId);
                if ($templateObj) {
                    $content .= ' ' . $templateObj->get('templatename');
                }
                break;

            case 'modTemplateVar':
                $content = $object->get('default_text');
                /* ToDo: append input_properties, output_properties */
                $content .= $object->get('input_properties');
                $content .= $object->get('output_properties');
                break;

            case 'modSnippet':
                $content = $object->get('snippet');
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
        /* append any property values */
        $nameAlias = $this->getNameAlias($objectType);
        if (($objectType !== 'modResource')) {
            $props = array();
            /* @var $object modElement */
            $props = @$object->getProperties();

            if (!empty($props)) {
                foreach ($props as $prop => $value) {
                    if ($this->validate($value)) {
                        $content .= ' ' . $value;
                    }
                }
            }
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
                        if (!$found) continue;
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
                $this->output .= "\n" .  $this->modx->lexicon('orphans.file_nf~~Could not open file') . ': ' . $file;
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
                    $s = strtoLower($matches[1]);
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
                    $s = strtoLower($matches[1]);
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
