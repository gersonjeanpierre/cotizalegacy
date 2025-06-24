
document.addEventListener('DOMContentLoaded', function() {
    const downloadPdfBtn = document.getElementById('downloadPdfBtn');
    
    function fechaLargaPeru() {
        const meses = [
            'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
            'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
        ];
        const hoy = new Date();
        const dia = hoy.getDate();
        const mes = meses[hoy.getMonth()];
        const anio = hoy.getFullYear();
        return `Lima ${dia} de ${mes} del ${anio}`;
    }
    if (downloadPdfBtn) {
        downloadPdfBtn.addEventListener('click', async function() {
            const cotizacionData = JSON.parse(this.dataset.cotizacion);
            const clienteData = JSON.parse(this.dataset.cliente);
            const detallesData = JSON.parse(this.dataset.detalles);
            const idRegistro = this.dataset.idRegistro; // Obtenemos el ID de registro

            // Obtener el template HTML
            try {
                const response = await fetch('./cotizacion_pdf/cotizacion_template.html'); // Ruta actualizada
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                let templateHtml = await response.text();

                // Rellenar el template con los datos de la cotización
                templateHtml = templateHtml.replace('{{fecha_actual_larga}}', fechaLargaPeru());
                templateHtml = templateHtml.replace('{{cotizacion.id_registro}}', cotizacionData.id_registro);
                templateHtml = templateHtml.replace('{{cotizacion.fecha_actual}}', new Date().toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })); // Fecha actual para el PDF
                templateHtml = templateHtml.replace('{{cotizacion.fecha}}', new Date(cotizacionData.fecha).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }));


                // Información del Cliente
                templateHtml = templateHtml.replace('{{cliente.nombre}}', clienteData.nombre);
                templateHtml = templateHtml.replace('{{cliente.razon_social}}', clienteData.razon_social);
                templateHtml = templateHtml.replace('{{cliente.ruc}}', clienteData.ruc);
                templateHtml = templateHtml.replace('{{cliente.celular}}', clienteData.celular);
                templateHtml = templateHtml.replace('{{cliente.correo}}', clienteData.correo);

                // Detalles de la Cotización
                let detallesHtml = '';
                let totalGeneral = 0;
                detallesData.forEach(detalle => {
                    totalGeneral += parseFloat(detalle.precio_total);
                    let opcionesHtml = '';
                    if (detalle.opciones && detalle.opciones.length > 0) {
                        opcionesHtml = `<ul class="list-disc list-inside text-sm text-gray-600">`;
                        detalle.opciones.forEach(opcion => {
                            opcionesHtml += `<li>${opcion.descripcion}</li>`;
                        });
                        opcionesHtml += `</ul>`;
                    } else {
                        opcionesHtml = `<em class="text-gray-500 text-sm">Sin opciones adicionales</em>`;
                    }

                    detallesHtml += `
                        <tr class="border-b border-gray-200 last:border-b-0">
                            <td class="py-3 px-4">${detalle.producto_nombre}</td>
                            <td class="py-3 px-4">${detalle.cantidad}</td>
                            <td class="py-3 px-4">${opcionesHtml}</td>
                            <td class="py-3 px-4 text-right">S/ ${parseFloat(detalle.precio_total).toFixed(2)}</td>
                        </tr>
                    `;
                });
                templateHtml = templateHtml.replace('{{detalles_cotizacion}}', detallesHtml);
                templateHtml = templateHtml.replace('{{total_general}}', totalGeneral.toFixed(2));

                // Opciones de html2pdf
                const options = {
                    margin: 0.1,
                    filename: `cotizacion_${idRegistro}.pdf`, // Usamos el idRegistro para el nombre del archivo
                    image: { type: 'jpeg', quality: 1 },
                    html2canvas: { scale: 1.5 },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
                };

                // Generar el PDF
                html2pdf().from(templateHtml).set(options).save();

            } catch (error) {
                console.error("Error al generar el PDF:", error);
                alert("Ocurrió un error al generar el PDF. Por favor, inténtalo de nuevo.");
            }
        });
    }
});

