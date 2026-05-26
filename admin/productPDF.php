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

        /**
         * Función que permite crear una tabla básica
         */
        function BasicTable($db) {

            $this->SetFont('Arial', 'B', 16); // Se indica la fuenta Arial, Negrita y Tamaño 15
            // Se hace la consulta con todo lo necesario
            $query = "SELECT a.Nombre, a.Autor, a.Editorial, a.Stock, a.Precio, g.Nombre as Genero
            FROM Articulos a
            INNER JOIN generos g on g.id = a.GeneroID
            where a.deleted = 0";
        
            $result = $db->query($query);
            $headers = false;

            while($fila = $result->fetch_assoc()) {
                if (!$headers) {
                    // Si no se han creado las cabeceras, se crean
                    foreach ($fila as $key => $value) {
                        $w = 80;
                        // Según su cabecera tendrán un ancho u otro.
                        if ($key != "Nombre") {
                            $w = 60;
                        }
                        if ($key == "Genero") {
                            $w = 30;
                        }
                        if ($key == "Stock" || $key == "Precio") {
                            $w = 20;
                        }
                        $this->Cell($w, 7, $key, 1, 0, 'C');
                    }
                    $this->Ln();
                    $headers = true;
                }
                // Se crean los datos de producto
                foreach($fila as $key=>$value) {
                    // Se asigna el ancho correspondiente para cada tipo
                    $w = 80;
                    if ($key != "Nombre") {
                        $w = 60;
                    }
                    if ($key == "Genero") {
                        $w = 30;
                    }
                    if ($key == "Stock"|| $key == "Precio") {
                        $w = 20;
                    }

                    if ($key == "Precio") {
                        // Si se trata del precio, se usa
                        //chr(128) para indicar el simbolo del euro.
                        // Ref: https://stackoverflow.com/questions/46494109/fpdf-euro-symbol-impossible-i-have-tried-all-the-solutions
                        $this->Cell($w, 7, $value . chr(128), 1, 0);
                    } else {
                        // Si no, se muestra normal.
                        $this->Cell($w, 7, $value, 1, 0);
                    }
                }
                $this->Ln();
            }
        }
    }

    // Se inicia la sesión
    session_start();
    // Si no hay usuario se manda al index.
    if (!isset($_SESSION['user'])) {
        header("Location: index.php");
        exit;
    }


    // Se crea el PDF
    $pdf = new PDF();
    $pdf->AliasNbPages(); // Se usa para obtener el total de páginas
    $pdf->AddPage('L'); // Se crea la página en horizontal
    $pdf->SetFont('Times', '', 13); // Fuente Times, sin ninguna modificación y tamaño 13.

    // Se hace la conexión a la BD.
    $db = new mysqli("localhost", "root", "", "proyecto");

    $pdf->SetFont('Arial', 'B', 20); // Se indica la fuenta Arial, Negrita y Tamaño 15
    // Se crea la cabecera
    $pdf->cell(0,10, "Listado de productos no eliminados", 0, 0, 'C');
    $pdf->ln();
    $pdf->ln();

    // Se llama la función para crear la tabla
    $pdf->BasicTable($db);
    // El output a la página actual llamandose listado.
    // Ref: https://www.fpdf.org/en/doc/output.htm
    $pdf->output('I',"listado.pdf");