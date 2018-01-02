<?php

class Form_Element
{
    public $name;
    public $value;
    public $libelle;
    public $attributs = array();
    public $style = array();
    public $glyphicon;
    public $glyphiconStyle = array('margin-right' => '10px');
    public $required = false;
    public $error = false;
    
    public function __construct($name, $value = '')
    {
        $this->name = $name;
        $this->value = $value;
    }
    
    public function hasError()
    {
        if ($this->error !== false) {
            return true;
        }
        
        return false;
    }
    
    function get()
    {
        return $this->getHTML();
    }
    
    function setValue($value)
    {
        $this->value = $value;
    }
    
    function setLibelle($libelle)
    {
        $this->libelle = $libelle;
    }
    
    function setAttributs(array $attributs)
    {
        $this->attributs = $attributs;
    }
    
    function setStyle(array $style)
    {
        $this->style = $style;
    }
    
    function setGlyphicon($glyphicon)
    {
        $this->glyphicon = $glyphicon;
    }
    
    function setGlyphiconStyle(array $style)
    {
        $this->glyphiconStyle = $style;
    }
    
    function setRequired($required)
    {
        $this->required = $required;
    }
    
    function getName()
    {
        return $this->name;
    }
    
    function getValue()
    {
        return $this->value;
    }
    
    function getLibelle()
    {
        if (!empty($this->libelle)) {
            return $this->libelle;
        } else {
            return $this->name;
        }
    }
    
    function getHTMLAttributs()
    {
        $attr = '';
        foreach ($this->attributs as $key => $value) {
            $attr .= ' ' . $key . '="' . $value . '"';
        }
        
        return $attr;
    }
    
    function getHTMLStyle()
    {
        $css = '';
        foreach ($this->style as $key => $value) {
            $css .= $key . ':' . $value . ';';
        }
        if (!empty($css)) {
            return ' style="' . $css . '"';
        }
        
        return '';
    }
    
    function getHTMLGlyphicon()
    {
        if (isset($this->glyphicon)) {
            return '<span title="' . $this->getLibelle() . '" class="glyphicon ' . $this->glyphicon . '"' . $this->getHTMLGlyphiconStyle() . '></span>';
        }
        
        return '';
    }
    
    function getHTMLGlyphiconStyle()
    {
        $css = '';
        foreach ($this->glyphiconStyle as $key => $value) {
            $css .= $key . ':' . $value . ';';
        }
        if (!empty($css)) {
            return ' style="' . $css . '"';
        }
        
        return '';
    }
    
    function isValid()
    {
        if ($this->required && empty($this->value))
        {
            if (!empty($this->libelle)) {
                $this->error = 'Le champ ' . $this->libelle . ' est obligatoire';
            } else {
                $this->error = $this->name . ' est obligatoire';
            }
            return false;
        }
        
        return true;
    }
    function __toString()
    {
        $result = '<div><p>' . $this->getHTMLGlyphicon() . '<span>' . $this->getLibelle() . '</span></p>' . $this->getHTML() . '</div>';
        
        return $result;
    }
}