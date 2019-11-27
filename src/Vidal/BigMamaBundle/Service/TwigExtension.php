<?php

namespace Vidal\BigMamaBundle\Service;

class TwigExtension extends \Twig_Extension
{
    protected $testMode;

    public function __construct($testMode)
    {
        $this->testMode = $testMode;
    }

    public function getName()
    {
        return 'big_mama_twig_extension';
    }

    /**
     * Return the functions registered as twig extensions
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'isTestMode' => new \Twig_Function_Method($this, 'isTestMode'),
        );
    }

    /**
     * Дополнительные фильтры
     */
    public function getFilters()
    {
        return array();
    }

    /**
     * Вытаскивает и преобразует URL картинки из новостей EVRIKA
     */
    public function isTestMode()
    {
        return $this->testMode;
    }
}