<?php
include_once('modules/common/form/form.php');
class filtres extends form
{
   
    function __construct()
    {
        $this->add('checkbox', 'past');
        $this->add('checkbox', 'end');
        $this->add('checkbox', 'ant');
    }
    
    function check($datas, $vars, $save = true)
    {
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
