import bootbox from 'bootbox';
import $ from 'jquery';
window.$ = window.jQuery = $;
import 'bootstrap';


function alertaRoja(titulo,subtitulo){
    $( "#divCartelAlerta" ).html('<div id="alertaRoja" class="alert alert-danger alert-dismissible fade show "  role="alert">\
        <strong>'+titulo+'</strong>'+subtitulo+'<button type="button" class="close" data-dismiss="alert" aria-label="Close">\
            <span aria-hidden="true">&times;</span>\
        </button>\
    </div>');
}
function alertaVerde(titulo,subtitulo){
    $( "#divCartelAlerta" ).html('<div class="alert alert-success alert-dismissible fade show "  role="alert">\
        <strong>'+titulo+'</strong>'+subtitulo+'<button type="button" class="close" data-dismiss="alert" aria-label="Close">\
            <span aria-hidden="true">&times;</span>\
        </button>\
    </div>');
}
function mostrarToast(titulo, subtitulo, tipo){
    $("#myToast").toast({
        autohide: true,
        delay: 3000,
    });
    switch (tipo) {
        case 'roja':
            $("#myToast").addClass('bg-danger');
            $("#myToast").removeClass('bg-success');
            $("#myToast").removeClass('bg-warning');
            break;
        case 'amarilla':
            $("#myToast").addClass('bg-warning');
            $("#myToast").removeClass('bg-danger');
            $("#myToast").removeClass('bg-success');
            break;
        case 'verde':
            $("#myToast").addClass('bg-success');
            $("#myToast").removeClass('bg-danger');
            $("#myToast").removeClass('bg-warning');
            break;
        default:
            break;
    }
    $("#tituloToast").text(titulo);
    $("#subtituloToast").text(subtitulo);
    $("#myToast").toast('show');
}
function consultando(mostrar){
    
    mostrar ? $("#spin").addClass("is-active") : $("#spin").removeClass("is-active");
}
function alertaAmarilla(titulo,subtitulo){
    console.log('sjksjsjsjs');
}

function cartelAlerta(titulo, subtitulo){
    /*bootbox.alert({
        title: titulo,
        message: subtitulo,
        size: 'medium',
        locale: 'es',
        centerVertical: true,
    });*/
}

export {alertaRoja, alertaVerde, alertaAmarilla, consultando, mostrarToast, cartelAlerta}