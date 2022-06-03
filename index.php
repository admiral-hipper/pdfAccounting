<?php
require_once 'vendor/autoload.php';
define('DB_HOST',"localhost");
define('DB_NAME',"Dots");
define('DB_USER','Mikhail');
define('DB_PASSWORD','password');
use Dompdf\Dompdf;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <title>HTML to PDF</title>
</head>

<body>
    <div class="container mt-2">
        <h2 class="page-title m-auto w-100 text-center">Questionnaire</h2>
        <div class="row mt-3">
            <form action="" class="col-12" method="post">
                <div class="row">
                    <input type="hidden" value="1" name="insert">
                    <input class="form-control mt-4" type="text" name="name" id="" placeholder="Name">
                    <input class="form-control mt-4" type="text" name="serial-number" placeholder="Serial number" id="">
                    <input class="form-control mt-4" type="number" name="room" id="" placeholder="Room">
                    <input class="form-control mt-4" type="text" name="owner" id="" placeholder="Owner">
                    <input class="btn btn-info mt-2 col-3" type="submit" value="Sumbit">
                </div>
            </form>
            <form action="" method="POST" class="mt-2 col-2">
                <input type="hidden" value="1" name="create-database">
                <input type="submit" class="btn btn-success" value="Create tables">
            </form>
            <form action="" method="POST" class="mt-2 col-2">
                <input type="hidden" value="1" name="pdf-generate">
                <input type="submit" class="btn btn-warning" value="Generate PDF">
            </form>
        </div>
        <?php
        try {
            $db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';', DB_USER, DB_PASSWORD);
            $rows = $db->query("SELECT * FROM Questionnaire");
            if (isset($_POST['create-database'])) {
                $db->exec("CREATE TABLE IF NOT EXISTS Questionnaire (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(128) NOT NULL,
                serial_number VARCHAR(128) NOT NULL,
                room INT NOT NULL,
                owner VARCHAR(128),
                INDEX(serial_number(30)),
                INDEX(room),
                INDEX(owner(20)))ENGINE=InnoDB;");
            }
            if (isset($_POST['delete'])) {
                $db->exec("DELETE FROM Questionnaire WHERE id=" . $_POST['delete'] . ";");
            }
            if (isset($_POST['name']) && isset($_POST['serial-number']) && isset($_POST['room']) && isset($_POST['owner'])) {
                $sth = $db->prepare("INSERT INTO Questionnaire VALUES(NULL,?,?,?,?);");
                $sth->bindParam(1, $_POST['name'], PDO::PARAM_STR);
                $sth->bindParam(2, $_POST['serial-number'], PDO::PARAM_INT);
                $sth->bindParam(3, $_POST['room'], PDO::PARAM_INT);
                $sth->bindParam(4, $_POST['owner'], PDO::PARAM_STR);
                $result = $sth->execute();
                $isSent = true;
            }
            if (isset($_POST['pdf-generate'])) {
                $html='<!DOCTYPE html>
                <html lang="en">
                
                <head>
                    <meta charset="UTF-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <!-- CSS only -->
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
                    <title>Table</title>
                </head>
                
                <body>
                <table style="width:100%;border:2px solid black" class="table table-striped mt-3">
                <thead>
                    <tr style="border-bottom:2px solid black;">
                        <th class="col-3">Name</th>
                        <th class="col-2">Serial number</th>
                        <th class="col-2">Room</th>
                        <th class="col-3">Owner</th>
                    </tr>
                </thead><tbody>';
                while($data=$rows->fetch(PDO::FETCH_OBJ)){
                    $html.="<tr style='width:100%;border:2px solid red'>
                    <td>{$data->name}</td>
                    <td>{$data->serial_number}</td>
                    <td>{$data->room}</td>
                    <td>{$data->owner}</td>
                    </tr>";
                }
                $html.="</tbody></table></body></html>";
                // instantiate and use the dompdf class
                $dompdf = new Dompdf();
                ob_end_clean();
                $dompdf->loadHtml($html);

                // (Optional) Setup the paper size and orientation
                // Render the HTML as PDF
                $dompdf->render();

                // Output the generated PDF to Browser
                $dompdf->stream();
            }
            
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        ?>
        <div class="row">
            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th class="col-3">Name</th>
                        <th class="col-2">Serial number</th>
                        <th class="col-2">Room</th>
                        <th class="col-3">Owner</th>
                        <th class="col-2">DELETE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $rows->fetch(PDO::FETCH_OBJ)) { ?>
                        <tr>
                            <td><?php echo $row->name; ?></td>
                            <td><?php echo $row->serial_number; ?></td>
                            <td><?php echo $row->room; ?></td>
                            <td><?php echo $row->owner; ?></td>
                            <td>
                                <form action="" method="post"><input type="submit" value="DELETE" class="btn btn-danger"><input type="hidden" value="<?php echo $row->id ?>" name="delete"></form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>