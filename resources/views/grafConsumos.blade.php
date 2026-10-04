@extends('adminlte::page')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    {!! ' <span class="seccion-negrita"></span> </strong>' . ' Consumos por medición - Lote: ' . Auth::user()->lote !!}
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <canvas id="graficoConsumos" style="width: 100%; height: 400px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var ctxConsumos = document.getElementById('graficoConsumos').getContext('2d');
    var consumosData = {!! $graficoConsumos !!};
    new Chart(ctxConsumos, {
        type: 'bar',
        data: {
            labels: consumosData.labels,
            datasets: [{
                label: 'Consumo',
                data: consumosData.consumos,
                backgroundColor: 'rgba(255, 159, 64, 0.9)',
                borderColor: 'rgba(255, 159, 64, 0.8)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    ticks: {
                        beginAtZero: true,
                        font: { size: 14 }
                    }
                },
                x: {
                    ticks: {
                        autoSkip: false,
                        maxRotation: 45,
                        minRotation: 45,
                        font: { size: 9 }
                    }
                }
            },
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>
@endsection

