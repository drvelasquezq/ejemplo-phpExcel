function ShowGrid(des, i, optional) {
    try {
        var title = document.getElementById('title' + String(i) + 'ID');
        var grid = document.getElementById('grid' + String(i) + 'ID');
        if (grid.style.display == 'block' && !optional) {
            grid.style.display = 'none';
            title.innerHTML = '+ ' + des;
            title.title = 'MOSTRAR ' + des;
        } else {
            grid.style.display = 'block';
            title.innerHTML = '- ' + des;
            title.title = 'OCULTAR ' + des;
        }
    } catch (e) {
        alert('ShowGrid: ' + e.message);
    }
}

function InsertConcep() {
    try {
        LockAplication('lock');
        var nom_concep = document.getElementById('nom_concepID');
        var cod_tiposx = document.getElementById('cod_tiposxID');
        var ind_estado = document.getElementById('ind_estadoID');
        var frm_concep = document.getElementById('frm_concepID');
        var cod_tipter = document.getElementById('cod_tipterID');
        if (!nom_concep.value) {
            alert('Ingrese el Nombre del Concepto de Nomina.');
            LockAplication('unlock');
            nom_concep.focus();
            return false;
        }
        if (!cod_tiposx.value) {
            alert('Seleccione el Tipo de Nomina.');
            LockAplication('unlock');
            cod_tiposx.focus();
            return false;
        }

        if (!cod_tipter.value) {
            alert('Seleccione el Tercero de Nomina.');
            LockAplication('unlock');
            cod_tipter.focus();
            return false;
        }

        if (!ind_estado.value) {
            alert('Seleccione el Estado del Concepto de Nomina.');
            LockAplication('unlock');
            ind_estado.focus();
            return false;
        }
        if (confirm('¿Está seguro de Insertar el Concepto de Nomina "' + nom_concep.value + '"?')) {
            document.getElementById('ActionID').value = 'insert';
            document.getElementById('tomoeID').value = '@QMZP@';
            frm_concep.submit();
        }
        LockAplication('unlock');
    } catch (e) {
        alert('InsertConcep: ' + e.message);
    }
}

function UpdateConcep( ) {
    try {
        var frm_concep = document.getElementById('frm_concepID');
        var size_grid = document.getElementById('size_concepID').value;

        for (var s = 0; s < Number(size_grid); s++) {
            if (document.getElementById('cod_concep' + s + 'ID')) {
                var cod_concep = document.getElementById('cod_concep' + s + 'ID');
                if (cod_concep.value == '') {
                    return alert('El Codigo de Concepto es Obligatorio');
                }
            }
            if (document.getElementById('nom_concep' + s + 'ID')) {
                var nom_concep = document.getElementById('nom_concep' + s + 'ID');
                if (nom_concep.value == '') {
                    return alert('El Nombre del Concepto es Obligatorio');
                }
            }
            if (document.getElementById('cod_tipos' + s + 'ID')) {
                var cod_tipos = document.getElementById('cod_tipos' + s + 'ID');
                if (cod_tipos.value == '') {
                    return alert('El Tipo del Concepto es Obligatorio');
                }
            }
            if (document.getElementById('cod_tipter' + s + 'ID')) {
                var cod_tipter = document.getElementById('cod_tipter' + s + 'ID');
                if (cod_tipter.value == '') {
                    return alert('El Tercero del Concepto es Obligatorio');
                }
            }

            if (document.getElementById('ind_estado' + s + 'ID')) {
                var ind_estado = document.getElementById('ind_estado' + s + 'ID');
                if (ind_estado.value == '') {
                    return alert('El Estado del Concepto es Obligatorio');
                }
            }
        }
        if (confirm('¿Está seguro de Actualizar los Conceptos de Nomina ')) {
            document.getElementById('ActionID').value = 'update';
            document.getElementById('tomoeID').value = '@QMZP@';
            frm_concep.submit();
        }
    } catch (e) {
        alert('UpdateConcep ' + e.message);
    }
}

function ParameNominaSubmit()
{
    try {
        LockAplication('lock');

        var frm_parame = document.getElementById('frm_parameID');
        var Action = document.getElementById('ActionID');
        var tomoe = document.getElementById('tomoeID');
        var cod_tiposx = document.getElementById('cod_tiposxID');
        var nom_tiposx = document.getElementById('nom_tiposxID');
        var ind_frepagQ = document.getElementById('ind_frepagQID');
        var ind_frepagM = document.getElementById('ind_frepagMID');
        var fec_pagos1 = document.getElementById('fec_pagos1ID');
        var fec_pagos2 = document.getElementById('fec_pagos2ID');

        if (!cod_tiposx.value) {
            LockAplication('lock');
            alert('Seleccione el Tipo de Nomina.');
            LockAplication('unlock');
            cod_tiposx.focus();
            return false;
        }
        if (!fec_pagos1.value) {
            LockAplication('lock');
            alert('Ingrese o seleccione desde el Calendario Emergente la Fecha de Pago 1.');
            LockAplication('unlock');
            fec_pagos1.focus();
            return false;
        }
        if (ind_frepagQ.checked == true && !fec_pagos2.value) {
            LockAplication('lock');
            alert('Ingrese o seleccione desde el Calendario Emergente la Fecha de Pago 2.');
            LockAplication('unlock');
            fec_pagos2.focus();
            return false;
        }
        for (var g = 1; g <= 6; g++) {
            if (VerifyInputData(String(g)) && !VerifyGrid(String(g))) {
                return false;
            }
        }
        var conf = '¿Está seguro de realizar la Parametrización de los Tipos y Conceptos de Nomina?\n';
        conf += '\nTipo de Nomina: ' + GetLabel('cod_tiposxID');
        if (confirm(conf))
        {
            Action.value = 'insert';
            tomoe.value = '@QMZP@';
            nom_tiposx.value = GetLabel('cod_tiposxID');
            frm_parame.submit();
        } else {
            LockAplication('unlock');
            AjaxLoader('none');
        }

    } catch (e) {
        alert('ConcilSubmit: ' + e.message);
    }

}

function showCheck(i) {
    if (Number(document.getElementById("cod_tipos" + i + "ID").value) == 1) {
        document.getElementById("ind_aplnov" + i + "ID").style.display = 'block';
    } else {
        document.getElementById("ind_aplnov" + i + "ID").style.display = 'none';
        document.getElementById("ind_aplnov" + i + "ID").value = 0;
    }
}