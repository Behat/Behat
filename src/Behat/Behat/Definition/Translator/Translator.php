<?php

namespace Behat\Behat\Definition\Translator;

class Translator extends \Symfony\Component\Translation\Translator implements TranslatorInterface
{
    
    public function trans($id, array $parameters = array(), $domain = null, $locale = null ) {
        
        if(array_key_exists('%count%', $parameters) && method_exists($this, 'transChoice')){
            return parent::transChoice($id, $parameters['%count%'], $parameters, $domain, $locale);
        }
        return parent::trans($id, $parameters, $domain, $locale);
    }
}
