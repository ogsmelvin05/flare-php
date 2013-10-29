<?php

namespace Flare\View\Response;

use Flare\View\Response;

/**
 * 
 * @author anthony
 * 
 */
class Javascript extends Response
{
    /**
     * 
     * @var string
     */
    protected $contentType = 'application/javascript';
    
    /**
     *
     * @var array
     */
    private $_scripts = array();
    
    /**
     * 
     * @param string|array $location
     * @return \Flare\View\Response\Javascript
     */
    public function merge($location)
    {
        if (is_array($location)) {
            foreach ($location as $loc) {
                $this->_scripts[] = array('is_path' => true, 'js' => str_replace('/', DIRECTORY_SEPARATOR, $loc));
            }
        } else {
            $this->_scripts[] = array('is_path' => true, 'js' => str_replace('/', DIRECTORY_SEPARATOR, $location));
        }
        return $this;
    }

    /**
     * 
     * @param string $content
     * @return \Flare\View\Response\Javascript
     */
    public function write($content)
    {
        $this->_scripts[] = array('is_path' => false, 'js' => $content);
        return $this;
    }

    /**
     * 
     * @return string
     */
    private function _compile()
    {
        $tmp = '';
        $content = '';
        foreach ($this->_scripts as $script) {
            if ($script['is_path']) {
                if (file_exists($script['js'])) {
                    ob_start();
                    include $script['js'];
                    $tmp = (string) ob_get_clean();
                    $content .= $tmp ? $tmp."\n" : '';
                }
            } else {
                $content .= $script['js']."\n";
            }
        }

        unset($tmp);
        return $content;
    }
    
    /**
     * 
     * @return string
     */
    public function render()
    {
        return $this->_compile();
    }
}