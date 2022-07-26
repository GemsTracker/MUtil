<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage ProgressBar
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\ProgressBar\Adapter;

/**
 * Added buffer capabilities to JsPush.
 *
 * Also when having trouble on IIS, edit the responseBufferLimit using the IIS Manager:
 * - On the server main page, under "Management", select "Configuration Editor";
 * - under "Section", enter 'system.webServer/handlers';
 * - next to "(Collection)" click "..." OR mark the element "(Collection)" and, under "Actions" und '(Collection)' Element, click "Edit Items";
 * - scroll down until you find your PHP version under "Name";
 * - at the bottom, the Properties are shown an can be edited manually, including responseBufferLimit, which should be set to 0 for flush() to work.
 *
 * The big Pro is that you can edit the properties for everything, not only PHP plus you can work with different versions (or even installations of the same version) of PHP.
 *
 * The source of this wisdom:
 * @link http://stackoverflow.com/questions/7178514/php-flush-stopped-flushing-in-iis7-5
 *
 * @package    MUtil
 * @subpackage ProgressBar
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
class JsPush extends \Zend_ProgressBar_Adapter_JsPush
{
    /**
     * When true data has been sent.
     *
     * @var boolean
     */
    protected $_dataSent = false;

    /**
     * The number of bytes to pad in Kilobytes
     *
     * This is needed as many servers need extra output passing to avoid buffering.
     *
     * Also this allows you to keep the server buffer high while using this JsPush.
     *
     * @var int
     */
    public $extraPaddingKb = 0;

    /**
     * The number of bytes to pad for the first push communication in Kilobytes. If zero
     * $extraPushPaddingKb is used.
     *
     * This is needed as many servers need extra output passing to avoid buffering.
     *
     * Also this allows you to keep the server buffer high while using this JsPush.
     *
     * @var int
     */
    public $initialPaddingKb = 0;

    /**
     * Outputs given data the user agent.
     *
     * This split-off is required for unit-testing.
     *
     * @param  string $data
     * @return void
     */
    protected function _outputData($data)
    {
        $data = $data . "\n";

        if (! $this->_dataSent) {
            // header("Content-Type: text/html; charset=utf-8");
        }
        if ($this->initialPaddingKb && (! $this->_dataSent)) {
            $padding = $this->initialPaddingKb;
        } else {
            $padding = $this->extraPaddingKb;
        }
        if ($padding) {
            $data = $data . str_repeat(' ', 1024 * $padding);
        }
        $this->_dataSent = true;

        // return parent::_outputData($data);
        return parent::_outputData('<br/>STARTDATA<br/>' . $data . '<br/>ENDDATA<br/>');
    }
}
