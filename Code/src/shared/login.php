<?php

require_once (CONFIG::get('ABSPATH') . 'app/m/cookie.php');
require_once (CONFIG::get('ABSPATH') . 'app/m/user.php');

function anonymous_session()
{
    try
    {
        Cookie::$cookiename = 'ANONYMOUS';
        $cookie = new Cookie();
        $cookie->validate();
        $session_id = $cookie->session_id();
    }
    catch (Exception_Cookie $e)
    {
        $session_id = md5(microtime());
        $cookie = new Cookie($session_id);
        $cookie->set();
    }
    session_id($session_id);
    session_start();
}

function check_login_standard()
{
    global $current_user, $current_user_acl, $url_string;
    try
    {
        $cookie = new Cookie();
        $cookie->validate();
        $userT = new User($db);
        $current_user = $userT->get_user($cookie->userauth());
        $current_user_acl = $current_user->getACL();
        session_id($cookie->session_id());
        session_start();
    }
    catch (Exception $e)
    {
        if (! ($e instanceof Exception_Cookie || $e instanceof Exception_User))
        {
            throw $e;
        }
        $q = hash2params(array( 'originating_uri' => $url_string ));
        header('Location: ' . CONFIG::get('APPURL') . 'app/c/login.php?' . $q);
        exit();
    }
}

function check_login_refresh()
{
    global $current_user, $current_user_acl, $url_string;
    try
    {
        Cookie::$cookiename = 'USERAUTH';
        $cookie = new Cookie();
        $cookie->validate();
        $current_user = new User($db, $cookie->session_id());
        $current_user_acl = $current_user->getACL();
        session_id($cookie->session_id());
        session_start();
    }
    catch (Exception_Cookie $e)
    {
        try
        {
            Cookie::$cookiename = 'ANONYMOUS';
            $cookie = new Cookie();
            $cookie->validate();
            $session_id = $cookie->session_id();
            session_id($session_id);
            session_start();
        }
        catch (Exception_Cookie $e)
        {    //continue
        }
    }
}

function check_login_special()
{
    global $current_user, $current_user_acl, $url_string;
    try
    {
        $cookie = new Cookie(false, $_REQUEST);
        $cookie->validate(15, false);
        $current_user = new User($db, $cookie->session_id());
        $current_user_acl = $current_user->getACL();
        session_id($cookie->session_id());
        session_start();
    }
    catch (Exception $e)
    {
        echo $e->getMessage();
        exit();
    }
}

function check_login_xmlrpc()
{
    global $current_user, $current_user_acl, $url_string;
    $cookie = new Cookie(false, $_REQUEST);
    $cookie->validate(15, false);
    $current_user = new User($db, $cookie->session_id());
    $current_user_acl = $current_user->getACL();
    session_id($cookie->session_id());
    session_start();
}


function check_login($type)
{
    global $url_string;
    switch ($type) {
        case 'anonymous':
            if (defined(DISABLED))
            {
                redirect(DISABLED);
            }
            anonymous_session();
        break;
        
        case 'special':
            check_login_special();
            if (!acl("open_url", $url_string))
            {
                redirect_to('noaccess.php');
            }
        break;
        
        case 'xmlrpc':
            check_login_xmlrpc();
            if (!acl("open_url", $url_string))
            {
                forward_to('xmlrpc', 'noaccess');
            }
        break;
        
        case 'standard':
            check_login_standard();
            if (!acl("open_url", $url_string))
            {
                redirect_to('noaccess.php');
            }
        break;
        
        default:
            redirect_to('noaccess.php');
        break;
    }
}

/**
 * Realiza el login del usuario con $username y $password
 *
 * @param $db
 * @param string $username
 * @param string $password
 * @param integer $expire segundos de duración de la cookie; 0 significa toda la sesión del browser
 */
function login(DB $db, $username, $password, $expire)
{
    $userT = new User( $db );
    $user_id = $userT->check_credentials( $username, $password );
    $user = new User($db, $user_id);
    $userauth = $user->get_userauth();
    $cookie = new Cookie( $userauth );
    $cookie->set( $expire );
    $_SESSION['pages_stack'] = array();
}

/**
 * Realiza el logout del usuario actual
 *
 */
function logout()
{
    $cookie = new Cookie();
    $cookie->validate();
    session_id( $cookie->session_id() );
    session_start();
    session_destroy();
    $cookie->logout();
}


