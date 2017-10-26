<!DOCTYPE html>
<html>
<title> Create Person - LMDb </title>
<body style="background-color:#CEDBED;">
<h1> Create Person </h1>

<form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <input type="radio" id="actor" name="persontype" value="actor"> <label for="actor">Actor</label>
    <input type="radio" id="director" name="persontype" value="director"> <label for="director">Director</label><br> <br>
    <input type="text" id="first" name="first" placeholder="First Name" size="20"> <br> <br>
    <input type="text" id="last" name="last" placeholder="Last Name" size="20"> <br> <br>
    <input type="date" id="dob" name="dob" placeholder="Birthday" size="20"> <br> <br>
    <input type="date" id="dod" name="dod" placeholder="Deathday" size="20"> <br> <br>
    <input type="radio" id="male" name="sex" value="male"> <label for="male">Male</label>
    <input type="radio" id="female" name="sex" value="female"> <label for="female">Female</label> <br> <br>
    <input type="submit" value="Submit">
</form>

<?php

function validationErrors() {
    $tablename = $_POST["persontype"];
    $last = $_POST["last"];
    $first = $_POST["first"];
    $sex = $_POST["sex"];
    $dob = $_POST["dob"];
    $dod = $_POST["dod"];
    return (empty($last) || empty($first) || empty($dob) || empty($dod) || empty($tablename) || ($tablename == "actor" && empty($sex)));
}

function fillOutFields() {
    // TODO: Fill out fields with previously submitted values
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (validationErrors()) {
        fillOutFields();
        print "Please fill out all fields";
        exit(1);
    }
    $db_connection = mysql_connect("localhost", "cs143", "");
    if (!$db_connection) {
        $errmsg = mysql_error($db_connection);
        print "Connection failed: $errmsg Exiting the program. <br>";
        exit(1);
    }
    $db_selected = mysql_select_db("CS143", $db_connection);
    if (!$db_selected) {
        $errmsg = mysql_error($db_selected);
        print "Unable to select the database specified. Exiting program.";
        exit(1);
    }

    $id_update_query = mysql_real_escape_string("UPDATE MaxPersonID SET id=id+1", $db_connection);
    $id_rs = mysql_query($id_update_query, $db_connection);
    $id_query = mysql_query("SELECT id FROM MaxPersonID", $db_connection);
    $id = (int) mysql_fetch_row($id_query)[0];
    $tablename = ucfirst($_POST["persontype"]); // Capitalize first letter of table name
    $last = $_POST["last"];
    $first = $_POST["first"];
    $sex = $_POST["sex"];
    $dob = $_POST["dob"];
    $dod = $_POST["dod"];
    if ($tablename == "Actor") {
        $query = "INSERT INTO {$tablename} VALUES ({$id}, '{$last}', '{$first}', '{$sex}', '{$dob}', '{$dod}')";
    } else {
        $query = "INSERT INTO {$tablename} VALUES ({$id}, '{$last}', '{$first}', '{$dob}', '{$dod}')"; // No sex
        // TODO: Don't show sex field if user selects director radio button?
    }
    print $query;
    $sanitized_name = mysql_real_escape_string($name, $db_connection);
    $sanitized_query = sprintf($query, $sanitized_name);
    $rs = mysql_query($sanitized_query, $db_connection);

    // TODO: Check the result of $rs
    // Free the result and close the connection to the database
    mysql_free_result($rs);
    mysql_close($db_connection);
    header( "Location: person.php?person_type={$tablename}&id={$id}"); // Redirect to display page


    // TODO: Redirect to person.php with person id as the parameter, and, with actor or director also as a param
}
?>

</body>
</html>