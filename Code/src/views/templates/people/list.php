
<?php 

if ($display['stored_people']) { 

?>
<h3>Stored people</h3>
<?php

    foreach ($display['stored_people'] as $key => $value) {
        d_($value);
    }

}
?>

<h3>Edit</h3>
<form id="main_form" name="user_edit" method="POST" action="<?= $display['button_OK'] ?>">
    <table>
        <tr>
            <th>First name</th>
            <th>Last name</th>
        </tr>
        <tr>
            <td><input type="text" name="people[0][firstname]" value="<?= $display['people'][0]['firstname'] ?>" /></td>
            <td><input type="text" name="people[0][surname]" value="<?= $display['people'][0]['surname'] ?>" /></td>
        </tr>
        <tr>
            <td><input type="text" name="people[1][firstname]" value="<?= $display['people'][1]['firstname'] ?>" /></td>
            <td><input type="text" name="people[1][surname]" value="<?= $display['people'][1]['surname'] ?>" /></td>
        </tr>
        <tr>
            <td><input type="text" name="people[2][firstname]" value="<?= $display['people'][2]['firstname'] ?>" /></td>
            <td><input type="text" name="people[2][surname]" value="<?= $display['people'][2]['surname'] ?>" /></td>
        </tr>
        <tr>
            <td><input type="text" name="people[3][firstname]" value="<?= $display['people'][3]['firstname'] ?>" /></td>
            <td><input type="text" name="people[3][surname]" value="<?= $display['people'][3]['surname'] ?>" /></td>
        </tr>
        <tr>
            <td><input type="text" name="people[4][firstname]" value="<?= $display['people'][4]['firstname'] ?>" /></td>
            <td><input type="text" name="people[4][surname]" value="<?= $display['people'][4]['surname'] ?>" /></td>
        </tr>
    </table>
    <input type="submit" value="OK" />
    <input type="reset" value="Reset" />
    <!-- <input type="button" value="Clear" /> -->
</form>
