<?php
    require('./libs/FPDF/fpdf.php');

    class PDF extends FPDF {
        function Header() {
            // Se asigna la imagen.
            $this->Image('./public-files/imgs/logo.png', 10, 6, 20);

            // Se hace un salto de linea
            $this->ln(20);
        }

        function Footer() {
            // Indicamos que aparezca debajo
            $this->setY(-15);

            // Indicamos la fuente
            $this->SetFont('Arial', 'I', 10);

            // Se indica el número de página
            $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0,0, 'C');
        }

        // Referencia: https://www.fpdf.org/en/tutorial/tuto5.htm
        function BasicTable($db, $cartId) {

            $this->SetFont('Arial', 'B', 16); // Se indica la fuenta Arial, Negrita y Tamaño 15
            $query = "SELECT a.Nombre, a.Precio, cantidad 
            FROM elementosCarrito ec
            INNER JOIN articulos a ON a.id = articuloId
            WHERE carritoId LIKE '" . $cartId . "'";
        
            $result = $db->query($query);
            $headers = false;
            $totalPrice = 0;

            while($fila = $result->fetch_assoc()) {
                $itemPrice = floatVal($fila['Precio']);
                $quantity = intval($fila['cantidad']);
                $productPrice = $itemPrice * $quantity;
                $totalPrice = $totalPrice + $productPrice;
                if (!$headers) {
                    foreach ($fila as $key => $value) {
                        $w = 100;
                        if ($key != "Nombre") {
                            $w = 60;
                        }
                        $this->Cell($w, 7, $key, 1, 0, 'C');
                    }
                    $this->Cell($w, 7, "Total", 1, 0, 'C');
                    $this->Ln();
                    $headers = true;
                }
                foreach($fila as $key=>$value) {
                    
                    $w = 100;
                    if ($key != "Nombre") {
                        $w = 60;
                    }
                    if ($key == "Precio") {
                        $this->Cell($w, 7, $value . chr(128), 1);
                    } else {
                        $this->Cell($w, 7, $value, 1);
                    }
                }
                $this->Cell($w, 7, $productPrice . chr(128), 1);
                $this->Ln();
            }
            $this->Cell(220, 7, "", 0, 0);
            $this->Cell(60, 7, "Precio final: $totalPrice" . chr(128), 1, 0, 'L');
        }
    }

    session_start();
    if (!isset($_SESSION['user']) || !isset($_GET['carrito'])) {
        header("Location: index.php");
        exit;
    }

    $cartId = $_GET['carrito'];

    $pdf = new PDF('L');
    $pdf->AliasNbPages(); // Se usa para obtener el total de páginas
    $pdf->AddPage(); // Se crea la página
    $pdf->SetFont('Times', '', 13); // Fuente Times, sin ninguna modificación y tamaño 13.

    $db = new mysqli("localhost", "root", "", "proyecto");

    $pdf->SetFont('Arial', 'B', 20); // Se indica la fuenta Arial, Negrita y Tamaño 15
    $pdf->cell(0,10, "Carrito de " . $_SESSION['user'] . " - $cartId", 0, 0, 'C');
    $pdf->ln();
    $pdf->ln();

    $pdf->BasicTable($db, $cartId);
    $pdf->output('I', "Carrito.pdf");
?>