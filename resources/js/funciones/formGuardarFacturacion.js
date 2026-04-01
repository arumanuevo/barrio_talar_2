

import {alertaRoja , alertaVerde, alertaAmarilla, mostrarToast, cartelAlerta } from './alerta.js';
export default function formGuardarFacturacion(){
    
    let diaDesde = $( "#diaDesde" ).val();
    let diaHasta = $( "#diaHasta" ).val();
    
    let totalPesos = $( "#totalPesos" ).val();
    //let porcentaje = $( "#valorFijo" ).val();
    let cantLotes = $( "#cantLotes" ).val();
    //let seccion = $( "#selectorSecciones" ).val();
    //console.log('sksjksks ', seccion);
    let facturaAysa = $( "#facturaAysa" ).val();
    let vencimientoAysa = $( "#vencimientoAysa" ).val();
   // let aProrratear = $( "#aProrratear" ).val();
   /* let valorFijo = $( "#valorFijo" ).val();
    let fijoPesos = $( "#fijoPesos" ).val();
    let fijoxLote = $( "#fijoxLote" ).val();
    let metrosPeriodo = $( "#metrosPeriodo" ).val();
    let valorxMetro = $( "#valorxMetro" ).val();*/

    if(facturaAysa == '' || vencimientoAysa == '' || totalPesos == ''){
        return 'ERROR';
    }
    let json = {

        cantLotes : cantLotes,
        facturaAysa : facturaAysa,
        vencimientoAysa : vencimientoAysa,
        //seccion : seccion,
        diaDesde : diaDesde,
        diaHasta : diaHasta    
    }
    console.log(json);
   /* let json = '{\
        "totalPesos" : "totalPesos",\
        "porcentaje" : "porcentaje",\
        "cantLotes" : "cantLotes",\
        "facturaAysa" : "facturaAysa",\
        "vencimientoAysa" : "vencimientoAysa",\
        "aProrratear" : "aProrratear",\
        "valorFijo" : "valorFijo",\
        "fijoPesos" : "fijoPesos",\
        "fijoxLote" : "fijoxLote",\
        "metrosPeriodo" : "metrosPeriodo",\
        "valorxMetro" : "valorxMetro",\
        "diaDesde" : "diaDesde",\
        "diaHasta" : "diaHasta"}';*/
    return json;
   
}