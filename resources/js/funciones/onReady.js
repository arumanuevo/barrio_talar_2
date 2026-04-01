
import * as data from './seteosGlobales.json';

function leerInspectores(){
   
    let dns = data.default[0].dnsApiAuth;
    let endPoint = data.default[0].endPoints.getInspectores;
   // let urlConsulta = dns+'getInspectores';
    let urlConsulta = dns + endPoint;
    let token = data.default[0].access_token;
    $.ajax({ 
        jsonp: true,
        jsonpCallback: 'getJson',
        type: 'GET',
            xhrFields: {
            withCredentials: false
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Content-Type' : 'application/json',
            'X-Requested-With' : 'XMLHttpRequest',
            'Authorization' : token
        },
        url: urlConsulta,
        crossDomain:true,
        async: true,                        
        success: function(data) {  
          
            if (data.msg != null){
                console.log(data.msg);
                //let numMedidor = data.msg.medidor;
               // $( "#codMedidor" ).text( numMedidor );
            }else{
               // $( "#codMedidor" ).text( 'N/A' );
            }
           
        } //fin sucess

      }); //fin ajax
      
} //fin leer inspectores
const FROM_PATTERN = 'YYYY-MM-DD';
const TO_PATTERN   = 'DD/MM/YYYY';
function settingsDataTable(){
    $('#tablaTodasMediciones').DataTable( {
        "scrollX": true,
        columnDefs: [{
            render: $.fn.dataTable.render.moment(FROM_PATTERN, TO_PATTERN),
            targets: 1
          }],
        "order": [[ 3, "desc" ]]
    } );
} //fin settings data table

function formatDate2(date, zona){
    let dia = new Date(date);
    let diaCorregido = new Date( dia.getTime() - dia.getTimezoneOffset() * -60000 );
    var d = new Date(diaCorregido),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();
    
    if (month.length < 2) 
        month = '0' + month;
    if (day.length < 2) 
        day = '0' + day;
    
    switch (zona) {
        case 'es':
            return [day, month, year].join('-');
            break;
        case 'en':
            return [year, month, day].join('-');
            break;
        default:
            break;
    }
} //fin formatdate2

function formatDate(date, zona) {
   
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();
    
    if (month.length < 2) 
        month = '0' + month;
    if (day.length < 2) 
        day = '0' + day;
    
    switch (zona) {
        case 'es':
            return [day, month, year].join('-');
            break;
        case 'en':
            return [year, month, day].join('-');
            break;
        default:
            break;
    }
    //return [year, month, day].join('-');
}
function diferenciaDias(fechaMedicion,fechaAnterior){
    let dif = Math.floor((Date.parse(fechaMedicion) - Date.parse(fechaAnterior)) / 86400000);
    return dif;
}
function addDays(date, days) {
    const copy = new Date(Number(date))
    copy.setDate(date.getDate() + days)
    return copy
}
function calcularVencimiento(){
    
    let fechaAnt = $( "#tomaAnt" ).val();
    let fechaMedicion = $( "#fechaToma" ).val();
    let periodo = $( "#periodo" ).val();
    let difDias = diferenciaDias(fechaMedicion,fechaAnt);
    console.log(difDias);
    //let diferenciaDias = Math.floor((Date.parse(fechaMedicion) - Date.parse(fechaAnt)) / 86400000);
    if (isNaN(difDias)){ //en caso de la primera medicion del año
        console.log('es la primera toma del añoooo');

        fechaAnt = fechaMedicion;
    }else{
       
        $("#tomaAnt").attr("disabled", true); 
    };

    let diaMedicion = new Date(fechaMedicion);
    let diaCorregido = new Date( diaMedicion.getTime() - diaMedicion.getTimezoneOffset() * -60000 );
    let vencimiento =  addDays(diaCorregido, parseInt(periodo));

   /* let diaMedicion = new Date(fechaMedicion);
    let vencimiento = new Date();
    vencimiento.setDate(diaMedicion.getDate() + parseInt(periodo) + 1);*/
    let fechaVencimiento = formatDate(vencimiento,'en');
    return fechaVencimiento;
}
function todayFecha(){
    var today = new Date();
    var dd = ("0" + (today.getDate())).slice(-2);
    var mm = ("0" + (today.getMonth() +　1)).slice(-2);
    var yyyy = today.getFullYear();
    today = yyyy + '-' + mm + '-' + dd ;
    $( "#fechaToma" ).val(today);
   
    return today;   
} //fin today fecha
function setNombreFoto(){
   // let fechaMedicion =todayFecha();
    let codLote = $( "#selectorLotes option:selected" ).val();
    let fechaToma = $( "#fechaToma" ).val();
  /*  let diaMedicion = new Date(fechaToma);
    let diaCorregido = new Date( diaMedicion.getTime() - diaMedicion.getTimezoneOffset() * -60000 );   */
    let diaCorregido = formatDate2(fechaToma, 'es');
   // $( "#foto" ).val(codLote+'_'+formatDate(diaCorregido,'es'));
    $( "#foto" ).val(codLote+'_'+ diaCorregido);
}


function setCantidadLotes(){
    console.log('asass');
    let dns = data.default[0].dnsApiAuth;
    let endPoint =  data.default[0].endPoints.getLotes;
    let urlConsulta = dns + endPoint;
    let token = data.default[0].access_token;

    $.ajax({ 
        jsonp: true,
        jsonpCallback: 'getJson',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Content-Type' : 'application/json',
            'X-Requested-With' : 'XMLHttpRequest',
            'Authorization' : token
        },
        type: 'GET',
            xhrFields: {
            withCredentials: false
        },
        url: urlConsulta,
        crossDomain:true,
        async: false,                        
        success: function(data) {   
            
            if (data.msg != null){
                let cantRegistros = data.msg.length;
                $( "#cantLotes" ).val( cantRegistros );
             
            }else{
              
            }
           
        } //fin sucess

      }); //fin ajax
}
function inicioMagnificPopup(){
    $('.fotoMedidor').magnificPopup({
        type: 'image'
        // other options
    });
}

function calcularFechaSumandoPeriodo(periodo, fechaMedicion) {
    // Asegurarnos de que los valores estén correctamente formateados
    if (!periodo || !fechaMedicion) {
        console.error("Periodo o fecha de medición no proporcionados");
        return null;
    }

    // Convertir fechaMedicion a un objeto Date
    let fechaBase = new Date(fechaMedicion);

    if (isNaN(fechaBase)) {
        console.error("Fecha de medición no válida");
        return null;
    }

    // Sumar días del periodo
    fechaBase.setDate(fechaBase.getDate() + parseInt(periodo));

    // Formatear la fecha resultante en YYYY-MM-DD para el input date
    let anio = fechaBase.getFullYear();
    let mes = (fechaBase.getMonth() + 1).toString().padStart(2, '0'); // Los meses son de 0 a 11
    let dia = fechaBase.getDate().toString().padStart(2, '0');

    return `${anio}-${mes}-${dia}`;
}
export {leerInspectores, settingsDataTable, 
    todayFecha, formatDate, calcularVencimiento,
     diferenciaDias, setNombreFoto, setCantidadLotes,
    inicioMagnificPopup, formatDate2, calcularFechaSumandoPeriodo};