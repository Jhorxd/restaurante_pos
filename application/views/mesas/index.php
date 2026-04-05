<?php
$estado_cls = [
    'libre' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
    'ocupada' => 'bg-rose-50 border-rose-200 text-rose-800',
    'reservada' => 'bg-amber-50 border-amber-200 text-amber-900',
    'limpieza' => 'bg-slate-100 border-slate-300 text-slate-600',
];
$estado_lbl = [
    'libre' => 'Libre',
    'ocupada' => 'Ocupada',
    'reservada' => 'Reservada',
    'limpieza' => 'Limpieza',
];
?>
<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0">
    <div class="p-4 sm:p-6 lg:p-10 max-w-[1600px] mx-auto">
        <header class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-8">
            <div>
                <nav class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Operación en salón</nav>
                <h1 class="text-3xl font-black text-slate-800">Mesas</h1>
                <p class="text-sm text-slate-500 mt-1">Estado en vivo, traslados y enlace con el punto de venta.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <?php if (!empty($es_admin)): ?>
                    <a href="<?= base_url('mesas/nuevo') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-100">
                        <i class="fas fa-plus"></i> Nueva mesa
                    </a>
                <?php endif; ?>
                <a href="<?= base_url('ventas/pos') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-bold">
                    <i class="fas fa-cash-register"></i> Ir al POS
                </a>
            </div>
        </header>

        <?php if ($this->session->flashdata('msg')): ?>
            <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">
                <?= htmlspecialchars($this->session->flashdata('msg')) ?>
            </div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-medium">
                <?= htmlspecialchars($this->session->flashdata('error')) ?>
            </div>
        <?php endif; ?>

        <div class="flex flex-wrap gap-3 mb-6 text-[11px] font-bold uppercase tracking-wider">
            <?php foreach ($estado_lbl as $k => $lbl): ?>
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border <?= $estado_cls[$k] ?>">
                    <span class="w-2 h-2 rounded-full bg-current opacity-60"></span> <?= $lbl ?>
                </span>
            <?php endforeach; ?>
        </div>

        <!-- Trasladar comensales -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-8">
            <h2 class="text-sm font-black text-slate-800 uppercase tracking-tight mb-3 flex items-center gap-2">
                <i class="fas fa-random text-blue-500"></i> Trasladar de mesa
            </h2>
            <p class="text-xs text-slate-500 mb-4">Origen debe estar ocupada o reservada; destino libre o en limpieza.</p>
            <form action="<?= base_url('mesas/trasladar') ?>" method="post" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Desde</label>
                    <select name="id_origen" class="px-3 py-2 rounded-xl border border-slate-200 text-sm font-medium min-w-[160px]" required>
                        <option value="">— Mesa origen —</option>
                        <?php foreach ($mesas as $x): if (!$x->activo) continue; ?>
                            <option value="<?= (int) $x->id ?>"><?= htmlspecialchars($x->codigo . ' · ' . $x->nombre) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Hacia</label>
                    <select name="id_destino" class="px-3 py-2 rounded-xl border border-slate-200 text-sm font-medium min-w-[160px]" required>
                        <option value="">— Mesa destino —</option>
                        <?php foreach ($mesas as $x): if (!$x->activo) continue; ?>
                            <option value="<?= (int) $x->id ?>"><?= htmlspecialchars($x->codigo . ' · ' . $x->nombre) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="px-5 py-2 bg-slate-800 text-white rounded-xl text-sm font-bold hover:bg-slate-900">Trasladar</button>
            </form>
        </div>

        <!-- Mapa simple: grid por pos_orden + offset visual pos_x/pos_y -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4" id="salon-mapa">
            <?php foreach ($mesas as $m): ?>
                <?php
                $st = isset($m->estado) ? $m->estado : 'libre';
                $box = isset($estado_cls[$st]) ? $estado_cls[$st] : $estado_cls['libre'];
                ?>
                <div class="mesa-card relative rounded-2xl border-2 p-4 shadow-sm transition-transform hover:shadow-md <?= $box ?> <?= empty($m->activo) ? 'opacity-50' : '' ?> <?= !empty($es_admin) ? 'cursor-move' : 'cursor-pointer' ?>"
                     data-id="<?= (int) $m->id ?>" 
                     data-pos-x="<?= (int) $m->pos_x ?>" 
                     data-pos-y="<?= (int) $m->pos_y ?>"
                     data-estado="<?= $st ?>"
                     data-venta="<?= (int)($m->id_venta_activa ?? 0) ?>"
                     style="grid-column: span 1; transform: translate(<?= (int) $m->pos_x * 4 ?>px, <?= (int) $m->pos_y * 4 ?>px);">
                    <?php if (!empty($m->zona)): ?>
                        <div class="text-[9px] font-bold uppercase opacity-60 mb-1"><?= htmlspecialchars($m->zona) ?></div>
                    <?php endif; ?>
                    <div class="text-lg font-black"><?= htmlspecialchars($m->codigo) ?></div>
                    <div class="text-xs font-medium opacity-90 truncate" title="<?= htmlspecialchars($m->nombre) ?>"><?= htmlspecialchars($m->nombre) ?></div>
                    <div class="text-[10px] mt-1 opacity-70"><?= (int) $m->capacidad ?> pax</div>
                    <?php if (empty($m->activo)): ?>
                        <span class="text-[10px] font-bold text-red-600">Inactiva</span>
                    <?php endif; ?>

                    <!-- Cambiar estado -->
                    <form action="<?= base_url('mesas/cambiar_estado') ?>" method="post" class="mt-3 space-y-2">
                        <input type="hidden" name="id" value="<?= (int) $m->id ?>">
                        <select name="estado" class="w-full text-[10px] font-bold uppercase rounded-lg border border-black/10 bg-white/80 px-2 py-1.5" onchange="this.form.submit()">
                            <?php foreach ($estado_lbl as $k => $lbl): ?>
                                <option value="<?= $k ?>" <?= $st === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <?php if (!empty($es_admin)): ?>
                        <div class="mt-3 flex flex-wrap gap-1">
                            <a href="<?= base_url('mesas/editar/' . $m->id) ?>" class="text-[10px] font-bold px-2 py-1 rounded-lg bg-white/90 text-slate-700 hover:bg-white border border-black/10">Editar</a>
                            <?php if (in_array($st, ['libre', 'limpieza'], true)): ?>
                                <a href="<?= base_url('mesas/eliminar/' . $m->id) ?>"
                                   onclick="return confirm('¿Eliminar esta mesa?');"
                                   class="text-[10px] font-bold px-2 py-1 rounded-lg bg-white/90 text-red-600 hover:bg-white border border-black/10">Eliminar</a>
                            <?php endif; ?>
                        </div>
                        <form action="<?= base_url('mesas/mover') ?>" method="post" class="form-mover mt-2 hidden text-[10px]">
                            <input type="hidden" name="id" value="<?= (int) $m->id ?>">
                            <input type="hidden" name="pos_orden" value="<?= (int) $m->pos_orden ?>">
                            <input type="hidden" name="pos_x" value="<?= (int) $m->pos_x ?>">
                            <input type="hidden" name="pos_y" value="<?= (int) $m->pos_y ?>">
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($mesas)): ?>
            <div class="text-center py-16 text-slate-400">
                <p class="font-medium">No hay mesas en esta sucursal.</p>
                <?php if (!empty($es_admin)): ?>
                    <a href="<?= base_url('mesas/nuevo') ?>" class="text-blue-600 font-bold text-sm mt-2 inline-block">Crear la primera</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Detalle Venta -->
<div id="modal-detalle" class="fixed inset-0 z-[100] bg-slate-900/60 items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 transform scale-95 transition-transform duration-300 flex flex-col max-h-[90vh]">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black text-slate-800" id="modal-titulo">Detalle Mesa</h3>
                <p class="text-xs text-slate-500" id="modal-subtitle">Cargando...</p>
            </div>
            <button onclick="cerrarDetalle()" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-400">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="px-5 py-4 overflow-y-auto flex-1">
            <ul id="modal-productos" class="space-y-3">
                <!-- loading -->
                <li class="text-center text-slate-400 text-sm py-4"><i class="fas fa-circle-notch fa-spin"></i> Cargando...</li>
            </ul>
        </div>
        <div class="px-5 py-4 bg-slate-50 border-t border-slate-100 rounded-b-2xl">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total</span>
                <span class="text-xl font-black text-slate-800" id="modal-total">S/ 0.00</span>
            </div>
            <div class="grid grid-cols-2 gap-2" id="modal-actions" style="display:none;">
                <button type="button" id="btn-precuenta" class="px-4 py-2.5 rounded-xl border-2 border-slate-200 text-slate-600 font-bold text-xs hover:border-slate-300 hover:bg-slate-100">
                    <i class="fas fa-print mr-1"></i> Pre-cuenta
                </button>
                <button type="button" id="btn-modificar" class="px-4 py-2.5 rounded-xl border-2 border-blue-200 text-blue-600 font-bold text-xs hover:border-blue-300 hover:bg-blue-50">
                    <i class="fas fa-edit mr-1"></i> Modificar
                </button>
                <button type="button" id="btn-cobrar" class="col-span-2 mt-1 px-4 py-3 rounded-xl bg-slate-800 text-white font-bold text-sm shadow hover:bg-slate-900 transition-colors">
                    <i class="fas fa-hand-holding-usd mr-2"></i> Cobrar Cuenta
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('modal-detalle');
    const mContainer = modal.querySelector('div');
    const mTitulo = document.getElementById('modal-titulo');
    const mSubtitle = document.getElementById('modal-subtitle');
    const mProductos = document.getElementById('modal-productos');
    const mTotal = document.getElementById('modal-total');
    const mActions = document.getElementById('modal-actions');
    const posUrl = '<?= base_url("ventas/pos") ?>';
    const precuentaUrl = '<?= base_url("ventas/pre_cuenta") ?>';
    const detalleUrl = '<?= base_url("ventas/detalle_pendiente") ?>';

    function cerrarDetalle() {
        modal.classList.remove('opacity-100');
        mContainer.classList.remove('scale-100');
        setTimeout(() => modal.classList.replace('flex', 'hidden'), 300);
    }

    function abrirDetalle(id_mesa, codigo_mesa, id_venta) {
        if (!id_venta || id_venta == 0) return;
        mTitulo.textContent = 'Mesa ' + codigo_mesa;
        mSubtitle.textContent = 'Cargando consumo...';
        mProductos.innerHTML = '<li class="text-center text-slate-400 text-sm py-4"><i class="fas fa-circle-notch fa-spin"></i> Obteniendo detalle...</li>';
        mTotal.textContent = 'S/ 0.00';
        mActions.style.display = 'none';
        
        modal.classList.replace('hidden', 'flex');
        // trigger animation
        setTimeout(() => {
            modal.classList.add('opacity-100');
            mContainer.classList.add('scale-100');
        }, 10);

        fetch(`${detalleUrl}/${id_venta}`)
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    mSubtitle.textContent = `Cuenta abierta (Ticket #${data.venta.id.toString().padStart(6,'0')})`;
                    mTotal.textContent = `S/ ${parseFloat(data.venta.total).toFixed(2)}`;
                    
                    if (data.detalles.length === 0) {
                        mProductos.innerHTML = '<li class="text-center text-slate-400 text-sm">Sin productos</li>';
                    } else {
                        mProductos.innerHTML = data.detalles.map(d => `
                            <li class="flex items-start justify-between">
                                <div class="flex items-start gap-2">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-slate-100 text-xs font-bold text-slate-600 shrink-0">${parseFloat(d.cantidad)}</span>
                                    <div>
                                        <p class="text-sm font-bold text-slate-700 leading-tight block">${d.nombre}</p>
                                    </div>
                                </div>
                                <span class="text-sm font-bold text-slate-900 ml-4 shrink-0">S/ ${parseFloat(d.subtotal).toFixed(2)}</span>
                            </li>
                        `).join('');
                    }

                    // Setup actions
                    mActions.style.display = 'grid';
                    document.getElementById('btn-precuenta').onclick = () => window.open(`${precuentaUrl}/${data.venta.id}`, '_blank');
                    document.getElementById('btn-modificar').onclick = () => window.location.href = `${posUrl}?mesa=${id_mesa}&id_venta=${data.venta.id}&modificar=1`;
                    document.getElementById('btn-cobrar').onclick = () => window.location.href = `${posUrl}?mesa=${id_mesa}&id_venta=${data.venta.id}&cobrar=1`;

                } else {
                    mProductos.innerHTML = `<li class="text-center text-red-500 text-sm py-4"><i class="fas fa-exclamation-triangle"></i> ${data.message}</li>`;
                }
            })
            .catch(err => {
                mProductos.innerHTML = '<li class="text-center text-red-500 text-sm py-4"><i class="fas fa-link-slash"></i> Error de conexión</li>';
            });
    }

    // Cerrar si se da un clic fuera
    modal.addEventListener('click', (e) => {
        if(e.target === modal) cerrarDetalle();
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let isDragging = false;
    let currentCard = null;
    let startX, startY, initialPosX, initialPosY, initialTransform;
    let timer = null;
    let movedPixels = 0; // Para saber si fue un clic arrastrado

    const showToast = (msg) => {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-slate-800 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-lg shadow-slate-900/20 z-50 transition-opacity duration-300';
        toast.textContent = msg;
        document.body.appendChild(toast);
        setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 2000);
    };

    document.querySelectorAll('.mesa-card').forEach(card => {
        card.addEventListener('mousedown', e => {
            if(e.target.tagName === 'SELECT' || e.target.tagName === 'BUTTON' || e.target.tagName === 'A' || e.target.closest('a')) return;
            
            isDragging = true;
            movedPixels = 0;
            currentCard = card;
            startX = e.clientX;
            startY = e.clientY;
            initialPosX = parseInt(card.dataset.posX) || 0;
            initialPosY = parseInt(card.dataset.posY) || 0;
            
            card.style.zIndex = 50;
            card.style.transition = 'none'; // Quitar transición para que el arrastre sea fluido
            card.classList.add('shadow-xl', 'scale-105');
        });
    });

    document.addEventListener('mousemove', e => {
        if(!isDragging || !currentCard) return;
        e.preventDefault();
        let dx = e.clientX - startX;
        let dy = e.clientY - startY;
        
        movedPixels = Math.abs(dx) + Math.abs(dy); // tracking drag distance

        <?php if (!empty($es_admin)): ?>
        // Solo administradores pueden arrastrar mover visualmente
        if (movedPixels > 5) {
            let newPosX = initialPosX + Math.round(dx / 4);
            let newPosY = initialPosY + Math.round(dy / 4);
            currentCard.style.transform = `translate(${newPosX * 4}px, ${newPosY * 4}px)`;
            currentCard.dataset.newPosX = newPosX;
            currentCard.dataset.newPosY = newPosY;
        }
        <?php endif; ?>
    });

    document.addEventListener('mouseup', e => {
        if(!isDragging || !currentCard) return;
        isDragging = false;
        
        let cardToHandle = currentCard; // Evitar que currentCard se limpie antes de usarla
        currentCard.style.zIndex = '';
        currentCard.style.transition = '';
        currentCard.classList.remove('shadow-xl', 'scale-105');
        currentCard = null;

        if (movedPixels < 5) {
            // FUE UN CLIC NORMAL:
            let id = cardToHandle.dataset.id;
            let est = cardToHandle.dataset.estado;
            let venta = parseInt(cardToHandle.dataset.venta);
            let cod = cardToHandle.querySelector('.text-lg').textContent;

            // Diferenciar flujos:
            if (est === 'libre' || est === 'limpieza') {
                // Ir directo al pos
                window.location.href = `${posUrl}?mesa=${id}`;
            } else {
                // Ocupada o reservada
                if (venta > 0) {
                    abrirDetalle(id, cod, venta);
                } else {
                    // Ocupada pero no tiene cuenta generada acá (quizas se ocupó manual)
                    // ir directo a abrir una
                    window.location.href = `${posUrl}?mesa=${id}`;
                }
            }
            return;
        }

        <?php if (!empty($es_admin)): ?>
        let finalPosX = parseInt(cardToHandle.dataset.newPosX);
        let finalPosY = parseInt(cardToHandle.dataset.newPosY);
        
        if(!isNaN(finalPosX) && !isNaN(finalPosY) && (finalPosX !== initialPosX || finalPosY !== initialPosY)) {
            // Guardar nueva posición
            let form = cardToHandle.querySelector('.form-mover');
            if(form) {
                form.querySelector('input[name="pos_x"]').value = finalPosX;
                form.querySelector('input[name="pos_y"]').value = finalPosY;
                
                let formData = new FormData(form);
                fetch(form.action, { method: 'POST', body: formData }).then(res => {
                    if (res.ok) {
                        cardToHandle.dataset.posX = finalPosX;
                        cardToHandle.dataset.posY = finalPosY;
                        showToast('Posición guardada');
                    } else {
                        cardToHandle.style.transform = `translate(${initialPosX * 4}px, ${initialPosY * 4}px)`;
                        showToast('Error al guardar la posición');
                    }
                }).catch(() => {
                    cardToHandle.style.transform = `translate(${initialPosX * 4}px, ${initialPosY * 4}px)`;
                    showToast('Error de conexión');
                });
            }
        }
        <?php endif; ?>
    });
});
</script>
