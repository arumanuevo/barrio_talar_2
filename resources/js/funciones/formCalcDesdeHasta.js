import {alertaRoja , alertaVerde, alertaAmarilla, mostrarToast } from './alerta.js';
export default function formCalcDesdeHasta(){
    let diaDesde = $( "#diaDesde" ).val();
    let diaHasta = $( "#diaHasta" ).val();
    //let seccion = $( "#selectorSecciones" ).val();
    //console.log('seccion ', seccion);
    if(diaDesde == ''){
        mostrarToast('ERROR!', 'Defina el dia de inicio.','roja');
        return 'ERROR';
    }
    if(diaHasta == ''){
        mostrarToast('ERROR!', 'Defina el dia final del calculo.','roja');
        return 'ERROR';
    }
   // console.log(diaHasta);
    if(diaDesde > diaHasta){
        mostrarToast('ERROR!', 'El dia de inicio es mayor que el dia final.','roja');
        return 'ERROR';
    }
    let json = {
        diaDesde : diaDesde,
        diaHasta : diaHasta,
       // seccion : seccion     
    }
    return json;
   
}