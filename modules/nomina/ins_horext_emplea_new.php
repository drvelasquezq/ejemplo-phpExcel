<?php

class Horext
{
    var $cHtml      = NULL;
    var $conexion   = NULL;
    var $cNull      = array(array('', '--'));

    function Horext($mConecction, $mData)
    {

        include("../" . DIR_APLICA_CENTRAL . "/lib/general/functions.inc");
        $this->conexion = $mConecction;
        $this->cData    = $mData;

        $mData['usr_creaci'] = $_SESSION["datos_usuario"]['cod_usuari'];
        $mData['fec_creaci'] = date('Y-m-d H:i:s');
        $mData["option"] = $mData["option"] ? $mData["option"] : 'Formulario';

        switch (strtolower($mData["option"])) {
            case 'insert':
                if ($mData['tomoe'] != '@QMZP@') {
                    echo "IMPOSIBLE REALIZAR LA TRANSACCION.<br />ACCI&Oacute;N RESTRINGIDA POR INTRARED LTDA.";
                    die();
                }
                $this->Insert($mData);
                break;
            case 'form':
                $this->Formulario_filter($mData);
                break;
            case 'form_usu':
                $this->Formulario_usu($mData);
                break;
            default:
                $this->Formulario($mData);
                break;
        }
    }

    function Formulario($mData)
    {

        $mHtml  = new DinamicHtml();
        $mHtml->SetBody('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">');
        $mHtml->SetJs("../" . DIR_APLICA_CENTRAL . "/js/horext.js");
        $mHtml->Body();
        $nomina = $this->GetNovedades();
        $mHtml->Table("class:StyleContainer; align:center; cellpadding:0; cellspacing:0; width:100%");
        $mHtml->Row();
        $mHtml->Cell("align:center");
        $mHtml->SubTitle("- Novedades Horas Extras", "name:SectionLink4; width:100%; ");
        $mHtml->CloseCell();
        $mHtml->CloseRow();
        //----------------------------------------------------------------------------------------------
        $mHtml->Row();
        $mHtml->Cell("name:section1; align:center; width:100%");
        $mHtml->SetBody('<div class="row d-flex justify-content-center">
                            <div class="col-10 table-responsive" style="max-height: 800px;overflow-y: auto;">
                                <table class="table table-striped table-bordered table-hover table-sm">
                                    <thead>
                                        <tr>
                                            <th class="text-center" scope="col">Periodo Nomina Desde</th>
                                            <th class="text-center" scope="col">Periodo Nomina Hasta</th>
                                        </tr>
                                    </thead>
                                    <tbody>');
        $html = '';
        foreach ($nomina as $row) {
            $link = "<a href='index.php?window=central&cod_servic=$_REQUEST[cod_servic]&option=form&fec_inicia=$row[fec_inicia]&fec_finalx=$row[fec_finalx]&accion=update' >$row[fec_finalx]</a>";
            $html .= '<tr>';
            $html .= '<td class="text-center">' . $row['fec_inicia'] . '</th>';
            $html .= '<td class="text-center">' . $link . '</td>';
            $html .= '</tr>';
        }
        $mHtml->SetBody($html);
        $mHtml->SetBody('           </tbody>
                                    </table>
                            </div>
                        </div>');
        $mHtml->CloseCell();
        $mHtml->CloseRow();

        //----------------------------------------------------------------------------------------------
        $mHtml->CloseTable();

        $mHtml->SetBody('<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>');
        $mHtml->SetBody('<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>');
        $mHtml->SetBody('<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>');
        $mHtml->CloseBody();
        $mHtml->SetBody('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">');

        echo $mHtml->MakeHtml();
    }

    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
    function Formulario_filter($mData)
    {
        $mHtml  = new DinamicHtml();
        $mHtml->SetBody('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">');

        $mHtml->SetJs("../" . DIR_APLICA_CENTRAL . "/js/horext.js");
        $mHtml->Body();
        $filtro_empleados = $this->GetHorasExtrasGen($mData);
        $mHtml->Table("class:StyleContainer; align:center; cellpadding:0; cellspacing:0; width:100%");
        $mHtml->Row();
        $mHtml->Cell("align:center");
        $mHtml->SubTitle("- Novedades Horas Extras", "name:SectionLink4; width:100%; ");
        $mHtml->CloseCell();
        $mHtml->CloseRow();
        //----------------------------------------------------------------------------------------------

        //----------------------------------------------------------------------------------------------
        $mHtml->Row();
        $mHtml->Cell("align:center");
        $mHtml->SubTitle("Periodo Extraido : " . $mData['fec_inicia'] . " >>> " . $mData['fec_finalx'], "name:SectionLink4; width:100%; ");
        $mHtml->CloseCell();
        $mHtml->CloseRow();
        //----------------------------------------------------------------------------------------------
        $mHtml->Row();
        $mHtml->Cell("name:section1; align:center; width:100%");
        $mHtml->SetBody('<div class="row d-flex justify-content-center">
                            <div class="col-12 table-responsive" style="max-height: 800px;overflow-y: auto;overflow-x: auto;white-space:nowrap;">
                                <table class="table table-striped table-bordered table-hover table-sm" style="font-size:0.8em;">
                                    <thead>
                                    <tr>
                                        <th class="text-center" scope="col">Identificación</th>
                                        <th class="text-center" scope="col">Nombre</th>
                                        <th class="text-center" scope="col">Fecha Inicial</th>
                                        <th class="text-center" scope="col">Fecha Final</th>
                                        <th class="text-center" scope="col">No. Horas</th>
                                        <th class="text-center" scope="col">Tipo de Hora</th>
                                        <th class="text-center" scope="col">Concepto</th>
                                        <th class="text-center" scope="col">Valor</th>
                                        <th class="text-center" scope="col"></th>
                                    </tr>
                                    </thead>
                                    <tbody>');
        $html = NULL;
        foreach ($filtro_empleados as $row) {
            $html .= '<tr>';
            $html .= '<td class="text-center">' . $row['cod_tercer'] . '</td>';
            $html .= '<td class="text-center">' . $row['nom_tercer'] . '</td>';
            $html .= '<td class="text-center">' . $row['fec_horini'] . '</td>';
            $html .= '<td class="text-center">' . $row['fec_horfin'] . '</td>';
            $html .= '<td class="text-center">' . $row['num_horasx'] . '</td>';
            $html .= '<td class="text-center">' . $this->GetGenera_Horext($row['cod_horext']) . '</th>';
            $html .= '<td class="text-center">Horas Extras</th>';
            $html .= '<td class="text-center">' . $row['val_horext'] . '</td>';
            $link = '<a href="index.php?window=central&cod_servic=' . $_REQUEST['cod_servic'] . '&cod_tercer=' . $row['cod_tercer'] . '&option=form_usu&fec_inicia=' . $mData['fec_inicia'] . '&fec_finalx=' . $mData['fec_finalx'] . '&accion=update" class="btn btn-sm btn-primary">Agregar [+]</a>';

            $html .= '<td class="text-center">' . $link . '</td>';

            $html .= '</tr>';
        }

        $mHtml->SetBody($html);
        $mHtml->SetBody('           </tbody>
                                    </table>
                            </div>
                        </div>');
        $mHtml->CloseCell();
        $mHtml->CloseRow();

        //----------------------------------------------------------------------------------------------
        $mHtml->CloseTable();

        $mHtml->SetBody('<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>');
        $mHtml->SetBody('<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>');
        $mHtml->SetBody('<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>');
        $mHtml->CloseBody();
        $mHtml->SetBody('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">');

        echo $mHtml->MakeHtml();
    }
    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
    function Formulario_usu($mData)
    {
        $mHtml  = new DinamicHtml();

		$mHtml -> SetJs ( "../".DIR_APLICA_CENTRAL."/js/horext.js" );
		$mHtml -> Body();


		$mHtml->SetBody( "	<style> 
						
						*
						{
							padding:0px;
							margin:0px;
							border:0px;
						}
						
	   					#nom_tercerID, #nom_tipserID 
						{
							font-weight:bold;
							padding:2px;
							margin:0px;
							text-transform:capitalize;
							padding-left:10px;
						}
						
						.StyleTd input
						{
							margin-left:10px;
						}	
						
						.StyleTd select
						{
							margin-left:10px;
							border:1px solid #c0c0c0;
							font-weight:bold;
							color:#555555;
						}	
						
						.StyleTitle
						{
							padding:0px;
							color:#555555;
							margin:0px;
							background-color:#dbdbdb;;
							border-top:1px solid white;
							border-bottom:1px solid #c0c0c0;
						}
						
						.StyleTitle td
						{
							color:#555555;
							padding:3px 10px;
							margin:0px;
						}
						
						.StyleLabel
						{
							margin:0px;
							padding:3px 10px;
						}
						
						.StyleTr, .StyleTable
						{
						     padding: 0;
						     margin: 0;
						     border: inset 0pt;
						     border-collapse: 0;
						     border-spacing: 0;
						     border: inset 0;
						     empty-cells: hidden;
						     baseline: 0;
						     middle: 0;
						}
						
						.StyleTd
						{
							 padding: 0;
						     margin: 0;
						}
						
						.StyleLabelTd
						{
							border-top:1px solid white;
							border-bottom:1px solid #eaeaea;
							padding:3px;
							margin:0px;
						}
						
						.StyleTextarea
						{
							border:1px solid #c0c0c0;
							margin:3px;
							padding:3px;
						}
						
						.StyleContainer
						{
							border:1px solid #c0c0c0;
							margin-bottom:10px;
						}

						table.StyleTitle1{ background-color: #777777; }

						.StyleTitle1 td{ 
							color: #FFFFFF; 
							padding: 3px 10px; 
							height: 17px; 
							border-bottom: 1px solid #000000; 
							font-family: Arial,Helvetica,sans-serif; 
							font-weight: bold; 
							font-size: 12px;
						}

						table#InFinaciera thead tr th{
							font-family: Arial; font-size: 13px; 
							height: 26px; 
							background-color: #D7D7D7; 
							border: 1px solid #D7D7D7;
						}

						.StyleTd2{
							background-color: #F6F6F6;
							border: 1px solid #F6F6F6;
							font-family: Arial; font-size: 12px; height: 20px;
						} 
						.StyleTd1{
							background-color: #F0F0F0;
							border: 1px solid #F0F0F0;
							font-family: Arial; font-size: 12px; height: 20px;
						} 

						.inputFocus{
						    background: none repeat scroll 0 0 #FFFFCC;
						    border: 1px solid #990000;
						    color: #990000;
						    font-family: Arial;
						    font-size: 11px;
                			text-align: right;
               
						}
            			select{
						    background: none repeat scroll 0 0 #FFF;
						    border: 1px solid #000;
						    color: gray;
						    font-family: Arial;
						    font-size: 11px;
                			width: 200px;
						}
            			select:focus{
						    background: none repeat scroll 0 0 #FFFFCC;
						    border: 1px solid #990000;
						    color: #990000;
						    font-family: Arial;
						    font-size: 11px;
                			width: 200px;
						}
						.inputText{
						    border: 1px solid #666666;
						    color: black;
						    font-family: Arial;
						    font-size: 11px;
                			text-align: right;
						}
            			.cp, .Button{cursor: pointer;}
			            .Button{
			              color: #444; font-weight: bold; padding: 2px; 8px; background-color: whiteSmoke; border: 1px solid #CCC;
			              font-size: 11px;
			              width: 90px;
			            }
            
						</style>" );

      $mHtml -> Form( array( "action" => "?",  "method" => "POST",  "name" => "formulario", "enctype" => "multipart/form-data" ) );
		  $mHtml -> Table( "class:StyleContainer; align:center; cellpadding:0; cellspacing:0; width:100%" );

      $mHtml -> SetBody(
				"<tr class='StyleTr' id='tag13ID' name='tag13'>
	                <td width='100%' align='center' class='StyleTd' id='tag14ID' name='tag14'>
	                  <table width='100%'' cellspacing='0' cellpadding='0' class='StyleTitle1' id='tag15ID' name='tag15' style='text-align:left'>
	                    <tbody>
	                    	<tr>
	                      		<td width='100%'>FILTROS DE BUSQUEDA</td>
	                    	</tr>
	                    </tbody>
	                  </table>
	                </td>
	             </tr>"
			  );
        
        $mHtml -> Row();
		    $mHtml -> Cell( "name:section1; align:center; width:100%" );
		    $mHtml -> Table( "align:center; width:100%" );		
        
        
        $mHtml -> Row();
        
        $_Html_  = '<td width="15%" align="right" class="StyleLabelTd" id="tag0ID" name="tag0">';
        $_Html_ .= '<label class="StyleLabel" id="tag1ID" name="tag1">* Identificación:</label>';
        $_Html_ .= '</td>';
        
        $_Html_ .= '<td align="center" class="StyleTd" id="tag2ID" name="tag2">';
        $_Html_ .= '<input type="text" onfocus="SetClassName( this, \'StyleTextFocus\' )" class="StyleText" id="cod_tercerID" onkeyup="ClearTercer();" onblur="SetClassName( this, \'StyleText\' ); FormatNumericInput( this ); GetTercer();" onkeypress="return NumericInput( event );" style="width: 90%" maxlength="12" name="cod_tercer" value="'.$_REQUEST[cod_tercer].'">';
        $_Html_ .= '</td>';
        
        $_Html_ .= '<td align="left" class="StyleTd" id="tag3ID" name="tag3" style="width: 10%">';
        $_Html_ .= '<img class="cp" src="../'.DIR_APLICA_CENTRAL.'/images/grid_popup.png" border="0" onclick="PopupTercer();" />';
        $_Html_ .= '</td>';
        
        $_Html_ .= '<td width="25%" align="right" class="StyleLabelTd" id="tag4ID" name="tag4">';
        $_Html_ .= '<label class="StyleLabel" id="tag5ID" name="tag5">* Nombre:</label>';
        $_Html_ .= '</td>';
        
        $_Html_ .= '<td align="center" class="StyleTd" id="tag6ID" name="tag6">';
        $_Html_ .= '<input type="text" onblur="SetClassName( this, \'StyleText\' )" onfocus="SetClassName( this, \'StyleTextFocus\' )" class="StyleText" id="nom_tercerID" onkeyup="SetAbreviatura();" size="25" maxlength="25" name="nom_tercer" style="width: 90%" value="'.$_REQUEST[nom_tercer].'">';
        $_Html_ .= '</td>';
        
        $mHtml -> SetBody($_Html_);
        
        $mHtml -> CloseRow();
      
        $mHtml -> CloseTable();
		

    		$mHtml -> Table( "align:center; width:100%" );		
    		$mHtml -> Row();		
    		$mHtml -> Button( "name:send; align:center; value:Buscar; onclick:AceptarParametros(); ", array( 'style' => 'text-align:center' ) );
    		$mHtml -> CloseRow();
    		$mHtml -> CloseTable();
    
    
    		$mHtml -> CloseRow();
        
        
        if(!empty($_REQUEST[cod_tercer])){
        
        
        
        
        $mHtml -> SetBody(
  				"<tr class='StyleTr' id='tag99ID' name='tag99'>
  	                <td width='100%' align='center' class='StyleTd' id='tag14ID' name='tag14'>
  	                  <table width='100%'' cellspacing='0' cellpadding='0' class='StyleTitle1' id='tag15ID' name='tag15' style='text-align:left'>
  	                    <tbody>
  	                    	<tr>
  	                      		<td width='100%'>HORAS EXTRA PARAMETRIZADAS</td>
  	                    	</tr>
  	                    </tbody>
  	                  </table>
  	                </td>
  	             </tr>"
  			  );
        
        $mHtml -> Row();
		    $mHtml -> Cell( "name:section1; align:center; width:100%" );
		    $mHtml -> Table( "align:center; width:100%" );		
        
        
     
        
        $_CONFHOREXT = $this -> GetConfHorext();
        $_NOMCONCEPT = $this -> GetNominaConcep();
        $_BASICO = $this -> GetBasico($_REQUEST[cod_tercer]);
        $_BASICO = $_BASICO[0][val_deveng];
        
    		$_HTML  = "<table cellspacing='1' id='InFinaciera' cellpadding='1' width='100%' style='border: 1px solid #D7D7D7;'>";
    		$_HTML .= "<thead>";
    		$_HTML .= "<tr>";	
    			$_HTML .= "<th colspan='2' width='18%'>* Fecha Inicial</th>";	
    			$_HTML .= "<th colspan='2' width='18%'>* Fecha Final</th>";	
    			$_HTML .= "<th width='10%'>* No. Horas</th>";	
          $_HTML .= "<th width='18%'>* Tipo de Hora</th>";	
          $_HTML .= "<th width='18%'>* Concepto</th>";	
          $_HTML .= "<th width='15%'>* Valor</th>";	

          $_HTML .= "<th width='8%'>&nbsp;</th>";	
    		$_HTML .= "</tr>";	
    		$_HTML .= "</thead>";
    		$_HTML .= "<tbody>";
        
        $_HOREXTEMPLEA = $this -> GetHorextEmplea($_REQUEST[cod_tercer]);

        
        for($l=0; $l<( count($_HOREXTEMPLEA)==0 ? 1 : count($_HOREXTEMPLEA) ); $l++){
          
        $_HTML .= "<tr class='StyleTd1' id='row".$l."ID'  >";	
        
        $_HTML .= "<td align='center'  class='StyleTd1' colspan='2'>";	
        //$_HTML .= '<input type="text"  size="10" maxlength="10" onkeyup="DateInput( this )" onkeypress="return NumericInput( event );" onblur="FormatDateInput( this );" id="fec_inihor'.$l.'ID" name="fec_inihor'.$l.'" class="inputText required calendar" value="'.$_HOREXTEMPLEA[$l][fec_horini].'">';
        
        $_HTML .= '<input 
                      type="text" 
                      onkeypress="return NumericInput( event )" 
                      onblur="FormatDateInput( this ); 
                      SetClassName( this, \'StyleText\' )" 
                      onkeyup="DateInput( this ); " 
                      maxlength="10" 
                      size="10" 
                      value="'.$_HOREXTEMPLEA[$l][fec_horini].'" 
                      id="fec_inihor'.$l.'ID" 
                      name="fec_inihor'.$l.'" 
                      class="required equals datepicker"
                   >';
                   
        $_HTML .= "</td>";	       
        
        $_HTML .= "<td align='center'  class='StyleTd1' colspan='2'>";	
        $_HTML .= '<input 
                      type="text" 
                      onkeypress="return NumericInput( event )" 
                      onblur="FormatDateInput( this ); 
                      SetClassName( this, \'StyleText\' )" 
                      onkeyup="DateInput( this ); " 
                      maxlength="10" 
                      size="10" 
                      value="'.$_HOREXTEMPLEA[$l][fec_horfin].'" 
                      id="fec_finhor'.$l.'ID" 
                      name="fec_finhor'.$l.'" 
                      class="required equals datepicker"
                   >';
        $_HTML .= "</td>";	
                
        $_HTML .= "<td align='center'  class='StyleTd1'>";	
        $_HTML .= '<input type="text" onkeypress="return NumericInput( event )" onblur="FormatNumericInput( this );" onkeyUp="calValor(this)" class="inputText required" id="num_horext'.$l.'ID" style="text-align:right; " size="5" maxlength="3" name="num_horext'.$l.'" value="'.$_HOREXTEMPLEA[$l][num_horasx].'">';
        $_HTML .= '</td>';
        
        $_HTML .= "<td align='center'  class='StyleTd1'>";	
  			$_HTML .= '<select class="StyleSelect required" name="cod_horext'.$l.'" id="cod_horext'.$l.'ID" style="width: 90%" onchange="calValor(this)">';
  			$_HTML .= '<option value="">--</option>';
  			for ( $c = 0, $len = count($_CONFHOREXT); $c < $len; $c++ ) {
  				$selected = $_CONFHOREXT[$c]['cod_horext'] === $_HOREXTEMPLEA[$l][cod_horext] ? 'selected="selected"' : NULL;
  				$_HTML .= '<option por_horext="'.$_CONFHOREXT[$c]['por_horext'].'" value="'.$_CONFHOREXT[$c]['cod_horext'].'" '.$selected.'>'.$_CONFHOREXT[$c]['nom_horext'].'%</option>';
  			}
  			$_HTML .= '</select>';
  		  $_HTML .= '</td>';
        
        $_HTML .= "<td align='center'  class='StyleTd1'>";	
  			$_HTML .= '<select class="StyleSelect required" name="cod_concep'.$l.'" id="cod_concep'.$l.'ID" style="width: 90%">';
  			$_HTML .= '<option value="">--</option>';
  			for ( $c = 0, $len = count($_NOMCONCEPT); $c < $len; $c++ ) {
  				$selected = $_NOMCONCEPT[$c]['cod_concep'] === $_HOREXTEMPLEA[$l][cod_concep] ? 'selected="selected"' : NULL;
  				$_HTML .= '<option value="'.$_NOMCONCEPT[$c]['cod_concep'].'" '.$selected.'>'.$_NOMCONCEPT[$c]['nom_concep'].'</option>';
  			}
  			$_HTML .= '</select>';
  		  $_HTML .= '</td>';
        
        $_HTML .= "<td align='center'  class='StyleTd1'>";	
        $_HTML .= '<input type="text" readonly="readonly" onkeypress="return NumericInput( event )" onblur="FormatNumericInput( this );"  class="inputText required" id="val_horext'.$l.'ID" style="text-align:right; " size="15" maxlength="15" name="val_horext'.$l.'" value="'.$_HOREXTEMPLEA[$l][val_horext].'" basico="'.$_BASICO.'">';
        $_HTML .= '</td>';
        
        $_HTML .= "<td width='8%' align='center'>".($l!=0 ? '<img border="0" onclick="RemoveGrid(this)" title="Eliminar Registro" src="../'.DIR_APLICA_CENTRAL.'/images/grid_drop.gif" class="cp">' : '&nbsp;')."</td>";
        
        $_HTML .= "</tr>";	
        
        
        }
        
        
        $_HTML .= "</tbody>";
    		$_HTML .= "</table>";
        
        $mHtml -> SetBody(
					"<tr class='StyleTr' id='tag13ID' name='tag13'>
		                <td width='100%' align='center' class='StyleTd' id='tag14ID' name='tag14' colspan='4'>
		                ".$_HTML."
		                </td>
		             </tr>"
				  );		
        
        
        $mHtml -> CloseTable();
		

    		$mHtml -> Table( "align:center; width:100%" );		
    		$mHtml -> Row();		
    		$mHtml -> Button( "name:send; align:center; value:Agregar [+]; onclick:AddGrid(); ", array( 'style' => 'text-align:center' ) );
        $mHtml -> Button( "name:send; align:center; value:Guardar; onclick:Insertar(); ", array( 'style' => 'text-align:center' ) );
    		$mHtml -> CloseRow();
    		$mHtml -> CloseTable();
    
        }
    
    		$mHtml -> CloseRow();
        
        $mHtml -> Hidden( "name:option; value:" );
        $mHtml -> Hidden( "name:size_horext; value:".(count($_HOREXTEMPLEA)>0?count($_HOREXTEMPLEA):1) );
    		$mHtml -> Hidden( "name:tomoe" );
    		$mHtml -> Hidden( "name:window; value:central" );
    		$mHtml -> Hidden( "name:cod_servic; value:$_REQUEST[cod_servic]" );
    		$mHtml -> Hidden( "name:Standar; value:".DIR_APLICA_CENTRAL );
    		
        $mHtml -> CloseTable();
    			
    		$mHtml -> CloseForm();
    		$mHtml -> CloseBody();
    		echo $mHtml -> MakeHtml();
    }
    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
    function GetNovedades()
    {
        $select = "SELECT DATE_FORMAT(fec_inicia, '%Y-%m-%d') AS fec_inicia, 
                          DATE_FORMAT(fec_finalx, '%Y-%m-%d') AS fec_finalx, 
                          DATE_FORMAT(fec_creaci, '%Y-%m-%d') AS fec_creaci
                     FROM " . CONS . ".tab_nomina_noveda
                   GROUP BY 1,2 ORDER BY fec_finalx DESC";
        $select = new Consulta($select, $this->conexion);
        $select = $select->ret_matriz('a');
        return $select;
    }
    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
    function GetHorasExtrasGen($mData)
    {
        $fecha1 = $mData['fec_inicia'];
        $fecha2 = $mData['fec_finalx'];
        $select =  "SELECT a.`cod_tercer`,
                            UPPER(b.`abr_tercer`) AS nom_tercer, 
                            a.`fec_horini`,
                            a.`fec_horfin`,
                            a.`num_horasx`,
                            a.`cod_horext`,
                            a.`val_horext`
                    FROM " . CONS . ".`tab_horext_emplea` AS a
                    INNER JOIN " . CONS . ".`tab_genera_tercer` AS b on a.`cod_tercer`= b.`cod_tercer`
                    WHERE a.`fec_horini`>= '$fecha1'
                    AND a.`fec_horfin`<= '$fecha2'
                    ORDER BY a.`fec_horfin`  DESC";
        $select = new Consulta($select, $this->conexion);
        $select = $select->ret_matriz('a');
        return $select;
    }
    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
    function GetGenera_Horext($cod_horext)
    {
        $select =  "SELECT `nom_horext`,`por_horext` FROM `tab_genera_horext` WHERE `cod_horext` = $cod_horext";
        $select = new Consulta($select, $this->conexion);
        $select = $select->ret_matriz('a');
        $respuesta = NULL;
        foreach ($select as $row) {
            $respuesta = $row['nom_horext'] . ' - ' . $row['por_horext'] . '%';
        }
        return $respuesta;
    }
    // +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
    function GetConfHorext(){
      
        $mSql  = "SELECT a.cod_horext, CONCAT(a.nom_horext, ' - ', a.por_horext) AS nom_horext, a.por_horext  
                      FROM ".CONS.".tab_genera_horext a 
                    ORDER BY 2 ";
          $mSql = new Consulta( $mSql, $this -> conexion );
          return $mSql -> ret_matriz( "a" );
        
      }
      
       function GetNominaConcep(){
        
        $mSql  = "SELECT a.cod_concep, a.nom_concep     
                      FROM ".CONS.".tab_nomina_concep a 
                  WHERE a.ind_deveng = 1
                    AND ( a.nom_concep LIKE '%extra%' OR  a.nom_concep LIKE '%hora%' )
                    ORDER BY 2 ";
          $mSql = new Consulta( $mSql, $this -> conexion );
          return $mSql -> ret_matriz( "a" );
        
      }
      
      function GetBasico($cod_tercer){
        $mSql  = "SELECT b.val_deveng
                      FROM ".CONS.".tab_nomina_concep a, 
                         ".CONS.".tab_nomina_deveng b
                  WHERE a.cod_concep = b.cod_concep
                    AND ( a.nom_concep LIKE '%salario%' )
                    AND b.cod_tercer = '".$cod_tercer."'
                    AND b.ind_basico = 1
                    LIMIT 1 ";
          $mSql = new Consulta( $mSql, $this -> conexion );
          return $mSql -> ret_matriz( "a" );
        
      }
      
      
      function GetHorextEmplea($cod_tercer){
        $mSql  = "SELECT a.cod_tercer,  a.fec_horini, a.fec_horfin, a.num_horasx, a.cod_horext,  a.cod_concep, a.val_horext
                      FROM ".CONS.".tab_horext_emplea a 
                  WHERE a.cod_tercer = '".$cod_tercer."'
                    ORDER BY 2 ";
          $mSql = new Consulta( $mSql, $this -> conexion );
          return $mSql -> ret_matriz( "a" );
      }
      
      
      function Insert($mData){
        
      
        
        $mHtml  = new DinamicHtml();
            $mHtml -> SetJs ( "../".DIR_APLICA_CENTRAL."/js/proveedores.js" );
        
        
        if($mData[size_horext]>0){
          $execute = new Consulta( "SELECT 1", $this -> conexion, 'BR' ); 
          
          $mSql  = "DELETE FROM ".CONS.".tab_horext_emplea 
                    WHERE cod_tercer = '".$mData[cod_tercer]."'";
                    
            $mSql = new Consulta( $mSql, $this -> conexion, 'R' );
          
          $mSql  = "INSERT INTO ".CONS.".tab_horext_emplea 
                       (cod_tercer,  fec_horini, fec_horfin, num_horasx, cod_horext, cod_concep, val_horext) 
                     VALUES ";
                     
          $_VALUES = array();
          
          for($i=0; $i<$mData[size_horext]; $i++){
            $_VALUES[] = "('".$mData[cod_tercer]."', '".$mData['fec_inihor'.$i]."', '".$mData['fec_finhor'.$i]."', '".$mData['num_horext'.$i]."', '".$mData['cod_horext'.$i]."', '".$mData['cod_concep'.$i]."', '".$mData['val_horext'.$i]."' )";
          }
          
          if(count($_VALUES)>0){
            $mSql = new Consulta( $mSql.join(',',$_VALUES), $this -> conexion, 'R' );  
          }       
          
        }
        
        $mHtml -> Body();
          $mHtml -> Form( array( 'action' => '?',  'method' => 'POST', 'name' => 'frm_hojvid' ) );
          $mHtml -> SetBody( '<div style="background: #777777; border-bottom: 1px solid #EEEEEE; color: #EEEEEE; font-weight: bold; font-size: 12px; padding: 3px; text-align: center">
                              INFORMACION DE HORAS EXTRA</div>' );
          $mHtml -> Table( "class:StyleContainer; align:center; cellpadding:0; cellspacing:0; width:85%" );
          $mHtml -> Row();
  
          if ( $consulta = new Consulta( "SELECT 1", $this -> conexion, "RC" ) ){
                $ok  = "Parametrizaci&oacute;n de Horas Extra realizada con &eacute;xito.<br />";
                $ok .= "Proveedor: <font color=\"#990000\">".$mData['abr_tercer']."</font><br />";
                $mHtml -> SetBody( ShowOk( $ok, 14 ) );
            }
     
          $mHtml -> Row();
          $mHtml -> Button( "name:send; align:center; value:Parametrizar; onclick:frm_hojvid.submit()", array( 'style' => 'text-align:center', 'colspan' => 2 ) );
          $mHtml -> CloseRow();
            
          $mHtml -> CloseTable();
          $mHtml -> CloseForm();
          $mHtml -> CloseBody();
          echo $mHtml -> MakeHtml();
        
      }

}

$html = new Horext($this->conexion, $_REQUEST);
?>
