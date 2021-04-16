<?php

/**
 *
 * @package    MUtil
 * @subpackage Model_Dependency
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model\Dependency;

/**
 * A basic dependency implementation that all the housekeeping work,
 * but leaves the actual changes alone.
 *
 * @package    MUtil
 * @subpackage Model_Dependency
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
abstract class DependencyAbstract extends \MUtil_Translate_TranslateableAbstract implements DependencyInterface
{
    /**
     * Array of setting => setting of setting changed by this dependency
     *
     * The settings array for those effected items that don't have an effects array
     *
     * @var array
     */
    protected $_defaultEffects = array();

    /**
     * Array of name => name of items dependency depends on.
     *
     * Can be overridden in sub class, when set to only field names this class will
     * change the array to the correct structure.
     *
     * @var array Of name => name
     */
    protected $_dependentOn = array();

    /**
     * Array of name => array(setting => setting) of fields with settings changed by this dependency
     *
     * Can be overridden in sub class, when set to only field names this class will use _defaultEffects
     * to change the array to the correct structure.
     *
     * @var array of name => array(setting => setting)
     */
    protected $_effecteds = array();

    /**
     * Set to false to disable automatically setting the onchange code
     *
     * @var boolean
     */
    protected $applyOnChange = true;

    /**
     * Set to false to disable automatically setting the onchange code
     *
     * @var boolean
     */
    protected $onChangeJs = 'this.form.submit();';

    /**
     * Constructor checks any subclass set variables
     */
    public function __construct()
    {
        // Make sub class specified dependents confirm to system
        if ($this->_dependentOn) {
            $this->setDependsOn($this->_dependentOn);
        }

        if ($this->_defaultEffects) {
            $this->_defaultEffects = array_combine($this->_defaultEffects, $this->_defaultEffects);
        }

        // Make sub class specified effectds confirm to system
        if ($this->_effecteds) {
            $this->setEffecteds($this->_effecteds);
        }
    }

    /**
     * All string values passed to this function are added as a field the
     * dependency depends on.
     *
     * @param mixed $dependsOn
     * @return \MUtil\Model\Dependency\DependencyAbstract (continuation pattern)
     */
    public function addDependsOn($dependsOn)
    {
        $dependsOn = \MUtil_Ra::flatten(func_get_args());

        foreach ($dependsOn as $dependOn) {
            $this->_dependentOn[$dependOn] = $dependOn;
        }

        return $this;
    }

    /**
     * Adds which settings are effected by a value
     *
     * Overrule this function, e.g. when a sub class changed a fixed setting,
     * but for diverse fields.
     *
     * @param string $effectedField A field name
     * @param mixed $effectedSettings A single setting or an array of settings
     * @return \MUtil\Model\Dependency\DependencyAbstract (continuation pattern)
     */
    public function addEffected($effectedField, $effectedSettings)
    {
        if ($effectedSettings) {
            foreach ((array) $effectedSettings as $setting) {
                if (is_array($setting)) {
                    \MUtil_Echo::track($setting);
                }
                $this->_effecteds[$effectedField][$setting] = $setting;
            }
        } else {
            $this->_effecteds[$effectedField] = [];
        }

        return $this;
    }

    /**
     * Add to the fields effected by this dependency
     *
     * Do not override this function, override addEffected() instead
     *
     * @param array $effecteds Of values accepted by addEffected as paramter
     * @return \MUtil\Model\Dependency\DependencyAbstract (continuation pattern)
     */
    public final function addEffecteds(array $effecteds)
    {
        foreach ($effecteds as $effectedField => $effectedSettings) {
            if (is_int($effectedField) && (! is_array($effectedSettings)) && $this->_defaultEffects) {
                $this->addEffected($effectedSettings, $this->_defaultEffects);
            } else {
                $this->addEffected($effectedField, $effectedSettings);
            }
        }

        return $this;
    }

    /**
     * Use this function for a default application of this dependency to the model
     *
     * @param \MUtil_Model_ModelAbstract $model Try not to store the model as variabe in the dependency (keep it simple)
     */
    public function applyToModel(\MUtil_Model_ModelAbstract $model)
    {
        if ($this->applyOnChange) {
            foreach ($this->getDependsOn() as $name) {
                if ($model->is($name, 'elementClass', 'Checkbox')) {
                    if (! $model->has($name, 'onclick')) {
                        $model->set($name, 'onclick', $this->onChangeJs);
                    }
                } else {
                    if (! $model->has($name, 'onchange')) {
                        $model->set($name, 'onchange', $this->onChangeJs);
                    }
                }
            }
        }
    }

    /**
     * Does this dependency depends on this field?
     *
     * @param $name Field name
     * @return boolean
     */
    public function dependsOn($name)
    {
        return isset($this->_dependentOn[$name]);
    }

    /**
     * Returns the changes that must be made in an array consisting of
     *
     * <code>
     * array(
     *  field1 => array(setting1 => $value1, setting2 => $value2, ...),
     *  field2 => array(setting3 => $value3, setting4 => $value4, ...),
     * </code>
     *
     * By using [] array notation in the setting name you can append to existing
     * values.
     *
     * Use the setting 'value' to change a value in the original data.
     *
     * When a 'model' setting is set, the workings cascade.
     *
     * @param array $context The current data this object is dependent on
     * @param boolean $new True when the item is a new record not yet saved
     * @return array name => array(setting => value)
     * /
    public function getChanges(array $context, $new)
    {

    }  // */

    /**
     * Return the array of fields this dependecy depends on
     *
     * @return array name => name
     */
    public function getDependsOn()
    {
        return $this->_dependentOn;
    }

    /**
     * Get the settings for this field effected by this dependency
     *
     * @param $name Field name
     * @return array of setting => setting of fields with settings for this $name changed by this dependency
     */
    public function getEffected($name)
    {
        if (isset($this->_effecteds[$name])) {
            return $this->_effecteds[$name];
        }

        return array();
    }

    /**
     * Get the fields and their settings effected by by this dependency
     *
     * @return array of name => array(setting => setting) of fields with settings changed by this dependency
     */
    public function getEffecteds()
    {
        return $this->_effecteds;
    }

    /**
     * Is this field effected by this dependency?
     *
     * @param $name
     * @return boolean
     */
    public function isEffected($name)
    {
        return isset($this->_effecteds[$name]);
    }

    /**
     * All string values passed to this function are set as the fields the
     * dependency depends on.
     *
     * @param mixed $dependsOn
     * @return \MUtil\Model\Dependency\DependencyAbstract (continuation pattern)
     */
    public function setDependsOn($dependsOn)
    {
        $this->_dependentOn = array();

        return $this->addDependsOn(func_get_args());
    }

    /**
     * Add to the fields effected by this dependency
     *
     * Do not override this function, override addEffected() instead
     *
     * @param array $effecteds Of values accepted by addEffected as paramter
     * @return \MUtil\Model\Dependency\DependencyAbstract (continuation pattern)
     */
    public final function setEffecteds(array $effecteds)
    {
        $this->_effecteds = array();

        return $this->addEffecteds($effecteds);
    }
}
