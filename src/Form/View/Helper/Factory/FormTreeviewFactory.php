<?php
namespace Components\Form\View\Helper\Factory;

use Components\Form\View\Helper\FormTreeview;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Form\View\Helper\FormElement;
use Laminas\View\HelperPluginManager;

class FormTreeviewFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName = null, array $options = null): FormTreeview
    {
        $helper = new FormTreeview();
        
        /** @var HelperPluginManager $viewHelperManager */
        $viewHelperManager = $container->get('ViewHelperManager');
        
        /** @var FormElement $formElementHelper */
        $formElementHelper = $viewHelperManager->get(FormElement::class);
        $formElementHelper->addType('treeview', 'formtreeview');
        // or
        $formElementHelper->addClass(FormTreeview::class, 'formtreeview');
        
        return $helper;
    }
}