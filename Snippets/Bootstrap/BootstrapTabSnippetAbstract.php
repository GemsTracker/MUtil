<?php

/**
 * Copyright (c) 2014, Erasmus MC
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
 * @subpackage Snippets_Bootstrap
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @version    $Id: BootstrapTabSnippetAbstract.php 1748 2014-02-19 18:09:41Z matijsdejong $
 */

/**
 *
 * @package    MUtil
 * @subpackage Snippets_Bootstrap
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
abstract class MUtil_Snippets_Bootstrap_BootstrapTabSnippetAbstract extends \MUtil_Snippets_TabSnippetAbstract
{
    /**
     *
     * @var string Class attribute for all tabs
     */
    protected $tabClass = 'nav navtabs';

    /**
     * Create the snippets content
     *
     * This is a stub function either override getHtmlOutput() or override render()
     *
     * @param \Zend_View_Abstract $view Just in case it is needed here
     * @return \MUtil_Html_HtmlInterface Something that can be rendered
     */
    public function getHtmlOutput(\Zend_View_Abstract $view)
    {
        $tabs = $this->getTabs();

        if ($tabs && ($this->displaySingleTab || count($tabs) > 1)) {
            // Set the correct parameters
            $this->getCurrentTab($tabs);

            // Let loose
            if (is_array($this->baseUrl)) {
                $this->href = $this->href + $this->baseUrl;
            }

            $tabRow = \MUtil_Html::create()->ul();
            $tabRow->class = $this->tabClass;

            foreach ($tabs as $tabId => $content) {

                $li = $tabRow->li();

                $li->a($this->getParameterKeysFor($tabId) + $this->href, $content);

                if ($this->currentTab == $tabId) {
                    $li->appendAttrib('class', $this->tabActiveClass);
                }
            }

            return $tabRow;
        } else {
            return null;
        }
    }


}
