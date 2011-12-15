<?php

/**
 * Copyright (c) 2011, Erasmus MC
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * Neither the name of Erasmus MC nor the
 *      names of its contributors may be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 *
 * @package    MUtil
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @version    $Id: Sample.php 203 2011-07-07 12:51:32Z matijs $
 */

/**
 *
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
class MUtil_Html_ProgressPanel extends MUtil_Html_HtmlElement
{
    const CODE = "MUtil_Html_ProgressPanel_Code";

    /**
     * Usually no text is appended after an element, but for certain elements we choose
     * to add a "\n" newline character instead, to keep the output readable in source
     * view.
     *
     * @var string Content added after the element.
     */
    protected $_appendString = "\n";

    /**
     * Default attributes.
     *
     * @var array The actual storage of the attributes.
     */
    protected $_attribs = array(
        'class' => 'progress',
        'id' => 'progress_bar'
    );

    /**
     * Usually no text is appended before an element, but for certain elements we choose
     * to add a "\n" newline character instead, to keep the output readable in source
     * view.
     *
     * @var string Content added before the element.
     */
    protected $_prependString = "\n";

    /**
     * Creates a 'div' progress panel
     *
     * @param mixed $arg_array A MUtil_Ra::args data collection.
     */
    public function __construct($arg_array = null)
    {
        $args = MUtil_Ra::args(func_get_args());

        parent::__construct('div', $args);
    }

    /**
     * Returns the JavaScript object associated with this object.
     *
     * WARNING: calling this object sets it's position in the order the
     * objects are rendered. If you use MUtil_Lazy objects, make sure they
     * have the correct value when rendering.
     *
     * @return MUtil_Html_Code_JavaScript
     */
    public function getCode()
    {
        if (! $this->offsetExists(self::CODE)) {
            $js = new MUtil_Html_Code_JavaScript(dirname(__FILE__) . '/ProgressPanel.js');
            // $js->setInHeader(false);
            $js->setField('FUNCTION_PREFIX', __CLASS__);

            $this->offsetSet(self::CODE, $js);
        }

        return $this->offsetGet(self::CODE);
    }

    /**
     * Creates a 'div' progress panel
     *
     * @param mixed $arg_array A MUtil_Ra::args data collection.
     * @return self
     */
    public static function progress($arg_array = null)
    {
        $args = func_get_args();
        return new self($args);
    }

    /**
     * Function to allow overloading  of tag rendering only
     *
     * Renders the element tag with it's content into a html string
     *
     * The $view is used to correctly encode and escape the output
     *
     * @param Zend_View_Abstract $view
     * @return string Correctly encoded and escaped html output
     */
    protected function renderElement(Zend_View_Abstract $view)
    {
        // Make sure the JS code is added
        $this->getCode();

        return parent::renderElement($view);
    }
}