<?php


class Label extends \Zend_Form_Decorator_Label
{
    /**
     * Render a label
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        $view    = $element->getView();
        if (null === $view) {
            return $content;
        }

        $label     = $this->getLabel();
        $separator = $this->getSeparator();
        $placement = $this->getPlacement();
        $tag       = $this->getTag();
        $tagClass  = $this->getTagClass();
        $id        = $this->getId();
        $class     = $this->getClass();
        $options   = $this->getOptions();

        $options['class'] = $class;
        $label            = trim($label);

        switch ($placement) {
            case self::IMPLICIT:
                // Break was intentionally omitted

            case self::IMPLICIT_PREPEND:
                $options['escape']     = false;
                $options['disableFor'] = true;

                $label = $view->formLabel(
                    $element->getFullyQualifiedName(),
                    $label . $separator . $content,
                    $options
                );
                break;

            case self::IMPLICIT_APPEND:
                $options['escape']     = false;
                $options['disableFor'] = true;

                $label = $view->formLabel(
                    $element->getFullyQualifiedName(),
                    $content . $separator . $label,
                    $options
                );
                break;

            case self::APPEND:
                // Break was intentionally omitted

            case self::PREPEND:
                // Break was intentionally omitted

            default:
                $label = $view->formLabel(
                    $element->getFullyQualifiedName(),
                    $label,
                    $options
                );
                break;
        }

        if (null !== $tag) {
            require_once 'Zend/Form/Decorator/HtmlTag.php';
            $decorator = new \Zend_Form_Decorator_HtmlTag();
            if (null !== $this->_tagClass) {
                $decorator->setOptions(array('tag'   => $tag,
                    'id'    => $id . '-label',
                    'class' => $tagClass));
            } else {
                $decorator->setOptions(array('tag'   => $tag,
                    'id'    => $id . '-label'));
            }

            $label = $decorator->render($label);
        }

        switch ($placement) {
            case self::APPEND:
                return $content . $separator . $label;

            case self::PREPEND:
                return $label . $separator . $content;

            case self::IMPLICIT:
                // Break was intentionally omitted

            case self::IMPLICIT_PREPEND:
                // Break was intentionally omitted

            case self::IMPLICIT_APPEND:
                return $label;
        }
    }
}
