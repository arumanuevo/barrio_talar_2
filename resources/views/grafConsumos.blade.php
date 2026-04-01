@extends('adminlte::page')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    {!! ' <span class="seccion-negrita"></span> </strong>' . ' Facturación Lote: ' . Auth::user()->lote !!}
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <canvas id="barChart" style="width: 100%; height: 400px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var ctx = document.getElementById('barChart').getContext('2d');
    
    var chartData = {!! $data !!};

    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Sumario',
                data: chartData.sumarios,
                backgroundColor: 'rgba(60,141,188,0.9)',
                borderColor: 'rgba(60,141,188,0.8)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    ticks: {
                        beginAtZero: true,
                        font: {
                            size: 14 // Tamaño de fuente para las etiquetas del eje Y
                        }
                    }
                },
                x: {
                    ticks: {
                        autoSkip: false,
                        maxRotation: 45,
                        minRotation: 45,
                        font: {
                            size: 9 // Tamaño de fuente para las etiquetas del eje X
                        }
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

