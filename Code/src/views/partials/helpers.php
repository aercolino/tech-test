<?php

function required_label( $html ) {
    return "$html<sup>*</sup>";
}


function options($elementos, $seleccionado) {
    $result = array();
    foreach ( $elementos as $elemento ) {
        $selected = ($elemento['id'] === $seleccionado) ? ' selected' : '';
        $result[] = "<option value=\"{$elemento['id']}\"$selected>{$elemento['label']}</option>";
    }
    $result = implode("\n", $result);
    return "$result\n";
}


function optionsKeyValue($elementos, $seleccionado) {
    $seleccionado = is_array($seleccionado) ? $seleccionado : array($seleccionado);
    $result = array();
    foreach ( $elementos as $key => $value ) {
        $attrs['selected'] = in_array($key, $seleccionado);
        $attrs['value'] = $key;
        $result[] = web_tag('option', $attrs, $value, true);
    }
    $result = implode("\n", $result);
    return "$result\n";
}


function href($url_link, $options = array())
{
    $confirm = ifSet($options['confirm'], '');
    $new_window = ifSet($options['new_window'], false);
    if (! $new_window)
    {
        $hash = params2hash($url_link);
        $new_window = 1 == (ifSet($hash['nw'], 0));
    }
    if ($new_window)
    {
        $width  = ifSet($options['width'],  '640');
        $height = ifSet($options['height'], '480');
        $target = ifSet($options['target'], '_blank');
        $href = "window_open('$url_link',$width,$height,'$target')";
        $result = $confirm
            ? "javascript:confirm('$confirm') ? $href : return false;"
            : "javascript:$href;";
    }
    else
    {
        $href = $url_link;
        $result = $confirm
            ? "javascript:confirm('$confirm') ? document.location.href='$href' : return false;"
            : $href;
    }
    return $result;
}

function web_tag( $name, $attributes = array(), $content = '', $close = false )
{
    $close = $content || $close;
    if (! is_array($attributes))
    {
        $attributes = array();
    }
    $open[] = $name;
    foreach ($attributes as $aName => $aValue)
    {
        if (is_null($aValue) || false === $aValue)
        {
            continue;
        }
        if (true === $aValue)
        {
            $aValue = $aName;
        }
        $open[] = $aName . '="' . htmlspecialchars($aValue) . '"';
    }
    $open = implode(' ', $open);
    if ($close)
    {
        $result = '<' . $open . '>' . $content . '</' . $name . '>';
    }
    else
    {
        $result = '<' . $open . ' />';
    }
    return $result;
}

function web_link( $href, $text = '', $target = '' )
{
    if ('' == $href)
    {
        return $text;
    }
    $attrs['href'] = $href;
    if ($target)
    {
        $attrs['target'] = $target;
    }
    if ('' == $text)
    {
        $text = $href;
    }
    $result = web_tag('a', $attrs, $text);
    return $result;
}

function web_meta( $data )
{
    if (! (is_array($data) && count($data)))
    {
        return '';
    }
    $content = array(
        web_tag('param', array(
            'name' => 'metaparam',
            'value' => '{ cache: true }',
        ))
    );
    foreach ($data as $aName => $aValue)
    {
        if (is_null($aValue))
        {
            continue;
        }
        $content[] = web_tag('param', array(
            'name' => $aName,
            'value' => $aValue,
        ));
    }
    $content = implode('', $content);
    $result = web_tag('object', array('class' => 'metaobject'), $content);
    return $result;
}

function web_radio( $name, $value, $options, $attributes = array(), $wrapper = array() )
{
    if (! is_array($options))
    {
        throw new Exception('Expected an array for radio options');
    }
    if (! is_array($wrapper) || ! count($wrapper))
    {
        $wrapper = array('', '');
    }
    if (! is_array($attributes))
    {
        $attributes = array();
    }
    $value = is_array($value) ? $value : array($value);
    $result = array();
    foreach ($options as $key => $val)
    {
        $attrs = $attributes;
        $attrs['type'] = 'radio';
        $attrs['checked'] = in_array($key, $value);
        $attrs['name'] = $name;
        $attrs['value'] = $key;
        $result[] = $wrapper[0] . web_tag('input', $attrs, x($val)) . $wrapper[1];
    }
    $result = implode("\n", $result);
    return $result;
}

function web_checkbox( $name, $value, $options, $attributes = array(), $wrapper = array() )
{
    if (! is_array($options))
    {
        throw new Exception('Expected an array for checkbox options');
    }
    if (! is_array($wrapper) || ! count($wrapper))
    {
        $wrapper = array('', '');
    }
    if (! is_array($attributes))
    {
        $attributes = array();
    }
    $value = is_array($value) ? $value : array($value);
    $result = array();
    foreach ($options as $key => $val)
    {
        $attrs = $attributes;
        $attrs['type'] = 'checkbox';
        $attrs['checked'] = in_array($key, $value);
        $attrs['name'] = $name;
        $attrs['value'] = $key;
        $result[] = $wrapper[0] . web_tag('input', $attrs, x($val)) . $wrapper[1];
    }
    $result = implode("\n", $result);
    return $result;
}

function web_select( $name, $value, $options, $attributes = array() )
{
    if (! is_array($options))
    {
        throw new Exception('Expected an array for select options');
    }
    $attributes = is_array($attributes) ? $attributes : array();
    $value = is_array($value) ? $value : array($value);
    $result = optionsKeyValue( $options, $value );
    $result = implode("\n", $result);
    $result = web_tag('select', $attributes, $result, true);
    return $result;
}

function web_wrap( $name, $content, $doit = true, $attributes = array() )
{
    $result = $doit ? web_tag($name, $attributes, $content, true) : $content;
    return $result;
}

function selectedOptions( $selected, $options )
{
    $result = array_intersect_key($options, array_flip($selected));
    return $result;
}

function web_add_css( $include_css )
{
    ob_start();
    foreach ( $include_css ?? [] as $key => $value )
    {
        if ('' === $value || 0 === strpos($value, '#'))
        {
?>
<link rel="stylesheet" type="text/css" href="<?= $key ?>"/>
<?php
        }
        else
        {
?>
<style type="text/css">
    /* <?= $key ?> */
    <?= $value ?>
</style>
<?php
        }
    }
    $result = ob_get_clean();
    return $result;
}

function web_add_js( $include_js )
{
    ob_start();
    foreach ( $include_js ?? [] as $key => $value )
    {
        if ('' === $value || 0 === strpos($value, '#'))
        {
?>
<script type="text/javascript" src="<?= $key ?>"></script>
<?php
        }
        else
        {
?>
<script type="text/javascript">
/* <?= $key ?> */
    <?= $value ?>
</script>
<?php
        }
    }
    $result = ob_get_clean();
    return $result;
}

function button($href, $text, $options = array())
{
    $href  = $href == '' ? '#' : $href;
    
    list($text, $title) = array(x("$text - label"), x("$text - alt"));
    
    $class = 'fg-button ui-state-default ui-corner-all';
    $extra = ifSet($options['class'], '');
    $class = $extra ? "$class $extra" : $class;
    
    $attrs = ifSet($options['attrs'], array());
    $attrs['href']  = $href;
    $attrs['title'] = $title;
    $attrs['class'] = $class;
    
    $result = web_tag('a', $attrs, $text);
    return $result;
}

function centered( $content )
{
    /*
     * NOTA: una tabla no funciona!! En cambio, la soluciÃ³n descrita en
     * http://www.cssplay.co.uk/menus/centered.html
     * sÃ­, y es la que aquÃ­ aplicamos
     *
     * <div style="float: left; overflow: hidden;  width: 100%; clear: both;">
     *     <div style="float: left; position: relative; left:  50%; margin: 0 auto; padding: 0;">
     *         <div style="float: left; position: relative; right: 50%;">
     *             <?= $content ?>
     *         </div>
     *     </div>
     * </div>
     */
    $result = ''
        . '<div class="centered"><div><div>'
        . $content
        . '</div></div></div>';
    return $result;
}

function rangePositionType( $num, $start, $end )
{
    if ($num < $start)
    {
        return 'before';
    }
    elseif ($start < $num && $num < $end)
    {
        return 'middle';
    }
    elseif ($end < $num)
    {
        return 'after';
    }
    elseif ($num == $start && $start == $end)
    {
        return 'unique';
    }
    elseif ($num == $start)
    {
        return 'first';
    }
    elseif ($num == $end)
    {
        return 'last';
    }
    else
    {
        return '';
    }
}

function makeListCell( $options, $num_row, $row, $num_col, $column, $value )
{
    $classTr = rangePositionType($num_row, 0, $options['num_rows'] - 1) . 'Tr';
    $classTd = rangePositionType($num_col, 0, $options['num_cols'] - 1) . 'Td';
    $class = "$classTr $classTd";
    $style = '';
    if($column['width'] != '')
    {
        $style = "width:{$column['width']};";
    }
    $attrs = array(
        'class' => $class,
        'style' => $style,
    );
    if (! $value)
    {
        $value = '&nbsp;';
    }
    $result = web_tag('td', $attrs, $value, true);
    return $result;
}

function makeListRow( $options, $num_row, $row )
{
    $makeListCell = $options['makeListCell'];
    global $display;
    $columns = $options['columns'];
    $row_filter = $options['row_filter'];
    if ($row_filter)
    {
        include $row_filter;
    }
    $columns = $options['columns'];
    $cells = array();
    $num_col = 0;
    foreach ($columns as $column)
    {
        $value = $row[ $column['field'] ];
        $cells[] = call_user_func_array($makeListCell, array( $options, $num_row, $row, $num_col, $column, $value ));
        $num_col += 1;
    }
    $result = '';
    if (count($cells))
    {
        //la siguiente columna ficticia vacía permite calibrar perfectamente los anchos de las columnas reales
        $cells[] = call_user_func_array($makeListCell, array( $options, $num_row, $row, $num_col, array(), '' ));
        $cells = implode("\n", $cells);
        $result = web_wrap('tr', $cells);
    }
    return $result;
}

function makeListHeader( $options )
{
    $makeListRow = $options['makeListRow'];
    unset($options['row_filter']);
    $options['num_rows'] = 1;
    $num_row = 0;
    $row = $options['headers'];
    $rows = call_user_func_array($makeListRow, array( $options, $num_row, $row ));
    $attrs = array(
        'border'      => 0,
        'cellspacing' => 0,
        'cellpadding' => 0,
        'class'       => 'tableList2Header',
    );
    $result = web_tag('table', $attrs, $rows, true);
    return $result;
}

function makeListTable( $options )
{
    $makeListRow = $options['makeListRow'];
    $rows = array();
    $data = ifSet($data, $options['data']);
    $num_row = 0;
    foreach ($data as $row)
    {
        $rows[] = call_user_func_array($makeListRow, array( $options, $num_row, $row ));
        $num_row += 1;
    }
    $attrs = array(
        'border'      => 0,
        'cellspacing' => 0,
        'cellpadding' => 0,
        'class'       => 'tableList2',
    );
    $result = ifSet($options['no_rows'], '');
    if (count($rows))
    {
        $rows = implode("\n", $rows);
        $result = web_tag('table', $attrs, $rows, true);
    }
    return $result;
}

function makeListPageParams( $total, $id, $max4page = 10 )
{
    $max4page = (int) $max4page;
    if (! $max4page)
    {
        $max4page = 10;
    }
    $page = (int) $_GET[ $id ];
    $sort = $_GET[ "s_$id" ];
     
    $page = max(array( $page, 1 ));
    $orderBy = '';
    if ($sort)
    {
        $criteria = split("_", $sort);
        $orderBy = $criteria[0] . " " . strtoupper( $criteria[1] );
    }
    
    $href = $_GET;
    unset( $href[ $id ] );
    $href = hash2params( $href );
    $href = '?' . $href . ($href ? '&' : '' ) . $id . '=';
    
    $pages = ceil($total / $max4page);
    
    $params = array(
        'start'    => ($page - 1 ) * $max4page,
        'count'    => $max4page,
        'order_by' => $orderBy,
        'page'     => $page,
        'pages'    => $pages,
        'total'    => $total,
        'href'     => $href,
    );
    return $params;
}

function makeList( $options )
{
    $id = ifSet($options['id'], '(undefined)');
    $options['id'] = $id;
    
    $template = ifSet( $options['template'], '' );
    if (! $template)
    {
        throw new Exception("The template for $id list is null");
    }
    if (! file_exists($template))
    {
        throw new Exception("The template for $id list does not exist");
    }
    $options['template'] = $template;
    
    $row_filter = ifSet( $options['row_filter'], '' );
    if ($row_filter && ! file_exists($row_filter))
    {
        throw new Exception("The row filter for $id list does not exist");
    }
    $options['row_filter'] = $row_filter;
    
    $data = ifSet($options['data'], array());
    if (! is_array($options['data']))
    {
        throw new Exception("The data for $id list is not an array");
    }
    $options['data'] = $data;
    
    $columns = ifSet( $options['columns'], array() );
    if (! count( $columns )) {
        throw new Exception("The columns for $id list are not defined");
    }
    foreach ($columns as $key => $column) {
        $columns[ $key ]['field'] = $key;
        $headers[ $key ] = x($column['label']);
    }
    $options['columns'] = $columns;
    $options['headers'] = $headers;
    
    $options['num_rows'] = count($data);
    $options['num_cols'] = count($columns);
    
    $options['makeListHeader'] = ifSet($options['makeListHeader'], 'makeListHeader');
    $options['makeListTable']  = ifSet($options['makeListTable'],  'makeListTable');
    $options['makeListRow']    = ifSet($options['makeListRow'],    'makeListRow');
    $options['makeListCell']   = ifSet($options['makeListCell'],   'makeListCell');
    
    ////
    
    global $display;
    ob_start();
    require( $template );
    $result = ob_get_clean();
    return $result;
}

