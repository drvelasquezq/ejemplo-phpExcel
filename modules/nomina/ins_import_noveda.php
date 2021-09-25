<?php

//ini_set('display_errors', true);
//error_reporting(E_ALL);

include '../' . DIR_APLICA_CENTRAL . '/lib/general/functions.inc';
include '../' . DIR_APLICA_CENTRAL . '/lib/general/dinamic_list.inc';
require_once '../' . DIR_APLICA_CENTRAL . '/lib/PHPExcel-1.8/Classes/PHPExcel.php';

new ins_import_noveda($this->conexion, $_REQUEST);

class ins_import_noveda {

    var $conexion;
    var $datos;
    var $codigoClasificacionHojaHorasExtra;
    var $codigoClasificacionVacacIncapLicen;
    var $codigoClasificacionIngresos;

    function __construct($conexion, $datos) {
        $this->conexion = $conexion;
        $this->datos = $datos;
        $this->codigoClasificacionHojaHorasExtra = '99';
        $this->codigoClasificacionVacacIncapLicen = '0';
        $this->codigoClasificacionIngresos = '1';
        if (!isset($this->datos['peticion'])) {
            $this->formularioInicial();
        }
    }

    private function formularioInicial() {
        $mHtml = new DinamicHtml();
        $mHtml->SetCss("../" . DIR_APLICA_CENTRAL . "/css/styles.css");
        $mHtml->SetCss("../" . DIR_APLICA_CENTRAL . "/css/dinamic_list.css");
        $mHtml->SetCss("../" . DIR_APLICA_CENTRAL . "/css/ryu_calendar.css");
        $mHtml->SetJs("../" . DIR_APLICA_CENTRAL . "/js/functions.js");
        $mHtml->SetJs("../" . DIR_APLICA_CENTRAL . "/js/general.js");
        $mHtml->SetJs("../" . DIR_APLICA_CENTRAL . "/js/ajax.js");
        $mHtml->SetJs("../" . DIR_APLICA_CENTRAL . "/js/new_ajax.js");
        $mHtml->SetJs("../" . DIR_APLICA_CENTRAL . "/js/servic.js");
        $mHtml->SetJs("../" . DIR_APLICA_CENTRAL . "/js/ryu_calendar.js");
        $mHtml->SetJs("../" . DIR_APLICA_CENTRAL . "/js/dinamic_list.js");
        $mHtml->SetJs("../" . DIR_APLICA_CENTRAL . "/js/ins_import_noveda.js");
        $mHtml->Body(array('style' => 'background:#eeeeee;'));
        $mHtml->Form(array("action" => "index.php?", "method" => "POST", "name" => "formularioImportarNovedad", "enctype" => "multipart/form-data"));
        $mHtml->Table("class:StyleContainer; align:center; cellpadding:0; cellspacing:0; width:100%");
        $mHtml->Row();
        $mHtml->Cell("align:center; width:100%");
        $mHtml->Title("IMPORTAR", array('width' => '100%', 'style' => 'text-align:left; background:#000000;'));
        $mHtml->CloseCell();
        $mHtml->CloseRow();
        $mHtml->Row();
        $mHtml->Cell("align:center");
        $mHtml->SubTitle("- Encabezado", "name:SectionLink1; width:100%; onclick:ShowSection('1')");
        $mHtml->CloseCell();
        $mHtml->CloseRow();
        $mHtml->Row();
        $mHtml->Cell("name:section1; align:center; width:100%");
        $mHtml->Table("align:center; width:100%");
        $mHtml->Row();
        $mHtml->Label("* Archivo:", NULL, "align:right");
        $mHtml->File("name:archivoDiligenciado", NULL);
        $mHtml->Label("Exportar archivo:", NULL, "align:right");
        $mHtml->Link('Clic aquí para exportar archivo para diligenciar', [
            'href' => '../' . DIR_APLICA_CENTRAL . '/modules/nomina/exp_archiv_noveda.php?Ajax=on&caso=exportarArchivoParaDiligenciar',
            'target' => '_blank'
        ]);
        $mHtml->CloseRow();
        $mHtml->Row();
        $mHtml->Button("name:btn_import; align:center; width:100%; value:Importar; onclick:EnviarFormulario()", array('style' => 'text-align:center', 'colspan' => 4));
        $mHtml->CloseRow();
        $mHtml->CloseTable();
        $mHtml->CloseCell();
        $mHtml->CloseRow();

        if (isset($_FILES['archivoDiligenciado'])) {
            $mHtml->Row();
            $mHtml->Cell("align:center");
            $mHtml->SubTitle("- Reporte", "name:SectionLink2; width:100%; onclick:ShowSection('2')");
            $mHtml->CloseCell();
            $mHtml->CloseRow();
            $mHtml->Row();
            $mHtml->Cell("name:section2; align:center; width:100%");
            $mHtml->Div("name:ListContainer; align:center");

            $erroresGenerales = [];
            $informacionGeneral = [];

            $rutaArchivoDiligenciadoTemporal = $_FILES['archivoDiligenciado']['tmp_name'];
            $rutaArchivoDiligenciadoMover = 'compro/' . $_FILES['archivoDiligenciado']['name'];
            $sePermiteMoverArchivo = move_uploaded_file($rutaArchivoDiligenciadoTemporal, $rutaArchivoDiligenciadoMover);

            if ($sePermiteMoverArchivo) {
                $hojasEsperadasArchivoPreConstruido = [
                    [
                        'codigo' => $this->codigoClasificacionVacacIncapLicen,
                        'nombre' => 'Vacac, Incap y Licen.',
                        'filaInicialAListarEmpleados' => 5,
                        'columnaNombreEmpleado' => 0,
                        'columnaIdentificacionEmpleado' => 1,
                        'erroresCargaArchivo' => [],
                        'informacionCargaArchivo' => [],
                        'distribucion' => [
                            [
                                'tipo' => 'fecha',
                                'posicionCeldaTituloConcepto' => 'D3',
                                'posicionCeldaFechaInicial' => ['columna' => 'D', 'fila' => '4'],
                                'posicionCeldaFechaFinal' => ['columna' => 'E', 'fila' => '4']
                            ],
                            [
                                'tipo' => 'valor',
                                'posicionCeldaTituloConcepto' => 'G3',
                                'posicionCeldaValor' => ['columna' => 'G', 'fila' => '3']
                            ],
                            [
                                'tipo' => 'fecha',
                                'posicionCeldaTituloConcepto' => 'I3',
                                'posicionCeldaFechaInicial' => ['columna' => 'I', 'fila' => '4'],
                                'posicionCeldaFechaFinal' => ['columna' => 'J', 'fila' => '4']
                            ],
                            [
                                'tipo' => 'fecha',
                                'posicionCeldaTituloConcepto' => 'L3',
                                'posicionCeldaFechaInicial' => ['columna' => 'L', 'fila' => '4'],
                                'posicionCeldaFechaFinal' => ['columna' => 'M', 'fila' => '4']
                            ],
                            [
                                'tipo' => 'fecha',
                                'posicionCeldaTituloConcepto' => 'O3',
                                'posicionCeldaFechaInicial' => ['columna' => 'O', 'fila' => '4'],
                                'posicionCeldaFechaFinal' => ['columna' => 'P', 'fila' => '4']
                            ],
                            [
                                'tipo' => 'fecha',
                                'posicionCeldaTituloConcepto' => 'R3',
                                'posicionCeldaFechaInicial' => ['columna' => 'R', 'fila' => '4'],
                                'posicionCeldaFechaFinal' => ['columna' => 'S', 'fila' => '4']
                            ],
                            [
                                'tipo' => 'fecha',
                                'posicionCeldaTituloConcepto' => 'U3',
                                'posicionCeldaFechaInicial' => ['columna' => 'U', 'fila' => '4'],
                                'posicionCeldaFechaFinal' => ['columna' => 'V', 'fila' => '4']
                            ],
                            [
                                'tipo' => 'fecha',
                                'posicionCeldaTituloConcepto' => 'X3',
                                'posicionCeldaFechaInicial' => ['columna' => 'X', 'fila' => '4'],
                                'posicionCeldaFechaFinal' => ['columna' => 'Y', 'fila' => '4']
                            ]
                        ]
                    ],
                    [
                        'codigo' => $this->codigoClasificacionIngresos,
                        'nombre' => 'Ingresos',
                        'filaInicialAListarEmpleados' => 5,
                        'columnaNombreEmpleado' => 0,
                        'columnaIdentificacionEmpleado' => 1,
                        'erroresCargaArchivo' => [],
                        'informacionCargaArchivo' => [],
                        'distribucion' => [
                            [
                                'tipo' => 'valor',
                                'posicionCeldaTituloConcepto' => 'D4',
                                'posicionCeldaValor' => ['columna' => 'D', 'fila' => '4']
                            ],
                            [
                                'tipo' => 'valor',
                                'posicionCeldaTituloConcepto' => 'F4',
                                'posicionCeldaValor' => ['columna' => 'F', 'fila' => '4']
                            ],
                            [
                                'tipo' => 'valor',
                                'posicionCeldaTituloConcepto' => 'G4',
                                'posicionCeldaValor' => ['columna' => 'G', 'fila' => '4']
                            ],
                            [
                                'tipo' => 'valor',
                                'posicionCeldaTituloConcepto' => 'H4',
                                'posicionCeldaValor' => ['columna' => 'H', 'fila' => '4']
                            ],
                            [
                                'tipo' => 'valor',
                                'posicionCeldaTituloConcepto' => 'I4',
                                'posicionCeldaValor' => ['columna' => 'I', 'fila' => '4']
                            ],
                            [
                                'tipo' => 'valor',
                                'posicionCeldaTituloConcepto' => 'J4',
                                'posicionCeldaValor' => ['columna' => 'J', 'fila' => '4']
                            ]
                        ]
                    ],
                    [
                        'codigo' => $this->codigoClasificacionHojaHorasExtra,
                        'nombre' => 'Extras y Recargos',
                        'filaInicialAListarEmpleados' => 5,
                        'columnaNombreEmpleado' => 0,
                        'columnaIdentificacionEmpleado' => 1,
                        'erroresCargaArchivo' => [],
                        'informacionCargaArchivo' => [],
                        'distribucion' => [
                            [
                                'tipo' => 'hora',
                                'posicionCeldaTituloConcepto' => 'D4',
                                'posicionCeldaHora' => ['columna' => 'D', 'fila' => '4']
                            ],
                            [
                                'tipo' => 'hora',
                                'posicionCeldaTituloConcepto' => 'E4',
                                'posicionCeldaHora' => ['columna' => 'E', 'fila' => '4']
                            ],
                            [
                                'tipo' => 'hora',
                                'posicionCeldaTituloConcepto' => 'F4',
                                'posicionCeldaHora' => ['columna' => 'F', 'fila' => '4']
                            ],
                            [
                                'tipo' => 'hora',
                                'posicionCeldaTituloConcepto' => 'G4',
                                'posicionCeldaHora' => ['columna' => 'G', 'fila' => '4']
                            ],
                            [
                                'tipo' => 'hora',
                                'posicionCeldaTituloConcepto' => 'I4',
                                'posicionCeldaHora' => ['columna' => 'I', 'fila' => '4']
                            ],
                            [
                                'tipo' => 'hora',
                                'posicionCeldaTituloConcepto' => 'J4',
                                'posicionCeldaHora' => ['columna' => 'J', 'fila' => '4']
                            ],
                            [
                                'tipo' => 'hora',
                                'posicionCeldaTituloConcepto' => 'K4',
                                'posicionCeldaHora' => ['columna' => 'K', 'fila' => '4']
                            ],
                            [
                                'tipo' => 'hora',
                                'posicionCeldaTituloConcepto' => 'M4',
                                'posicionCeldaHora' => ['columna' => 'M', 'fila' => '4']
                            ],
                            [
                                'tipo' => 'hora',
                                'posicionCeldaTituloConcepto' => 'N4',
                                'posicionCeldaHora' => ['columna' => 'N', 'fila' => '4']
                            ]
                        ]
                    ]
                ];
                $longitudHojasEsperadasArchivoPreConstruido = count($hojasEsperadasArchivoPreConstruido);

                $consultaConceptosDevengadosQueAplicanNovedad = "
                    SELECT a.cod_concep,
                           a.nom_concep
                      FROM " . CONS . ".tab_nomina_concep a
                     WHERE a.ind_deveng = '1'
                       AND a.ind_aplnov = '1'
                       AND a.ind_estado = '1';";
                $resultadoConsultaConceptosDevengadosQueAplicanNovedad = new Consulta($consultaConceptosDevengadosQueAplicanNovedad, $this->conexion);
                $conceptosDevengadosQueAplicanNovedad = $resultadoConsultaConceptosDevengadosQueAplicanNovedad->ret_matriz('a');
                $longitudConceptosDevengadosQueAplicanNovedad = count($conceptosDevengadosQueAplicanNovedad);

                $consultaConceptosHorasExtra = "
                    SELECT a.cod_horext AS cod_concep,
                           CONCAT(a.nom_horext, ' - ', a.por_horext) AS nom_concep,
                           por_horext
                      FROM " . CONS . ".tab_genera_horext a
                     WHERE a.ind_estado = '1';";
                $resultadoConsultaConceptosHorasExtra = new Consulta($consultaConceptosHorasExtra, $this->conexion);
                $conceptosHorasExtra = $resultadoConsultaConceptosHorasExtra->ret_matriz('a');
                $longitudConceptosHorasExtra = count($conceptosHorasExtra);

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

                $rutaArchivo = $rutaArchivoDiligenciadoMover;

                $inputFileType = PHPExcel_IOFactory::identify($rutaArchivo);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($rutaArchivo);

                $worksheetList = $objReader->listWorksheetNames($rutaArchivo);

                $cantidadHojas = count($worksheetList);

                $informacionHojas = [];

                $contadorHojas = 0;
                for ($i = 0; $i < $longitudHojasEsperadasArchivoPreConstruido; $i++) {
                    for ($j = 0; $j < $cantidadHojas; $j++) {

                        $hoja = $objPHPExcel->getSheet($j);
                        $cantidadFilas = $hoja->getHighestRow();

                        $celdaPeriodoHoja = $hoja->getCell('D1');
                        $periodoHoja = $celdaPeriodoHoja->getValue();
                        if (PHPExcel_Shared_Date::isDateTime($celdaPeriodoHoja)) {
                            $periodoHoja = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($periodoHoja));
                        }

                        $fechaPeriodoHoja = new DateTime($periodoHoja);
                        $fechaPeriodoHoja->add(new DateInterval('P1D'));

                        $informacionHojas[$contadorHojas] = [
                            'nombreHoja' => $worksheetList[$j],
                            'cantidadFilas' => (int) $cantidadFilas,
                            'periodoHojaInicial' => $fechaPeriodoHoja->format('Y-m-') . '01',
                            'periodoHojaFinal' => $fechaPeriodoHoja->format('Y-m-d'),
                            'contenidoHoja' => []
                        ];

                        if ($hojasEsperadasArchivoPreConstruido[$i]['nombre'] === $informacionHojas[$contadorHojas]['nombreHoja']) {
                            $longitudDistribucionHoja = count($hojasEsperadasArchivoPreConstruido[$i]['distribucion']);

                            if ($fechaPeriodoHoja->format('Y-m-d') !== $fechaMaximaFinal->format('Y-m-d')) {
                                $hojasEsperadasArchivoPreConstruido[$i]['erroresCargaArchivo'][] = 'La fecha que se trata de cargar se restringe ' . 
                                    'debido a que se esperaba por el sistema la fecha ' . $fechaMaximaFinal->format('Y-m-d') . 
                                    ', que correspondería al siguiente mes del que se encuentra la última prenomina aprobada y no anulada.';
                                $contadorHojas++;
                                continue;
                            }

                            $contadorEmpleados = 0;
                            for ($fila = $hojasEsperadasArchivoPreConstruido[$i]['filaInicialAListarEmpleados']; $fila < $informacionHojas[$contadorHojas]['cantidadFilas']; $fila++) {

                                $celdaNombreEmpleado = $hoja->getCellByColumnAndRow($hojasEsperadasArchivoPreConstruido[$i]['columnaNombreEmpleado'],
                                        $fila);

                                if (is_null($celdaNombreEmpleado->getValue())) {
                                    break;
                                }

                                $celdaIdentificacionEmpleado = $hoja->getCellByColumnAndRow($hojasEsperadasArchivoPreConstruido[$i]['columnaIdentificacionEmpleado'],
                                        $fila);

                                /*
                                 * se valida que el empleado que se ingresa en la hoja exista y cumpla las condiciones
                                 */
                                $consultaExistenciaYCumplimientoCondicionesEmpleado = "
                                    SELECT 1
                                      FROM " . CONS . ".tab_tercer_emplea a
                                INNER JOIN " . CONS . ".tab_genera_tercer b ON a.cod_tercer = b.cod_tercer
                                     WHERE b.ind_estado = '1'
                                       AND a.cod_tercer = '" . $celdaIdentificacionEmpleado->getValue() . "'
                                       AND (a.fec_retiro IS NULL OR a.fec_retiro = '0000-00-00');";
                                $resultadoConsultaExistenciaYCumplimientoCondicionesEmpleado = new Consulta($consultaExistenciaYCumplimientoCondicionesEmpleado, $this->conexion);
                                if ($resultadoConsultaExistenciaYCumplimientoCondicionesEmpleado->ret_num_rows() === 0) {
                                    $hojasEsperadasArchivoPreConstruido[$i]['informacionCargaArchivo'][] = 'No se encontró un empleado con el número de identificación ' .
                                        $celdaIdentificacionEmpleado->getValue() . ', puede que no exista, que esté en estado inactivo o que tenga una fecha de retiro. ' .
                                        'Celda: ' . $celdaIdentificacionEmpleado->getColumn() . $fila . ', Hoja: ' . $hojasEsperadasArchivoPreConstruido[$i]['nombre'];
                                    continue;
                                }

                                $informacionHojas[$contadorHojas]['contenidoHoja'][$contadorEmpleados] = [
                                    'empleado' => [
                                        'nombre' => $celdaNombreEmpleado->getValue(),
                                        'identificacion' => $celdaIdentificacionEmpleado->getValue(),
                                    ],
                                    'conceptos' => []
                                ];

                                for ($columna = 0; $columna < $longitudDistribucionHoja; $columna++) {

                                    if ($columna === 0) {
                                        if ($hojasEsperadasArchivoPreConstruido[$i]['codigo'] === $this->codigoClasificacionVacacIncapLicen) {
                                            $informacionHojas[$contadorHojas]['contenidoHoja'][$contadorEmpleados]['conceptos'][] = [
                                                'codigo' => '1',
                                                'nombre' => 'salarios',
                                                'fechaInicial' => $informacionHojas[$contadorHojas]['periodoHojaInicial'],
                                                'fechaFinal' => $informacionHojas[$contadorHojas]['periodoHojaFinal'],
                                                'numeroDias' => 30,
                                                'tipo' => 'fecha'
                                            ];
                                        }
                                    }

                                    $concepto = [];

                                    $celdaNombreConcepto = $hoja->getCell($hojasEsperadasArchivoPreConstruido[$i]['distribucion'][$columna]['posicionCeldaTituloConcepto']);

                                    $concepto['nombre'] = $celdaNombreConcepto->getValue();

                                    $coincideConcepto = false;

                                    $longitudQueSeCompara = $longitudConceptosDevengadosQueAplicanNovedad;
                                    $conceptosQueSeComparan = $conceptosDevengadosQueAplicanNovedad;
                                    if ($hojasEsperadasArchivoPreConstruido[$i]['codigo'] === $this->codigoClasificacionHojaHorasExtra) {
                                        $longitudQueSeCompara = $longitudConceptosHorasExtra;
                                        $conceptosQueSeComparan = $conceptosHorasExtra;
                                    }

                                    for ($x = 0; $x < $longitudQueSeCompara; $x++) {
                                        if (strtolower($concepto['nombre']) === strtolower($conceptosQueSeComparan[$x]['nom_concep'])) {
                                            $coincideConcepto = true;
                                            break;
                                        }
                                    }
                                    if ($coincideConcepto) {
                                        $concepto['codigo'] = $conceptosQueSeComparan[$x]['cod_concep'];
                                        if ($hojasEsperadasArchivoPreConstruido[$i]['codigo'] === $this->codigoClasificacionHojaHorasExtra) {
                                            $concepto['porcentaje'] = (float) $conceptosQueSeComparan[$x]['por_horext'];
                                        }
                                    } else {
                                        continue;
                                    }

                                    if ($hojasEsperadasArchivoPreConstruido[$i]['distribucion'][$columna]['tipo'] === 'fecha') {
                                        $celdaFechaInicial = $hoja->getCell($hojasEsperadasArchivoPreConstruido[$i]['distribucion'][$columna]['posicionCeldaFechaInicial']['columna'] . $fila);

                                        $ubicacionCeldaFechaInicial = $celdaFechaInicial->getColumn() . $fila;

                                        $esNuloFechaInicial = false;

                                        if (is_null($celdaFechaInicial->getValue())) {
                                            $esNuloFechaInicial = true;
                                        }

                                        $celdaFechaFinal = $hoja->getCell($hojasEsperadasArchivoPreConstruido[$i]['distribucion'][$columna]['posicionCeldaFechaFinal']['columna'] . $fila);

                                        $ubicacionCeldaFechaFinal = $celdaFechaFinal->getColumn() . $fila;

                                        $esNuloFechaFinal = false;

                                        if (is_null($celdaFechaFinal->getValue())) {
                                            $esNuloFechaFinal = true;
                                        }

                                        if ($esNuloFechaInicial && $esNuloFechaFinal) {
                                            continue;
                                        }

                                        if (!$esNuloFechaInicial && $esNuloFechaFinal) {
                                            $hojasEsperadasArchivoPreConstruido[$i]['informacionCargaArchivo'][] = 'Parece que se puso valor de Fecha Inicial en la celda ' . $ubicacionCeldaFechaInicial . ', ' .
                                                'sin embargo, no se puso valor de Fecha Final en la celda ' . $ubicacionCeldaFechaFinal . 
                                                ' y por lo tanto, no se registra el concepto al empleado.';
                                            continue;
                                        }

                                        if ($esNuloFechaInicial && !$esNuloFechaFinal) {
                                            $hojasEsperadasArchivoPreConstruido[$i]['informacionCargaArchivo'][] = 'Parece que se puso valor de Fecha Final en la celda ' . $ubicacionCeldaFechaFinal . ', ' .
                                                'sin embargo, no se puso valor de Fecha Inicial en la celda ' . $ubicacionCeldaFechaInicial . 
                                                ' y por lo tanto, no se registra el concepto al empleado.';
                                            continue;
                                        }

                                        $concepto['fechaInicial'] = $celdaFechaInicial->getValue();

                                        if (PHPExcel_Shared_Date::isDateTime($celdaFechaInicial)) {
                                            $concepto['fechaInicial'] = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($concepto['fechaInicial']));
                                        }
                                        $fechaInicial = new DateTime($concepto['fechaInicial']);
                                        $fechaInicial->add(new DateInterval('P1D'));
                                        $concepto['fechaInicial'] = $fechaInicial->format('Y-m-d');

                                        $concepto['fechaFinal'] = $celdaFechaFinal->getValue();
                                        if (PHPExcel_Shared_Date::isDateTime($celdaFechaFinal)) {
                                            $concepto['fechaFinal'] = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($concepto['fechaFinal']));
                                        }
                                        $fechaFinal = new DateTime($concepto['fechaFinal']);
                                        $fechaFinal->add(new DateInterval('P1D'));
                                        $concepto['fechaFinal'] = $fechaFinal->format('Y-m-d');

                                        if ($concepto['fechaInicial'] < $informacionHojas[$contadorHojas]['periodoHojaInicial']) {
                                            $hojasEsperadasArchivoPreConstruido[$i]['informacionCargaArchivo'][] = 'La Fecha Inicial con valor ' . 
                                                $concepto['fechaInicial'] . ' de la celda ' . $ubicacionCeldaFechaInicial . 
                                                ' no puede ser menor que la Fecha del Periodo Inicial con valor ' . 
                                                $informacionHojas[$contadorHojas]['periodoHojaInicial'] . 
                                                '. Por lo tanto, no se inserta el registro al empleado.';
                                        }

                                        if ($concepto['fechaFinal'] > $informacionHojas[$contadorHojas]['periodoHojaFinal']) {
                                            $hojasEsperadasArchivoPreConstruido[$i]['informacionCargaArchivo'][] = 'La Fecha Final con valor ' . 
                                                $concepto['fechaFinal'] . ' de la celda ' . $ubicacionCeldaFechaFinal . 
                                                ' no puede ser mayor que la Fecha del Periodo Inicial con valor ' . 
                                                $informacionHojas[$contadorHojas]['periodoHojaFinal'] . 
                                                '. Por lo tanto, no se inserta el registro al empleado.';
                                        }

                                        if ($concepto['fechaInicial'] > $concepto['fechaFinal']) {
                                            $hojasEsperadasArchivoPreConstruido[$i]['informacionCargaArchivo'][] = 'La Fecha Inicial con valor ' . 
                                                $concepto['fechaInicial'] . ' de la celda ' . $ubicacionCeldaFechaInicial . 
                                                ' no puede ser mayor que la fecha Final con valor ' . 
                                                $concepto['fechaFinal'] . ' de la celda ' . $ubicacionCeldaFechaFinal . 
                                                '. Por lo tanto, no se inserta el registro al empleado.';
                                            continue;
                                        }

                                        $intervaloEntreFechaInicialFechaFinal = $fechaInicial->diff($fechaFinal);
                                        $diasDiferencia = (int) $intervaloEntreFechaInicialFechaFinal->format('%a');
                                        $concepto['numeroDias'] = $diasDiferencia + 1;

                                        $informacionHojas[$contadorHojas]['contenidoHoja'][$contadorEmpleados]['conceptos'][0]['numeroDias'] -= $concepto['numeroDias'];
                                    }

                                    if ($hojasEsperadasArchivoPreConstruido[$i]['distribucion'][$columna]['tipo'] === 'valor') {
                                        $celdaValor = $hoja->getCell($hojasEsperadasArchivoPreConstruido[$i]['distribucion'][$columna]['posicionCeldaValor']['columna'] . $fila);

                                        if (is_null($celdaValor->getValue())) {
                                            continue;
                                        }

                                        $concepto['valor'] = $celdaValor->getValue();
                                    }

                                    if ($hojasEsperadasArchivoPreConstruido[$i]['distribucion'][$columna]['tipo'] === 'hora') {

                                        $celdaHora = $hoja->getCell($hojasEsperadasArchivoPreConstruido[$i]['distribucion'][$columna]['posicionCeldaHora']['columna'] . $fila);

                                        if (is_null($celdaHora->getValue())) {
                                            continue;
                                        }

                                        $concepto['horas'] = (int) $celdaHora->getValue();

                                        $consultaSalarioBasicoDevengadoEmpleado = "
                                            SELECT a.val_deveng
                                              FROM " . CONS . ".tab_nomina_deveng a
                                             WHERE a.cod_tercer = '" . $informacionHojas[$contadorHojas]['contenidoHoja'][$contadorEmpleados]['empleado']['identificacion'] . "'
                                               AND a.cod_concep = '1'
                                               AND a.ind_basico = '1';";
                                        $resultadoConsultaSalarioBasicoDevengadoEmpleado = new Consulta($consultaSalarioBasicoDevengadoEmpleado, $this->conexion);

                                        $salarioBasicoDevengadoEmpleado = 0;
                                        if ($resultadoConsultaSalarioBasicoDevengadoEmpleado->ret_num_rows() > 0) {
                                            $salarioBasicoDevengadoEmpleado = (float) $resultadoConsultaSalarioBasicoDevengadoEmpleado->ret_arreglo()['val_deveng'];
                                        }
                                        
                                        $informacionHojas[$contadorHojas]['contenidoHoja'][$contadorEmpleados]['empleado']['salarioBasicoDevengado'] = $salarioBasicoDevengadoEmpleado;

                                        $concepto['valor'] = round(($concepto['horas'] * $concepto['porcentaje']) * ($salarioBasicoDevengadoEmpleado / 240), 0);

                                        if ($concepto['valor'] === .0) {

                                            $hojasEsperadasArchivoPreConstruido[$i]['informacionCargaArchivo'][] = 'Para el empleado con el número de identificación ' . 
                                                $informacionHojas[$contadorHojas]['contenidoHoja'][$contadorEmpleados]['empleado']['identificacion'] . 
                                                ' y nombre ' . 
                                                $informacionHojas[$contadorHojas]['contenidoHoja'][$contadorEmpleados]['empleado']['nombre'] . 
                                                ' se intentaron cargar ' . $concepto['horas'] . ' horas extra en el Tipo de Hora: ' . $concepto['nombre'] . 
                                                ', sin embargo, al realizar el calculo del Valor el resultado fue 0, por lo tanto, no se incluye el registro. Por favor verificar la hoja de vida del empleado. ' . 
                                                'Celda: ' . $celdaIdentificacionEmpleado->getColumn() . $fila . ', Hoja: ' . 
                                                $hojasEsperadasArchivoPreConstruido[$i]['nombre'];
                                            continue;
                                        }
                                    }

                                    $concepto['tipo'] = $hojasEsperadasArchivoPreConstruido[$i]['distribucion'][$columna]['tipo'];

                                    $informacionHojas[$contadorHojas]['contenidoHoja'][$contadorEmpleados]['conceptos'][] = $concepto;
                                }
                                $contadorEmpleados++;
                            }
                            $contadorHojas++;
                        }
                    }
                }

                $longitudInformacionHojas = count($informacionHojas);

                if (empty($erroresGenerales)) {
                    new Consulta('SELECT 1', $this->conexion, 'BR');

                    $insercionesNovedades = [];
                    $insercionesHorasExtra = [];

                    for ($i = 0; $i < $longitudHojasEsperadasArchivoPreConstruido; $i++) {
                        for ($j = 0; $j < $longitudInformacionHojas; $j++) {
                            if ($hojasEsperadasArchivoPreConstruido[$i]['nombre'] === $informacionHojas[$j]['nombreHoja']) {

                                if (!empty($hojasEsperadasArchivoPreConstruido[$i]['erroresCargaArchivo'])) {
                                    $erroresGenerales[] = 'No se recorre el contenido de la hoja ' . $hojasEsperadasArchivoPreConstruido[$i]['nombre'] . ' para insertar porque tiene errores.';
                                    continue;
                                }

                                /*
                                 * se valida que en la fecha que viene de la hoja que se está recorriendo no tenga registros existentes
                                 * en caso de tener registros existentes se valida que puedan ser eliminados
                                 */
                                if ($hojasEsperadasArchivoPreConstruido[$i]['codigo'] === $this->codigoClasificacionHojaHorasExtra) {
                                    $consultaValidacionExistenciaFechasHoja = "
                                        SELECT 1
                                          FROM " . CONS . ".tab_horext_emplea a
                                         WHERE a.fec_horini = '" . $informacionHojas[$j]['periodoHojaInicial'] . "'
                                           AND a.fec_horfin = '" . $informacionHojas[$j]['periodoHojaFinal'] . "'
                                      GROUP BY a.fec_horini,
                                               a.fec_horfin;";
                                } else {
                                    $consultaValidacionExistenciaFechasHoja = "
                                        SELECT 1
                                          FROM " . CONS . ".tab_nomina_noveda a
                                         WHERE a.fec_inicia = '" . $informacionHojas[$j]['periodoHojaInicial'] . "'
                                           AND a.fec_finalx = '" . $informacionHojas[$j]['periodoHojaFinal'] . "'
                                      GROUP BY a.fec_inicia,
                                               a.fec_finalx;";
                                }
                                $resultadoConsultaValidacionExistenciaFechasHoja = new Consulta($consultaValidacionExistenciaFechasHoja, $this->conexion);
                                if ($resultadoConsultaValidacionExistenciaFechasHoja->ret_num_rows() > 0) {
                                    $consultaValidacionExistenciaFechasNominaTotalesEmpleados = "
                                        SELECT 1
                                          FROM " . CONS . ".tab_nomina_empres a
                                         WHERE a.fec_inicia = '" . $informacionHojas[$j]['periodoHojaInicial'] . "'
                                           AND a.fec_finalx = '" . $informacionHojas[$j]['periodoHojaFinal'] . "'
                                           AND a.ind_aproba = '1'
                                           AND a.ind_anulad = '0';";
                                    $resultadoConsultaValidacionExistenciaFechasNominaTotalesEmpleados = new Consulta($consultaValidacionExistenciaFechasNominaTotalesEmpleados, $this->conexion);
                                    if ($resultadoConsultaValidacionExistenciaFechasNominaTotalesEmpleados->ret_num_rows() > 0) {
                                        $hojasEsperadasArchivoPreConstruido[$i]['erroresCargaArchivo'][] = 'Con la fecha ' . $informacionHojas[$j]['periodoHojaFinal'] . 
                                            ' se encontró una Prenomina aprobada y no anulada, por lo que no es posible eliminar antes de cargar la información de la hoja ' . 
                                            $hojasEsperadasArchivoPreConstruido[$i]['nombre'] . ' los registros ya existentes, por lo tanto, la información de dicha hoja no se carga.';
                                            continue;
                                    } else {
                                        if ($hojasEsperadasArchivoPreConstruido[$i]['codigo'] === $this->codigoClasificacionHojaHorasExtra) {
                                            $eliminacionRegistrosConceptosPreviamenteInsertados = "
                                                DELETE FROM " . CONS . ".tab_horext_emplea
                                                 WHERE fec_horini = '" . $informacionHojas[$j]['periodoHojaInicial'] . "'
                                                   AND fec_horfin = '" . $informacionHojas[$j]['periodoHojaFinal'] . "';";
                                        } else {
                                            $eliminacionRegistrosConceptosPreviamenteInsertados = "
                                                DELETE FROM " . CONS . ".tab_nomina_noveda
                                                 WHERE fec_inicia = '" . $informacionHojas[$j]['periodoHojaInicial'] . "'
                                                   AND fec_finalx = '" . $informacionHojas[$j]['periodoHojaFinal'] . "';";
                                        }
                                        new Consulta($eliminacionRegistrosConceptosPreviamenteInsertados, $this->conexion, 'R');
                                        $hojasEsperadasArchivoPreConstruido[$i]['informacionCargaArchivo'][] = 'Se encontraron con la fecha ' . $informacionHojas[$j]['periodoHojaFinal'] .
                                            ' registros ya existentes que se eliminaron antes de cargar la información de la hoja ' . $hojasEsperadasArchivoPreConstruido[$i]['nombre'] .
                                            ' ya que en la fecha no se encuentra aún una Prenomina aprobada y no anulada.';
                                    }
                                }

                                for ($z = 0; $z < count($informacionHojas[$j]['contenidoHoja']); $z++) {

                                    for ($x = 0; $x < count($informacionHojas[$j]['contenidoHoja'][$z]['conceptos']); $x++) {

                                        if ($hojasEsperadasArchivoPreConstruido[$i]['codigo'] === $this->codigoClasificacionHojaHorasExtra) {
                                            if ($informacionHojas[$j]['contenidoHoja'][$z]['conceptos'][$x]['tipo'] === 'hora') {
                                                $insercionesHorasExtra[$informacionHojas[$j]['contenidoHoja'][$z]['empleado']['identificacion']][] = [
                                                    'codigoTercero' => $informacionHojas[$j]['contenidoHoja'][$z]['empleado']['identificacion'],
                                                    'fechaPeriodoInicial' => $informacionHojas[$j]['periodoHojaInicial'],
                                                    'fechaPeriodoFinal' => $informacionHojas[$j]['periodoHojaFinal'],
                                                    'numeroHoras' => $informacionHojas[$j]['contenidoHoja'][$z]['conceptos'][$x]['horas'],
                                                    'codigoTipoHoraExtra' => $informacionHojas[$j]['contenidoHoja'][$z]['conceptos'][$x]['codigo'],
                                                    'codigoConcepto' => '69',
                                                    'valorHorasExtra' => $informacionHojas[$j]['contenidoHoja'][$z]['conceptos'][$x]['valor']
                                                ];
                                            }
                                        } else {
                                            if ($informacionHojas[$j]['contenidoHoja'][$z]['conceptos'][$x]['tipo'] === 'fecha') {
                                                $insercionesNovedades[$informacionHojas[$j]['contenidoHoja'][$z]['empleado']['identificacion']][] = [
                                                    'fechaPeriodoInicial' => $informacionHojas[$j]['periodoHojaInicial'],
                                                    'fechaPeriodoFinal' => $informacionHojas[$j]['periodoHojaFinal'],
                                                    'codigoTercero' => $informacionHojas[$j]['contenidoHoja'][$z]['empleado']['identificacion'],
                                                    'codigoConcepto' => $informacionHojas[$j]['contenidoHoja'][$z]['conceptos'][$x]['codigo'],
                                                    'numeroDias' => $informacionHojas[$j]['contenidoHoja'][$z]['conceptos'][$x]['numeroDias'],
                                                    'fechaInicial' => $informacionHojas[$j]['contenidoHoja'][$z]['conceptos'][$x]['fechaInicial'],
                                                    'fechaFinal' => $informacionHojas[$j]['contenidoHoja'][$z]['conceptos'][$x]['fechaFinal'],
                                                    'observaciones' => ''
                                                ];
                                            }

                                            if ($informacionHojas[$j]['contenidoHoja'][$z]['conceptos'][$x]['tipo'] === 'valor') {
                                                $insercionesNovedades[$informacionHojas[$j]['contenidoHoja'][$z]['empleado']['identificacion']][] = [
                                                    'fechaPeriodoInicial' => $informacionHojas[$j]['periodoHojaInicial'],
                                                    'fechaPeriodoFinal' => $informacionHojas[$j]['periodoHojaFinal'],
                                                    'codigoTercero' => $informacionHojas[$j]['contenidoHoja'][$z]['empleado']['identificacion'],
                                                    'codigoConcepto' => $informacionHojas[$j]['contenidoHoja'][$z]['conceptos'][$x]['codigo'],
                                                    'numeroDias' => '0',
                                                    'fechaInicial' => '0000-00-00',
                                                    'fechaFinal' => '0000-00-00',
                                                    'observaciones' => $informacionHojas[$j]['contenidoHoja'][$z]['conceptos'][$x]['valor']
                                                ];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $consultaInsercionNovedades = "
                        INSERT INTO " . CONS . ".tab_nomina_noveda (
                            fec_inicia,
                            fec_finalx,
                            cod_tercer,
                            cod_concep,
                            num_diasxx,
                            fec_inicio,
                            fec_finali,
                            obs_noveda,
                            usr_creaci,
                            fec_creaci) VALUES ";

                    $longitudInsercionesNovedades = count($insercionesNovedades);
                    $contadorInsercionesNovedades = 0;
                    $insertaAlMenosUnaNovedad = false;

                    foreach ($insercionesNovedades as $identificacion => $empleado) {
                        $longitudNovedadesEmpleado = count($empleado);
                        for ($i = 0; $i < $longitudNovedadesEmpleado; $i++) {
                            $consultaInsercionNovedades .= '(';
                            $consultaInsercionNovedades .= "'" . $empleado[$i]['fechaPeriodoInicial'] . "',";
                            $consultaInsercionNovedades .= "'" . $empleado[$i]['fechaPeriodoFinal'] . "',";
                            $consultaInsercionNovedades .= "'" . $empleado[$i]['codigoTercero'] . "',";
                            $consultaInsercionNovedades .= "'" . $empleado[$i]['codigoConcepto'] . "',";
                            $consultaInsercionNovedades .= "'" . $empleado[$i]['numeroDias'] . "',";
                            $consultaInsercionNovedades .= "'" . $empleado[$i]['fechaInicial'] . "',";
                            $consultaInsercionNovedades .= "'" . $empleado[$i]['fechaFinal'] . "',";
                            $consultaInsercionNovedades .= "'" . $empleado[$i]['observaciones'] . "',";
                            $consultaInsercionNovedades .= "'" . $_SESSION['datos_usuario']['cod_usuari'] . "',";
                            $consultaInsercionNovedades .= "NOW()";
                            $consultaInsercionNovedades .= ')';
                            if ($i < $longitudNovedadesEmpleado - 1) {
                                $consultaInsercionNovedades .= ',';
                            }
                            if (!$insertaAlMenosUnaNovedad) {
                                $insertaAlMenosUnaNovedad = true;
                            }
                        }
                        if ($contadorInsercionesNovedades < $longitudInsercionesNovedades - 1) {
                            $consultaInsercionNovedades .= ',';
                        }
                        $contadorInsercionesNovedades++;
                    }

                    $consultaInsercionHorasExtra = "
                        INSERT INTO " . CONS . ".tab_horext_emplea (
                            cod_tercer,
                            fec_horini,
                            fec_horfin,
                            num_horasx,
                            cod_horext,
                            cod_concep,
                            val_horext) VALUES ";
                    $longitudInsercionesHorasExtra = count($insercionesHorasExtra);
                    $contadorInsercionesHorasExtra = 0;
                    $insertaAlMenosUnaHoraExtra = false;

                    foreach ($insercionesHorasExtra as $identificacion => $empleado) {
                        $longitudHorasExtraEmpleado = count($empleado);
                        for ($i = 0; $i < $longitudHorasExtraEmpleado; $i++) {
                            $consultaInsercionHorasExtra .= '(';
                            $consultaInsercionHorasExtra .= "'" . $empleado[$i]['codigoTercero'] . "',";
                            $consultaInsercionHorasExtra .= "'" . $empleado[$i]['fechaPeriodoInicial'] . "',";
                            $consultaInsercionHorasExtra .= "'" . $empleado[$i]['fechaPeriodoFinal'] . "',";
                            $consultaInsercionHorasExtra .= "'" . $empleado[$i]['numeroHoras'] . "',";
                            $consultaInsercionHorasExtra .= "'" . $empleado[$i]['codigoTipoHoraExtra'] . "',";
                            $consultaInsercionHorasExtra .= "'" . $empleado[$i]['codigoConcepto'] . "',";
                            $consultaInsercionHorasExtra .= "'" . $empleado[$i]['valorHorasExtra'] . "'";
                            $consultaInsercionHorasExtra .= ')';
                            if ($i < $longitudHorasExtraEmpleado - 1) {
                                $consultaInsercionHorasExtra .= ',';
                            }
                            if (!$insertaAlMenosUnaHoraExtra) {
                                $insertaAlMenosUnaHoraExtra = true;
                            }
                        }
                        if ($contadorInsercionesHorasExtra < $longitudInsercionesHorasExtra - 1) {
                            $consultaInsercionHorasExtra .= ',';
                        }
                        $contadorInsercionesHorasExtra++;
                    }

                    if ($insertaAlMenosUnaNovedad) {
                        new Consulta($consultaInsercionNovedades, $this->conexion, 'R');
                        $informacionGeneral[] = 'Se insertaron exitosamente las novedades.';
                    } else {
                        $informacionGeneral[] = 'No se insertó ninguna novedad';
                    }
                    
                    if ($insertaAlMenosUnaHoraExtra) {
                        new Consulta($consultaInsercionHorasExtra, $this->conexion, 'R');
                        $informacionGeneral[] = 'Se insertaron exitosamente las horas extras.';
                    } else {
                        $informacionGeneral[] = 'No se insertó ninguna Hora extra';
                    }

                    new Consulta('SELECT 1', $this->conexion, 'RC');
                }
            } else {
                $erroresGenerales[] = 'El sistema no tiene permisos suficientes para mover el archivo que se está tratando de importar. El archivo no se carga.';
            }

            $html = '<table cellspacing="1" cellpadding="4">';

            $html .= '<tr>';
            $html .= '<td colspan="3" style="text-align: center; background-color: #777777; color: #ffffff">';
            $html .= '<b>' . 'REPORTE' . '</b>';
            $html .= '</td>';
            $html .= '</tr>';

            //errores generales
            $longitudErroresGenerales = count($erroresGenerales);

            $html .= '<tr>';
            $html .= '<td colspan="2" nowrap rowspan="' . ($longitudErroresGenerales === 0 ? 1 : $longitudErroresGenerales) . '"
                style="background-color: #c1c1c1">';
            $html .= '<b>' . 'Reporte de errores generales' . '</b>';
            $html .= '</td>';

            $html .= '<td style="background-color: #f9f9f9; border-bottom: 1px solid #c1c1c1;">';
            if ($longitudErroresGenerales > 0) {
                $html .= $erroresGenerales[0];
            } else {
                $html .= 'No hay errores generales';
            }
            $html .= '</td>';

            $html .= '</tr>';
            
            for ($i = 1; $i < $longitudErroresGenerales; $i++) {
                $html .= '<tr>';
                $html .= '<td style="background-color: #f9f9f9; border-bottom: 1px solid #c1c1c1;">';
                $html .= $erroresGenerales[$i];
                $html .= '</td>';
                $html .= '</tr>';
            }

            if (isset($hojasEsperadasArchivoPreConstruido)) {
                for ($i = 0; $i < $longitudHojasEsperadasArchivoPreConstruido; $i++) {
                    $html .= '<tr>';

                    $longitudErroresCargaArchivoHoja = count($hojasEsperadasArchivoPreConstruido[$i]['erroresCargaArchivo']);
                    $longitudInformacionCargaArchivoHoja = count($hojasEsperadasArchivoPreConstruido[$i]['informacionCargaArchivo']);

                    $sumaLongitudes = ($longitudErroresCargaArchivoHoja === 0 ? 1 : $longitudErroresCargaArchivoHoja) + ($longitudInformacionCargaArchivoHoja === 0 ? 1 : $longitudInformacionCargaArchivoHoja);

                    $html .= '<td  nowrap rowspan="' . $sumaLongitudes . '" style="background-color: #c1c1c1">';
                    $html .= '<b>' . $hojasEsperadasArchivoPreConstruido[$i]['nombre'] . '</b>';
                    $html .= '</td>';

                    $html .= '<td rowspan="' . ($longitudErroresCargaArchivoHoja === 0 ? 1 : $longitudErroresCargaArchivoHoja) . '"
                        style="background-color: #e9e9e9; font-size: smaller; text-align: right;">';
                    $html .= '<b>' . 'Errores' . '</b>';
                    $html .= '</td>';

                    $html .= '<td style="background-color: #f9f9f9; border-bottom: 1px solid #c1c1c1;">';
                    if ($longitudErroresCargaArchivoHoja > 0) {
                        $html .= $hojasEsperadasArchivoPreConstruido[$i]['erroresCargaArchivo'][0];
                    } else {
                        $html .= 'No hay errores para la hoja';
                    }
                    $html .= '</td>';
                    $html .= '</tr>';
                    
                    for ($j = 1; $j < $longitudErroresCargaArchivoHoja; $j++) {
                        $html .= '<tr>';
                        $html .= '<td style="background-color: #f9f9f9; border-bottom: 1px solid #c1c1c1;">';
                        $html .= $hojasEsperadasArchivoPreConstruido[$i]['erroresCargaArchivo'][$j];
                        $html .= '</td>';
                        $html .= '</tr>';
                    }

                    $html .= '<tr>';

                    $html .= '<td rowspan="' . ($longitudInformacionCargaArchivoHoja === 0 ? 1 : $longitudInformacionCargaArchivoHoja) . '"
                        style="background-color: #e9e9e9; font-size: smaller; text-align: right;">';
                    $html .= '<b>' . 'Información' . '</b>';
                    $html .= '</td>';

                    $html .= '<td style="background-color: #f9f9f9; border-bottom: 1px solid #c1c1c1;">';
                    if ($longitudInformacionCargaArchivoHoja > 0) {
                        $html .= $hojasEsperadasArchivoPreConstruido[$i]['informacionCargaArchivo'][0];
                    } else {
                        $html .= 'No hay información que transmitir al usuario para la hoja';
                    }
                    $html .= '</td>';

                    $html .= '</tr>';

                    for ($j = 1; $j < $longitudInformacionCargaArchivoHoja; $j++) {
                        $html .= '<tr>';
                        $html .= '<td style="background-color: #f9f9f9; border-bottom: 1px solid #c1c1c1;">';
                        $html .= $hojasEsperadasArchivoPreConstruido[$i]['informacionCargaArchivo'][$j];
                        $html .= '</td>';
                        $html .= '</tr>';
                    }
                    
                }
            }

            //información general
            $longitudInformacionGeneral = count($informacionGeneral);

            $html .= '<tr>';
            $html .= '<td colspan="2" rowspan="' . ($longitudInformacionGeneral === 0 ? 1 : $longitudInformacionGeneral) . '"
                style="background-color: #c1c1c1">';
            $html .= '<b>' . 'Reporte de información general' . '</b>';
            $html .= '</td>';

            $html .= '<td style="background-color: #f9f9f9; border-bottom: 1px solid #c1c1c1;">';
            if ($longitudInformacionGeneral > 0) {
                $html .= $informacionGeneral[0];
            } else {
                $html .= 'No hay información general que transmitir al usuario';
            }
            $html .= '</td>';

            $html .= '</tr>';
            
            for ($i = 1; $i < $longitudInformacionGeneral; $i++) {
                $html .= '<tr>';
                $html .= '<td style="background-color: #f9f9f9; border-bottom: 1px solid #c1c1c1;">';
                $html .= $informacionGeneral[$i];
                $html .= '</td>';
                $html .= '</tr>';
            }

            $html .= '</table>';

            $mHtml->SetBody($html);
            $mHtml->CloseDiv();
            $mHtml->CloseCell();
            $mHtml->CloseRow();
        }
        $mHtml->CloseTable();
        $mHtml->Hidden("name:window; value:central");
        $mHtml->Hidden("name:standar; value:" . DIR_APLICA_CENTRAL);
        $mHtml->CloseForm();
        $mHtml->CloseBody();
        echo $mHtml->MakeHtml();
    }

}
