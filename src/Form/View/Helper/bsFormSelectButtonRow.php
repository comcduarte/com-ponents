<?php
namespace Components\Form\View\Helper;

use Laminas\Form\ElementInterface;
use Laminas\Form\Exception;
use Laminas\Form\LabelAwareInterface;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\Captcha;
use Laminas\Form\Element\MonthSelect;
use Laminas\Form\View\Helper\AbstractHelper;
use Laminas\Form\View\Helper\FormElement;
use Laminas\Form\View\Helper\FormElementErrors;
use Laminas\Form\View\Helper\FormLabel;
use Laminas\Form\View\Helper\FormRow;
use Laminas\Form\Element\Select;

class bsFormSelectButtonRow extends AbstractHelper
{
    const LABEL_APPEND  = 'append';
    const LABEL_PREPEND = 'prepend';
    
    /**
     * The class that is added to element that have errors
     *
     * @var string
     */
    protected $inputErrorClass = 'input-error';
    
    /**
     * The attributes for the row label
     *
     * @var array
     */
    protected $labelAttributes;
    
    /**
     * Where will be label rendered?
     *
     * @var string
     */
    protected $labelPosition = self::LABEL_PREPEND;
    
    /**
     * Are the errors are rendered by this helper?
     *
     * @var bool
     */
    protected $renderErrors = true;
    
    /**
     * Form label helper instance
     *
     * @var FormLabel
     */
    protected $labelHelper;
    
    /**
     * Form element helper instance
     *
     * @var FormElement
     */
    protected $elementHelper;
    
    /**
     * Form element errors helper instance
     *
     * @var FormElementErrors
     */
    protected $elementErrorsHelper;
    
    /**
     * @var string
     */
    protected $partial;
    
    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()}.
     *
     * @param  null|ElementInterface $select
     * @param  null|string           $labelPosition
     * @param  bool                  $renderErrors
     * @param  string|null           $partial
     * @return string|FormRow
     */
    public function __invoke(
        Select $select = null,
        Button $button = null,
        $labelPosition = null,
        $renderErrors = null,
        $partial = null
        ) {
            if (! $select) {
                return $this;
            }
            
            if (is_null($labelPosition)) {
                $labelPosition = $this->getLabelPosition();
            }
            
            if ($renderErrors !== null) {
                $this->setRenderErrors($renderErrors);
            }
            
            if ($partial !== null) {
                $this->setPartial($partial);
            }
            
            return $this->render($select, $button, $labelPosition);
    }
    
    /**
     * Utility form helper that renders a label (if it exists), an element and errors
     *
     * @param  ElementInterface $select
     * @param  null|string      $labelPosition
     * @throws Exception\DomainException
     * @return string
     */
    public function render(Select $select, Button $button, $labelPosition = null)
    {
        $escapeHtmlHelper    = $this->getEscapeHtmlHelper();
        $labelHelper         = $this->getLabelHelper();
        $elementHelper       = $this->getElementHelper();
        $elementErrorsHelper = $this->getElementErrorsHelper();
        
        $label           = $select->getLabel();
        $inputErrorClass = $this->getInputErrorClass();
        
        if (is_null($labelPosition)) {
            $labelPosition = $this->labelPosition;
        }
        
        if (isset($label) && '' !== $label) {
            // Translate the label
            if (null !== ($translator = $this->getTranslator())) {
                $label = $translator->translate($label, $this->getTranslatorTextDomain());
            }
        }
        
        // Does this element have errors ?
        if ($select->getMessages() && $inputErrorClass) {
            $classAttributes = $select->hasAttribute('class') ? $select->getAttribute('class') . ' ' : '';
            $classAttributes = $classAttributes . $inputErrorClass;
            
            $select->setAttribute('class', $classAttributes);
        }
        
        if ($this->partial) {
            $vars = [
                'select'            => $select,
                'button'            => $button,
                'label'             => $label,
                'labelAttributes'   => $this->labelAttributes,
                'labelPosition'     => $labelPosition,
                'renderErrors'      => $this->renderErrors,
            ];
            
            return $this->view->render($this->partial, $vars);
        }
        
        if ($this->renderErrors) {
            $elementErrors = $elementErrorsHelper->render($select);
        }
        
        $elementString = $elementHelper->render($select);
        
        // hidden elements do not need a <label> -https://github.com/zendframework/zf2/issues/5607
        $type = $select->getAttribute('type');
        if (isset($label) && '' !== $label && $type !== 'hidden') {
            $labelAttributes = [];
            
            if ($select instanceof LabelAwareInterface) {
                $labelAttributes = $select->getLabelAttributes();
            }
            
            if (! $select instanceof LabelAwareInterface || ! $select->getLabelOption('disable_html_escape')) {
                $label = $escapeHtmlHelper($label);
            }
            
            if (empty($labelAttributes)) {
                $labelAttributes = $this->labelAttributes;
            }
            
            // Multicheckbox elements have to be handled differently as the HTML standard does not allow nested
            // labels. The semantic way is to group them inside a fieldset
            if ($type === 'multi_checkbox'
                || $type === 'radio'
                || $select instanceof MonthSelect
                || $select instanceof Captcha
                ) {
                    $markup = sprintf(
                        '<fieldset><legend>%s</legend>%s</fieldset>',
                        $label,
                        $elementString
                        );
                } else {
                    // Ensure element and label will be separated if element has an `id`-attribute.
                    // If element has label option `always_wrap` it will be nested in any case.
                    if ($select->hasAttribute('id')
                        && ($select instanceof LabelAwareInterface && ! $select->getLabelOption('always_wrap'))
                        ) {
                            $labelOpen = '';
                            $labelClose = '';
                            $label = $labelHelper->openTag($select) . $label . $labelHelper->closeTag();
                        } else {
                            $labelOpen  = $labelHelper->openTag($labelAttributes);
                            $labelClose = $labelHelper->closeTag();
                        }
                        
                        if ($label !== '' && (! $select->hasAttribute('id'))
                            || ($select instanceof LabelAwareInterface && $select->getLabelOption('always_wrap'))
                            ) {
                                $label = '<span>' . $label . '</span>';
                            }
                            
                            // Button element is a special case, because label is always rendered inside it
                            if ($select instanceof Button) {
                                $labelOpen = $labelClose = $label = '';
                            }
                            
                            if ($select instanceof LabelAwareInterface && $select->getLabelOption('label_position')) {
                                $labelPosition = $select->getLabelOption('label_position');
                            }
                            
                            switch ($labelPosition) {
                                case self::LABEL_PREPEND:
                                    $markup = $labelOpen . $label . $elementString . $labelClose;
                                    break;
                                case self::LABEL_APPEND:
                                default:
                                    $markup = $labelOpen . $elementString . $label . $labelClose;
                                    break;
                            }
                }
                
                if ($this->renderErrors) {
                    $markup .= $elementErrors;
                }
        } else {
            if ($this->renderErrors) {
                $markup = $elementString . $elementErrors;
            } else {
                $markup = $elementString;
            }
        }
        
        return $markup;
    }
    
    /**
     * Set the class that is added to element that have errors
     *
     * @param  string $inputErrorClass
     * @return $this
     */
    public function setInputErrorClass($inputErrorClass)
    {
        $this->inputErrorClass = $inputErrorClass;
        return $this;
    }
    
    /**
     * Get the class that is added to element that have errors
     *
     * @return string
     */
    public function getInputErrorClass()
    {
        return $this->inputErrorClass;
    }
    
    /**
     * Set the attributes for the row label
     *
     * @param  array $labelAttributes
     * @return $this
     */
    public function setLabelAttributes($labelAttributes)
    {
        $this->labelAttributes = $labelAttributes;
        return $this;
    }
    
    /**
     * Get the attributes for the row label
     *
     * @return array
     */
    public function getLabelAttributes()
    {
        return $this->labelAttributes;
    }
    
    /**
     * Set the label position
     *
     * @param  string $labelPosition
     * @throws Exception\InvalidArgumentException
     * @return $this
     */
    public function setLabelPosition($labelPosition)
    {
        $labelPosition = strtolower($labelPosition);
        if (! in_array($labelPosition, [self::LABEL_APPEND, self::LABEL_PREPEND])) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects either %s::LABEL_APPEND or %s::LABEL_PREPEND; received "%s"',
                __METHOD__,
                __CLASS__,
                __CLASS__,
                (string) $labelPosition
                ));
        }
        $this->labelPosition = $labelPosition;
        
        return $this;
    }
    
    /**
     * Get the label position
     *
     * @return string
     */
    public function getLabelPosition()
    {
        return $this->labelPosition;
    }
    
    /**
     * Set if the errors are rendered by this helper
     *
     * @param  bool $renderErrors
     * @return $this
     */
    public function setRenderErrors($renderErrors)
    {
        $this->renderErrors = (bool) $renderErrors;
        return $this;
    }
    
    /**
     * Retrieve if the errors are rendered by this helper
     *
     * @return bool
     */
    public function getRenderErrors()
    {
        return $this->renderErrors;
    }
    
    /**
     * Set a partial view script to use for rendering the row
     *
     * @param null|string $partial
     * @return $this
     */
    public function setPartial($partial)
    {
        $this->partial = $partial;
        return $this;
    }
    
    /**
     * Retrieve current partial
     *
     * @return null|string
     */
    public function getPartial()
    {
        return $this->partial;
    }
    
    /**
     * Retrieve the FormLabel helper
     *
     * @return FormLabel
     */
    protected function getLabelHelper()
    {
        if ($this->labelHelper) {
            return $this->labelHelper;
        }
        
        if ($this->view !== null && method_exists($this->view, 'plugin')) {
            $this->labelHelper = $this->view->plugin('form_label');
        }
        
        if (! $this->labelHelper instanceof FormLabel) {
            $this->labelHelper = new FormLabel();
        }
        
        if ($this->hasTranslator()) {
            $this->labelHelper->setTranslator(
                $this->getTranslator(),
                $this->getTranslatorTextDomain()
                );
        }
        
        return $this->labelHelper;
    }
    
    /**
     * Retrieve the FormElement helper
     *
     * @return FormElement
     */
    protected function getElementHelper()
    {
        if ($this->elementHelper) {
            return $this->elementHelper;
        }
        
        if ($this->view !== null && method_exists($this->view, 'plugin')) {
            $this->elementHelper = $this->view->plugin('form_element');
        }
        
        if (! $this->elementHelper instanceof FormElement) {
            $this->elementHelper = new FormElement();
        }
        
        return $this->elementHelper;
    }
    
    /**
     * Retrieve the FormElementErrors helper
     *
     * @return FormElementErrors
     */
    protected function getElementErrorsHelper()
    {
        if ($this->elementErrorsHelper) {
            return $this->elementErrorsHelper;
        }
        
        if ($this->view !== null && method_exists($this->view, 'plugin')) {
            $this->elementErrorsHelper = $this->view->plugin('form_element_errors');
        }
        
        if (! $this->elementErrorsHelper instanceof FormElementErrors) {
            $this->elementErrorsHelper = new FormElementErrors();
        }
        
        return $this->elementErrorsHelper;
    }
}
