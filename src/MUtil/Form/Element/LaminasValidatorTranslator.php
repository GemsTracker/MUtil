<?php

declare(strict_types=1);

/**
 * @package    MUtil
 * @subpackage Form\Element
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace MUtil\Form\Element;

use Zalt\Base\SymfonyTranslator;

/**
 * @package    MUtil
 * @subpackage Form\Element
 * @since      Class available since version 1.0
 */
class LaminasValidatorTranslator
{
    protected static ?SymfonyTranslator $symfonyTranslator = null;


    public static function getSymfonyTranslator(): ?SymfonyTranslator
    {
        return self::$symfonyTranslator;
    }

    public static function hasSymfonyTranslator(): bool
    {
        return self::$symfonyTranslator instanceof SymfonyTranslator;
    }

    public static function setSymfonyTranslator(SymfonyTranslator $translator): void
    {
        self::$symfonyTranslator = $translator;
    }
}