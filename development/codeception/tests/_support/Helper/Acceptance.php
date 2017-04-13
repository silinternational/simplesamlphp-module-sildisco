<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends \Codeception\Module
{
    
    public function savePageSource() {
        $this->getModule('WebDriver')->_savePageSource(codecept_output_dir().'pageSource_1.html');
    }
    
    public function getElements($identifier) {
        $elements = [];
        foreach ($this->getModule('PhpBrowser')->_findElements($identifier) as $nextEl) {
            $elements[] = $nextEl;
        }
        return $elements;        
    }
    
    public function assertTextContains($needle, $haystack){
        $this->assertContains($needle, $haystack);
    }
        
}
