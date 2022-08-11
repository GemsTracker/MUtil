<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Model_Iterator
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model\Iterator;

use \DateTimeImmutable;

/**
 *
 *
 * @package    MUtil
 * @subpackage Model_Iterator
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class FolderModelIterator extends \FilterIterator
{
    /**
     * Optional preg expression for relative filename, use of backslashes for directory seperator required
     *
     * @var string
     */
    protected $mask;

    /**
     * The starting path
     *
     * @var string
     */
    protected $startPath;

    /**
     *
     * @param \Iterator $iterator
     * @param string $startPath
     * @param string $mask Preg expression for relative filename, use of / slashes for directory seperator required
     */
    public function __construct(\Iterator $iterator, $startPath = '', $mask = false)
    {
        parent::__construct($iterator);

        $this->startPath = realpath($startPath);
        if ($this->startPath) {
            $this->startPath = $this->startPath . DIRECTORY_SEPARATOR;
        }
        // \MUtil\EchoOut\EchoOut::track($startPath, $this->startPath);

        $this->mask = $mask;
    }

    /**
     * \FilterIterator::accept Check whether the current element of the iterator is acceptable
     *
     * @return boolean
     */
    public function accept()
    {
        $file = parent::current();

        if (! $file instanceof SplFileInfo) {
            return false;
        }

        if (!$file->isFile() || !$file->isReadable()) {
            return false;
        }

        if ($this->mask) {
            $rel = str_replace('\\', '//', \MUtil\StringUtil\StringUtil::stripStringLeft($file->getRealPath(), $this->startPath));

            if (!preg_match($this->mask, $rel)) {
                return false;
            }
        }

        return true;
    }

    /**
     * FilesystemIterator::current The current file
     *
     * @return mixed null or artray
     */
    public function current()
    {
        $file = parent::current();


        if (! $file instanceof SplFileInfo) {
            return null;
        }

        $real = \MUtil\File::cleanupSlashes($file->getRealPath());

        // The relative file name uses the windows directory seperator convention as this
        // does not screw up the use of this value as a parameter
        $rel = \MUtil\File::cleanupSlashes(\MUtil\StringUtil\StringUtil::stripStringLeft($real, $this->startPath));

        // Function was first implemented in PHP 5.3.6
        if (method_exists($file, 'getExtension')) {
            $extension = $file->getExtension();
        } else {
            $extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
        }

        $date = new \DateTimeImmutable();
        
        return array(
            'fullpath'  => $real,
            'relpath'   => $rel,
            'urlpath'   => self::fromNameToUrlSave($rel),
            'path'      => \MUtil\File::cleanupSlashes($file->getPath()),
            'filename'  => $file->getFilename(),
            'extension' => $extension,
            'content'   => \MUtil\Lazy::call('file_get_contents', $real),
            'size'      => $file->getSize(),
            'changed'   => $date->setTimestamp($file->getMTime()),
            );
    }

    /**
     * @param $filename 
     * @return string Remove \, / and . from name
     */
    public static function fromNameToUrlSave($filename)
    {
        return str_replace(['\\', '/', '.'], ['|', '|', '%2E'], $filename);
    }

    /**
     * @param $filename
     * @return string The real name for the Url save name
     */
    public static function fromUrlSaveToName($filename)
    {
        return str_replace(['|', '%2E'], ['/', '.'], $filename);
    }
}
