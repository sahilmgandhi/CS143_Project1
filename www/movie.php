<!DOCTYPE html>
<html>
<?php
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

$actorErrorFlag = false;
$actorErrorMsg = "";
$directorErrorFlag = false;
$directorErrorMsg = "";

$id = (int)$_GET["id"];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int)$_POST["id"]; // We sent the id as a POST parameter in this case
    if (!empty($_POST["newActor"])) {
        $role = $_POST["role"];
        if (empty($role)){
            $actorErrorMsg = "Please make sure that the role is not empty";
            $actorErrorFlag = TRUE;
        }
        if (!$actorErrorFlag){
            $first = split(" ", $_POST["name"])[0];
            $last = split(" ", $_POST["name"])[1];
            $query = "INSERT INTO MovieActor SELECT $id, a.id, '$role' FROM Actor AS a WHERE a.first='$first' AND a.last='$last'";
            $newActorRs = mysql_query($query, $db_connection);
            if (mysql_affected_rows() == 0){
                $actorErrorMsg =  "Unable to find this actor. Please check if you typed the name correctly, or go <a href=newperson.php>here</a> to add the Actor.";
                $actorErrorFlag = TRUE;
            }    
        }
    } else if (!empty($_POST["newDirector"])) {
        $first = split(" ", $_POST["name"])[0];
        $last = split(" ", $_POST["name"])[1];
        $query = "INSERT INTO MovieDirector SELECT $id, d.id FROM Director AS d WHERE d.first='$first' AND d.last='$last'";
        $newDirectorRs = mysql_query($query, $db_connection);
        if (mysql_affected_rows() == 0){
            $directorErrorMsg = "Unable to find this Director. Please check if you typed the name correctly, or go <a href=newperson.php>here</a> to add the Actor.";
            $directorErrorFlag = TRUE;
        }
    } else if (!empty($_POST["newReview"])) {
        $name = $_POST["name"];
        if (empty($name)){
            $name = "Anonymous";
        }
        $rating = (int)$_POST["rating"];
        $comment = $_POST["comment"];
        $query = "INSERT INTO Review SELECT '$name', NOW(), $id, $rating, '$comment'";
        $reviewRs = mysql_query($query, $db_connection);
    }
}


$moviequery = "SELECT * FROM Movie WHERE id={$id}";
$rs = mysql_query($moviequery, $db_connection);
if (mysql_num_rows($rs) == 0) {
    header("Location: notfound.php"); // Redirect to display page
    return;
}
$row = mysql_fetch_row($rs);
$title = $row[1];
$year = $row[2];
$rating = $row[3];
$company = $row[4];
mysql_free_result($rs);

$actorquery = "SELECT * FROM MovieActor AS ma, Actor AS a WHERE ma.mid={$id} AND a.id = ma.aid";
$actorrs = mysql_query($actorquery, $db_connection);

$directorquery = "SELECT * FROM MovieDirector AS md, Director AS d WHERE md.mid={$id} AND d.id=md.did";
$directorrs = mysql_query($directorquery, $db_connection);

$genrequery = "SELECT * FROM MovieGenre WHERE mid={$id}";
$genrers = mysql_query($genrequery, $db_connection);

$reviewsquery = "SELECT * FROM Review WHERE mid={$id}";
$reviewsrs = mysql_query($reviewsquery, $db_connection);

$reviewsaveragequery = "SELECT AVG(rating) FROM Review WHERE mid={$id}";
$reviewavgrs = mysql_query($reviewsaveragequery, $db_connection);
$reviewavgrow = mysql_fetch_row($reviewavgrs);
$reviewavg = $reviewavgrow[0];
if (is_null($reviewavg)) {
    $reviewstring = "<i>Not enough reviews yet for an average score</i>";
} else {
    $reviewstring = "Users gave this movie an average score of {$reviewavg}/5";
}

$salesQuery = "SELECT * FROM Sales WHERE mid={$id}";
$sales = mysql_query($salesQuery, $db_connection);
$thisSale = FALSE;
if ($sales != FALSE)
    $thisSale = mysql_fetch_row($sales);

$ratingQuery = "SELECT * FROM MovieRating WHERE mid={$id}";
$ratings = mysql_query($ratingQuery, $db_connection);
$thisRating = FALSE;
if ($ratings != FALSE)
    $thisRating = mysql_fetch_row($ratings);

// Free the result and close the connection to the database
mysql_close($db_connection);
?>
<title> <?php echo $title ?> - LMDb </title>
<body style="background-color:#CEDBED;">
<form method="GET" action="search.php">
    <input type="text" id="search" name="search" placeholder="Search" size="100" value="<?php echo $_GET["search"] ?>"/>
    <input type="submit" id="submit" name="submit" value="Search">
</form>
<h1> <?php echo "$title ($year)" ?> </h1> <br>
<?php echo $reviewstring ?> <br>

<?php
echo "Genres: ";
$genreHtml = "";
while ($row = mysql_fetch_row($genrers)) {
    $genreHtml .= "<b>{$row[1]}  </b>";
}
if (empty($genreHtml)) {
    $genreHtml .= "<i>None</i>";
}
echo $genreHtml;
?>
<br>
<?php
if (!empty($rating)) {
    echo "Rated $rating <br>";
}
//// Producer
if (!empty($company)) {
    echo "Produced by: $company <br>";
}

////////// Director
echo "Directed by: ";
$directorsHtml = "";
while ($row = mysql_fetch_row($directorrs)) {
    $did = $row[1];
    $name = $row[4] . ' ' . $row[3];
    $directorsHtml .= "<a href=person.php?person_type=Director&id={$did}>{$name}</a> ";
}
if (empty($directorsHtml)) {
    $directorsHtml = "<i>Unknown</i> "; // Keep this space at the end to be trimmed off
}
echo substr($directorsHtml, 0, -1);
echo "<br>";

////// Sales Information
$ticketsSold = 0;
$totalIncome = 0;
if ($thisSale != FALSE) {
    $ticketsSold = $thisSale[1];
    $totalIncome = $thisSale[2];
}
echo "Total tickets sold: $ticketsSold <br>";
echo "Total income: $totalIncome <br>";

////// Rotten/IMDB Information

$imdbRating = NULL;
$rotRating = NULL;
if ($thisRating != FALSE) {
    $imdbRating = $thisRating[1];
    $rotRating = $thisRating[2];
}

if (is_null($imdbRating))
    echo "The IMDB Rating currently does not exist <br>";
else
    echo "The IMDB Rating is: $imdbRating <br>";
if (is_null($rotRating))
    echo "The Rotten Tomatoes Rating currently does not exist <br>";
else
    echo "The Rotten Tomatoes Rating is: $rotRating <br>";

?>

<h4>Add New Director</h4>
<form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <input type="text" id="name" name="name" placeholder="Director Full Name" size="20 value="<?php echo isset($_POST['name']) ? $_POST['name'] : '' ?>"">
    <input type="hidden" name="id" value="<?php echo $id; ?>"/>
    <input type="submit" id="newDirector" name="newDirector"> <br> <br>
</form>

<?php
if ($directorErrorFlag){
    echo $directorErrorMsg;
}
?>

<?php

echo "<br><br>";
////////// Actors
$actorsHtml = "";
echo "<table border=1 cellspacing=1 cellpadding=2>\n";
echo "<tr align=center>";
while ($row = mysql_fetch_row($actorrs)) {
    $aid = $row[1];
    $aname = $row[5] . ' ' . $row[4];
    $role = $row[2];
    $actorsHtml .= "<tr><td><a href=person.php?person_type=Actor&id={$aid}>{$aname}</a></td>\t";
    $actorsHtml .= "<td>{$role}</td></tr>";

}
if (empty($actorsHtml)) {
    $actorsHtml = "<i>No known actors for this movie</i>";
} else {
    $actorsHtml = "<th>Actor</th><th>Role</th>" . $actorsHtml;
}
echo $actorsHtml;
echo "</table><br>";
?>
<h4>Add New Actor</h4>
<form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <input type="text" id="name" name="name" placeholder="Actor Full Name" size="20" value="<?php echo isset($_POST['name']) ? $_POST['name'] : '' ?>">
    <input type="text" id="role" name="role" placeholder="Actor Role" size="50" maxlength="50" value="<?php echo isset($_POST['role']) ? $_POST['role'] : '' ?>">
    <input type="hidden" name="id" value="<?php echo $id; ?>"/>
    <input type="submit" id="newActor" name="newActor"> <br> <br>
</form>

<?php
if ($actorErrorFlag){
    echo $actorErrorMsg;
}
?>

<?php
echo "<h2>Reviews</h2>";
$reviewsHtml = "";
while ($row = mysql_fetch_row($reviewsrs)) {
    $name = $row[0];
    $time = $row[1];
    $rating = $row[3];
    $comment = $row[4];

    $reviewsHtml .= "<b>{$name}</b> - <i>{$rating}/5 Stars<br>Posted at {$time}</i><br>{$comment}<br><br>";
}

if (empty($reviewsHtml)) {
    $reviewsHtml = "<i>No reviews yet!</i>";
}

echo $reviewsHtml;

?>

<br> <br>
<form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <input type="text" id="name" name="name" placeholder="Name" size="20" maxlength="20" value="<?php echo isset($_POST['name']) ? $_POST['name'] : '' ?>"> <br> <br>
    <p>Your rating:</p>
    <select name="rating">
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
        <option value="5">5</option>
    </select> <br>
    <input type="text" id="comment" name="comment" placeholder="Review/Comment" size="100" maxlength="500" value="<?php echo isset($_POST['comment']) ? $_POST['comment'] : '' ?>"> <br> <br>
    <input type="hidden" name="id" value="<?php echo $id; ?>"/>
    <input type="submit" id="newReview" name="newReview"> <br> <br>
</form>

</body>
</html>