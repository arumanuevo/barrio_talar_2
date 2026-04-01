
//import {leerInspectores , settingsDataTable, inicioMagnificPopup } from './funciones/onReady';
import * as data from '../funciones/seteosGlobales.json';

//import * as moment from 'moment';
import {alertaVerde, consultando, mostrarToast, cartelAlerta } from '../funciones/alerta';
//window.$ = window.jQuery = require('jquery');
//import 'bootstrap';
//import bootbox from 'bootbox';
var objListaMediciones = {    

    display: 
        function () { 
            let idBorrar;
            ///////////////////////
            $(document).on('click', '#btnBorrarMedicion', function(e) {
               
                /*let dnsGeneral = data.default[0].dnsApi;
                let dnsApiAuth = data.default[0].dnsApiAuth;
                let endPoint = data.default[0].endPoints.postBorrarMedicion;   
                let urlConsulta = dnsGeneral + dnsApiAuth + endPoint;
                let token = data.default[0].access_token;*/
                let id = $(this).val();
                let borrar = '{"id" : "'+id+'"}';
                idBorrar = borrar;
              
                $('#ss').modal('show');
               /* bootbox.confirm({
                    title: 'Importante',
                    message: '¿Esta seguro de que quiere borrar el registro?',
                    size: 'medium',
                    locale: 'es',
                    centerVertical: true,
                    callback: function (result) {
                        if (result){
                            consultando(true);
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                    'Content-Type' : 'application/json',
                                    'X-Requested-With' : 'XMLHttpRequest',
                                    //'Authorization' : token
                                },
                                type: "POST", 
                                url: urlConsulta,
                                data: borrar,                               
                                success:function(datos){ 
                                    console.log(datos);
                                     consultando(false);
                                    // cartelAlerta('Transacción Exitosa!', 'Se Eliminó el registro solicitado.');
                                     location.reload();
                                 },
                                 jsonp: true,
                                 jsonpCallback: 'getJson',
                                 crossDomain:true
                            }); //fin ajax

                            
                            //console.log($(this).val());
                        }

                        console.log(result);
                        
                    }
                });*/
                

            });


            $(document).on('click', '#confirmEliminar', function() {
               
               let dnsGeneral = data.default[0].dnsApi;
               let dnsApiAuth = data.default[0].dnsApiAuth;
               let endPoint = data.default[0].endPoints.postBorrarMedicion;   
               //let urlConsulta = dnsGeneral + dnsApiAuth + endPoint;
               let urlConsulta = dnsApiAuth + endPoint;
               let token = data.default[0].access_token;
               console.log('id: ',idBorrar);
               $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Content-Type' : 'application/json',
                    'X-Requested-With' : 'XMLHttpRequest',
                    //'Authorization' : token
                },
                type: "POST", 
                url: urlConsulta,
                data: idBorrar,                               
                success:function(datos){ 
                    console.log(datos);
                     consultando(false);
                    // cartelAlerta('Transacción Exitosa!', 'Se Eliminó el registro solicitado.');
                     location.reload();
                 },
                 jsonp: true,
                 jsonpCallback: 'getJson',
                 crossDomain:true
            }); //fin ajax

             
            });
    
            // Ocultar el modal cuando se hace clic en el botón de cancelar
            $(document).on('click', '[data-dismiss="modal"]', function() {
                $('#ss').modal('hide');
            });
            //////////////////////////

           
            
            $(function() {
               // $("#tablaTodasMediciones").css({"display : none", "color": "white"});
                
               /* $('.parent-container').magnificPopup({
                    delegate: 'a', // child items selector, by clicking on it popup will open
                    type: 'image',
                    titleSrc: 'title',
                    closeOnContentClick : true,
                    image: {
                        tError: '<a>La imagen</a> no pudo ser cargada.'
                      },
                    // other options
                });*/
            });
        }//fin funcion display
}//fin objeto
export default objListaMediciones;