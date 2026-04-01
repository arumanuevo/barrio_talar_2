import * as data from './funciones/seteosGlobales.json';
var objListaLoteMedidor = {    

    display: 
        function () { 

            $(function() {
                
                $(document).on('click', '.delete-lote', function (event) {
                    event.preventDefault();
                
                    var loteId = $(this).data('lote-id');
                    let datos = { id : loteId};
                    console.log(datos);
                    if (confirm('¿Estás seguro de que deseas borrar este lote?')) {
                        let csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;
                
                        let dnsGeneral = data.default[0].dnsApi;
                        let dnsApiAuth = data.default[0].dnsApiAuth;
                        //let endPoint =  'borrarLote/lotes/' + loteId; 
                        let endPoint =  'borrarLote'; 
                        let urlConsulta = dnsGeneral + dnsApiAuth + endPoint;
                        console.log(urlConsulta);
                        let token = data.default[0].access_token;
                        $.ajax({
                            headers: {
                               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                'Content-Type' : 'application/json',
                                'X-Requested-With' : 'XMLHttpRequest',
                               
                            },
                            type: "GET",
                            xhrFields: {
                                withCredentials: true
                            },
                            url: urlConsulta,
                            data: datos,
                            success:function(datos){ 
                               //  console.log(datos);
                                /* consultando(false);
                                 $("#ajaxform")[0].reset();
                                 cartelAlerta('Transacción Exitosa!', 'Se realizó el almacenamiento de la medición');*/
                                 setTimeout(() => {
                                    location.reload();
                                }, 500);
        
                             },
                             jsonp: true,
                             jsonpCallback: 'getJson',
                        }); //fin ajax
              
                    //});
                        /*fetch(urlConsulta, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            }
                        })
                        .then(response => {
                            if (response.ok) {
                                console.log('Lote borrado con éxito');
                                // Recarga la página después de un breve tiempo (por ejemplo, 500ms)
                                setTimeout(() => {
                                    window.location.reload();
                                }, 500);
                            } else {
                                console.error('Error al borrar el lote');
                            }
                        })
                        .catch(error => {
                            console.error('Error al borrar el lote', error);
                        });*/
                    }
                });
                
           
              /*  $(document).on('click', '#pruebaApi', function(event) {
                    let dns = data.default[0].dnsApiAuth;
                    let endPoint = data.default[0].endPoints.getPrueba;
                    let urlConsulta = dns+endPoint;
                    let token = data.default[0].access_token;

                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Content-Type' : 'application/json',
                            'X-Requested-With' : 'XMLHttpRequest',
                            'Authorization' : token
                        },
                        beforeSend: function (xhr) {
                           // xhr.setRequestHeader('Authorization', 'Bearer t-7614f875-8423-4f20-a674-d7cf3096290e');
                        },
                        type: "GET", 
                        url: urlConsulta,
                       // data: 'nada', 
                        async: true, 
                        success:function(datos){ 
                            console.log(datos);
                        }
                    });
                });*/

                /*$('#tblListaLoteMedidor').DataTable( {
                    "scrollX": true,   
                    "pagingType": "numbers",  
                    language: {
                        "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
                    },    
                    //"order": [[ 1, "desc" ]]
                } );*/

               /* $('#tblListaLoteMedidor').DataTable({
                    "serverSide": true,
                    "ajax": {
                        "url": "ListaCompletaLotes",
                        "type": "GET",
                    },
                    "columns": [
                        { "data": "lote" },
                        { "data": "medidor" },
                        { "data": "email" },
                        { "data": "ocupacion" },
                        { "data": "nombre" }
                    ],
                    "pagingType": "numbers",
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
                    }
                });*/

                /*$('#tblListaLoteMedidor').DataTable({
                    "serverSide": true,
                    "ajax": {
                        "url": "{{ route('ListaCompletaLotes') }}", // Ruta de tu controlador
                        "type": "GET",
                    },
                    "columns": [
                        { "data": "lote" },
                        { "data": "medidor" },
                        { "data": "email" },
                        { "data": "ocupacion" },
                        { "data": "nombre" }
                    ],
                    "pagingType": "full_numbers", // Utiliza paginación completa
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
                    }
                 });*/
                 
                
            });

        }//fin funcion display
}//fin objeto
export default objListaLoteMedidor;