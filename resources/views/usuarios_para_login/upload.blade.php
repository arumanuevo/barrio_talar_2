<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Usuarios desde CSV</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white shadow-lg rounded-lg p-8 max-w-5xl w-full">
            <h1 class="text-2xl font-bold text-gray-800 mb-6 text-center">Importar Usuarios desde CSV</h1>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                    <p class="font-medium">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('errors') && count(session('errors')) > 0)
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                    <p class="font-medium">Se encontraron los siguientes errores:</p>
                    <ul class="list-disc pl-5 mt-2">
                        @foreach(session('errors') as $error)
                            <li class="text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="csvForm" action="{{ route('usuarios_para_login.process_csv') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="flex flex-col">
                    <label for="csv_file" class="text-sm font-medium text-gray-700 mb-1">Selecciona el archivo CSV:</label>
                    <input
                        type="file"
                        name="csv_file"
                        id="csv_file"
                        class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        accept=".csv"
                        required
                    >
                </div>

                <div class="flex justify-between">
                    <button
                        type="button"
                        onclick="previewCSV()"
                        class="px-6 py-2 bg-yellow-500 text-white font-medium rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition-colors"
                    >
                        Vista Previa
                    </button>
                    <button
                        type="submit"
                        class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                    >
                        Importar CSV
                    </button>
                </div>
            </form>

            <!-- Sección para la vista previa -->
            <div id="previewSection" class="mt-8 hidden">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Vista Previa del CSV</h2>
                <div class="mb-4 p-3 bg-gray-100 rounded-md text-sm text-gray-700" id="previewInfo">
                    <!-- Información sobre el delimitador y encabezados -->
                </div>
                <div class="overflow-x-auto mb-4">
                    <table class="min-w-full bg-white border border-gray-200" id="previewTable">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-2 px-4 border-b">EMAIL</th>
                                <th class="py-2 px-4 border-b">LOTE</th>
                                <th class="py-2 px-4 border-b">MEDIDOR</th>
                                <th class="py-2 px-4 border-b">Nombre</th>
                                <th class="py-2 px-4 border-b">Ocupación</th>
                            </tr>
                        </thead>
                        <tbody id="previewTableBody">
                            <!-- Aquí se mostrarán las filas de vista previa -->
                        </tbody>
                    </table>
                </div>
                <!-- Paginación -->
                <div class="flex justify-center mt-4" id="paginationControls">
                    <button
                        onclick="prevPage()"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md mr-2 hover:bg-gray-300"
                        id="prevButton"
                        disabled
                    >
                        Anterior
                    </button>
                    <span class="px-4 py-2" id="pageInfo">Página 1 de 1</span>
                    <button
                        onclick="nextPage()"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md ml-2 hover:bg-gray-300"
                        id="nextButton"
                        disabled
                    >
                        Siguiente
                    </button>
                </div>
            </div>

            <div class="mt-8 text-sm text-gray-600">
                <p class="text-center">
                    <strong>Nota:</strong> Asegúrate de que el archivo CSV tenga las columnas en el siguiente orden: <strong>EMAIL, LOTE, MEDIDOR, name, ocupación</strong>.
                </p>
            </div>
        </div>
    </div>

    <script>
        let csvData = [];
        let currentPage = 1;
        const rowsPerPage = 20;

        async function previewCSV() {
            const fileInput = document.getElementById('csv_file');
            const previewSection = document.getElementById('previewSection');
            const previewInfo = document.getElementById('previewInfo');

            if (fileInput.files.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Advertencia',
                    text: 'Por favor, selecciona un archivo CSV.',
                });
                return;
            }

            const formData = new FormData();
            formData.append('csv_file', fileInput.files[0]);

            try {
                const response = await axios.post('{{ route("usuarios_para_login.preview_csv") }}', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });

                const data = response.data;
                csvData = data.preview;

                previewInfo.innerHTML = `
                    <p><strong>Delimitador detectado:</strong> "${data.delimiter === ';' ? 'Punto y coma' : 'Coma'}"</p>
                    <p><strong>Encabezados:</strong> ${data.headers.join(', ')}</p>
                    <p><strong>Coinciden los encabezados:</strong> ${data.headers_match ? 'Sí' : 'No'}</p>
                    <p><strong>Total de registros:</strong> ${data.total_rows}</p>
                `;

                renderTablePage(1);
                previewSection.classList.remove('hidden');
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al procesar el archivo: ' + error.response?.data?.message || error.message,
                });
            }
        }

        function renderTablePage(page) {
            const previewTableBody = document.getElementById('previewTableBody');
            const startIndex = (page - 1) * rowsPerPage;
            const endIndex = startIndex + rowsPerPage;
            const paginatedData = csvData.slice(startIndex, endIndex);

            previewTableBody.innerHTML = '';
            paginatedData.forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="py-2 px-4 border-b">${row.EMAIL}</td>
                    <td class="py-2 px-4 border-b">${row.LOTE}</td>
                    <td class="py-2 px-4 border-b">${row.MEDIDOR}</td>
                    <td class="py-2 px-4 border-b">${row.name}</td>
                    <td class="py-2 px-4 border-b">${row.ocupacion}</td>
                `;
                previewTableBody.appendChild(tr);
            });

            updatePaginationControls(page);
        }

        function updatePaginationControls(page) {
            const totalPages = Math.ceil(csvData.length / rowsPerPage);
            const prevButton = document.getElementById('prevButton');
            const nextButton = document.getElementById('nextButton');
            const pageInfo = document.getElementById('pageInfo');

            prevButton.disabled = page <= 1;
            nextButton.disabled = page >= totalPages;
            pageInfo.textContent = `Página ${page} de ${totalPages}`;
            currentPage = page;
        }

        function prevPage() {
            if (currentPage > 1) {
                renderTablePage(currentPage - 1);
            }
        }

        function nextPage() {
            const totalPages = Math.ceil(csvData.length / rowsPerPage);
            if (currentPage < totalPages) {
                renderTablePage(currentPage + 1);
            }
        }
    </script>
</body>
</html>