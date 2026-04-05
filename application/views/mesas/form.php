<?php
$edit = !empty($m);
$m = $edit ? $m : (object) [
    'codigo' => '', 'nombre' => '', 'capacidad' => 4, 'zona' => '',
    'pos_orden' => 0, 'pos_x' => 0, 'pos_y' => 0, 'notas' => '',
    'activo' => 1, 'estado' => 'libre',
];
?>
<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0">
    <div class="p-4 sm:p-6 lg:p-10 max-w-2xl mx-auto">
        <a href="<?= base_url('mesas') ?>" class="text-sm font-bold text-blue-600 hover:text-blue-800 flex items-center gap-2 mb-6">
            <i class="fas fa-arrow-left"></i> Volver a mesas
        </a>
        <h1 class="text-2xl font-black text-slate-800 mb-2"><?= $edit ? 'Editar mesa' : 'Nueva mesa' ?></h1>
        <p class="text-sm text-slate-500 mb-8">Código único por sucursal. Posición X/Y desplaza la tarjeta en el mapa del salón.</p>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm"><?= htmlspecialchars($this->session->flashdata('error')) ?></div>
        <?php endif; ?>

        <form action="<?= base_url('mesas/guardar') ?>" method="post" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
            <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int) $m->id ?>"><?php endif; ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Código *</label>
                    <input type="text" name="codigo" required value="<?= htmlspecialchars($m->codigo) ?>"
                           class="mt-1 w-full px-4 py-3 rounded-xl border border-slate-200 font-mono font-bold" placeholder="M01">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Nombre *</label>
                    <input type="text" name="nombre" required value="<?= htmlspecialchars($m->nombre) ?>"
                           class="mt-1 w-full px-4 py-3 rounded-xl border border-slate-200" placeholder="Mesa ventana">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Capacidad</label>
                    <input type="number" name="capacidad" min="1" max="50" value="<?= (int) $m->capacidad ?>"
                           class="mt-1 w-full px-4 py-3 rounded-xl border border-slate-200">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Zona</label>
                    <input type="text" name="zona" value="<?= htmlspecialchars($m->zona ?? '') ?>"
                           class="mt-1 w-full px-4 py-3 rounded-xl border border-slate-200" placeholder="Salón, Terraza, VIP…">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Orden lista</label>
                    <input type="number" name="pos_orden" value="<?= (int) $m->pos_orden ?>"
                           class="mt-1 w-full px-4 py-3 rounded-xl border border-slate-200">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Posición en mapa</label>
                    <div class="mt-1 w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 text-sm">
                        <i class="fas fa-arrows-alt text-blue-500 mr-2"></i> Se ajusta visualmente arrastrando en el salón.
                    </div>
                    <input type="hidden" name="pos_x" value="<?= (int) $m->pos_x ?>">
                    <input type="hidden" name="pos_y" value="<?= (int) $m->pos_y ?>">
                </div>
            </div>

            <?php if ($edit): ?>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Estado</label>
                    <select name="estado" class="mt-1 w-full px-4 py-3 rounded-xl border border-slate-200 font-bold">
                        <?php foreach (['libre', 'ocupada', 'reservada', 'limpieza'] as $st): ?>
                            <option value="<?= $st ?>" <?= (isset($m->estado) && $m->estado === $st) ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Notas</label>
                <input type="text" name="notas" value="<?= htmlspecialchars($m->notas ?? '') ?>"
                       class="mt-1 w-full px-4 py-3 rounded-xl border border-slate-200" placeholder="Opcional">
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="activo" value="1" <?= !empty($m->activo) ? 'checked' : '' ?> class="rounded border-slate-300 text-blue-600">
                <span class="text-sm font-bold text-slate-700">Mesa activa (visible en POS)</span>
            </label>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-black uppercase tracking-widest text-sm">
                Guardar
            </button>
        </form>
    </div>
</div>
