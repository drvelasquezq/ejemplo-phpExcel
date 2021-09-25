<?php

//ini_set('display_errors', true);
//error_reporting(E_ALL & ~E_NOTICE);

class ConcepNomina {

    var $cHtml = NULL;
    var $conexion = NULL;
    var $cNull = array(array('', '--'));
    var $cBanks = array();
    var $cYears = array();
    var $cConsec = NULL;
    var $cParcon = NULL;
    var $cCentros = NULL;

    function __construct($mConecction, $mData) {

        include( "../" . DIR_APLICA_CENTRAL . "/lib/general/functions.inc" );
        $this->conexion = $mConecction;
        $this->cData = $mData;

        $mData['usr_creaci'] = $_SESSION["datos_usuario"]['cod_usuari'];
        $mData['fec_creaci'] = date('Y-m-d H:i:s');

        $mData["Action"] = $mData["Action"] ? $mData["Action"] : 'template';

        switch (strtolower($mData["Action"])) {
            case 'insert' :
                if ($mData['tomoe'] != '@QMZP@') {
                    echo "IMPOSIBLE REALIZAR LA TRANSACCION.<br />ACCIÓN RESTRINGIDA POR INTRARED LTDA.";
                    die();
                }
                $this->Insert($mData);
                break;
            case 'update' :
                if ($mData['tomoe'] != '@QMZP@') {
                    echo "IMPOSIBLE REALIZAR LA TRANSACCION.<br />ACCIÓN RESTRINGIDA POR INTRARED LTDA.";
                    die();
                }
                $this->Update($mData);
                break;
            default :
                $this->Template($mData);
                break;
        }
    }

    function GetNominaTipos() {
        $mSql = "
            SELECT a.cod_concep,
                   a.nom_concep
              FROM " . CONS . ".tab_tiposx_concep a
             WHERE a.ind_estado = 1
          ORDER BY 2 ";
        $consul = new Consulta($mSql, $this->conexion);
        return $consul->ret_matriz('i');
    }

    function GetNominaTipter() {
        $mSql = "
            SELECT a.cod_tipter,
                   a.nom_tipter
              FROM " . CONS . ".tab_nomina_tipter a
             WHERE a.ind_estado = 1
          ORDER BY 2 ";
        $consul = new Consulta($mSql, $this->conexion);
        return $consul->ret_matriz('i');
    }

    function GetNominaConcepConsec() {
        $mSql = "
            SELECT MAX(a.cod_concep) AS max_concep
              FROM " . CONS . ".tab_nomina_concep a ";
        $consul = new Consulta($mSql, $this->conexion);
        $consec = $consul->ret_matriz('a');
        $consec = $consec[0]['max_concep'];
        return $consec == NULL ? 1 : $consec + 1;
    }

    function GetNominaConcep() {
        $mSql = "
            SELECT a.cod_concep,
                   a.nom_concep,
                   a.ind_deveng,
                   a.ind_provis,
                   a.ind_deducc,
                   a.ind_estado,
                   a.cod_tipter,
                   ind_aplnov
              FROM " . CONS . ".tab_nomina_concep a 
             WHERE a.ind_estado = '1'
          ORDER BY 1 ";
        $consul = new Consulta($mSql, $this->conexion);
        return $consul->ret_matriz('a');
    }

    function Template($mData) {
        $this->cHtml = new DinamicHtml();
        $this->cHtml->SetCss("../" . DIR_APLICA_CENTRAL . "/modules/nomina/nomina.css");
        $this->cHtml->SetJs("../" . DIR_APLICA_CENTRAL . "/js/ins_nomina_concep.js");

        $this->cHtml->Body();

        $this->cHtml->Form(array('action' => '?', 'method' => 'POST', 'name' => 'frm_concep', 'enctype' => 'multipart/form-data'));

        $this->cHtml->Table("class:StyleContainer; align:center; cellpadding:0; cellspacing:0; width:100%");

        $this->cHtml->Row();
        $this->cHtml->Cell("align:center");

        $html = NULL;

        $html .= '<div class="e_style">CONCEPTOS DE NOMINA</div>';

        $_CONCEP = $this->GetNominaConcep();
        $size_concep = sizeof($_CONCEP);
        $_TIPOSX = $this->GetNominaTipos();
        $size_tiposx = sizeof($_TIPOSX);
        $_TIPTER = $this->GetNominaTipter();
        $size_tipter = sizeof($_TIPTER);

        $_ESTADO[] = array('1', 'ÁCTIVO');
        $_ESTADO[] = array('0', 'INÁCTIVO');
        $size_estado = sizeof($_ESTADO);

        //----------|
        $g = '0'; //|
        //----------|
        $title = 'INSERTAR NUEVO CONCEPTO DE NOMINA';
        $html .= '<div id="title' . $g . 'ID" class="t_style" onclick="ShowGrid( \'' . $title . '\', \'' . $g . '\' );" title="OCULTAR ' . $title . '">- ' . $title . '</div>';
        $html .= '<div id="grid' . $g . 'ID" style="display:block;">';

        $html .= '<table align="center" width="100%" cellspacing="0" cellpadding="0">';
        $html .= '<tr>';
        $html .= '<th nowrap class="h_style" align="left" width="5%">';
        $html .= '<label for="cod_concepID">CÓDIGO:</label>';
        $html .= '</th>';
        $html .= '<td nowrap class="h_style" align="left" width="5%">';
        $html .= '<input class="info_style fwb" type="text" name="cod_concep" id="cod_concepID" readonly="readonly" value="' . $this->GetNominaConcepConsec() . '" />';
        $html .= '</td>';
        $html .= '<th nowrap class="h_style" align="left" width="5%">';
        $html .= '<label for="nom_concepID">* NOMBRE:</label>';
        $html .= '</th>';
        $html .= '<td nowrap class="h_style" align="left" width="15%">';
        $html .= '<input class="input_style tal w99" type="text" name="nom_concep" id="nom_concepID" maxlength="100" />';
        $html .= '</td>';

        $html .= '<th nowrap class="h_style" align="left" width="5%">';
        $html .= '<label for="cod_tiposxID">* TIPOS:</label>';
        $html .= '</th>';
        $html .= '<td nowrap class="h_style" align="left" width="15%">';
        $html .= '<select class="select_style" name="cod_tipos" id="cod_tiposxID">';
        $html .= '<option value="">--</option>';
        for ($e = 0; $e < $size_tiposx; $e++) {
            $selected = $_TIPOSX[$e][0] === $mData['cod_tipos'] ? 'selected="selected"' : NULL;
            $html .= '<option value="' . $_TIPOSX[$e][0] . '" ' . $selected . '>' . $_TIPOSX[$e][1] . '</option>';
        }
        $html .= '</select>';
        $html .= '</td>';

        $html .= '<th nowrap class="h_style" align="left" width="5%">';
        $html .= '<label for="cod_tipterID">* TERCERO:</label>';
        $html .= '</th>';
        $html .= '<td nowrap class="h_style" align="left" width="15%">';
        $html .= '<select class="select_style" name="cod_tipter" id="cod_tipterID">';
        $html .= '<option value="">--</option>';
        for ($e = 0; $e < $size_tipter; $e++) {
            $selected = $_TIPTER[$e][0] === $mData['cod_tipter'] ? 'selected="selected"' : NULL;
            $html .= '<option value="' . $_TIPTER[$e][0] . '" ' . $selected . '>' . $_TIPTER[$e][1] . '</option>';
        }
        $html .= '</select>';
        $html .= '</td>';


        $html .= '<th nowrap class="h_style" align="left" width="5%">';
        $html .= '<label for="ind_estadoID">* ESTADO:</label>';
        $html .= '</th>';
        $html .= '<td nowrap class="h_style" align="left" width="15%">';
        $html .= '<select class="select_style" name="ind_estado" id="ind_estadoID">';
        $html .= '<option value="">--</option>';
        for ($e = 0; $e < $size_estado; $e++) {
            $selected = $_ESTADO[$e][0] === $mData['ind_estado'] ? 'selected="selected"' : NULL;
            $html .= '<option value="' . $_ESTADO[$e][0] . '" ' . $selected . '>' . $_ESTADO[$e][1] . '</option>';
        }
        $html .= '</select>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        $html .= '<div class="tac">';
        $html .= '<span class="a_style fcv tac" onclick="InsertConcep();">[ INSERTAR ]</span>';
        $html .= '</div>';

        $html .= '</div>';

        $this->cHtml->SetBody($html);

        $this->cHtml->CloseCell();
        $this->cHtml->CloseRow();

        $this->cHtml->Row();
        $this->cHtml->Cell("align:center");

        $html = NULL;

        //----------|
        $g = '1'; //|
        //----------|
        $title = 'LISTADO DE CONCEPTOS DE NOMINA';
        $html .= '<div id="title' . $g . 'ID" class="t_style" onclick="ShowGrid( \'' . $title . '\', \'' . $g . '\' );" title="OCULTAR ' . $title . '">- ' . $title . '</div>';
        $html .= '<div id="grid' . $g . 'ID" style="display:block;">';

        $html .= '<table align="center" width="100%" cellspacing="0" cellpadding="0">';
        $html .= '<tr>';
        $html .= '<th nowrap class="h_style" align="center" width="5%">&nbsp;&nbsp;#&nbsp;&nbsp;</th>';
        $html .= '<th nowrap class="h_style" align="center" width="20%">CÓDIGO</th>';
        $html .= '<th nowrap class="h_style" align="center" width="20%">NOMBRE</th>';
        $html .= '<th nowrap class="h_style" align="center" width="20%">TIPO</th>';
        $html .= '<th nowrap class="h_style" align="center" width="20%">TERCERO</th>';
        $html .= '<th nowrap class="h_style" align="center" width="20%">ESTADO</th>';
        $html .= '<th nowrap class="h_style" align="center" width="10%">APLICA NOVEDAD</th>';
        $html .= '</tr>';

        for ($i = 0; $i < $size_concep; $i++) {
            $_ROW = $_CONCEP[$i];
            $ind = 0;
            if ($_ROW['ind_deveng'] == '1') {
                $ind = 1;
            }
            if ($_ROW['ind_provis'] == 1) {
                $ind = 2;
            }
            if ($_ROW['ind_deducc'] == 1) {
                $ind = 3;
            }

            $html .= '<tr>';
            $html .= '<th nowrap class="h_style" align="center">' . ( $i + 1 ) . '</th>';
            $html .= '<td nowrap class="h_style" align="center">';
            $html .= '<input class="info_style tac" type="text" name="cod_concep' . $i . '" id="cod_concep' . $i . 'ID" readonly="readonly" value="' . $_ROW['cod_concep'] . '" />';
            $html .= '</td>';
            $html .= '<td nowrap class="h_style" align="center">';
            $html .= '<input class="input_style tal w99" type="text" name="nom_concep' . $i . '" id="nom_concep' . $i . 'ID" maxlength="100" value="' . $_ROW['nom_concep'] . '" />';
            $html .= '</td>';
            $html .= '<td nowrap class="h_style" align="center">';
            $html .= '<select class="select_style" name="cod_tipos' . $i . '" id="cod_tipos' . $i . 'ID"  onchange="showCheck(' . $i . ')"  >';
            $html .= '<option value="">--</option>';
            for ($e = 0; $e < $size_tiposx; $e++) {
                $selected = $_TIPOSX[$e][0] == $ind ? 'selected="selected"' : NULL;
                $html .= '<option value="' . $_TIPOSX[$e][0] . '" ' . $selected . '>' . $_TIPOSX[$e][1] . '</option>';
            }
            $html .= '</select>';
            $html .= '</td>';

            $html .= '<td nowrap class="h_style" align="center">';
            $html .= '<select class="select_style" name="cod_tipter' . $i . '" id="cod_tipter' . $i . 'ID">';
            $html .= '<option value="">--</option>';
            for ($e = 0; $e < $size_tipter; $e++) {
                $selected = $_TIPTER[$e][0] == $_ROW['cod_tipter'] ? 'selected="selected"' : NULL;
                $html .= '<option value="' . $_TIPTER[$e][0] . '" ' . $selected . '>' . $_TIPTER[$e][1] . '</option>';
            }
            $html .= '</select>';
            $html .= '</td>';

            $html .= '<td nowrap class="h_style" align="center">';
            $html .= '<select class="select_style" name="ind_estado' . $i . '" id="ind_estado' . $i . 'ID">';
            $html .= '<option value="">--</option>';
            for ($e = 0; $e < $size_estado; $e++) {
                $selected = $_ESTADO[$e][0] === $_ROW['ind_estado'] ? 'selected="selected"' : NULL;
                $html .= '<option value="' . $_ESTADO[$e][0] . '" ' . $selected . '>' . $_ESTADO[$e][1] . '</option>';
            }
            $html .= '</select>';
            $html .= '</td>';

            $html .= '<td nowrap class="h_style" align="center">';
            $html .= '<input type="checkbox" name="ind_aplnov[' . $i . ']" id="ind_aplnov' . $i . 'ID" ' . ((int) $_ROW['ind_aplnov'] == 1 ? 'checked="checked"' : NULL) . '  style="display: ' . ((int) $ind == 1 ? 'block' : 'none' ) . '" value="' . ((int) $_ROW['ind_aplnov']) . '" onclick="this.value = (this.checked == true ? 1 : 0 ); " />';
            $html .= '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';

        $html .= '<input type="hidden" name="size_concep" id="size_concepID" value="' . $size_concep . '" />';

        $html .= '<div class="tac">';
        $html .= '<span class="a_style fcv tac" onclick="UpdateConcep();">[ ACTUALIZAR ]</span>';
        $html .= '</div>';

        $html .= '</div>';

        $this->cHtml->SetBody($html);

        $this->cHtml->CloseCell();
        $this->cHtml->CloseRow();

        $this->cHtml->CloseTable();

        $this->cHtml->Hidden("name:Standar; value:" . DIR_APLICA_CENTRAL);
        $this->cHtml->Hidden("name:Action");
        $this->cHtml->Hidden("name:tomoe");

        $this->cHtml->CloseForm();
        $this->cHtml->CloseBody();
        echo $this->cHtml->MakeHtml();
    }

    //@GENERA EL INSERT EN tab_nomina_concep
    function Insert($mData) {
        $_DATA['cod_concep'] = $mData['cod_concep'];
        $_DATA['nom_concep'] = $mData['nom_concep'];
        $_DATA['cod_tipos'] = $mData['cod_tipos'];
        $_DATA['ind_estado'] = '1';
        $_DATA['usr_creaci'] = $mData['usr_creaci'];
        $_DATA['fec_creaci'] = $mData['fec_creaci'];

        $ind_deveng = 0;
        $ind_provis = 0;
        $ind_deducc = 0;

        if ($_DATA['cod_tipos'] == 1) {
            $ind_deveng = 1;
        }
        if ($_DATA['cod_tipos'] == 2) {
            $ind_provis = 1;
        }
        if ($_DATA['cod_tipos'] == 3) {
            $ind_deducc = 1;
        }
        $execute = new Consulta("SELECT 1", $this->conexion, 'BR');
        $mSql = "
            INSERT INTO " . CONS . ".tab_nomina_concep (
                cod_concep,
                nom_concep,
                ind_deveng,
                ind_provis,
                ind_deducc,
                ind_estado,
                cod_tipter,
                usr_creaci,
                fec_creaci ) VALUES (
                '" . $mData['cod_concep'] . "',
                '" . $mData['nom_concep'] . "',
                '" . $ind_deveng . "',
                '" . $ind_provis . "',
                '" . $ind_deducc . "',
                '" . $mData['ind_estado'] . "',
                '" . $mData['cod_tipter'] . "',
                '" . $mData['usr_creaci'] . "',
                '" . $mData['fec_creaci'] . "' ) ";

        //echo '<hr />' . $mSql;
        $execute = new Consulta($mSql, $this->conexion, 'R');
        $this->cHtml = new DinamicHtml();
        $this->cHtml->SetCss("../" . DIR_APLICA_CENTRAL . "/css/styles.css");
        $this->cHtml->SetJs("../" . DIR_APLICA_CENTRAL . "/js/functions.js");
        $this->cHtml->SetJs("../" . DIR_APLICA_CENTRAL . "/js/general.js");
        $this->cHtml->SetCss("../" . DIR_APLICA_CENTRAL . "/modules/nomina/nomina.css");

        $this->cHtml->Body();

        $this->cHtml->Form(array('action' => '?', 'method' => 'POST', 'name' => 'frm_parame'));

        $this->cHtml->SetBody('<div class="e_style">PARAMETROS DE NOMINA</div>');

        $this->cHtml->Table("class:StyleContainer; align:center; cellpadding:0; cellspacing:0; width:85%");
        $this->cHtml->Row();

        if ($consulta = new Consulta("SELECT 1", $this->conexion, "RC")) {
            $ok = "Se Inserto el Concepto de Nomina con éxito.<br />";
            $this->cHtml->SetBody(ShowOk($ok, 14));
        }

        $this->cHtml->Row();
        $this->cHtml->Button("name:send; align:center; value:Parametrizar; onclick:frm_tipnom.submit()", array('style' => 'text-align:center', 'colspan' => 2));
        $this->cHtml->CloseRow();

        $this->cHtml->CloseTable();
        $this->cHtml->CloseForm();
        $this->cHtml->CloseBody();
        echo $this->cHtml->MakeHtml();
    }

    function Update($mData) {
        $size_concep = $mData['size_concep'];
        $execute = new Consulta("SELECT 1", $this->conexion, 'BR');

        for ($s = 0; $s < $size_concep; $s++) {

            $ind_deveng = 0;
            $ind_provis = 0;
            $ind_deducc = 0;

            if ($mData['cod_tipos' . $s] == 1) {
                $ind_deveng = 1;
            }
            if ($mData['cod_tipos' . $s] == 2) {
                $ind_provis = 1;
            }
            if ($mData['cod_tipos' . $s] == 3) {
                $ind_deducc = 1;
            }
            $mSql = "
                UPDATE " . CONS . ".tab_nomina_concep
                   SET nom_concep = '" . $mData['nom_concep' . $s] . "',
                       ind_deveng = '" . $ind_deveng . "',
                       ind_provis = '" . $ind_provis . "',
                       ind_deducc = '" . $ind_deducc . "',
                       ind_estado = '" . $mData['ind_estado' . $s] . "',
                       cod_tipter = '" . $mData['cod_tipter' . $s] . "',
                       ind_aplnov = " . ((int) $mData['cod_tipos' . $s] == 1 ? "'" . ((int) $mData['ind_aplnov'][$s]) . "'" : "NULL" ) . ",
                       usr_modifi = '" . $mData['usr_creaci' . $s] . "',
                       fec_modifi =  NOW()
                 WHERE cod_concep = '" . $mData['cod_concep' . $s] . "'";
            $execute = new Consulta($mSql, $this->conexion, 'R');
        }

        $this->cHtml = new DinamicHtml();
        $this->cHtml->SetCss("../" . DIR_APLICA_CENTRAL . "/modules/nomina/nomina.css");

        $this->cHtml->Body();

        $this->cHtml->Form(array('action' => '?', 'method' => 'POST', 'name' => 'frm_parame'));

        $this->cHtml->SetBody('<div class="e_style">PARAMETROS DE NOMINA</div>');

        $this->cHtml->Table("class:StyleContainer; align:center; cellpadding:0; cellspacing:0; width:85%");
        $this->cHtml->Row();

        if ($consulta = new Consulta("SELECT 1", $this->conexion, "RC")) {
            $ok = "Se Actualio los Conceptos de Nomina con éxito.<br />";
            $this->cHtml->SetBody(ShowOk($ok, 14));
        }

        $this->cHtml->Row();
        $this->cHtml->Button("name:send; align:center; value:Parametrizar; onclick:frm_tipnom.submit()", array('style' => 'text-align:center', 'colspan' => 2));
        $this->cHtml->CloseRow();

        $this->cHtml->CloseTable();
        $this->cHtml->CloseForm();
        $this->cHtml->CloseBody();
        echo $this->cHtml->MakeHtml();
    }

}

$html = new ConcepNomina($this->conexion, $_POST);