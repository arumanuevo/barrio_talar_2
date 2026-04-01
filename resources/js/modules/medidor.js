
//import Webcam from 'webcam-easy';
import formMedida from '../funciones/formMedida.js';
//import formMedida from './funciones/formMedida.js';
//import leerInspectores from './funciones/onReady';
//import settingsDataTable from './funciones/onReady';
import {calcularVencimiento, leerInspectores , 
    settingsDataTable, todayFecha, setNombreFoto } from '../funciones/onReady';
import {alertaVerde, cartelAlerta, consultando, mostrarToast} from '../funciones/alerta';
import * as data from '../funciones/seteosGlobales.json';
//window.$ = window.jQuery = require('jquery');
var objMedidor = {    
    // <-- add this line to declare the object
    display: 
        function () { 

            todayFecha();
            $("#webcam-control").addClass("d-none");
          
           // mostrarToast('Transacción Exitosa!', 'Se realizó el almacenamiento de la medición','verde');
            let vencimiento = calcularVencimiento();
            console.log('---------');
            console.log(vencimiento);
            $( "#vencimiento" ).val(vencimiento);
            $( "#ajaxform" ).on( "submit", function( event ) {
                event.preventDefault();
                console.log('prevengo');
            });
       
           
            $(document).on('change', '#sinFoto', function() {
               
                if(this.checked){
                    $( "#foto" ).val('Sin foto');
                    $('#btnActivarCamara').attr('disabled', true);
                }else{
                    $( "#foto" ).val('N/A');
                    $('#btnActivarCamara').attr('disabled', false);
                }
               // $( "#foto" ).val('Sin foto');
            });

            $(document).on('click', '#btnGuardarMedicion', function(event) {
             
                let valorMedido = parseFloat($('#valorMedido').val());
                let tomaAnterior = parseFloat($('#tomaAnterior').val());
                if (valorMedido < tomaAnterior) {
                    alert('El Valor Medido debe ser mayor que la Toma Anterior.');
                    return false;
                };
                let dns = data.default[0].dnsApiAuth;
                let endPoint = data.default[0].endPoints.postMed;
                let urlConsulta = dns + endPoint;
                let token = data.default[0].access_token;
               
                let resultado = formMedida(); //para el get
                
                let jsonResultado = JSON.stringify(resultado); //para el post
 
                consultando(true);
                console.log(resultado);
                if (resultado == 'ERROR'){consultando(false); return };
                
               // return;
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Content-Type' : 'application/json',
                        'X-Requested-With' : 'XMLHttpRequest',
                        'Authorization' : token
                    },
                    type: "POST",
                    xhrFields: {
                    withCredentials: false
                    },
                    url: urlConsulta,
                    data:  jsonResultado, 
                    success:function(datos){ 
                        
                         console.log(datos);
                         consultando(false);
                         $("#ajaxform")[0].reset();
                         alert('La medicion se almaceno con exito!');
                         //cartelAlerta('Transacción Exitosa!', 'Se realizó el almacenamiento de la medición');
                       // const confirmModal = new bootstrap.Modal(document.getElementById('modalExito'), {});
                       // confirmModal.show();

                     },
                     jsonp: true,
                     jsonpCallback: 'getJson',
                }); //fin ajax

            });//fin evento boton guardar medicion

            $(document).on('change', '#fechaToma', function() {
                let vencimiento = calcularVencimiento();
                $( "#vencimiento" ).val(vencimiento);
                
            });
            $(document).on('change', '#periodo', function() {
                let vencimiento = calcularVencimiento();
                $( "#vencimiento" ).val(vencimiento);
            })

           /* $(document).on('change', '#selectorLotes', function() {
                console.log('sjsjsjs');
            });*/
            $(document).on('change', '#selectorLotes', function() {
                var authToken = document.getElementById('bearerToken').value;
                console.log('token: ',authToken);
                var apiToken = $('meta[name="api-token"]').attr('content');
                console.log('api token: ', apiToken);
               // var apiToken = '{{ $token }}';
       // console.log('Bearer Token:', apiToken);
                let dns = data.default[0].dnsApiAuth;
                let endPoint = data.default[0].endPoints.getMedidor;
                let idLote = $(this).val();
                let urlConsulta = dns + endPoint + '?numLote='+idLote;
                let token = data.default[0].access_token;
                console.log(token);
                consultando(true);
              
                if(idLote=='N/A'){
                    $("#ajaxform")[0].reset();
                    $('#btnActivarCamara').attr('disabled', true);
                    consultando(false);
                    $( "#periodo" ).val(30);
                    $( "#tomaAnt" ).val( 'dd-mm-aaaa' );
                    let vencimiento = calcularVencimiento();
                    $( "#vencimiento" ).val(vencimiento);
                    $('#alertaRoja').alert('close');
                    $('#btnGuardarMedicion').attr('disabled', true);
                    todayFecha();
                }else{
                    $('#btnActivarCamara').attr('disabled', false);
                    $("#fechaToma").attr("disabled", false);
                    $('#btnGuardarMedicion').attr('disabled', false);
                }

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
                            let numMedidor = data.msg.medidor;
                            $( "#codMedidor" ).val( numMedidor );
                            consultando(false);
                                                   
                            try {                        
                                let periodo = data.ultimaMedicion.periodo;   
                                let fechaAnterior = data.ultimaMedicion.fecha;    
                                let tomaAnterior = data.ultimaMedicion.valormedido;       //nota: en este punto sin haber almacenado la nueva medicion la toma anterior es la "actual"             
                                let vencimiento = calcularVencimiento();
                                $("#tomaAnt").attr("disabled", true);  
                                $("#tomaAnterior").attr("disabled", true);                            
                                $( "#vencimiento" ).val(vencimiento);
                                $( "#periodo" ).val( periodo );
                                $( "#tomaAnt" ).val( fechaAnterior );
                                $( "#tomaAnterior" ).val( tomaAnterior );
                                
                                todayFecha();
                            } catch (error) { //no hay registros cargados de este lote
                                $("#tomaAnt").attr("disabled", false);
                                $("#tomaAnterior").attr("disabled", false); 
                                $( "#periodo" ).val(30);
                                $( "#tomaAnt" ).val( 'dd-mm-aaaa' );
                                $( "#foto" ).val( 'N/A' );
                               // $( "#tomaAnterior" ).val( 'N/A' );
                                let vencimiento = calcularVencimiento();
                                $( "#vencimiento" ).val(vencimiento);
                                $('#alertaRoja').alert('close');
                                todayFecha();
                            }
                           
                        }else{
                           
                            $( "#codMedidor" ).val( 'N/A' );
                            //$( "#periodo" ).val(30);
                        }
                       
                    } //fin sucess
            
                  }); //fin ajax
             
            }); 

    

        }//fin funcion display
}//fin objeto
export default objMedidor;