<?php
/**
 * Devuelve la traducción de la cadena recibida
 * según la lengua en uso; la signature de la
 * función es la misma de sprintf
 * (http://es.php.net/manual/en/function.sprintf.php)
 *
 * @return string
 */
function x() {
    global $i18n; //sólo se carga en el hash $i18n el fichero de la lengua en uso
    $args = func_get_args();
    $key = array_shift( $args );
    if( count( $args ) == 1 && is_array( $args[0] ) ) {
        $args = $args[0];
    }
    if ( !isset( $i18n ) ) {
        $tmp = $key;
    }
    elseif ( empty( $i18n[$key] ) ) {
        $tmp = $key;
        addlog(__FILE__, $key);
    }
    else {
        $tmp = $i18n[$key];
    }
    if ( ! is_array( $args ) ) {
        $args = array();
    }
    array_unshift( $args, $tmp );
    $tmp = call_user_func_array( 'sprintf', $args );
    return  $tmp;
}

/**
 * 'xe( $key )' hace el echo de x( $key )
 *
 * @param string $key
 */
function xe( $key ) {
    echo x( $key );
}
