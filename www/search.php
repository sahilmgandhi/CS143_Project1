<!DOCTYPE html>
<html>
<title> LMDB </title>

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Bootstrap Core CSS -->
    <link href="jsAndCss/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMejsAndCss CSS -->
    <link href="jsAndCss/vendor/metisMenu/metisMenu.min.css" rel="stylesheet">

    <!-- Social jsAndCssttons CSS -->
    <link href="jsAndCss/vendor/bootstrap-social/bootstrap-social.css" rel="stylesheet">

    <!-- Custom jsAndCssS -->
    <link href="jsAndCss/dist/css/sb-admin-2.css" rel="stylesheet">

    <!-- Custom jsAndCssnts -->
    <link href="jsAndCss/vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

</head>

<body>
<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
    <div class="navbar-header">
        <a class="navbar-brand" href="index.php">LMDb - Localhost Movie Database</a>
    </div>
    <div class="navbar-default sidebar" role="navigation">
        <div class="sidebar-nav navbar-collapse">
            <ul class="nav" id="side-menu">
                <li class="sidebar-search">
                    <div class="input-group custom-search-form">
                        <form method="GET" action="search.php">
                            <input type="text" class="form-control" id="search" name="search"
                                   placeholder="Search Movies/Actors" size="100" value="<?php echo $_GET["search"] ?>"/>
                        </form>
                    </div>
                </li>
                <li>
                    <a href="index.php"><i class="fa fa-dashboard fa-fw"></i> Main Menu</a>
                </li>
                <li>
                    <a href="newperson.php"><i class="fa fa-user fa-fw"></i> Add new Actor/Director </a>
                </li>
                <li>
                    <a href="newmovie.php"><i class="fa fa-film fa-fw"></i> Add new Movie</a>
                </li>
            </ul>
        </div>
        <!-- /.sidebar-collapse -->
    </div>
</nav>

<div id="page-wrapper">


    <div class="row">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h1> Localhost Movie DataBase </h1>
            </div>
            <div class="panel-body">

                <?php

                if ($_SERVER["REQUEST_METHOD"] == "GET" && !empty($_GET["search"])) {

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

                    $search = trim($_GET["search"]);
                    $searchsplit = split(' ', $search);


                    $actorquery = "SELECT * FROM Actor WHERE ";
                    foreach ($searchsplit as $word) {
                        $actorquery .= "CONCAT(first, ' ', last) LIKE '%$word%' AND ";
                    }
                    $actorquery = substr($actorquery, 0, -5);
                    $actorrs = mysql_query($actorquery, $db_connection);

                    $moviequery = "SELECT * FROM Movie WHERE ";
                    foreach ($searchsplit as $word) {
                        $moviequery .= "title LIKE '%$word%' AND ";
                    }
                    $moviequery = substr($moviequery, 0, -5);
                    $moviers = mysql_query($moviequery, $db_connection);

                    $actorsHtml = "";
                    while ($row = mysql_fetch_row($actorrs)) {
                        $aid = $row[0];
                        $aname = $row[2] . ' ' . $row[1];
                        $actorsHtml .= "<tr><td><a href=person.php?person_type=Actor&id={$aid}>{$aname}</a></td>\t";

                    }
                    echo "<div class=\"row\">";
                    if (empty($actorsHtml)) {
                        echo "<i>No actors found</i>";
                    } else {
                        echo "Actors found:";
                        echo "<div class=\"panel-body\">";
                        echo "<table width=\"100%\" class=\"tabel table-striped table-bordered table-hover\" id=\"ActorTable\">\n";
                        echo "<thead> <tr align=center>";
                        $actorsHtml = "<th>Actor</th> </thead><tbody>" . $actorsHtml;
                        echo $actorsHtml;
                        echo "</tbody>";
                        echo "</table><br></div>";
                    }
                    echo "</div>";

                    $movieHtml = "";
                    while ($row = mysql_fetch_row($moviers)) {
                        $mid = $row[0];
                        $title = $row[1];
                        $movieHtml .= "<tr><td><a href=movie.php?id={$mid}>{$title}</a></td>\t";

                    }
                    echo "<div class=\"row\">";
                    if (empty($movieHtml)) {
                        echo "<i> No movies found</i>";
                    } else {
                        echo "<div class=\"panel-body\">";
                        echo "<table width=\"100%\" class=\"tabel table-striped table-bordered table-hover\" id=\"MovieTable\">\n";
                        echo "<thead> <tr align=center>";
                        $movieHtml = "<th>Movie</th> </thead><tbody>" . $movieHtml;
                        echo $movieHtml;
                        echo "</tbody></table><br></div>";
                    }
                    echo "</div>";
                    // Free the result and close the connection to the database
                    mysql_free_result($rs);
                    mysql_close($db_connection);
                }
                ?>
            </div>
        </div>
    </div>

    <script src="jsAndCss/vendor/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="jsAndCss/vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="jsAndCss/vendor/metisMenu/metisMenu.min.js"></script>

    <!-- DataTables JavaScript -->
    <script src="jsAndCss/vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="jsAndCss/vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
    <script src="jsAndCss/vendor/datatables-responsive/dataTables.responsive.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="jsAndCss/dist/js/sb-admin-2.js"></script>

    <!-- Page-Level Demo Scripts - Tables - Use for reference -->
    <script>
        $(document).ready(function () {
            $('#ActorTable').DataTable({
                responsive: true
            });
            $('#MovieTable').DataTable({
                responsive: true
            });
        });
    </script>
</body>
</html>