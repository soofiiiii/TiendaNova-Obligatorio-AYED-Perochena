<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informes y Estadísticas</title>
    <!-- Carga de Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Configuración para usar la fuente Inter -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
    </style>
</head>
<body class="p-4 sm:p-6 md:p-8">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-4 sm:mb-0">Informes y Estadísticas</h1>
    </div>

    <!-- Filtros y Acciones -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div class="flex items-center space-x-4 mb-4 sm:mb-0">
            <div class="relative">
                <select class="appearance-none bg-white border border-gray-300 text-gray-700 py-2 px-4 pr-8 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option>Últimos 30 días</option>
                    <option>Últimos 7 días</option>
                    <option>Hoy</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                </div>
            </div>
            
            <div class="text-sm text-gray-500 flex items-center whitespace-nowrap hidden md:flex">
                Comparar con: <span class="font-medium text-gray-700 ml-1">Del 7 de mayo al 5 de junio de 2024</span>
                <svg class="w-4 h-4 ml-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </div>
        <button class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-1 px-4 border border-gray-300 rounded-lg shadow-sm text-sm transition duration-150 ease-in-out">
            Editar Configuración
        </button>
    </div>

    <!-- Contenedor Principal del Dashboard -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- Columna Izquierda (lg:col-span-2) -->
        <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Card 1: Ventas Totales (Gráfico de Línea) -->
            <div class="bg-white rounded-xl shadow-xl p-6 flex flex-col justify-between h-80 md:h-64">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-semibold text-gray-700 mb-1">Ventas totales</h3>
                        <p class="text-3xl font-bold text-gray-900 mt-1" id="ventasTotalesValue"></p>
                    </div>
                    <button class="text-gray-400 hover:text-indigo-500 transition duration-150"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg></button>
                </div>
                <div class="h-40">
                    <canvas id="graficoVentasNetas"></canvas>
                </div>
                <p class="text-xs text-gray-400 mt-2">Del 6 de junio al 4 de julio de 2023</p>
            </div>

            <!-- Card 2: Valor de Inventario por Rama (Lista con Scroll) -->
            <div class="bg-white rounded-xl shadow-xl p-6 h-80 md:h-64 flex flex-col justify-start">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-semibold text-gray-700 mb-1">Valor de Inventario</h3>
                        <p class="text-3xl font-bold text-gray-900 mt-1" id="valorInventarioValue"></p>
                    </div>
                    <button class="text-gray-400 hover:text-indigo-500 transition duration-150"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg></button>
                </div>
                
                <div class="mt-4 flex-1 flex flex-col min-h-0">
                    <h4 class="font-semibold text-gray-700 mb-2">Valor de Inventario por Rama</h4>
                    <!-- Contenedor con scroll forzado -->
                    <div id="valorPorRamaList" class="max-h-36 overflow-y-auto pr-2">
                        <!-- Contenido generado por JS -->
                        <div class="text-gray-500 text-sm">Cargando datos...</div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Productos por Categoría (Gráfico de Barras - Full Width MD) -->
            <div class="md:col-span-2 bg-white rounded-xl shadow-xl p-6 flex flex-col justify-between h-96">
                <div class="flex justify-between items-start">
                    <h3 class="font-semibold text-gray-700 mb-1">Productos por Categoría</h3>
                    <button class="text-gray-400 hover:text-indigo-500 transition duration-150">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </button>
                </div>
                <div class="flex-grow h-full pt-4 pb-2">
                    <canvas id="graficoCategorias"></canvas>
                </div>
            </div>

            <!-- Card 4: KPIs Generales -->
            <div class="md:col-span-2 grid grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Productos -->
                <div class="bg-white rounded-xl shadow p-5">
                    <p class="text-sm text-gray-500 mb-1">Total Productos</p>
                    <p class="text-3xl font-bold text-gray-900" id="totalProductosValue">0</p>
                </div>
                <!-- Total Categorías -->
                <div class="bg-white rounded-xl shadow p-5">
                    <p class="text-sm text-gray-500 mb-1">Total Categorías</p>
                    <p class="text-3xl font-bold text-gray-900" id="totalCategoriasValue">0</p>
                </div>
                <!-- Proveedores -->
                <div class="bg-white rounded-xl shadow p-5">
                    <p class="text-sm text-gray-500 mb-1">Proveedores</p>
                    <p class="text-3xl font-bold text-gray-900" id="totalProveedoresValue">0</p>
                </div>
                <!-- Prod. Inactivos -->
                <div class="bg-white rounded-xl shadow p-5">
                    <p class="text-sm text-gray-500 mb-1">Prod. Inactivos</p>
                    <p class="text-3xl font-bold text-gray-900" id="productosInactivosValue">0</p>
                </div>
            </div>

            <!-- Card 5: KPIs de Pedidos -->
            <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-6">
                <!-- Pedidos Pendientes -->
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-red-500">
                    <p class="text-sm text-gray-500 mb-1">Pedidos Pendientes</p>
                    <p class="text-3xl font-bold text-gray-900" id="pedidosPendientesValue">0</p>
                </div>
                <!-- Pedidos Recibidos -->
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-blue-500">
                    <p class="text-sm text-gray-500 mb-1">Pedidos Recibidos</p>
                    <p class="text-3xl font-bold text-gray-900" id="pedidosRecibidosValue">0</p>
                </div>
                <!-- Pedidos Cancelados -->
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-gray-500">
                    <p class="text-sm text-gray-500 mb-1">Pedidos Cancelados</p>
                    <p class="text-3xl font-bold text-gray-900" id="pedidosCanceladosValue">0</p>
                </div>
            </div>

            <!-- Card de Alertas/Estado -->
            <div class="md:col-span-2" id="alertaCategorias">
                <!-- Contenido generado por JS -->
            </div>

        </div>
        
        <!-- Columna Derecha (lg:col-span-1) -->
        <div class="lg:col-span-1 grid grid-cols-1 gap-6">

            <!-- Card 6: Distribución de Productos (Gráfico de Dona) -->
            <div class="bg-white rounded-xl shadow-xl p-6">
                <div class="flex justify-between items-start">
                    <h3 class="font-semibold text-gray-700 mb-4">Distribución de Productos</h3>
                    <button class="text-gray-400 hover:text-indigo-500 transition duration-150"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg></button>
                </div>
                <div class="flex flex-col items-center">
                    <div class="w-3/4 max-w-xs mb-4">
                        <canvas id="graficoActivos"></canvas>
                    </div>
                    <div class="text-xs space-y-1">
                        <div class="flex items-center">
                            <span class="w-3 h-3 bg-indigo-600 rounded-full mr-2"></span>
                            <span>Activos: <span class="font-bold" id="activosCount">0</span></span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></span>
                            <span>Inactivos: <span class="font-bold" id="inactivosCount">0</span></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 7: Ventas por Producto (Lista de Barras) -->
            <div class="bg-white rounded-xl shadow-xl p-6">
                <h3 class="font-semibold text-gray-700 mb-4">Productos con Más Ventas</h3>
                <ul id="ventasPorProductoList" class="space-y-4">
                    <!-- Contenido generado por JS -->
                    <div class="text-gray-500 text-sm">Cargando datos...</div>
                </ul>
            </div>
            
        </div>

    </div>

    <!-- Carga de Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Función de formato de moneda (locale: ES, currency: USD)
        const formatCurrency = (amount) => new Intl.NumberFormat('es-ES', { 
            style: 'currency', 
            currency: 'USD', 
            minimumFractionDigits: 2 
        }).format(amount);

         const stats = <?= json_encode($stats, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>;

        // 2. FUNCIÓN DE RENDERIZADO DE DATOS
        function renderKpisAndLists() {
            // Renderizar KPIs
            document.getElementById('ventasTotalesValue').innerHTML = `${formatCurrency(stats.ventasTotales)}<span class="text-green-600 text-base font-normal ml-2">▲ 20%</span>`;
            document.getElementById('valorInventarioValue').textContent = formatCurrency(stats.valorInventario);
            
            document.getElementById('totalProductosValue').textContent = stats.totalProductos;
            document.getElementById('totalCategoriasValue').textContent = stats.totalCategorias;
            document.getElementById('totalProveedoresValue').textContent = stats.totalProveedores;
            document.getElementById('productosInactivosValue').textContent = stats.productosInactivos;
            
            document.getElementById('pedidosPendientesValue').textContent = stats.pedidosPendientes;
            document.getElementById('pedidosRecibidosValue').textContent = stats.pedidosRecibidos;
            document.getElementById('pedidosCanceladosValue').textContent = stats.pedidosCancelados;
            
            document.getElementById('activosCount').textContent = stats.productosActivos;
            document.getElementById('inactivosCount').textContent = stats.productosInactivos;

            // Renderizar Alerta de Categorías
            const alertaDiv = document.getElementById('alertaCategorias');
            if (stats.categoriasSinProductos > 0) {
                alertaDiv.innerHTML = `
                    <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl shadow transition duration-300 hover:shadow-lg">
                        <p class="font-semibold flex items-center">
                            <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.332 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            ¡Atención! Existen ${stats.categoriasSinProductos} categorías sin productos asignados.
                        </p>
                        <p class="text-sm ml-7">Revisá el árbol de categorías para mejorar la navegación.</p>
                    </div>
                `;
            } else {
                alertaDiv.innerHTML = `
                    <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl shadow transition duration-300 hover:shadow-lg">
                        <p class="font-semibold flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            ¡Todo en orden! Todas las categorías tienen productos asignados.
                        </p>
                    </div>
                `;
            }

            // Renderizar Valor de Inventario por Rama
            const ramaList = document.getElementById('valorPorRamaList');
            ramaList.innerHTML = '';
            const totalInventario = Math.max(stats.valorInventario, 1);
            
            Object.entries(stats.valorPorRama).forEach(([cat, valor]) => {
                const porcentaje = (valor / totalInventario) * 100;
                const listItem = document.createElement('li');
                listItem.className = 'text-sm p-2 rounded-lg hover:bg-indigo-50 transition duration-150 ease-in-out cursor-pointer';
                listItem.innerHTML = `
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700 font-medium">${cat}</span>
                        <span class="text-gray-900 font-semibold text-base">
                            ${formatCurrency(valor)}
                        </span>
                    </div>
                    <div class="h-1 bg-gray-200 rounded-full mt-1">
                        <div class="h-full bg-indigo-500 rounded-full"
                             style="width: ${Math.min(100, porcentaje)}%;">
                        </div>
                    </div>
                `;
                ramaList.appendChild(listItem);
            });

            // Renderizar Ventas por Producto
            const ventasList = document.getElementById('ventasPorProductoList');
            ventasList.innerHTML = '';
            
            // Convertir objeto a array para ordenar
            const sortedVentas = Object.entries(stats.ventasPorProducto)
                .sort(([, a], [, b]) => b - a); // Ordenar por valor (venta) descendente

            const maxVenta = sortedVentas.length > 0 ? sortedVentas[0][1] : 1; 
            const topProducts = sortedVentas.slice(0, 4); // Mostrar solo los 4 principales
            
            if (topProducts.length === 0) {
                ventasList.innerHTML = '<li class="text-gray-500 text-sm">No hay datos de ventas disponibles.</li>';
            } else {
                topProducts.forEach(([nombre, venta]) => {
                    const porcentaje = (venta / maxVenta) * 100;
                    const listItem = document.createElement('li');
                    listItem.className = 'text-sm';
                    listItem.innerHTML = `
                        <div class="flex justify-between">
                            <span class="text-gray-700">${nombre}</span>
                            <span class="text-gray-900 font-semibold">${formatCurrency(venta)}</span> 
                        </div>
                        <div class="h-1 bg-gray-200 rounded-full mt-1">
                            <div class="h-full bg-indigo-500 rounded-full transition-all duration-500 ease-out" style="width: ${porcentaje.toFixed(2)}%;"></div>
                        </div>
                    `;
                    ventasList.appendChild(listItem);
                });
            }
        }


        // 3. FUNCIÓN DE RENDERIZADO DE GRÁFICOS (Chart.js)
        function renderCharts() {
            // Configuración Base de Gráficos
            const chartDefaults = {
                responsive: true,
                maintainAspectRatio: false, 
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                let label = context.label || '';
                                if (label) { label += ': '; }
                                // Formato de moneda para ventas
                                if (context.chart.id === 'ventasNetas') {
                                     return label + formatCurrency(context.parsed.y);
                                }
                                if (context.chart.id === 'activosChart') {
                                    const total = stats.distActivos.activos + stats.distActivos.inactivos;
                                    const percentage = (context.parsed / total * 100).toFixed(1) + '%';
                                    return `${label} ${context.formattedValue} (${percentage})`;
                                }
                                return label + context.formattedValue;
                            }
                        }
                    },
                    layout: { padding: 0 } 
                },
                scales: {
                     y: { display: false, beginAtZero: true }, 
                     x: { display: false }
                }
            };

            // a) Gráfico de Torta/Dona (Distribución de Productos)
            new Chart(document.getElementById('graficoActivos'), {
                id: 'activosChart',
                type: 'doughnut', 
                data: {
                    labels: ['Activos', 'Inactivos'],
                    datasets: [{
                        data: [stats.distActivos.activos, stats.distActivos.inactivos],
                        backgroundColor: ['#4F46E5', '#F59E0B'], // Indigo, Amber
                        hoverBackgroundColor: ['#4338CA', '#D97706'],
                        borderWidth: 0,
                        spacing: 8,
                    }]
                },
                options: {
                    ...chartDefaults,
                    scales: {}, // No necesita escalas para Doughnut
                    cutout: '80%',
                }
            });

            // b) Gráfico de Barras (Productos por Categoría)
            const categoriasData = {
                labels: Object.keys(stats.productosPorCategoria),
                data: Object.values(stats.productosPorCategoria),
            };
            
            new Chart(document.getElementById('graficoCategorias'), {
                type: 'bar',
                data: {
                    labels: categoriasData.labels,
                    datasets: [{
                        label: 'Cantidad de Productos',
                        data: categoriasData.data,
                        backgroundColor: '#4F46E5', // Indigo 600
                        hoverBackgroundColor: '#4338CA',
                        borderRadius: 6,
                    }]
                },
                options: {
                    ...chartDefaults,
                    scales: {
                        y: {
                            display: true, // Mostrar eje Y
                            beginAtZero: true,
                            grid: { display: true, color: 'rgba(200, 200, 200, 0.2)', drawBorder: false },
                            ticks: { color: '#6B7280' }
                        },
                        x: {
                            display: true, // Mostrar eje X
                            grid: { display: false },
                            ticks: { color: '#6B7280' }
                        }
                    }
                }
            });

            // c) Gráfico de Línea (Ventas Totales - Sparkline)
            new Chart(document.getElementById('graficoVentasNetas'), {
                id: 'ventasNetas',
                type: 'line',
                data: {
                    labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'], 
                    datasets: [{
                        label: 'Ventas Totales',
                        data: [12000, 29000, 45000, 38000, 52000, 87000, 93000], 
                        backgroundColor: 'rgba(79, 70, 229, 0.2)', // Relleno suave de color Indigo
                        borderColor: '#4F46E5', 
                        tension: 0.4, 
                        fill: true,
                        pointRadius: 0,
                        borderWidth: 2
                    }]
                },
                options: {
                    ...chartDefaults,
                    scales: {
                        y: { display: false, beginAtZero: true }, 
                        x: { display: false }
                    }
                }
            });
        }

        // Ejecutar al cargar la página
        document.addEventListener('DOMContentLoaded', () => {
            renderKpisAndLists();
            renderCharts();
        });
    </script>
</body>
</html>