<?php

class Form_filtres extends form
{
    
    function __construct()
    {
        $this->add($past = new Element_Checkbox('date_passee'));
        $past->setAttributs(array('data-toggle' => 'toggle', 'data-onstyle' => 'danger', 'data-on' => 'Off', 'data-off' => 'On'));
        
        $this->add($details = new Element_Checkbox('details'));
        $details->setAttributs(array('data-toggle' => 'toggle', 'data-onstyle' => 'danger'));
        
        $this->add($ant = new Element_Checkbox('date_anterieure'));
        $ant->setAttributs(array('data-toggle' => 'toggle', 'data-onstyle' => 'danger'));
    }
    
    function check($datas, $vars, $save = true)
    {
        $past = $this->getElement('past');
        $end  = $this->getElement('end');
        $ant  = $this->getElement('ant');
        $filtrer  = $this->getElement('filtrer');
        
        // Voir les tâches avant
        if (isset($datas['past']) && isset($datas['filtrer']))
        {
            $vars['past'] = true;
            if ($save)
                $_SESSION['voir_tache_ancienne'] = true;
        }
        elseif(isset($datas['filtrer']))
        {
            if ($save)
                unset($_SESSION['voir_tache_ancienne']);
        }
        elseif (isset($_SESSION['voir_tache_ancienne']) && $save)
        {
            $vars['past'] = true;
        }
        
        // Voir les tâches après
        if (isset($datas['ant']) && isset($datas['filtrer']))
        {
            $vars['ant'] = true;
            if ($save) {
                $_SESSION['voir_tache_futur'] = true;
            }
        }
        elseif(isset($datas['filtrer']))
        {
            if ($save) {
                unset($_SESSION['voir_tache_futur']);
            }
        }
        elseif(isset($_SESSION['voir_tache_futur']) && $save)
        {
            $vars['ant'] = true;
        }
        
        // Voir les tâches après
        if (isset($datas['details']) && isset($datas['filtrer']))
        {
            $vars['details'] = true;
            if ($save) {
                $_SESSION['voir_details'] = true;
            }
        }
        elseif(isset($datas['filtrer']))
        {
            if ($save) {
                unset($_SESSION['voir_details']);
            }
        }
        elseif(isset($_SESSION['voir_details']) && $save)
        {
            $vars['details'] = true;
        }
        return $vars;
    }
}
