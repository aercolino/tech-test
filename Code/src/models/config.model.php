<?php

class CONFIG
{
    static public $ENV; //entorno actual
    static public $APP; //aplicación actual
    
    /**
     * Claves internas
     *
     * @var array
     */
    static protected $internal_keys = array();
    
    /**
     * Claves definidas en ficheros de configuración
     *
     * @var array
     */
    static protected $external_keys = array(
        'SUPERADMIN',
        'DATAPATH',
        'LOGSPATH',
        'APPHOST',
        'ABSPATH',
        'APPURL',
    );
    
    /**
     * Almacena los valores de configuración de todas las claves
     * de todas las aplicaciones en todod los entornos
     *
     * @var array
     */
    static protected $_config = array();
    
    /**
     * Devuelve el valor de la clave $name,
     * para la aplicación $app, en el entorno $env
     *
     * @param string $name
     * @param string $app
     * @param string $env
     * @return mixed
     */
    static public function get($name, $app = null, $env = null)
    {
        list( $app, $env ) = self::checkAppEnv($app, $env);
        if (! isset(self::$_config[ "$app:$env" ]))
        {
            throw new Exception('Expected a configuration identifier');
        }
        $config = self::$_config[ "$app:$env" ];
        foreach (explode('.', $name) as $subname)
        {
            if (! isset($config[ $subname ]))
            {
                throw new Exception('Expected a configuration key '.$subname);
            }
            $config = $config[ $subname ];
        }
        return $config;
    }
    
    /**
     * Asigna el valor $value a la clave $name,
     * para la aplicación $app, en el entorno $env
     *
     * @param string $name
     * @param mixed $value
     * @param string $app
     * @param string $env
     */
    static public function set($name, $value, $app = null, $env = null)
    {
        list( $app, $env ) = self::checkAppEnv($app, $env);
        self::$_config[ "$app:$env" ][ $name ] = $value;
    }
    
    /**
     * Devuelve un array con los valores del aplicación y el entorno,
     * según los parámetros $app y $env, cuyos defaults son CONFIG::$APP
     * y CONFIG::$ENV
     *
     * @param string $app
     * @param string $env
     * @return array
     */
    static public function checkAppEnv( $app, $env )
    {
        if (is_null($app))
        {
            $app = self::$APP;
        }
        if (is_null($env))
        {
            $env = self::$ENV;
        }
        return array( $app, $env );
    }
    
    /**
     * Carga y valida el fichero de configuración del aplicación $app,
     * para el entorno $env
     *
     * @param string $app
     * @param string $env
     */
    static public function load( $app = null, $env = null )
    {
        list( $app, $env ) = self::checkAppEnv($app, $env);
        $config = array();
        require dirname(__FILE__, 2) . "/config/$env/$app.php";
        self::checkKeysAllSetAndNotNull($config, self::$external_keys);
        self::$_config["$app:$env"] = $config;
    }
    
    /**
     * Devuelve las $keys que no están definidas en $config
     *
     * @param array $config
     * @param array $keys
     * @return array
     */
    static public function findKeysNotSet( $config, $keys )
    {
        $config_keys = array_keys($config);
        $result = array_diff($keys, $config_keys);
        return $result;
    }
    
    /**
     * Devuelve las $keys de $config cuyos valores son nulos
     *
     * @param array $config
     * @param array $keys
     * @return array
     */
    static public function findKeysWithNoValue( $config, $keys )
    {
        $result = array();
        foreach ($keys as $key)
        {
            if (! (isset($config[ $key ]) && '' != $config[ $key ]))
            {
                $result[] = $key;
            }
        }
        return $result;
    }
    
    /**
     * Devuelve true si $config contiene valores no nulos en todas las $keys
     *
     * @param array $config
     * @param array $keys
     */
    static public function checkKeysAllSetAndNotNull( $config, $keys )
    {
        $not_defined = self::findKeysNotSet($config, $keys);
        if (count($not_defined))
        {
            throw new Exception('Should be set: ' . implode(', ', $not_defined));
        }
        
        $not_defined = self::findKeysWithNoValue($config, $keys);
        if (count($not_defined))
        {
            throw new Exception('Should be not null: ' . implode(', ', $not_defined));
        }
    }
    
    /**
     * Devuelve el $path con una barra final, según $terminar_con_barra
     *
     * @param string $path
     * @param boolean $terminar_con_barra
     * @return string
     */
    static public function checkPath( $path, $terminar_con_barra = true )
    {
        $path = preg_replace('@/$@', '', $path);
        $result = $path . ($terminar_con_barra ? '/' : '');
        return $result;
    }
        
}
