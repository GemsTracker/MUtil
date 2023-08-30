<?php

namespace MUtil\Translate;

use Zalt\Base\SymfonyTranslator;
use Zalt\Base\TranslatorInterface;

/**
 * @deprecated use \Zalt\Base\TranslatorInterface ( \Zalt\Base\SymfonyTranslator )
 */
class Translator implements TranslatorInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator
    )
    {
    }

    public function plural(string $singular, string $plural, int $number, ?string $locale = null): string
    {
        return $this->translator->plural($singular, $plural, $number, $locale);
    }

    public function getLocale(): string
    {
        return $this->translator->getLocale();
    }

    public function _(?string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }
}