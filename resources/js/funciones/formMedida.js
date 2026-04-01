
import {alertaRoja , alertaVerde, alertaAmarilla, mostrarToast, cartelAlerta } from './alerta.js';
import {calcularVencimiento, diferenciaDias, formatDate } from './onReady';

export default function formMedida(){
    let codLote = $( "#selectorLotes option:selected" ).val();
    //codLote = 55;
    let codMedidor = $( "#codMedidor" ).val();
    let fechaAnt = $( "#tomaAnt" ).val();
    let tomaAnterior = $( "#tomaAnterior" ).val();
    let fechaMedicion = $( "#fechaToma" ).val();
    let nombreFoto = $( "#foto" ).val();
    
    
   /* let objFechaMedicion = new Date(fechaMedicion);
    let diaCorregido = new Date( objFechaMedicion.getTime() - objFechaMedicion.getTimezoneOffset() * -60000 );
    fechaMedicion = formatDate(diaCorregido, 'es');*/
   // let dia = new Date(fechaMedicion);
    
    //console.log(fechaMedicion);
    let periodo = $( "#periodo" ).val();
    let valorMedido = $( "#valorMedido" ).val();
    //let inspector = $( "#selectorInspector option:selected" ).val();
    let inspector = $( "#inspector" ).val();
    let difDias = diferenciaDias(fechaMedicion,fechaAnt);
   
    if (isNaN(difDias)){ //en caso de la primera medicion del año
        difDias = periodo;
        fechaAnt = fechaMedicion;
    };
  /*  codLote== 'Lote...' ? $('#trLote').css({ 'background-color' : '#f8d7da' }) : $('#trLote').css({ 'background-color' : '#d4edda' });
    codMedidor== 'N/A' ? $('#trMedidor').css({ 'background-color' : '#f8d7da' }) : $('#trMedidor').css({ 'background-color' : '#d4edda' });
    fechaMedicion== '' ? $('#trFecha').css({ 'background-color' : '#f8d7da' }) : $('#trFecha').css({ 'background-color' : '#d4edda' });
    valorMedido== '' ? $('#trTomaActual').css({ 'background-color' : '#f8d7da' }) : $('#trTomaActual').css({ 'background-color' : '#d4edda' });
    inspector== 'N/A' ? $('#trInspector').css({ 'background-color' : '#f8d7da' }) : $('#trInspector').css({ 'background-color' : '#d4edda' });*/
  /*  let diferenciaDias = Math.floor((Date.parse(fechaMedicion) - Date.parse(fechaAnt)) / 86400000);
    if (diferenciaDias == NaN){ //en caso de la primera medicion del año
        diferenciaDias = periodo;
        fechaAnt = fechaMedicion;
    }

    console.log(diferenciaDias);
    let diaMedicion = new Date(fechaMedicion);
    let vencimiento = new Date();
    vencimiento.setDate(diaMedicion.getDate() + 30);
    let fechaVencimiento = formatDate(vencimiento);*/
  
    let vencimiento = calcularVencimiento();
    $( "#vencimiento" ).val(vencimiento);

    if( codMedidor == 'N/A' || fechaMedicion == '' || valorMedido == '' || inspector == '' || diferenciaDias < periodo ){
        return 'ERROR';
    }
    if(codLote == 'N/A'){
       // mostrarToast('ERROR!', 'Defina el lote.','roja');
        //cartelAlerta('ERROR!', 'Defina el lote.');
        alert('ERROR!', 'Defina el lote.');
        return 'ERROR';
    }
    if(nombreFoto == 'N/A'){
       // mostrarToast('ERROR!', 'Defina la fotografia de la medicion.','roja');
       // cartelAlerta('ERROR!', 'Defina la fotografia de la medicion.');
        alert('Defina la fotografia de la medicion', 'Defina la fotografia de la medicion.');
        return 'ERROR';
    }
    
    if(tomaAnterior == 'N/A'){
        // mostrarToast('ERROR!', 'Defina la fotografia de la medicion.','roja');
         //cartelAlerta('ERROR!', 'Defina el valor de la toma anterior.');
         alert('Defina el valor de la toma anterior.', 'Defina el valor de la toma anterior.');
         $("#tomaAnterior").attr("disabled", false); 
         return 'ERROR';
     }else{
      //  $("#tomaAnterior").attr("disabled", true); 
     }
    if(difDias < periodo ){
        //alertaRoja('Error en la carga : ',"No ha vencido la toma anterior. Verifique el periodo.");
       // mostrarToast('Error!', 'No ha vencido la toma anterior. Verifique el periodo.','roja');
        //cartelAlerta('Error!', 'No ha vencido la toma anterior. Verifique el periodo.');
        alert('No ha vencido la toma anterior. Verifique el periodo.', 'No ha vencido la toma anterior. Verifique el periodo.');
        $( "#fechaToma" ).attr("disabled", false);
        $("#tomaAnt").attr("disabled", false); 
        return 'ERROR';
    }else{
       // alertaVerde('Exito : ','Carga Exitosa!')
       $("#tomaAnt").attr("disabled", true); 
        let _token   = $('meta[name="csrf-token"]').attr('content');
      /*  let json = {
            lote : codLote,
            medidor : codMedidor,
            periodo : periodo,
            fechaAnt : fechaAnt,
            tomaAnterior : tomaAnterior,
            vencimiento : vencimiento,
            fechaMedicion : fechaMedicion,
            valorMedido : valorMedido,
            inspector : inspector,
            foto: nombreFoto          
        }*/
        let json = '{\
            "lote" : "'+codLote+'",\
            "medidor" : "'+codMedidor+'",\
            "periodo" : "'+periodo+'",\
            "fechaAnt" : "'+fechaAnt+'",\
            "tomaAnterior" : "'+tomaAnterior+'",\
            "vencimiento" : "'+vencimiento+'",\
            "fechaMedicion" : "'+fechaMedicion+'",\
            "valorMedido" : "'+valorMedido+'",\
            "inspector" : "'+inspector+'",\
            "foto": "'+nombreFoto+'"}';
        let data = {
                lote: codLote,
                medidor: codMedidor,
                periodo: periodo,
                fechaAnt: fechaAnt,
                tomaAnterior: tomaAnterior,
                vencimiento: vencimiento,
                fechaMedicion: fechaMedicion,
                valorMedido: valorMedido,
                inspector: inspector,
                foto: nombreFoto
            };
        return data;
        //return json;
    }
    
}
