<?php
declare(strict_types=1);

namespace Sdesmet\ContainerAttributes\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Layout\Element;

class LayoutRenderElementObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $layout = $observer->getEvent()->getLayout();
        $elementName = $observer->getEvent()->getElementName();

        if ($layout->isContainer($elementName)
            && ($containerTag = $layout->getElementProperty($elementName, Element::CONTAINER_OPT_HTML_TAG))
        ) {
            $nodes = $layout->getXpath(sprintf('//*[@name="%s"]/attribute[@name]', $elementName));
            if ($nodes) {
                foreach ($nodes as $node) {
                    $name = $node->attributes()->name;
                    $value = $node->attributes()->value;
                    $output = $observer->getEvent()->getTransport()->getOutput();
                    $output = preg_replace(
                        "/^(<$containerTag.*?)(>)/",
                        sprintf("$1 %s$2", ($name && $value) ? sprintf("%s=\"%s\"", $name, $value) : $name),
                        $output
                    );
                    $observer->getEvent()->getTransport()->setOutput($output);
                }
            }
        }
    }
}
