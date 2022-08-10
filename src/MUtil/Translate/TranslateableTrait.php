<?php

/**
 *
 * @package    MUtil
 * @subpackage Translate
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Translate;

use InvalidArgumentException;

/**
 * Add auto translate functions to a class
 *
 * Can be implemented as Trait in PHP 5.4 or copied into source
 *
 * @package    MUtil
 * @subpackage Translate
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.1.35
 */
trait TranslateableTrait
{
    /**
     *
     * @var Translator
     */
    protected $translate;

    /**
     * Translates the given message.
     *
     * When a number is provided as a parameter named "%count%", the message is parsed for plural
     * forms and a translation is chosen according to this number using the following rules:
     *
     * Given a message with different plural translations separated by a
     * pipe (|), this method returns the correct portion of the message based
     * on the given number, locale and the pluralization rules in the message
     * itself.
     *
     * The message supports two different types of pluralization rules:
     *
     * interval: {0} There are no apples|{1} There is one apple|]1,Inf] There are %count% apples
     * indexed:  There is one apple|There are %count% apples
     *
     * The indexed solution can also contain labels (e.g. one: There is one apple).
     * This is purely for making the translations more clear - it does not
     * affect the functionality.
     *
     * The two methods can also be mixed:
     *     {0} There are no apples|one: There is one apple|more: There are %count% apples
     *
     * An interval can represent a finite set of numbers:
     *  {1,2,3,4}
     *
     * An interval can represent numbers between two numbers:
     *  [1, +Inf]
     *  ]-1,2[
     *
     * The left delimiter can be [ (inclusive) or ] (exclusive).
     * The right delimiter can be [ (exclusive) or ] (inclusive).
     * Beside numbers, you can use -Inf and +Inf for the infinite.
     *
     * @see https://en.wikipedia.org/wiki/ISO_31-11
     *
     * @param string|null      $id         The message id (may also be an object that can be cast to string)
     * @param array       $parameters An array of parameters for the message
     * @param string|null $domain     The domain for the message or null to use the default
     * @param string|null $locale     The locale or null to use the default
     *
     * @throws InvalidArgumentException If the locale contains invalid characters
     */
    public function _(?string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        return $this->translate->trans($id, $parameters, $domain, $locale);
    }

    /**
     *
     * Translates the given string using plural notations
     * Returns the translated string
     *
     * @param string             $singular Singular translation string
     * @param string             $plural   Plural translation string
     * @param integer            $number   Number for detecting the correct plural
     * @param string|null        $locale   The locale or null to use the default
     * @return string
     */
    public function plural(string $singular, string $plural, int $number, ?string $locale = null): string
    {
        return $this->translate->plural($singular, $plural, $number, $locale);
    }
}
