<?php
namespace Components\Form\View\Helper;

use Laminas\Form\ElementInterface;
use Laminas\Form\View\Helper\AbstractHelper;

class FormTreeview extends AbstractHelper
{
    const ULCLASS = 'FormTreeViewUL';
    const CHILDCLASS = 'FormTreeViewULNested';
    
    protected $class;
    
    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()}.
     *
     * @param  ElementInterface|null $element
     * @return string|FormTreeview
     */
    public function __invoke(ElementInterface $element = null)
    {
        if (! $element) {
            return $this;
        }
        
        $this->class = $this->uuidv4();
        return $this->render($element);
    }
    
    public function render(ElementInterface $element)
    {
        $rendered = $this->renderStyle();
        
        $ai = new \RecursiveArrayIterator($element->getData());
        $ti = new \RecursiveTreeIterator($ai);
        
        $current_depth = 0;
        $previous_depth = -1;
        $html = "";
        
        foreach ($ti as $key => $value) {
            $current_depth = $ti->getDepth();
            
            if ($current_depth > $previous_depth) {
                //-- Start a new UL tag --//
                if ($current_depth == 0) {
                    $class = $this::ULCLASS;
                } else {
                    $class = $this::CHILDCLASS;
                }
                $html .= "<ul class='$class'>" . PHP_EOL;
            } elseif ($previous_depth > $current_depth) {
                //-- Close LI/UL tags until we're at current depth --//
                for ($i = $previous_depth; $i > $current_depth; $i--) {
                    $html .= '</li>' . PHP_EOL;
                    $html .= '</ul>' . PHP_EOL;
                }
                
                //-- Close previous LI tag --//
                $html .= '</li>' . PHP_EOL;
            } else {
                //-- Close previous LI tag --//
                $html .= '</li>' . PHP_EOL;
            }
            
            if ($ti->callHasChildren()) {
                //-- Render Branch --//
                $html .= $this->renderBranch($key);
            } else {
                //-- Render Leaves --//
                $html .= $this->renderLeaves($value);
            }
            
            $previous_depth = $current_depth;
        }
        
        $rendered .= $html;
        $rendered .= $this->renderScript();
        
        return $rendered;
    }
    
    private function renderBranch($key)
    {
        $html = "<li><span class='$this->class'><i class='fas fa-caret-right'></i>" . $key . '</span>';
        return $html;
    }
    
    private function renderLeaves($value)
    {
        $html = '<li>' . $value;
        return $html;
    }
    
    private function renderStyle()
    {
        $rendered = sprintf('
        <style>
        
        /* Remove default bullets */
        ul, #%s {
        list-style-type: none;
        }
        
        /* Remove margins and padding from the parent ul */
        #%s {
            margin: 0;
            padding: 0;
        }
        
        /* Style the caret/arrow */
        .care {
            cursor: pointer;
            user-select: none; /* Prevent text selection */
        }
        
        /* Create the caret/arrow with a unicode, and style it */
        .fa-caret-right::before {
            //content: "\25B6";
            color: black;
            display: inline-block;
            margin-right: 6px;
        }
        
        /* Rotate the caret/arrow icon when clicked on (using JavaScript) */
        .care-down::before {
            transform: rotate(90deg);
        }
        
        /* Hide the nested list */
        .%s {
            display: none;
        }
        
        /* Show the nested list when the user clicks on the caret/arrow (with JavaScript) */
        .active {
            display: block;
        }
        </style>
        ',$this::ULCLASS, $this::ULCLASS, $this::CHILDCLASS);
        return $rendered;
    }

    private function renderScript()
    {
        $rendered = sprintf('
            <script>
                var toggler = document.getElementsByClassName("%s");
            var i;
            
            for (i = 0; i < toggler.length; i++) {
              toggler[i].addEventListener("click", function() {
                this.parentElement.querySelector(".%s").classList.toggle("active");
                this.classList.toggle("%s");
              });
            }
            </script>
        ',$this->class,$this::CHILDCLASS,'x');
        
        return $rendered;
    }

    private function uuidv4() {
        return sprintf('%04x%04x-%04x-%03x4-%04x-%04x%04x%04x',
            mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 4095),
            bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
            mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)
            );
    }
}