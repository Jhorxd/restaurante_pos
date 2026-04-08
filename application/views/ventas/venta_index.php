<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0">

    <div class="p-4 sm:p-6 lg:p-10 w-full">

        <!-- Header -->
        <header class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">
                    Gestión de Ventas
                </nav>
                <h1 class="text-3xl font-black text-slate-800">
                    Historial de Ventas
                </h1>
                <p class="mt-1 text-xs text-slate-400">
                    Revisa las ventas realizadas y descarga el comprobante en PDF.
                </p>
            </div>
        </header>

        <!-- Alertas -->
        <?php if ($this->session->flashdata('msg')): ?>
            <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium flex items-center gap-2">
                <i class="fas fa-check-circle text-emerald-500"></i>
                <span><?= $this->session->flashdata('msg'); ?></span>
            </div>
        <?php endif; ?>

        <!-- Card tabla -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-3">
                <div class="text-xs text-slate-400 uppercase tracking-[0.2em] font-bold">
                    Últimas Ventas
                </div>
                <!-- Aquí luego puedes poner filtros o buscador -->
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[720px]">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-widest border-b border-slate-100">
                            <th class="px-4 py-3 font-bold">Ticket</th>
                            <th class="px-4 py-3 font-bold">Fecha / Hora</th>
                            <th class="px-4 py-3 font-bold">Cajero</th>
                            <th class="px-4 py-3 font-bold">Mesa</th>
                            <th class="px-4 py-3 font-bold text-right">Total</th>
                            <th class="px-4 py-3 font-bold text-center">Método</th>
                            <th class="px-4 py-3 font-bold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (!empty($ventas)): foreach ($ventas as $v): ?>
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <!-- Ticket -->
                                <td class="px-4 py-3 align-middle">
                                    <div class="inline-flex items-center gap-2">
                                        <span class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-[11px] font-black text-slate-500">
                                            VT
                                        </span>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-800 text-sm leading-tight">
                                                #<?= str_pad($v->id, 6, '0', STR_PAD_LEFT); ?>
                                            </span>
                                            <span class="text-[10px] text-slate-400 uppercase tracking-widest">
                                                ID Interno
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Fecha -->
                                <td class="px-4 py-3 align-middle">
                                    <div class="text-xs text-slate-800 font-medium">
                                        <?= date('d/m/Y', strtotime($v->fecha_registro)); ?>
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        <?= date('H:i:s', strtotime($v->fecha_registro)); ?>
                                    </div>
                                </td>

                                <!-- Cajero -->
                                <td class="px-4 py-3 align-middle">
                                    <div class="text-xs font-medium text-slate-800">
                                        <?= htmlspecialchars($v->cajero); ?>
                                    </div>
                                    <div class="text-[10px] text-slate-400 uppercase tracking-widest">
                                        Cajero
                                    </div>
                                </td>

                                <td class="px-4 py-3 align-middle">
                                    <?php if (!empty($v->mesa_codigo)): ?>
                                        <span class="text-xs font-bold text-slate-800"><?= htmlspecialchars($v->mesa_codigo) ?></span>
                                        <div class="text-[10px] text-slate-400 truncate max-w-[120px]" title="<?= htmlspecialchars($v->mesa_nombre ?? '') ?>">
                                            <?= htmlspecialchars($v->mesa_nombre ?? '') ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-[10px] text-slate-400 font-bold uppercase">Mostrador</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Total -->
                                <td class="px-4 py-3 text-right align-middle">
                                    <div class="text-sm font-black text-slate-900">
                                        S/ <?= number_format($v->total, 2); ?>
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        Importe total
                                    </div>
                                </td>

                                <!-- Método -->
                                <td class="px-4 py-3 text-center align-middle">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                        <?= $v->metodo_pago === 'efectivo'
                                            ? 'bg-emerald-50 text-emerald-600'
                                            : 'bg-slate-100 text-slate-600'; ?>">
                                        <span class="w-1.5 h-1.5 rounded-full mr-1.5
                                            <?= $v->metodo_pago === 'efectivo'
                                                ? 'bg-emerald-500'
                                                : 'bg-slate-400'; ?>"></span>
                                        <?= htmlspecialchars($v->metodo_pago); ?>
                                    </span>
                                </td>

                                <!-- Acciones -->
                                <td class="px-4 py-3 text-right align-middle">
                                    <div class="flex justify-end gap-2">
                                        <a href="<?= base_url('ventas/ticket/'.$v->id); ?>"
                                           target="_blank"
                                           class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold shadow-sm shadow-blue-100">
                                            <i class="fas fa-file-pdf mr-1 text-xs"></i>
                                            Ver PDF
                                        </a>
                                        <button type="button"
                                                onclick="abrirDetalleInsumos(<?= $v->id ?>)"
                                                title="Ver insumos descontados"
                                                class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-violet-50 hover:bg-violet-100 text-violet-700 text-[11px] font-bold border border-violet-200 transition-colors">
                                            <i class="fas fa-list-ul text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center">
                                    <div class="inline-flex flex-col items-center">
                                        <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center mb-3">
                                            <i class="fas fa-receipt text-slate-300 text-lg"></i>
                                        </div>
                                        <p class="text-sm text-slate-500 font-medium">
                                            No hay ventas registradas.
                                        </p>
                                        <p class="text-xs text-slate-400 mt-1">
                                            Cuando realices ventas desde el POS, aparecerán listadas aquí.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- ═══════════ MODAL AUDITORÍA DE INSUMOS ═══════════ -->
<div id="modal-insumos" class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     style="background:rgba(15,23,42,0.55); backdrop-filter:blur(4px);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden">
        <!-- Header modal -->
        <div class="flex items-center justify-between px-6 py-4 bg-violet-600">
            <div class="flex items-center gap-3">
                <i class="fas fa-boxes text-white text-lg"></i>
                <div>
                    <h3 class="text-white font-black text-sm uppercase tracking-widest">Insumos Descontados</h3>
                    <p id="modal-venta-ref" class="text-violet-200 text-xs mt-0.5">Venta #—</p>
                </div>
            </div>
            <button onclick="cerrarModalInsumos()" class="text-white/70 hover:text-white transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <!-- Contenido dinámico -->
        <div id="modal-insumos-body" class="p-6 max-h-[60vh] overflow-y-auto">
            <div class="flex items-center justify-center py-10">
                <div class="animate-spin w-8 h-8 border-4 border-violet-200 border-t-violet-600 rounded-full"></div>
            </div>
        </div>
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 text-right">
            <button onclick="cerrarModalInsumos()"
                    class="px-5 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-sm font-bold transition-all">
                Cerrar
            </button>
        </div>
    </div>
</div>

<script>
const _BASE = '<?= base_url() ?>';

function abrirDetalleInsumos(idVenta) {
    const modal = document.getElementById('modal-insumos');
    const body  = document.getElementById('modal-insumos-body');
    document.getElementById('modal-venta-ref').textContent = 'Venta #' + String(idVenta).padStart(6, '0');
    body.innerHTML = '<div class="flex items-center justify-center py-10"><div class="animate-spin w-8 h-8 border-4 border-violet-200 border-t-violet-600 rounded-full"></div></div>';
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    fetch(_BASE + 'ventas/detalle_insumos_venta/' + idVenta)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                body.innerHTML = '<p class="text-center text-sm text-red-500 py-8">Error al cargar los datos.</p>';
                return;
            }
            if (!data.insumos || data.insumos.length === 0) {
                body.innerHTML = `
                    <div class="text-center py-10">
                        <i class="fas fa-info-circle text-4xl text-slate-200 mb-4"></i>
                        <p class="text-sm text-slate-500 font-medium">Esta venta no tiene insumos compuestos.</p>
                        <p class="text-xs text-slate-400 mt-1">Solo se vendieron productos simples (descuento de stock propio).</p>
                    </div>`;
                return;
            }
            let rows = '';
            data.insumos.forEach(ins => {
                rows += `<tr class="hover:bg-slate-50 transition-colors border-b border-slate-100">
                    <td class="px-4 py-3 text-sm font-bold text-slate-800">${ins.insumo_nombre}</td>
                    <td class="px-4 py-3 text-center"><span class="font-black text-violet-700">${parseFloat(ins.cantidad).toFixed(4)}</span></td>
                    <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-700">${parseFloat(ins.stock_resultante).toFixed(2)}</span></td>
                    <td class="px-4 py-3 text-xs text-slate-400 max-w-[160px] truncate" title="${ins.nota || ''}">${ins.nota || '—'}</td>
                </tr>`;
            });
            body.innerHTML = `
            <table class="w-full text-left min-w-[500px]">
                <thead>
                    <tr class="text-[10px] uppercase tracking-widest text-slate-400 border-b border-slate-200">
                        <th class="px-4 py-2 font-bold">Insumo</th>
                        <th class="px-4 py-2 font-bold text-center">Cant. descontada</th>
                        <th class="px-4 py-2 font-bold text-center">Stock resultante</th>
                        <th class="px-4 py-2 font-bold">Referencia</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>`;
        })
        .catch(() => {
            body.innerHTML = '<p class="text-center text-sm text-red-500 py-8">Error de red al cargar insumos.</p>';
        });
}

function cerrarModalInsumos() {
    const modal = document.getElementById('modal-insumos');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.getElementById('modal-insumos').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalInsumos();
});
</script>
