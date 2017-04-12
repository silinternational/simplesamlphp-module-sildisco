<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends \Codeception\Module
{
    public function setMaxRedirects($limit) {
        $this->getModule('PhpBrowser')->client->setMaxRedirects($limit);
    }
    
    public function showWebPage() {
        echo PHP_EOL . "Current Web Page" .
             $this->getModule('PhpBrowser')->_getResponseContent() . 
             PHP_EOL;
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
