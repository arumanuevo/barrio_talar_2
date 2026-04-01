import * as data from '../funciones/seteosGlobales.json';
import {alertaVerde, consultando, mostrarToast, cartelAlerta } from '../funciones/alerta';
import formGuardarFacturacion from '../funciones/formGuardarFacturacion';
var objGuardarFacturacion = { 

    display: 
        function () { 
           
           
            $(document).on('click', '#btnFacturacion', function(event) {
                event.preventDefault()
                
               // callback(){//stuff that happens when they click Ok.}
                let dnsGeneral = data.default[0].dnsApi;
                let dnsApiAuth = data.default[0].dnsApiAuth;
                let endPoint = data.default[0].endPoints.postGuardarFacturas;  
               // let endPoint = data.default[0].endPoints.postSanti;  
                let urlConsulta =dnsApiAuth + endPoint;
                
                let token = data.default[0].access_token;
               
                let jsonForm = formGuardarFacturacion();
                console.log(jsonForm);
                if(jsonForm == 'ERROR'){
                    alert('Debe completar todos los datos del arqueo para poder emitir una factura.');
                    //cartelAlerta('Error', 'Debe completar todos los datos para poder emitir una factura.');
                    return;
                }
               
             
                var datosFormateadosPost = [];
                let fijoVariable = parseFloat($("#fijovariable").val());
                
                datosFormateadosPost = JSON.stringify({ datos: data.default[0].datosCSVSum,
                    factN : jsonForm.facturaAysa, vAysa : jsonForm.vencimientoAysa, seccion : jsonForm.seccion,
                    Desde : jsonForm.diaDesde, Hasta : jsonForm.diaHasta, FijoVariable : fijoVariable, CantLotes : jsonForm.cantLotes });
               //console.log('med: ', data.default[0].datosCSVSum);
                console.log('med: ', datosFormateadosPost);
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
                    data: datosFormateadosPost,
                 
                    success:function(datos){ 
                        console.log(datos);
                         consultando(false);
                         alert('Transaccion exitosa: Se realizó el almacenamiento de la facturacion');
                        // cartelAlerta('Transacción Exitosa!', 'Se realizó el almacenamiento de la facturacion');
                         $("#btnFacturacion").attr("disabled", true);
                     },
                    error: function (request, status, error) {
                        //console.log(request.responseText);
                        console.log(error);
                    },
                     jsonp: true,
                     jsonpCallback: 'getJson',
                     crossDomain:true
                }); //fin ajax

            });
        }

}

export default objGuardarFacturacion;