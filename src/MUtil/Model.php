<?php

/**
 *
 * @package    MUtil
 * @subpackage Model
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil;

use _PHPStan_9a6ded56a\Nette\Neon\Exception;

use DateTimeImmutable;
use DateTimeInterface;
use Zalt\Loader\DependencyResolver\ConstructorDependencyParametersResolver;
use Zalt\Loader\ProjectOverloader;
use Zalt\Model\MetaModelLoader;


/**
 * A model combines knowedge about a set of data with knowledge required to manipulate
 * that set of data. I.e. it can store data about fields such as type, label, length,
 * etc... and meta data about the object like the current query filter and sort order,
 * with manipulation methods like save(), load(), loadNew() and delete().
 *
 * @see \MUtil\Model\ModelAbstract
 *
 * @package    MUtil
 * @subpackage Model
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class Model
{
    /**
     * Url parameter to reset searches
     */
    const AUTOSEARCH_RESET = 'reset';

    /**
     * Indentifier for bridges meta key
     */
    const META_BRIDGES = 'bridges';

    /**
     * In order to keep the url's short and to hide any field names from
     * the user, model identifies key values by using 'id' for a single
     * key value and id1, id2, etc... for multiple keys.
     */
    const REQUEST_ID = 'id';

    /**
     * Helper constant for first key value in multi value key.
     */
    const REQUEST_ID1 = 'id1';

    /**
     * Helper constant for second key value in multi value key.
     */
    const REQUEST_ID2 = 'id2';

    /**
     * Helper constant for third key value in multi value key.
     */
    const REQUEST_ID3 = 'id3';

    /**
     * Helper constant for forth key value in multi value key.
     */
    const REQUEST_ID4 = 'id4';

    /**
     * Default parameter name for sorting ascending.
     */
    const SORT_ASC_PARAM  = 'asort';

    /**
     * Default parameter name for sorting descending.
     */
    const SORT_DESC_PARAM = 'dsort';

    /**
     * Default parameter name for wildcard text search.
     */
    const TEXT_FILTER = 'search';

    /**
     * Type identifiers for calculated fields
     */
    const TYPE_NOVALUE      = 0;

    /**
     * Type identifiers for string fields, default type
     */
    const TYPE_STRING       = 1;

    /**
     * Type identifiers for numeric fields
     */
    const TYPE_NUMERIC      = 2;

    /**
     * Type identifiers for date fields
     */
    const TYPE_DATE         = 3;

    /**
     * Type identifiers for date time fields
     */
    const TYPE_DATETIME     = 4;

    /**
     * Type identifiers for time fields
     */
    const TYPE_TIME         = 5;

    /**
     * Type identifiers for sub models that can return multiple row per item
     */
    const TYPE_CHILD_MODEL  = 6;

    /**
     * The default bridges for each new model
     *
     * @var array string => bridge class
     */
    private static $_bridges = array(
        'display'   => \Zalt\Model\Bridge\DisplayBridge::class,
        'form'      => \Zalt\Snippets\ModelBridge\ZendFormBridge::class,
        'itemTable' => \Zalt\Snippets\ModelBridge\DetailTableBridge::class,
        'table'     => \Zalt\Snippets\ModelBridge\TableBridge::class,
    );

    /**
     *
     * @var array of \MUtil\Loader\PluginLoader
     */
    private static $_loaders = array();

    /**
     *
     * @var array of global for directory paths
     */
    private static $_nameSpaces = array('MUtil');

    /**
     *
     * @var ProjectOverloader
     */
    private static $_source;

    /**
     *
     * @var array ['type' => ['key' => 'value']]
     */
    private static $_typeDefaults = [];
    
    /**
     * Static variable for debuggging purposes. Toggles the echoing of e.g. of sql
     * select statements, using \MUtil\EchoOut\EchoOut.
     *
     * Implemention classes can use this variable to determine whether to display
     * extra debugging information or not. Please be considerate in what you display:
     * be as succint as possible.
     *
     * Use:
     *     \MUtil\Model::$verbose = true;
     * to enable.
     *
     * @var boolean $verbose If true echo retrieval statements.
     */
    public static $verbose = false;

    /**
     * Add a namespace to all loader
     *
     * @param string $nameSpace The namespace without any trailing _
     * @return boolean True when the namespace is new
     */
    public static function addNameSpace($nameSpace)
    {
        if (!in_array($nameSpace, self::$_nameSpaces)) {
            self::$_nameSpaces[] = $nameSpace;

            foreach (self::$_loaders as $subClass => $loader) {
                if ($loader instanceof \MUtil\Loader\PluginLoader) {
                    $loader->addPrefixPath(
                            $nameSpace . '_Model_' . $subClass,
                            $nameSpace . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . $subClass);
                }
            }

            return true;
        }
    }

    public static function addTypeDefaults(int $type, array $defaults = [])
    {
        foreach ($defaults as $key => $value) {
            self::$_typeDefaults[$type][$key] = $value;
        }
    }

    public static function addTypesDefaults(array $typeDefaults = [])
    {
        foreach ($typeDefaults as $type => $defaults) {
            self::addTypeDefaults($type, $defaults);
        }
    }

    /**
     * Returns the plugin loader for bridges
     *
     * @return \MUtil\Loader\PluginLoader
     */
    public static function getBridgeLoader()
    {
        return self::getLoader('Bridge');
    }

    public static function getDateTimeInterface($dateValue, $fromStorage = true) : ?DateTimeInterface
    {
        if (! $dateValue) {
            return null;
        }
        if ($dateValue instanceof DateTimeInterface) {
            return $dateValue;
        }
        if ($dateValue instanceof \MUtil\Date) {
            return $dateValue->getDateTime();
        }
        if ($dateValue instanceof \Zend_Date) {
            $now = new DateTimeImmutable();
            return $now->setTimestamp($dateValue->getTimestamp());
        }
        if (is_int($dateValue)) {
            $now = new DateTimeImmutable();
            return $now->setTimestamp($dateValue);
        }

        if (is_array($fromStorage)) {
            $formats = $fromStorage;
        } elseif ($fromStorage) {
            $formats = [
                self::getTypeDefault(self::TYPE_DATETIME, 'storageFormat'),
                self::getTypeDefault(self::TYPE_DATE, 'storageFormat'),
                self::getTypeDefault(self::TYPE_TIME, 'storageFormat'),
                'c',
            ];
        } else {
            $formats = [
                self::getTypeDefault(self::TYPE_DATETIME, 'dateFormat'),
                self::getTypeDefault(self::TYPE_DATE, 'dateFormat'),
                self::getTypeDefault(self::TYPE_TIME, 'dateFormat'),
            ];
        }
        foreach ((array) $formats as $format) {
            if ($format === null) {
                continue;
            }
            $date = DateTimeImmutable::createFromFormat($format, trim($dateValue));
            if ($date) {
                return $date;
            }
        }

        return null;
    }

    /**
     * Returns an arrat of bridge type => class name for
     * getting the default bridge classes for a model.
     *
     * @return array
     */
    public static function getDefaultBridges()
    {
        return self::$_bridges;
    }

    /**
     * Returns the plugin loader for dependencies
     *
     * @return ProjectOverloader
     */
    public static function getDependencyLoader()
    {
        return self::getLoader('Dependency');
    }

    /**
     * Returns a subClass plugin loader
     *
     * @param string $prefix The prefix to load the loader for. Is CamelCased and should not contain an '_', '/' or '\'.
     * @return ProjectOverloader
     */
    public static function getLoader($prefix)
    {
        if (! isset(self::$_loaders[$prefix])) {
            
            $loader = self::getSource()->createSubFolderOverloader(ucfirst($prefix));
            $loader->setDependencyResolver(new ConstructorDependencyParametersResolver());
            
            self::$_loaders[$prefix] = $loader;
        }

        return self::$_loaders[$prefix];
    }

    public static function getMetaModelLoader(): MetaModelLoader
    {
        static $metaModelLoader;

        if (! $metaModelLoader) {
            $metaModelLoader = self::$_source->getContainer()->get(MetaModelLoader::class);
        }
        return $metaModelLoader;
    }

    /**
     * Get or create the current source
     *
     * @return ProjectOverloader
     */
    public static function getSource()
    {
        if (! self::$_source instanceof ProjectOverloader) {
            // Autoload?
            throw new Exception("Use of MUtil\Model->getSource() while no ProjectOverloader set!");
        }

        return self::$_source;
    }

    public static function getTypeDefault(int $type, string $key)
    {
        $defaults = self::getTypeDefaults($type);
        if (isset($defaults[$key])) {
            return $defaults[$key];
        }
        return null;
    }

    public static function getTypeDefaults(int $type): array
    {
        $ml   = self::getMetaModelLoader();
        $type = $ml->getDefaultTypeInterface($type);
        if ($type) {
            return $type->getSettings();
        }
        return [];
    }

    /**
     * Is a source available
     *
     * @return boolean
     */
    public static function hasSource()
    {
        return self::$_source instanceof \MUtil\Registry\SourceInterface;
    }

    /**
     * @param mixed $value
     * @param array|string|null $inFormat
     * @param array|string|null $outFormat
     * @return string|null
     */
    public static function reformatDate($value, $inFormat = null, $outFormat = null): ?string
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof DateTimeInterface) {
            $date = $value;
        } else {
            if (! $inFormat) {
                $inFormat = [
                    self::getTypeDefault(self::TYPE_DATETIME, 'dateFormat'),
                    self::getTypeDefault(self::TYPE_DATE, 'dateFormat'),
                    self::getTypeDefault(self::TYPE_TIME, 'dateFormat'),
                ];
            }
            foreach ((array) $inFormat as $currentType => $format) {
                $date = DateTimeImmutable::createFromFormat($format, trim($value));
                if ($date) {
                    break;
                }
            }
        }
        
        if (! $date) {
            return null;
        }
        
        if (! $outFormat) {
            $outFormat = self::getTypeDefault($currentType, 'storageFormat');
        }
        
        return $date->format($outFormat);
    }
    
    /**
     * Sets the plugin loader for bridges
     *
     * @param \MUtil\Loader\PluginLoader $loader
     */
    public static function setBridgeLoader(\MUtil\Loader\PluginLoader $loader)
    {
        self::setLoader($loader, 'Bridge');
    }

    /**
     * Returns an arrat of bridge type => class name for
     * getting the default bridge classes for a model.
     *
     * @return array
     */
    public static function setDefaultBridge($key, $className)
    {
        self::$_bridges[$key] = $className;
    }

    /**
     * Sets the plugin loader for dependencies
     *
     * @param PluginLoader $loader
     */
    public static function setDependencyLoader(PluginLoader $loader)
    {
        self::setLoader($loader, 'Dependency');
    }

    /**
     * Sets the plugin loader for a subclass
     *
     * @param \Zalt\Loader\ProjectOverloader $loader
     * @param string $prefix The prefix to set  the loader for. Is CamelCased and should not contain an '_', '/' or '\'.
     */
    public static function setLoader(ProjectOverloader $loader, $prefix)
    {
        self::$_loaders[$prefix] = $loader;
    }

    /**
     * Set the current source for loaders
     *
     * @param ProjectOverloader $source
     * @param boolean $setExisting When true the source is set for all exiting loaders
     * @return void
     */
    public static function setSource(ProjectOverloader $projectOverloader, $setExisting = true)
    {
        self::$_source = $projectOverloader;
    }
}
