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
    
    function check($datas, $vars)
    {
        if (isset($datas['filtrer']))
        {
            if (isset($datas['past']))
            {
                $vars['past'] = true;
                $_SESSION['voir_tache_ancienne'] = true;
            }
            else {
                unset($_SESSION['voir_tache_ancienne']);
            }
            if (isset($datas['end']))
            {
                $vars['end'] = true;
                $_SESSION['voir_tache_effectue'] = true;
            }
            else {
                unset($_SESSION['voir_tache_effectue']);
            }
            if (isset($datas['ant']))
            {
                $vars['ant'] = true;
                $_SESSION['voir_tache_futur'] = true;
            }
            else {
                unset($_SESSION['voir_tache_futur']);
            }
        }
        if (isset($_SESSION['voir_tache_ancienne']))
        {
            $vars['past'] = true;
        }
        if (isset($_SESSION['voir_tache_effectue']))
        {
            $vars['end'] = true;
        }
        if (isset($_SESSION['voir_tache_futur']))
        {
            $vars['ant'] = true;
        }
        return $vars;
    }
}
