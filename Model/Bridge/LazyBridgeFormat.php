<?php

/**
 * Copyright (c) 2015, Erasmus MC
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
 * DISCLAIMED. IN NO EVENT SHALL MAGNAFACTA BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 *
 * @package    MUtil
 * @subpackage Model\Bridge
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 * @version    $Id: LazyBridgeFormat.php 2430 2015-02-18 15:26:24Z matijsdejong $
 */

namespace MUtil\Model\Bridge;

/**
 *
 *
 * @package    MUtil
 * @subpackage Model\Bridge
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.7.2 Dec 4, 2015 3:00:44 PM
 */
class LazyBridgeFormat extends \MUtil_Lazy_LazyAbstract
{
    /**
     *
     * @var \MUtil_Model_Bridge_BridgeAbstract
     */
    protected $bridge;

    /**
     *
     * @var string
     */
    protected $fieldName;

    /**
     *
     * @var \MUtil_Lazy_RepeatableInterface
     */
    protected $repeater;

    /**
     *
     * @param \MUtil_Model_Bridge_BridgeInterface $bridge
     * @param string $fieldName
     */
    public function __construct(\MUtil_Model_Bridge_BridgeAbstract $bridge, $fieldName)
    {
        $this->bridge    = $bridge;
        $this->fieldName = $fieldName;
    }

    /**
     * The functions that fixes and returns a value.
     *
     * Be warned: this function may return a lazy value.
     *
     * @param \MUtil_Lazy_StackInterface $stack A \MUtil_Lazy_StackInterface object providing variable data
     * @return mixed
     */
    public function __toValue(\MUtil_Lazy_StackInterface $stack)
    {
        if (! $this->repeater) {
            $this->repeater = $this->bridge->getRepeater();
        }

        $out     = null;
        $current = $this->repeater->__current();
        if ($current) {
            if (isset($current->{$this->fieldName})) {
                $out = $current->{$this->fieldName};
            }
        }
        return $this->bridge->format($this->fieldName, $out);
     }
}
