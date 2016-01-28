<?php

/*
I'm not going to use Auth as of now, but I'll leave this here...
*/
return;


/**
 * Granted es una clase abstracta che actúa como contenedor de
 * funciones de acceso. Si se declarase la función user_edit()
 * en Granted, en el código habría que usarse la comprobación
 * acl( 'user_edit' )
 *
 */
class Granted
{
    //el constructor privado garantiza que la clase no se pueda instanciar
    private function __construct()
    {}
         
    /**
     * Devuelve true si el usuario actual tiene acceso a la URL $url
     *
     * @param string $url
     * @return boolean
     */
    static public function open_url( $url )
    {
        list($controller, $action, $type) = controller_action($url);
        switch ($controller)
        {
          
            case 'admin':
                $result = acl('p:Admin');
            break;
            
            
            case 'profile':
                $result = User::current() instanceof User;
            break;
            
            
            case 'special':
                $result = acl('p:Special');
            break;
            
            
            default:
                $result = true;
            break;
        }
        return $result;
    }
    
}

/**
 * Returns true if the current user has an acl compatible with $check.
 * Arguments of static methods of Granted can be passed as extra args.
 *
 * @param string $check
 * @return boolean
 */
function acl( $check )
{
    global $current_user_acl;
    
    $acl = $current_user_acl;
    if ($acl[ 'p' ][ 'ForbiddenAccess' ])
    {
        return false;
    }
    if ($acl[ 'p' ][ 'System' ])
    {
        return true;
    }
    
    $result = false;
    $match = substr($check, 0, 2);
    switch ($match)
    {
        case 'u:':
        case 'g:':
        case 'p:':
            $type = substr($check, 0, 1);
            $target = substr($check, 2);
            $result = check($acl[ $type ], $target);
        break;
        default:
            $args = array_slice(func_get_args(), 1);
            if (is_callable(array( 'Granted', $check )))
            {
                $result = call_user_func_array(array( 'Granted', $check ), $args);
            }
    }
    return $result;
}

