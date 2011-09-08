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
 * @author Matijs de Jong
 * @since 1.0
 * @version 1.1
 * @package MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

/**
 * @author Matijs de Jong
 * @package MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */
class MUtil_Html_ColElement extends MUtil_Html_HtmlElement implements MUtil_Html_ColumnInterface
{
    /**
     * Returns the cell or a MUtil_MultiWrapper containing cells that occupy the column position, taking colspan and other functions into account.
     * 
     * @param int $col The numeric column position, starting at 0;
     * @return MUtil_Html_HtmlElement Probably an element of this type, but can also be something else, posing as an element.
     */
    public function getColumn($col)
    {
        // this element is not part of the "real" column
        return null;
    }
    
    /**
     * Returns the cells that occupies the column position, taking colspan and other functions into account, in an array.
     * 
     * @param int $col The numeric column position, starting at 0;
     * @return array Of probably one MUtil_Html_HtmlElement
     */
    public function getColumnArray($col)
    {
        // this element is not part of the "real" column
        return array();
    }
    
    /**
     * Return the number of columns, taking such niceties as colspan into account
     * 
     * @return int
     */
    public function getColumnCount()
    {
        if (isset($this->span) && is_int($this->span)) {
            return intval($this->span);
        }

        return 1;
    }

    /**
     * Static helper function for creation, used by @see MUtil_Html_Creator.
     * 
     * @param mixed $arg_array Optional MUtil_Ra::args processed settings
     * @return MUtil_Html_ColElement
     */
    public static function col($arg_array = null)
    {
        $args = func_get_args();
        return new self(__FUNCTION__, $args);
    }
}