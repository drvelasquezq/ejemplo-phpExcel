<?php

//ini_set('display_errors', true);
//error_reporting(E_ALL);

require '../../lib/general/ajax.inc';
require '../../lib/PHPExcel-1.8/Classes/PHPExcel.php';

new Ajax($AjaxConnection, $_AJAX);

class Ajax {

	var $conexion;
    var $datos;
    var $codigoCalsificacionHojaHorasExtra;

    public function __construct($conexion, $datos) {
    	$this->conexion = $conexion;
    	$this->datos = $datos;
    	$this->codigoCalsificacionHojaHorasExtra = '99';
    	switch ($this->datos['caso']) {
    		case 'exportarArchivoParaDiligenciar':
    			$this->exportarArchivoParaDiligenciar();
    			break;
    	}
    }

    private function exportarArchivoParaDiligenciar() {

        $consultaMaximaFechaNominaTotalesEmpleados = "
            SELECT MAX(a.fec_finalx) AS fechaMaximaFinal
              FROM " . CONS . ".tab_nomina_empres a
             WHERE a.ind_aproba = '1'
               AND a.ind_anulad = '0';";
        $resultadoConsultaMaximaFechaNominaTotalesEmpleados = new Consulta($consultaMaximaFechaNominaTotalesEmpleados, $this->conexion);
        $fechaMaximaFinal = new DateTime();
        if ($resultadoConsultaMaximaFechaNominaTotalesEmpleados->ret_num_rows() > 0) {
            $fechaMaximaFinal = new DateTime($resultadoConsultaMaximaFechaNominaTotalesEmpleados->ret_arreglo()['fechaMaximaFinal']);
        }

        /*
         * Se añade una determinada cantidad de días para asegurar que calcule el siguiente mes
         */
        $fechaMaximaFinal->add(new DateInterval('P10D'));

        $fechaMaximaFinal->modify('last day of this month');

        $consultaEmpleadosActivos = "
            SELECT a.cod_tercer,
                   CONCAT(b.nom_tercer, ' ', b.ape_terce1, ' ', ape_terce2) as nombreCompletoTercero
              FROM " . CONS . ".tab_tercer_emplea a
        INNER JOIN " . CONS . ".tab_genera_tercer b ON a.cod_tercer = b.cod_tercer
             WHERE b.ind_estado = '1'
               AND (a.fec_retiro IS NULL OR a.fec_retiro = '0000-00-00')
          ORDER BY nombreCompletoTercero ASC;";
        $resultadoConsultaEmpleadosActivos = new Consulta($consultaEmpleadosActivos, $this->conexion);
        $empleadosActivos = $resultadoConsultaEmpleadosActivos->ret_matriz('a');
        $longitudEmpleadosActivos = count($empleadosActivos);

        $rutaArchivo = './CARGUE DE NOVEDADES DE NOMINA.xlsx';
        $inputFileType = PHPExcel_IOFactory::identify($rutaArchivo);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($rutaArchivo);

        $hojasEsperadasArchivoPreConstruido = [
            [
                'nombre' => 'Vacac, Incap y Licen.',
                'filaInicialAListarEmpleados' => 5,
                'columnaNombreEmpleado' => 0,
                'columnaIdentificacionEmpleado' => 1
            ],
            [
                'nombre' => 'Ingresos',
                'filaInicialAListarEmpleados' => 5,
                'columnaNombreEmpleado' => 0,
                'columnaIdentificacionEmpleado' => 1
            ],
            [
                'nombre' => 'Extras y Recargos',
                'filaInicialAListarEmpleados' => 5,
                'columnaNombreEmpleado' => 0,
                'columnaIdentificacionEmpleado' => 1
            ]
        ];
        $longitudHojasEsperadasArchivoPreConstruido = count($hojasEsperadasArchivoPreConstruido);
        
        $worksheetList = $objReader->listWorksheetNames($rutaArchivo);
        $cantidadHojas = count($worksheetList);

        $informacionHojas = [];

        for ($i = 0; $i < $longitudHojasEsperadasArchivoPreConstruido; $i++) { 
            for ($j = 0; $j < $cantidadHojas; $j++) {
                $informacionHojas[$j] = [
                    'nombreHoja' => $worksheetList[$j]
                ];

                if ($hojasEsperadasArchivoPreConstruido[$i]['nombre'] === $informacionHojas[$j]['nombreHoja']) {
                    $objPHPExcel->setActiveSheetIndex($j);

                    $objPHPExcel->getActiveSheet()->setCellValue('D1', PHPExcel_Shared_Date::PHPToExcel($fechaMaximaFinal));

                    $numeroFila = $hojasEsperadasArchivoPreConstruido[$i]['filaInicialAListarEmpleados'];
                    for ($z = 0; $z < $longitudEmpleadosActivos; $z++) {
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($hojasEsperadasArchivoPreConstruido[$i]['columnaNombreEmpleado'],
                            $numeroFila, utf8_encode($empleadosActivos[$z]['nombreCompletoTercero']));
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($hojasEsperadasArchivoPreConstruido[$i]['columnaIdentificacionEmpleado'],
                            $numeroFila, $empleadosActivos[$z]['cod_tercer']);
                        $numeroFila++;
                    }
                }
            }
        }

        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Archivo Para Importar Novedades.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }
}