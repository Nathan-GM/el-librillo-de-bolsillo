<?php
    require('../libs/FPDF/fpdf.php');

    class PDF extends FPDF {
        function Header() {
            // Se asigna la imagen.
            $this->Image('../public-files/imgs/logo.png', 10, 6, 20);

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

        function BasicTable($db) {

            $this->SetFont('Arial', 'B', 16); // Se indica la fuenta Arial, Negrita y Tamaño 15
            $query = "SELECT a.Nombre, a.Autor, a.Editorial, a.Stock, a.Precio, g.Nombre as Genero
            FROM Articulos a
            INNER JOIN generos g on g.id = a.GeneroID";
        
            $result = $db->query($query);
            $headers = false;

            while($fila = $result->fetch_assoc()) {
                if (!$headers) {
                    foreach ($fila as $key => $value) {
                        $w = 70;
                        if ($key != "Nombre") {
                            $w = 60;
                        }
                        if ($key == "Genero") {
                            $w = 50;
                        }
                        if ($key == "Stock" || $key == "Precio") {
                            $w = 20;
                        }
                        $this->Cell($w, 7, $key, 1, 0, 'C');
                    }
                    $this->Ln();
                    $headers = true;
                }
                foreach($fila as $key=>$value) {
                    
                    $w = 70;
                    if ($key != "Nombre") {
                        $w = 60;
                    }
                    if ($key == "Genero") {
                        $w = 50;
                    }
                    if ($key == "Stock"|| $key == "Precio") {
                        $w = 20;
                    }

                    if ($key == "Precio") {
                        $this->Cell($w, 7, $value . chr(128), 1, 0);
                    } else {
                        $this->Cell($w, 7, $value, 1, 0);
                    }
                }
                $this->Ln();
            }
        }
    }

    session_start();
    if (!isset($_SESSION['user'])) {
        header("Location: index.php");
        exit;
    }

    $pdf = new PDF();
    $pdf->AliasNbPages(); // Se usa para obtener el total de páginas
    $pdf->AddPage('L'); // Se crea la página
    $pdf->SetFont('Times', '', 13); // Fuente Times, sin ninguna modificación y tamaño 13.

    $db = new mysqli("localhost", "root", "", "proyecto");

    $pdf->SetFont('Arial', 'B', 20); // Se indica la fuenta Arial, Negrita y Tamaño 15
    $pdf->cell(0,10, "Listado de productos", 0, 0, 'C');
    $pdf->ln();
    $pdf->ln();

    $pdf->BasicTable($db);
    $pdf->output('I',"listado.pdf");