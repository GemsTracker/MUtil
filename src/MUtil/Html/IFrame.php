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
 * @version    $Id$
 */

/**
 * Class for IFRAME element. Assumes first passed argument is the src attribute,
 * unless specified otherwise. Always specifies closing tag.
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5.2
 */
class MUtil_Html_IFrame extends \MUtil_Html_HtmlElement
{
    /**
     * Some elements, e.g. iframe elements, must always be rendered with a closing
     * tag because otherwise some poor browsers get confused.
     *
     * Overrules $renderWithoutContent: the element is always rendered when
     * $renderClosingTag is true.
     *
     * @see $renderWithoutContent
     *
     * @var boolean The element is always rendered with a closing tag.
     */
    public $renderClosingTag = true;

    /**
     * An iframe element.
     *
     * Any extra parameters are added as either content, attributes or handled
     * as special types, if defined as such for this element.
     *
     * @param mixed $src We assume the first element is src, unless a later element is explicitly specified as such
     * @param mixed $arg_array \MUtil_Ra::args arguments
     */
    public function __construct($src, $arg_array = null)
    {
        $args = \MUtil_Ra::args(func_get_args(), array('src' => 'MUtil_Html_SrcArrayAttribute'));

        if (isset($args['src']) && (! $args['src'] instanceof \MUtil_Html_AttributeInterface)) {
            $args['src'] = new \MUtil_Html_SrcArrayAttribute($args['src']);
        }

        parent::__construct('iframe', $args);
    }

    /**
     * Static helper function to create an iframe element.
     *
     * Any extra parameters are added as either content, attributes or handled
     * as special types, if defined as such for this element.
     *
     * @param mixed $src We assume the first element is src, unless a later element is explicitly specified as such
     * @param mixed $arg_array \MUtil_Ra::args arguments
     */
    public static function iFrame($src, $arg_array = null)
    {
        $args = \MUtil_Ra::args(func_get_args(), array('src' => 'MUtil_Html_SrcArrayAttribute'));
        return new self($args);
    }
}
