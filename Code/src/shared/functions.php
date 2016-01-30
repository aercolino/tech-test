<?php

function isActionOfKind( $controller, $action, $kind ) {
    return
           in_array( "$controller/*",       $kind )
        || in_array( "$controller/$action", $kind )
    ;
}

function controller_action( $url_string )
{
    $valid_url = preg_match('@^/index\.php(/.+)?$@', $url_string, $matches);
    if (! $valid_url)
    {
        throw new Exception('Expected a valid URL');
    }
    $controller_action = $matches[1] ?? '';

    $explicit_controller = preg_match('@^/([\w-]+)@', $controller_action, $matches);
    if ($explicit_controller) {
        $controller = $matches[ 1 ];
        $controller_action = substr($controller_action, strlen($controller) + 1);
    }
    else
    {
        $controller = 'index';
    }

    $explicit_action = preg_match('@^/([\w-]+)@', $controller_action, $matches);
    if ($explicit_action) {
        $action = $matches[ 1 ];
        $controller_action = substr($controller_action, strlen($action) + 1);
    }
    else
    {
        $action = 'index';
    }
    
    if (isActionOfKind($controller, $action, array(
        'index/*',
        'people/*',
    )))
    {
        $type = 'anonymous';
    }
    elseif (isActionOfKind($controller, $action, array(
        'special/*',
    )))
    {
        $type = 'special';
    }
    elseif (isActionOfKind($controller, $action, array(
        'xmlrpc/*',
    )))
    {
        $type = 'xmlrpc';
    }
    else
    {
        $type = 'standard';
    }


    
    $result = array(
        $controller,
        $action,
        $type,
    );
    return $result;
}

function check( $arr, $key ) {
    return isset( $arr[ $key ] ) && $arr[ $key ];
}

function ifSet( $value, $else = null ) {
    return isset( $value ) ? $value : $else;
}

function d_( $data, $name = null, $obj = null ) {
    $objClass = is_object($obj) ? get_class( $obj ) : null;
    $msg = "";
    if( $objClass )  { $msg .= "<div>class: <span style='color:blue;'>$objClass</span></div>"; }
    $msg .= "<div><b>$name</b></div>";
    echo "<div style='margin: 3px; border:1px solid silver; padding: 2px;'>";
    echo $msg;
    if( $data ) { echo "<pre>"; print_r( $data ); echo "</pre>"; }
    echo "</div>";
}

function is_serialized($data) {
    // if it isn't a string, it isn't serialized
    if ( !is_string($data) )
        return false;
    $data = trim($data);
    if ( 'N;' == $data )
        return true;
    if ( !preg_match('/^([adObis]):/', $data, $badions) )
        return false;
    switch ( $badions[1] ) {
        case 'a' :
        case 'O' :
        case 's' :
            if ( preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data) )
                return true;
            break;
        case 'b' :
        case 'i' :
        case 'd' :
            if ( preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data) )
                return true;
            break;
    }
    return false;
}

function ex() {
    $params = func_get_args();
    $msg = array_shift( $params );
    return serialize( array( $msg, $params ) );
}

function global_exception_handler( Exception $e ) {
    $e_msg = $e->getMessage();
    if (is_serialized( $e_msg )) {
        $tmp = unserialize( $e_msg );
    }
    else {
        $tmp = array( $e_msg );
    }
    $e_msg = call_user_func_array( 'x', $tmp );
    
    $trace = $e_msg.'<p><br/></p>'.$e->getTraceAsString();
    $msg = $e->getFile() .'('. $e->getLine() .')';
    d_( $trace, $msg, $e );
}

/**
 * Redirecciona a la url $where, completándola con CONFIG::get('APPURL')
 * si hace falta
 *
 * @param string $where
 */
function redirect( $where )
{
    $where_begins_with_http   = 0 === strpos( $where, 'http://' );
    $where_begins_with_APPURL = 0 === strpos( $where, CONFIG::get('APPURL') );
    if ($where_begins_with_http || $where_begins_with_APPURL)
    {
        $url = $where;
    }
    else
    {
        $url = CONFIG::get('APPURL') . $where;
    }
    header( "Location: $url" );
    exit();
}

/**
 * Añade $where_from en la cima de la pila de las páginas de la sesión
 * pages_stack
 *
 * @param string $where_from
 */
function pages_push($where_from)
{
    if (!(isset($_SESSION['pages_stack']) && is_array($_SESSION['pages_stack'])))
    {
        $_SESSION['pages_stack'] = array();
    }
    array_push($_SESSION['pages_stack'], $where_from);
}

/**
 * Quita el último $where_from de la cima de la pila de las páginas de la sesión
 * pages_stack, y lo devuelve
 *
 * @return string
 */
function pages_pop()
{
    if (!(isset($_SESSION['pages_stack']) && is_array($_SESSION['pages_stack'])))
    {
        $_SESSION['pages_stack'] = array();
    }
    $where_from = array_pop($_SESSION['pages_stack']);
    return $where_from;
}

/**
 * Redirecciona a la url $where_to, guardando la url $where_from
 * en la pila de las páginas de la sesión pages_stack; si la $where_from
 * es nula, se usa la $url_string global en su lugar, con lo que conviene
 * usar la $where_from sólo cuando difiere en algo de la página actual
 *
 * @param string $where_to
 * @param string $where_from
 */
function redirect_push( $where_to, $where_from = null )
{
    if (is_null($where_from))
    {
        global $url_string;
        $where_from = $url_string;
    }
    pages_push($where_from);
    redirect($where_to);
}

/**
 * Redirecciona a la url $where_to, quitando el top de la pila de las páginas
 * de la sesión pages_stack; si la $where_to es nula, se usa el top en su
 * lugar, con lo que conviene usar la $where_to sólo cuando difiere en algo
 * de la página anterior
 *
 * @param string $where_to
 */
function redirect_pop( $where_to = null )
{
    $where_from = pages_pop();
    if (is_null($where_to))
    {
        $where_to = $where_from;
    }
    redirect($where_to);
}


/**
 * pasa el control a una acción de un controlador
 * sin ningún roundtrip por el browser
 */
function forward_to($controller, $action)
{
    global $db, $app, $current_user, $current_user_acl, $url_string, $display, $pagination_num, $errors;
    include CONFIG::get('ABSPATH') . '/src/controllers/init.php';
}

/**
 * pasa el control a una acción de un controlador
 * mediante un roundtrip por el browser
 */
function redirect_to( $where, $exit = true, $unset_from_page = true ) {
/**
 * si la ventana de la acción era un popup
 * el redirect debe usar javascript para cerrar el popup
 * y cargar la nueva página (controlador/accion) en el opener
 * si la ventana de la acción no era un popup
 * el redirect puede usar el header location
 */
    if ( $unset_from_page ) {
        unset( $_SESSION['from_page'] );
    }
    $url =
          0 === strpos( $where, 'http://' ) ? $where
        : 0 === strpos( $where, CONFIG::get('APPURL') ) ? $where
        : CONFIG::get('APPURL').$where
    ;

    header( "Location: $url" );
    
    echo "&nbsp;";

    if ($exit) {
        exit();
    }
}

function renderTemplate( $template )
{
    if ( $template && ! file_exists( $template ) )
    {
        throw new Exception( x( "el template %s no existe", $template ) );
    }
    $result = '';
    global $display, $include_css, $include_js;
    if ($template) {
        ob_start();
        include( $template );
        $result = ob_get_clean();
    }
    return $result;
}

function renderLayout( $page_content, $layout )
{
    if ( $layout && ! file_exists( $layout ) )
    {
        throw new Exception( x( "el layout %s no existe", $layout ) );
    }
    $result = $page_content;
    global $display, $include_css, $include_js;
    if ($layout) {
        $display['page_content'] = $result;
        ob_start();
        include( $layout );
        $result = ob_get_clean();
    }
    return $result;
}

function makePage( $template, $layout )
{
    $result = renderTemplate($template);
    $result = renderLayout($result, $layout);
    return $result;
}

/**
 * Crea la página y la envía al browser
 * Si cacheId es no nulo, se cachea la página generada
 *
 * @param string  $template
 * @param string  $layout
 * @param boolean $exit
 * @param string  $cache_id
 */
function render( $template, $layout, $exit = true, $cache_id = null, $cache_tags = null )
{
    global $cache;
    $result = makePage($template, $layout);
    if (! empty($cache_id))
    {
        $cache_tags = ifSet($cache_tags, array());
        $cache->save($result, $cache_id, $cache_tags);
    }
    echo $result;
    if ($exit)
    {
        exit();
    }
}

function popup_close_refresh() {
    return <<< END_popup_close_refresh
jQuery( function() {
    window.opener.refresh_page( '' );
    window.open('', '_parent', '');
    window.close();
} );

END_popup_close_refresh;
}

function popup_refresh() {
    return <<< END_popup_close_refresh
jQuery( function() {
    window.opener.refresh_page( '' );
} );

END_popup_close_refresh;
}

function parent_popup_close_refresh() {
    return <<< END_parent_popup_close_refresh
jQuery( function() {
    parent.window.opener.refresh_page( '' );
    parent.window.close();
} );

END_parent_popup_close_refresh;
}

function popup_close() {
    return <<< END_popup_close
jQuery( function() {
    window.close();
} );

END_popup_close;
}


function popup_opener_reload() {}

function popup_opener_submit() {}

function popup_opener_close() {}

function popup_opener_redirect() {}


function popup_close_and_redirect_opener($location) {
    return <<< END_popup_close_and_redirect_opener
jQuery( function() {
    window.opener.document.location.href = '$location';
    window.close();
} );

END_popup_close_and_redirect_opener;
}

function window_open($location,$width=300,$height=150) {
    return <<< END_window_open
jQuery( function() {
    window.open( '$location' , '_request_info', 'width=$width,height=$height,menubar=no,location=no,resizable=yes,scrollbars=no,status=no' );
} );

END_window_open;
}

function scape( $text ) {
    if ($text!=='') {
        if ((get_magic_quotes_gpc()) && (!is_array($text)))
            return stripslashes($text);
        else if ((get_magic_quotes_gpc()) && (is_array($text))){
            $n = count($text);
            for ($i=0;$i<$n;$i++) {
                $text[$i]= (is_array($text[$i])) ? $text[$i] : stripslashes($text[$i]);
            }
            unset($n);
        }
        return $text;
    }
    return '';
}
/**
 * mueve un array de ficheros de una carpeta a otra
 * cambiando el nombre de cada uno de ellos como indicado
 *
 * @param string $pathFrom
 * @param array $filenamesFrom
 * @param string $pathTo
 * @param array $filenamesTo
 */
function addFilesToFolder( $pathFrom, $filenamesFrom, $pathTo, $filenamesTo, $move = true ) {
    addlog(__FILE__, __FUNCTION__.':INIT');
    
    addlog(__FILE__, __FUNCTION__.':pathFrom='.$pathFrom);
    addlog(__FILE__, __FUNCTION__.':filenamesFrom='.$filenamesFrom);
    addlog(__FILE__, __FUNCTION__.':pathTo='.$pathTo);
    addlog(__FILE__, __FUNCTION__.':filenamesTo='.$filenamesTo);
    
    if (! is_array( $filenamesFrom )) {
        $filenamesFrom = array( $filenamesFrom );
    }
    if (! is_array( $filenamesTo )) {
        $filenamesTo = array( $filenamesTo );
    }
    $top = count( $filenamesFrom );
    if( $top != count( $filenamesTo )) {
        throw new Exception( 'los arrays de ficheros tienen longitudes diferentes' );
    }
    if( $top == 0 ) {
        return;
    }
        
    for( $n = 0; $n < $top; $n++ ) {
        
        $filenameTo   = $filenamesTo[ $n ];
        $filenameFrom = $filenamesFrom[ $n ];
        $filenameFrom = preg_replace( '|^/tmp/|', '', $filenameFrom );
        
        
        addlog(__FILE__, __FUNCTION__.':filenameTo='.$filenameTo);
        addlog(__FILE__, __FUNCTION__.':filenameFrom='.$filenameFrom);
        addlog(__FILE__, __FUNCTION__.':pathTo='.$pathTo);
        addlog(__FILE__, __FUNCTION__.':is_dir('. $pathTo .')='.is_dir( $pathTo ));
        
        
        if( ! is_dir( $pathTo ) ) {
            @mkdir( $pathTo, 0755, true );
            @chmod($pathTo, 0755);
        }
        
        addlog(__FILE__, __FUNCTION__.':is_file('. $pathFrom.$filenameFrom .')='.is_file($pathFrom.$filenameFrom));
        
        if (is_file($pathFrom.$filenameFrom)) {         
            if( copy( $pathFrom.$filenameFrom, $pathTo.$filenameTo ) ) {
                addlog(__FILE__, __FUNCTION__.':copy('. $pathFrom.$filenameFrom .','. $pathTo.$filenameTo .')');
                if( $move ) {
                    unlink( $pathFrom.$filenameFrom );
                }
            }
        }
        else {
            throw new Exception( 'un fichero no se ha podido procesar' );
        }
    }
    addlog(__FILE__, __FUNCTION__.':END');
}

/**
 * mueve un array de ficheros de una carpeta a otra
 * cambiando el nombre de cada uno de ellos como indicado
 *
 * @param string $pathFrom
 * @param array $filenamesFrom
 * @param string $pathTo
 * @param array $filenamesTo
 */
function replaceFilesToFolder( $pathFrom, $filenamesFrom, $pathTo, $filenamesTo, $filenamesOld='', $move = true ) {
    if (! is_array( $filenamesFrom )) {
        $filenamesFrom = array( $filenamesFrom );
    }
    if (! is_array( $filenamesTo )) {
        $filenamesTo = array( $filenamesTo );
    }
    if (! empty( $filenamesOld ) && ! is_array( $filenamesOld )) {
        $filenamesOld = array( $filenamesOld );
    }
    $top = count( $filenamesFrom );
    if( $top != count( $filenamesTo ) || $top != count( $filenamesOld ) ) {
        throw new Exception( 'los arrays de ficheros tienen longitudes diferentes' );
    }
    if( $top == 0 ) {
        return;
    }
    for( $n = 0; $n < $top; $n++ ) {
        $filenameOld  = $filenamesOld[ $n ];
        $filenameTo   = $filenamesTo[ $n ];
        $filenameFrom = $filenamesFrom[ $n ];
        $filenameFrom = preg_replace( '|^/tmp/|', '', $filenameFrom );
        if( ! is_dir( $pathTo ) ) {
            @mkdir( $pathTo, 0755, true );
            @chmod($pathTo, 0755);
        }
        
        if (is_file($pathFrom.$filenameFrom)) {
            // unlink( $filenameOld );
            if( copy( $pathFrom.$filenameFrom, $pathTo.$filenameTo ) ) {
                if( $move ) {
                    unlink( $pathFrom.$filenameFrom );
                    if ( ! empty( $filenamesOld ) && $filenameOld != $pathTo.$filenameTo ) {
                        unlink( $filenameOld );
                    }
                }
            }
        }
        else {
            throw new Exception( 'un fichero no se ha podido procesar' );
        }
    }
}


function saveToFile( $string, $filename, $mode = 'aw' ) {
    if( '' == $filename || '' == basename( $filename ) ) {
        throw new Exception( 'el nombre del fichero es nulo' );
    }
    
    $filename = CONFIG::get('LOGSPATH').date('Ymd').$filename;
    
    $path = dirname( $filename );
    if( ! is_dir( $path ) ) {
        @mkdir( $path, 0755, true );
        @chmod($path, 0755);
    }
    $file = fopen( $filename, $mode );
    @fwrite( $file, date('Y-m-d H:i:s')." ".$string."\n" );
    fclose( $file );
}


function loadFromFile( $filename, $mode = 'r' ) {
    if( '' == $filename ) {
        throw new Exception( 'el nombre del fichero es nulo' );
    }
    $path = dirname( $filename );
    if( ! is_dir( $path ) ) {
        throw new Exception( 'la ruta del fichero es incorrecta' );
    }
    $file = fopen( $filename, $mode );
    $string = @fread( $file, filesize( $filename ) );
    fclose( $file );
    
    return $string;
}

function sinAcentos( $string ) {
    $con = array(
          'à', 'á', 'â', 'ã', 'ä', 'å'
        , 'è', 'é', 'ê'     , 'ë'
        , 'ì', 'í', 'î'     , 'ï'
        , 'ò', 'ó', 'ô', 'õ', 'ö'
        , 'ù', 'ú', 'û'     , 'ü'
    );
    $con = array(
          '\00C0', '\00C1', '\00C2', '\00C3', '\00C4', '\00C5'
        , '\00C8', '\00C9', '\00CA'         , '\00CB'
        , '\00CC', '\00CD', '\00CE'         , '\00CF'
        , '\00D2', '\00D3', '\00D4', '\00D5', '\00D6'
        , '\00D9', '\00DA', '\00DB'         , '\00DC'
    );
    $sin = array(
          'a', 'a', 'a', 'a', 'a', 'a'
        , 'e', 'e', 'e'     , 'e'
        , 'i', 'i', 'i'     , 'i'
        , 'o', 'o', 'o', 'o', 'o'
        , 'u', 'u', 'u'     , 'u'
    );
    $aux = str_ireplace( $con, $sin, $string );
    return $aux;
}

/**
 * Devuelve la cadena $it transformada como un identificador
 *
 * @param string $it
 * @return string
 */
function slug( $it ) {
    $it = quitarAcentos($it);
    $it = preg_replace(
          array( '/\s/', '/([A-Z])/e'        , '/\W/' )
        , array( '_'   , "strtolower( '$1' )", '' )
        , $it
    );
    return $it;
}

function page_post_get()
{
    $result = isset($_SESSION['page_post']) && is_array($_SESSION['page_post']) ? $_SESSION['page_post'] : null;
    return $result;
}

function page_post_set( $post = null )
{
    if (is_null($post))
    {
        $post = $_POST;
    }
    $_SESSION['page_post'] = $post;
}

function page_post_reset()
{
    unset($_SESSION['page_post']);
}

function post_data() {
global $refresh_value;
    if ( isset( $_SESSION['page_post'] ) ) {
        $data = $_SESSION['page_post'];
        unset( $_SESSION['page_post'] );
        $data['refresh'] = true;
    }
    else {
        $data = $_POST;
        if ( $refresh_value ) {
            $data['refresh'] = true;
            unset($refresh_value);
        }
    }
    return $data;
}


function post_errors() {
global $refresh_value,$errors;
    if ( isset( $_SESSION['page_error'] ) || isset( $_SESSION['errors'] ) ) {
        $page_error = $_SESSION['page_error'];
        unset( $_SESSION['page_error'] );
        $errors = $_SESSION['errors'];
        unset( $_SESSION['errors'] );
    }
    else {
        $page_error = '';
        if (!$refresh_value) {
            $errors = array();
        }
        unset($refresh_value);
    }
    return array( $page_error, $errors );
}


function hash2params( $hash ) {
    $aux = array();
    foreach( $hash as $name => $value ) {
        $aux[] = rawurlencode( $name )."=".rawurlencode( $value );
    }
    return implode( '&', $aux );
}


function params2hash( $url ) {
    $params = url_split($url)['params'];
    if (! $params) {
        return [];
    }
    $aux = [];
    $pairs = explode( '&', $params );
    foreach( $pairs as $pair ) {
        $aux2 = explode( '=', $pair );
        $aux[ urldecode( $aux2[ 0 ] ) ] = urldecode( $aux2[ 1 ] ?? '' );
    }
    return $aux;
}


function url_split($url) {
    $split = preg_split('@\?((?:(?:[^=]+)=(?:[^&]+)&)*(?:[^=]+)=(?:[^#]+))@', $url, null, PREG_SPLIT_DELIM_CAPTURE);
    $result['path']     = $split[0] ?? '';
    $result['params']   = $split[1] ?? '';
    $result['fragment'] = $split[2] ?? '';
    return $result;
}


function url_join($parts) {
    $result = $parts['path'] . 
        ($parts['params']   ? '?' : '') . $parts['params'] . 
        ($parts['fragment'] ? '#' : '') . $parts['fragment'];
    return $result;
}


function hrefDownloadAttachment( $path, $filename, $nice_filename, $file_table = '', $file_id = '', $extra = '' ) {
    $params = array(
          'path'     => $path
        , 'file'     => $filename
        , 'table'    => $file_table
        , 'id'       => $file_id
        , 'filename' => $nice_filename
    );
    $params = hash2params( $params );
    $href = CONFIG::get('APPURL').'/src/shared/download.php?'.$params;
    return $href;
}

function hrefFreeaccessFile( $path, $filename, $nice_filename, $file_table = '', $file_id = '' ) {
    $params = array(
          'path'     => $path
        , 'file'     => $filename
        , 'table'    => $file_table
        , 'id'       => $file_id
        , 'filename' => $nice_filename
    );
    $params = hash2params( $params );
    $href = CONFIG::get('APPURL').'/src/shared/freeaccess.php?'.$params;
    return $href;
}

function merge_params( $url, $hash ) {
    if (isset($hash['controller'])) {
        $url = replace_controller($url, $hash['controller']);
        unset($hash['controller']);
    }
    if (isset($hash['action'])) {
        $url = replace_action($url, $hash['action']);
        unset($hash['action']);
    }
    $params = params2hash($url);
    $params = array_merge($params, $hash);
    $params = hash2params($params);
    $parts = url_split($url);
    $parts['params'] = $params;
    $result = url_join($parts);
    return $result;
}


function replace_controller($url, $controller) {
    $result = preg_replace('@^/index.php/[\w-]+@', "/index.php/$controller", $url);
    return $result;
}


function replace_action($url, $action) {
    $result = preg_replace('@^/index.php/([\w-]+)/[\w-]+@', "/index.php/$1/$action", $url);
    return $result;
}


function add_params( $url, $hash ) {
    if (strpos( $url, '?' ) === false) {
        return $url.'?'.hash2params( $hash );
    }
    else {
        return $url.'&'.hash2params( $hash );
    }
}


function unquote($text) {
    return substr($text,1,strlen($text)-2);
}


function split_mail( $mail ) {
    if (preg_match( '/"([^"]+)" <([^>]+)>/', $mail, $parts )) {
        $result = array(
            'name' => $parts[1],
            'mail' => $parts[2],
        );
    } else {
        $result = array(
            'name' => '',
            'mail' => $mail,
        );
    }
    return $result;
}


function replace_commas( $value ) {
    $result = str_replace(',', ' ', $value);
    return $result;
}


function unique_list( $list, $split = false ) {
    if (is_array( $list )) {
        $list = array_map('replace_commas', $list);
        $imploded = implode (',', $list);
    } else {
        $list = replace_commas( $list );
        $imploded = $list;
    }
    $exploded = explode(',', $imploded);
    $unique = array_unique( $exploded );
    if ($split) {
        $result = $unique;
    } else {
        $result = implode (',', $unique);
    }
    return $result;
}


function sendMail( $from, $to, $subject, $message, $attachments = null, $bcc = null ) {
    if( empty( $to ) ) {
        return false;
    }
    if( empty( $bcc ) ) {
        $bcc = array();
    }
    
    $mail = new Zend_Mail('utf-8');
    
    $from = split_mail( $from );
    $mail->setFrom( $from['mail'], $from['name'] );
    
    $to = unique_list( $to, true );
    foreach ($to as $item) {
        $split = split_mail( $item );
        $mail->addTo( $split['mail'], $split['name'] );
    }
    
    $bcc = unique_list( $bcc, true );
    $bcc = array_diff( $bcc, $to );
    foreach ($bcc as $item) {
        $split = split_mail( $item );
        $mail->addBcc( $split['mail'] );
    }
    
    $mail->setSubject( $subject );
    $mail->setBodyHtml( $message, 'utf-8' );
    
    if (is_array($attachments)) {
        foreach ($attachments as $attachment) {
            $binario = file_get_contents( $attachment );
            $mime_part = $mail->createAttachment( $binario );
            $mime_part->filename = basename( $attachment );
        }
    }
    
    $mail->send();
}


// devuelve un timestamp de un formato fecha
// $dbDate dd-mm-aaaa

function getTs($dbDate) {
    if ($dbDate!=null) {
        $dbDate = trim($dbDate);
        $day = substr($dbDate,0,2);
        $mon = substr($dbDate,3,2);
        $year = substr($dbDate,6,4);
        return mktime(0,0,0,$mon,$day,$year);
    }
    else
        return '';
}

// devuelve un timestamp de un formato fecha
// $dbDate aaaa-mm-dd

function getTsDBFormat($dbDate) {
    if ($dbDate!=null) {
        $dbDate = trim($dbDate);
        $day = substr($dbDate,8,2);
        $mon = substr($dbDate,5,2);
        $year = substr($dbDate,0,4);
        return mktime(0,0,0,$mon,$day,$year);
    }
    else
        return '';
}


function getTsEnd($dbDate) {
    if ($dbDate!=null) {
        $dbDate = trim($dbDate);
        $day = substr($dbDate,0,2);
        $mon = substr($dbDate,3,2);
        $year = substr($dbDate,6,4);
        return mktime(23,59,59,$mon,$day,$year);
    }
    else
        return '';
}

function getFriendlyDateOnly($time) {
    if (!empty($time)) {
        $fecha = getdate($time);
        if (mb_strlen($fecha['mon'])==1) $fecha['mon'] = "0".$fecha['mon'];
        if (mb_strlen($fecha['mday'])==1) $fecha['mday'] = "0".$fecha['mday'];
        return $fecha['mday']."/".$fecha['mon']."/".$fecha['year'];
    }
    else
        return '';
}

function get_freepath() {
    global $current_user;
    $userdir = $current_user->get( 'id' ).'/';
    $daydir = date( 'Y-m-d/' );
    $freepath = CONFIG::get('FREEPATH') . $daydir . $userdir;
    if (! is_dir( $freepath )) {
        @mkdir( $freepath, 0755, true );
        @chmod($freepath, 0755);
    }
    return $freepath;
}


function exec_FOP( $files, $mode = '-q', $asynch = false ) {
    $config_file = $files[ 'config_file' ];
    $fo_file     = $files[ 'fo_file' ];
    $xml_file    = $files[ 'xml_file' ];
    $xsl_file    = $files[ 'xsl_file' ];
    $pdf_file    = $files[ 'pdf_file' ];
    $log_file    = $files[ 'log_file' ];
    
    $config_file_ok = !empty( $config_file ) && file_exists( $config_file );
    $fo_file_ok     = !empty( $fo_file )     && file_exists( $fo_file );
    $xml_file_ok    = !empty( $xml_file )    && file_exists( $xml_file );
    $xsl_file_ok    = !empty( $xsl_file )    && file_exists( $xsl_file );
    $pdf_file_ok    = !empty( $pdf_file );
    $log_file_ok    = !empty( $log_file );
    
    $input_ok = $fo_file_ok || ( $xml_file_ok && $xsl_file_ok );
    
    if( ! ( $config_file_ok && $input_ok && $pdf_file_ok ) ) {
        echo "error_fop: $config_file_ok && ($fo_file_ok || ( $xml_file_ok && $xsl_file_ok )) && $pdf_file_ok\n";
        print_r( $files );
    
        exit;
    }
    
    $config_file_param = $config_file_ok ? ' -c '  .$config_file : '';
    $fo_file_param     = $fo_file_ok     ? ' -fo ' .$fo_file     : '';
    $xml_file_param    = $xml_file_ok    ? ' -xml '.$xml_file    : '';
    $xsl_file_param    = $xsl_file_ok    ? ' -xsl '.$xsl_file    : '';
    $pdf_file_param    = $pdf_file_ok    ? ' -pdf '.$pdf_file    : '';
    
    if ($asynch) {
        $command = "nice ".CONFIG::get('app_FOPBIN')." -r $mode";
    }
    else {
        $command = CONFIG::get('app_FOPBIN')." -r $mode";
    }
    $command .= $config_file_param
        .($fo_file_ok ? $fo_file_param : $xml_file_param.$xsl_file_param )
        .$pdf_file_param
    ;
    
    $log_tmp = 'makePrintPdfs.txt';
    saveToFile($command,$log_tmp);
    
    $pre_fop = "export JAVACMD=".CONFIG::get('app_JAVAPATH')."java ; ";

    if ($asynch) {
        saveToFile('1:'.$pre_fop.escapeshellcmd( $command ) .( $log_file_ok ? " > $log_file 2>&1" : '' ) . ' &',$log_tmp);
        exec( $pre_fop.escapeshellcmd( $command ) .( $log_file_ok ? " > $log_file 2>&1" : '' ) . ' &' );
    }
    else {
        saveToFile('2:'.$pre_fop.escapeshellcmd( $command ) .( $log_file_ok ? " > $log_file 2>&1" : '' ),$log_tmp);
        exec( $pre_fop.escapeshellcmd( $command ) .( $log_file_ok ? " > $log_file 2>&1" : '' ) );
    }
}

function xml_output( $content ) {
    header('Content-type: text/xml');
    $xml = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
    $xml .= $content                                . "\n";
    echo $xml;
    exit();
}

function xml_document( $encoding, $content ) {
    $xml = '<?xml version="1.0" encoding="'.$encoding.'"?>' . "\n";
    $xml .= "$content\n";
    return $xml;
}


function xml_cdata( $content ) {
    return "<![CDATA[$content]]>";
}


function xml_element( $element, $attributes = '', $content = '' ) {
    if (is_array($attributes)) {
        foreach ($attributes as $key => $value) {
            $attributes[ $key ] = $key . '="' . htmlentities( $value, ENT_QUOTES ) . '"';
        }
        $attributes = implode( ' ', $attributes );
    }
    return "<$element".($attributes ? " $attributes" : '') . ('' === $content ? ' />' : ">$content</$element>");
}


function xml_from_array( $array, $num_prefix = 'num_', $item_name = 'item' ) {
    if (! $item_name) {
        $item_name = 'item';
    }
    if(!is_array($array)) {
        $result = xml_cdata( $array );
        return $result;
    }
    else {
        $result = '';
        foreach( $array as $key => $val ) {
            $attrs = array();
            if (is_numeric($key)) {
                if ($num_prefix) {
                    $key = $num_prefix . $key;
                } else {
                    $attrs[ 'num' ] = $key;
                    $key = $item_name;
                }
            } elseif ('' == $key) {
                $key = $item_name;
            }
            $key = preg_replace( '/\s+/', '_', $key );
            $result .= xml_element( $key, $attrs, xml_from_array( $val, $num_prefix, $item_name ) );
        }
        return $result;
    }
}


function xml_transform( $xml_file, $xsl_file ) {
    // Load the XML source
    $xml = new DOMDocument;
    $xml->load($xml_file);
    
    $xsl = new DOMDocument;
    $xsl->load($xsl_file);
    
    // Configure the transformer
    $proc = new XSLTProcessor;
    $proc->importStyleSheet($xsl); // attach the xsl rules
    $result = $proc->transformToXML($xml);
    
    return $result;
}


function makeFree( $href ) {
    $free = str_replace( '/download.php?', '/freeaccess.php?',$href );
    
    $freepath = get_freepath();
    
    $tmp1 = params2hash( $free );
    $file1 = $tmp1['path'].'/'.$tmp1['file'];
    $new_file1 = $freepath.basename( $file1 );
    
//echo "$file1 | $new_file1";
    copy( $file1, $new_file1 );
    
    $freepath = preg_replace( '@^'.CONFIG::get('FREEPATH').'@', '', $freepath );
    $freepath = substr( $freepath, 0, strlen( $freepath ) - 1 );
    $free = merge_params( $free, array( 'path' => $freepath ) );
    $free_parsed = parse_url( $free );
    if (! $free_parsed['host']) {
        $free = CONFIG::get('APPHOST').$free;
    }
    return $free;
}

/**
 * Retorna el nombre del fichero del path dado sin extension
 *
 * @param string $file
 * @return string
 */
function basename2($file) {
    $filename = "";
    if ($file) {
        $filename = basename($file);
        $filename = preg_replace('/\.\w+$/','',$filename);
    }
    return $filename;
}

/**
 * rmdir que elimina todo un subárbol
 *
 * @param string $path
 * @return boolean
 */
function rmdir2($path) {
    if (!is_dir($path)) {
        return false;
    }
    $stack = Array($path);
    while ($dir = array_pop($stack)) {
        if (@rmdir($dir)) {
            continue;
        }
        $stack[] = $dir;
        $dh = opendir($dir);
        while (($child = readdir($dh)) !== false) {
            if ($child[0] == '.') {
                continue;
            }
            $child = $dir . DIRECTORY_SEPARATOR . $child;
            if (is_dir($child)) {
                $stack[] = $child;
            }
            else {
                unlink($child);
            }
        }
    }
    return true;
}

function cb_preg_merge( $element ) {
    return "/\b$element\b/";
}

/**
 * Devuelve una cadena obtenida reemplazando los marcadores en $string
 * con sus respectivos valores en $subs
 *
 * @param string $string
 * @param array $subs
 * @return string
 */
function preg_merge( $string, $subs ) {
    $patterns = array_map( 'cb_preg_merge', array_keys( $subs ) );
    $tmp = preg_replace( $patterns, array_values( $subs ), $string );
    return $tmp;
}

function check_locked($id,$table,$clean=false) {
global $db,$current_user,$appVars,$errors,$display;
    $user_lock = $appVars->ValueLikeKey( 'editing by:', $table, $id );
    if ($user_lock) {
        $user = new User ($db,$user_lock);
        $_SESSION['page_error'] = 'no se ha podido crear el registro';
        $errors[ 'locked' ]['label'] = x('El documento está siendo editado por ').$user->getNicename();
        $_SESSION['errors'] = $errors;
        redirect_to( CONFIG::get('APPURL').'/src/controllers' );
    }
    else {
        if ($clean)
            $appVars->RemoveAllRecordsByOnlyKey( 'editing by:'.$current_user->get('id') );
        $appVars->Replace( 'editing by:'.$current_user->get('id'), time(), $table, $id );
        $display['locked_done'] = 1;
    }
}

function simplexml_xml( $xml, $xpath ) {
    $tmp = array();
    $results = $xml->xpath( $xpath );
    if( $results && count( $results ) ) foreach ( $results as $result ) {
        $tmp[] = $result->asXML();
    }
    return implode( "\n", $tmp );
}

function simplexml_text( $xml, $xpath ) {
    return strip_tags( simplexml_xml( $xml, $xpath ) );
}


function getNiceDate($ts='',$format='d/m/Y') {
    if (empty($ts)) {
        return '';
    }
    // return date('d/m/Y',$ts);
    return date($format,$ts);
}

function getTextDate($ts='') {
    if (empty($ts)) {
        return '';
    }
    $month = 'Mes'.date( 'm', $ts);
    $date = date( 'j', $ts ).' de '.x($month).' de '.date( 'Y', $ts );
    return $date;
}

/**
 * Retorna ts de una fecha tipo dd/mm/aaaa
 *
 * @param unknown_type $date
 * @return unknown
 */
function getTsFromDate($date) {
    $date = explode("/",$date);
    return mktime(0, 0, 0, intval( $date[1] ), intval( $date[0] ), intval( $date[2] ));
}


function setOrDefault( $var, $def ) {
    return isset( $var ) ? $var : $def;
}

/**
 * Devuelve la cadena $str, camelizada
 *
 * @param string $str
 * @return string
 */
function strtocamel( $str ) {
    $result = preg_replace( '/(?:^|_)([a-z])/e', 'strtoupper(\\1)', $str );
    return $result;
}

/**
 * Devuelve la cadena $str, des-camelizada
 *
 * @param string $str
 * @return string
 */
function strtocamel_undo( $str ) {
    $result = preg_replace( '/([A-Z])/e', '"_" . strtolower(\\1)', $str );
    $result = substr( $result, 1 );
    return $result;
}



/**
 * Devuelve el texto $decrypted encriptado con la clave $key
 * Si la clave es nula, devuelve $decrypted
 *
 * @param string $decrypted
 * @param string $key
 * @param integer $cypher
 * @param integer $mode
 * @return string
 */
function encrypt( $decrypted, $key = null, $cypher = MCRYPT_BLOWFISH, $mode = MCRYPT_MODE_CFB ) {
    if (empty( $key )) {
        return $encrypted;
    }
    $td = mcrypt_module_open( $cypher, '', $mode, '' );
    
    $iv_size = mcrypt_enc_get_iv_size( $td );
    $iv = mcrypt_create_iv( $iv_size, MCRYPT_RAND );
    
    $key_size = mcrypt_enc_get_key_size( $td );
    $key = substr( md5( $key ), 0, $key_size );

    mcrypt_generic_init( $td, $key, $iv );
    $encrypted = base64_encode( mcrypt_generic( $td, $decrypted ) );
    mcrypt_generic_deinit( $td );
    
    mcrypt_module_close( $td );
    
    return $iv . $encrypted;
}


/**
 * Devuelve el texto $encrypted desencriptado con la clave $key
 * Si la clave es nula, devuelve $decrypted
 *
 * @param string $encrypted
 * @param string $key
 * @param integer $cypher
 * @param integer $mode
 * @return string
 */
function decrypt( $encrypted, $key = null, $cypher = MCRYPT_BLOWFISH, $mode = MCRYPT_MODE_CFB ) {
    if (empty( $key )) {
        return $encrypted;
    }
    $td = mcrypt_module_open( $cypher, '', $mode, '' );
    
    $iv_size = mcrypt_enc_get_iv_size( $td );
    $iv = substr( $encrypted, 0, $iv_size );
    
    $key_size = mcrypt_enc_get_key_size( $td );
    $key = substr( md5( $key ), 0, $key_size );

    $encrypted = substr( $encrypted, $iv_size );
    
    mcrypt_generic_init( $td, $key, $iv );
    $decrypted = trim( mdecrypt_generic( $td, base64_decode( $encrypted ) ) );
    mcrypt_generic_deinit( $td );
    
    mcrypt_module_close( $td );
    
    return $decrypted;
}

/**
 * Devuelve FALSE, TRUE, NULL según la fecha $date caiga
 * antes, durante, o después del intervalo de fechas
 * definido por $milestone - $delta y $milestone
 *
 * @param timestamp $date
 * @param timestamp $milestone
 * @param string $delta
 * @return boolean
 */
function is_date_in( $date, $milestone, $delta, $no_time = true ) {
    $format = $no_time ? 'Y-m-d' : 'Y-m-d H:i:s';
    $date_from_str = date( $format, strtotime( $delta, $milestone ) );
    $date_str      = date( $format, $date );
    $date_to_str   = date( $format, $milestone );
    
    if ($date_str < $date_from_str) {
        $result = false;
    } elseif ($date_to_str < $date_str) {
        $result = null;
    } else {
        $result = true;
    }
    return $result;
}
    

/**
 * Devuelve TRUE si hoy es el día en que cae
 * la fecha $milestone - $delta
 *
 *
 * @param timestamp $milestone
 * @param string $delta
 * @return boolean
 */
function is_date( $milestone, $delta = null ) {
    $date = time();
    $milestone = empty($delta) ? $milestone : strtotime( $delta, $milestone );
    $delta = '-1 day';
    $result = is_date_in( $date, $milestone, $delta );
    return $result;
}
    

/**
 * Trabaja con image_clip
 *
 * @param string $old_path
 * @param integer $new_W
 * @param integer $new_H
 * @return array
 */
function image_clip_data( $old_path, $new_W = 0, $new_H = 0 ) {
    if (! ($new_W > 0 && $new_H > 0)) {
        return array();
    }
    list( $old_W, $old_H ) = getimagesize( $old_path );
    $old_aspect = $old_W / $old_H;
    $new_aspect = $new_W / $new_H;
    
    if ($old_aspect > $new_aspect) {
        //la imagen vieja es más horizontal de la nueva
        $select_H = $old_H;
        $select_W = round( $new_aspect * $select_H );
        $select_y = 0;
        $select_x = round( ($old_W - $select_W) / 2 );
    } elseif ($old_aspect < $new_aspect) {
        //la imagen vieja es más vertical de la nueva
        $select_W = $old_W;
        $select_H = round( (1 / $new_aspect) * $select_W );
        $select_x = 0;
        $select_y = round( ($old_H - $select_H) / 2 );
    } else {
        //las dos imágenes tienen la misma relación de aspecto
        $select_W = $old_W;
        $select_H = $old_H;
        $select_x = 0;
        $select_y = 0;
    }
    $result = array(
        'select' => array(
            'X' => $select_x,
            'Y' => $select_y,
            'W' => $select_W,
            'H' => $select_H,
        ),
        'old' => array(
            'W' => $old_W,
            'H' => $old_H,
        ),
    );

    return $result;
}


/**
 * Incorpora la imagen vieja que se halla en $old_path
 * al centro de una imagen nueva que se hallará en $new_path
 *
 * Si las dos imágenes no tienen la misma relación de aspecto,
 * la nueva imagen tendrá dos franjas opuestas menos de la imagen vieja
 *
 * @param string $old_path
 * @param string $new_path
 * @param integer $new_W
 * @param integer $new_H
 * @param integer $new_quality
 */
function image_clip( $old_path, $new_path, $new_W, $new_H, $new_quality ) {
    $data = image_clip_data( $old_path, $new_W, $new_H );
    
    $new = imagecreatetruecolor( $new_W, $new_H );
    $white = imagecolorallocate($new, 255, 255, 255);
    imagefill($new, 0, 0, $white);
    
    $old = imagecreatefromjpeg( $old_path );
    $new_X = $new_Y = 0;
    imagecopyresampled(
        $new,           $old,
        $new_X, $new_Y, $data['select']['X'], $data['select']['Y'],
        $new_W, $new_H, $data['select']['W'], $data['select']['H']
    );
    imagedestroy( $old );
    imagejpeg( $new, $new_path, $new_quality );
    imagedestroy( $new );
}


/**
 * Trabaja con image_fit
 *
 * @param string $old_path
 * @param integer $new_W
 * @param integer $new_H
 * @return array
 */
function image_fit_data( $old_path, $new_W = 0, $new_H = 0 ) {
    if (! ($new_W > 0 && $new_H > 0)) {
        return array();
    }
    list( $old_W, $old_H ) = getimagesize( $old_path );
    $old_aspect = $old_W / $old_H;
    $new_aspect = $new_W / $new_H;
    
    if ($old_aspect > $new_aspect) {
        //la imagen vieja es más horizontal de la nueva
        $select_W = $new_W;
        $select_H = round( (1 / $old_aspect) * $select_W );
        $select_x = 0;
        $select_y = round( ($new_H - $select_H) / 2 );
    } elseif ($old_aspect < $new_aspect) {
        //la imagen vieja es más vertical de la nueva
        $select_H = $new_H;
        $select_W = round( $old_aspect * $select_H );
        $select_y = 0;
        $select_x = round( ($new_W - $select_W) / 2 );
    } else {
        //las dos imágenes tienen la misma relación de aspecto
        $select_W = $new_W;
        $select_H = $new_H;
        $select_x = 0;
        $select_y = 0;
    }
    $result = array(
        'select' => array(
            'X' => $select_x,
            'Y' => $select_y,
            'W' => $select_W,
            'H' => $select_H,
        ),
        'old' => array(
            'W' => $old_W,
            'H' => $old_H,
        ),
    );

    return $result;
}


/**
 * Incorpora la imagen vieja que se halla en $old_path
 * al centro de una imagen nueva que se hallará en $new_path
 *
 * Si las dos imágenes no tienen la misma relación de aspecto,
 * la nueva imagen tendrá dos franjas opuestas más de la vieja imagen
 *
 * @param string $old_path
 * @param string $new_path
 * @param integer $new_W
 * @param integer $new_H
 * @param integer $new_quality
 */
function image_fit( $old_path, $new_path, $new_W, $new_H, $new_quality ) {
    $data = image_fit_data( $old_path, $new_W, $new_H );
    
    $new = imagecreatetruecolor( $new_W, $new_H );
    $white = imagecolorallocate($new, 255, 255, 255);
    imagefill($new, 0, 0, $white);
    
    $old = imagecreatefromjpeg( $old_path );
    $old_X = $old_Y = 0;
    imagecopyresampled(
        $new,                                       $old,
        $data['select']['X'], $data['select']['Y'], $old_X, $old_Y,
        $data['select']['W'], $data['select']['H'], $data['old']['W'], $data['old']['H']
    );
    imagedestroy( $old );
    imagejpeg( $new, $new_path, $new_quality );
    imagedestroy( $new );
}


function percent( $part, $total, $precision = 1, $format = '%8.1f%%', $default = '' ) {
    if (! $total) {
        return $default;
    }
    $result = sprintf( $format, round(100 * $part / $total, $precision ) );
    return $result;
}

function getHoursMinutes( $minuts )
{
    $hores = floor( $minuts / 60 );
    $minutes = $minuts%60;
    
    $hours_minutes = ($hores)? (($hores<10)? "0".$hores.":":$hores.":") : "00:";
    $hours_minutes .= ($minutes)? (($minutes<10)? "0".$minutes :$minutes) : "00";
    
    return $hours_minutes;

}

function addlog($filename, $data)
{
    file_put_contents(
        CONFIG::get('LOGSPATH') . basename2($filename) . '_' . date('Y-m-d') . '.log',
        "\n" . date(DATE_ISO8601) . ' - ' . var_export($data, true),
        FILE_APPEND
    );
}
