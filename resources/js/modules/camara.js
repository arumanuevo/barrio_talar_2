
import Webcam from 'webcam-easy';
import formMedida from '../funciones/formMedida.js';
//import formMedida from './funciones/formMedida.js';
//import leerInspectores from './funciones/onReady';
//import settingsDataTable from './funciones/onReady';
import {calcularVencimiento, leerInspectores , settingsDataTable, todayFecha, setNombreFoto } from '../funciones/onReady';
import {alertaVerde, consultando, mostrarToast, cartelAlerta } from '../funciones/alerta';
import * as data from '../funciones/seteosGlobales.json';
//window.$ = window.jQuery = require('jquery');
var objCamara = {    
    // <-- add this line to declare the object
    display: 
        function () { 

            const webcamElement = document.getElementById('webcam');

            const canvasElement = document.getElementById('canvas');

            const snapSoundElement = document.getElementById('snapSound');

            const webcam = new Webcam(webcamElement, 'user', canvasElement, snapSoundElement);
            
            
            $(document).on('change', '#webcam-switch', function() {
                if(!this.checked){
                    cameraStopped();
                    webcam.stop();
                    console.log("webcam stopped");
                }
            });
            $(document).on('click', '#btnActivarCamara', function() {
            
               // if(this.checked){
                    $('.md-modal').addClass('md-show');
                    webcam.start()
                        .then(result =>{
                           cameraStarted();
                           console.log("webcam started");
                           $('#webcam-switch').prop("checked", true).trigger('change');
                         /*  setNombreFoto();
                           let nombreFoto = $( "#foto" ).val();
                           $('#download-photo').attr("download", nombreFoto);*/
                        })
                        .catch(err => {
                            displayError();
                        });
               // }
             /*   else {        
                    cameraStopped();
                    webcam.stop();
                    console.log("webcam stopped");
                }  */      
            });
         
            $(document).on('click', '#cameraFlip', function() {
                webcam.flip();
                webcam.start(); 
            });
         
            $(document).on('click', '#closeError', function() {
                //$('#webcam-switch').prop("checked", false).trigger('change');
            });
        
            function displayError(err = ''){
                if(err!=''){
                    $("#errorMsg").html(err);
                }
                $("#errorMsg").removeClass("d-none");
            }
            
            function cameraStarted(){
                $("#errorMsg").addClass("d-none");
                $('.flash').hide();
                $("#webcam-caption").html("Activada");
                $("#webcam-control").removeClass("d-none");
                $("#webcam-control").removeClass("webcam-off");
                $("#webcam-control").addClass("webcam-on");
                $(".webcam-container").removeClass("d-none");
                if( webcam.webcamList.length > 1){
                    $("#cameraFlip").removeClass('d-none');
                }
                $("#wpfront-scroll-top-container").addClass("d-none");
                window.scrollTo(0, 0); 
              /*  $('body').css('overflow-y','hidden');*/
            }
            
            function cameraStopped(){
                $("#errorMsg").addClass("d-none");
                $("#wpfront-scroll-top-container").removeClass("d-none");
                $("#webcam-control").removeClass("webcam-on");
                $("#webcam-control").addClass("webcam-off");
                $("#cameraFlip").addClass('d-none');
                $(".webcam-container").addClass("d-none");
                $("#webcam-caption").html("Click to Start Camera");
                $('.md-modal').removeClass('md-show');
                $("#webcam-control").addClass("d-none");
            }
            
            $(document).on('click', '#take-photo', function() {
                beforeTakePhoto();
                let picture = webcam.snap();
                document.querySelector('#download-photo').href = picture;
                afterTakePhoto();
            });
           /* $("#take-photo").click(function () {
                beforeTakePhoto();
                let picture = webcam.snap();
                document.querySelector('#download-photo').href = picture;
                afterTakePhoto();
               
            });*/
            
            function beforeTakePhoto(){
                $('.flash')
                    .show() 
                    .animate({opacity: 0.3}, 500) 
                    .fadeOut(500)
                    .css({'opacity': 0.7});
                window.scrollTo(0, 0); 
                $('#webcam-control').addClass('d-none');
                $('#cameraControls').addClass('d-none');
            }
           
            function afterTakePhoto(){
                webcam.stop();
                $('#canvas').removeClass('d-none');
                $('#take-photo').addClass('d-none');
                $('#exit-app').removeClass('d-none');
                $('#download-photo').removeClass('d-none');
                $('#resume-camera').removeClass('d-none');
                $('#cameraControls').removeClass('d-none');
                setNombreFoto();
                $("#fechaToma").attr("disabled", true); //esto obliga a no poder cambiar la fecha de toma luego de sacar la foto, para que no quede el nombre mal
                let nombreFoto = $( "#foto" ).val();
                console.log(nombreFoto);
                $('#download-photo').attr("download", nombreFoto);
            }
            
            function removeCapture(){
                $('#canvas').addClass('d-none');
                $('#webcam-control').removeClass('d-none');
                $('#cameraControls').removeClass('d-none');
                $('#take-photo').removeClass('d-none');
                $('#exit-app').addClass('d-none');
                $('#download-photo').addClass('d-none');
                $('#resume-camera').addClass('d-none');
            }
            $(document).on('click', '#resume-camera', function() {
                $("#webcam-control").addClass("d-none");
                webcam.stream()
                .then(facingMode =>{
                    removeCapture();
                });
            });
           /* $("#resume-camera").click(function () {
                webcam.stream()
                    .then(facingMode =>{
                        removeCapture();
                    });
            });*/
            $(document).on('click', '#resume-camera', function() {
                removeCapture();
               // $("#webcam-switch").prop("checked", false).change();
                $('#webcam-switch').prop("checked", false).trigger('change');
            });
            $(document).on('click', '#exit-app', function() {
                removeCapture();
               // $("#webcam-switch").prop("checked", false).change();
                $('#webcam-switch').prop("checked", false).trigger('change');
            });
          /*  $("#exit-app").click(function () {
                removeCapture();
               // $("#webcam-switch").prop("checked", false).change();
                $('#webcam-switch').prop("checked", false).trigger('change');
            });*/



        }//fin funcion display
}//fin objeto
export default objCamara;