<?php
$tab = isset($tab_activa) ? $tab_activa : 'todos';
?>
<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0"
     x-data="res_categorias()">

    <!-- Contenedor Principal: Tamaño normalizado -->
    <div class="p-4 sm:p-5 lg:p-8 w-full max-w-[1600px] mx-auto space-y-6">
        
        <!-- Header Compacto -->
        <header class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-0.5">
                <nav class="flex items-center gap-2 text-[9px] font-black text-blue-600 uppercase tracking-widest">
                    <i class="fas fa-home"></i>
                    <span>Inventario</span>
                    <i class="fas fa-chevron-right text-[7px] opacity-40"></i>
                    <span class="text-slate-400">Productos</span>
                </nav>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">Catálogo de <span class="text-blue-600">Productos</span></h1>
            </div>
            
            <div class="flex items-center gap-2">
                <button @click="abrirModalGestion()" 
                        class="group flex items-center px-4 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-xs transition-all hover:bg-slate-50 active:scale-95 shadow-sm">
                    <i class="fas fa-cog mr-2 text-slate-400 group-hover:rotate-90 transition-transform"></i> 
                    Categorías
                </button>
                <a href="<?= base_url('productos/nuevo') ?>" 
                   class="flex items-center px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold text-xs transition-all shadow-lg active:scale-95">
                    <i class="fas fa-plus mr-2 text-blue-400"></i> Nuevo Producto
                </a>
            </div>
        </header>

        <!-- Tarjetas de Estadísticas Compactas -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base">
                    <i class="fas fa-box"></i>
                </div>
                <div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Total</span>
                    <div class="text-lg font-black text-slate-800 leading-none"><?= $conteos['todos'] ?? 0 ?></div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
                <?php 
                    $low_stock = 0;
                    if (isset($productos_dashboard) && is_array($productos_dashboard)) {
                        foreach ($productos_dashboard as $p) {
                            if (isset($p->behavior) && $p->behavior !== 'cocteles' && $p->stock <= $p->stock_minimo) $low_stock++;
                        }
                    }
                ?>
                <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-base">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <span class="text-[9px] font-black text-red-400 uppercase tracking-widest block leading-tight">Crítico</span>
                    <div class="text-lg font-black text-slate-800 leading-none"><?= $low_stock ?></div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base">
                    <i class="fas fa-cocktail"></i>
                </div>
                <div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Barra</span>
                    <div class="text-lg font-black text-slate-800 leading-none"><?= $conteos['licores'] ?? 0 ?></div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-base">
                    <i class="fas fa-mortar-pestle"></i>
                </div>
                <div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Recetas</span>
                    <?php 
                        $con_receta = 0;
                        if (isset($productos_dashboard) && is_array($productos_dashboard)) {
                            foreach ($productos_dashboard as $p) { if (!empty($p->tiene_receta)) $con_receta++; }
                        }
                    ?>
                    <div class="text-lg font-black text-slate-800 leading-none"><?= $con_receta ?></div>
                </div>
            </div>
        </div>

        <!-- Filtros y Tabla -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex overflow-x-auto gap-2 pb-1 scrollbar-hide">
                    <?php $is_todos = ($tab === 'todos'); ?>
                    <a href="<?= base_url('productos?tab=todos') ?>"
                       class="px-4 py-2 rounded-lg font-black text-[10px] uppercase tracking-widest transition-all
                       <?= $is_todos ? 'bg-slate-800 text-white' : 'bg-slate-50 text-slate-400 hover:bg-slate-100' ?>">
                        Todos
                    </a>

                    <?php if (!empty($categorias)): foreach ($categorias as $cat): 
                        $is = ($tab == $cat->id);
                        $bg_map = [
                            'emerald' => $is ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-600',
                            'violet'  => $is ? 'bg-violet-600 text-white' : 'bg-violet-50 text-violet-600',
                            'amber'   => $is ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-600',
                            'rose'    => $is ? 'bg-rose-500 text-white' : 'bg-rose-50 text-rose-600',
                            'blue'    => $is ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-600',
                        ];
                        $cls = $bg_map[$cat->color] ?? $bg_map['blue'];
                    ?>
                        <a href="<?= base_url('productos?tab=' . $cat->id) ?>"
                           class="px-4 py-2 rounded-lg font-black text-[10px] uppercase tracking-widest transition-all flex items-center gap-2 <?= $cls ?>">
                            <i class="fas <?= !empty($cat->icono) ? $cat->icono : 'fa-tag' ?> opacity-60"></i>
                            <?= htmlspecialchars($cat->nombre) ?>
                        </a>
                    <?php endforeach; endif; ?>
                </div>

                <div class="relative w-full md:w-64">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-xs"></i>
                    <input type="text" id="busqueda_productos" placeholder="Buscar producto..." 
                           class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-100 rounded-xl outline-none focus:bg-white focus:border-blue-300 text-xs font-bold transition-all">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[800px]">
                    <thead>
                        <tr class="bg-slate-50 text-[9px] font-black uppercase text-slate-400 tracking-widest">
                            <th class="px-6 py-4">Producto</th>
                            <th class="px-4 py-4 text-center">Categoría</th>
                            <th class="px-4 py-4 text-right">Precio</th>
                            <th class="px-4 py-4 text-center">Stock</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla_productos" class="divide-y divide-slate-50">
                        <?php if (!empty($productos)): foreach ($productos as $p): ?>
                        <tr class="group hover:bg-slate-50 transition-all duration-200">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg overflow-hidden bg-slate-100 flex-shrink-0 border border-slate-200">
                                        <?php if ($p->imagen): ?>
                                            <img src="<?= base_url('uploads/productos/'. $p->imagen) ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center text-slate-300 transform scale-75"><i class="fas fa-image"></i></div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="text-[13px] font-bold text-slate-800 leading-tight"><?= htmlspecialchars($p->nombre) ?></div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-[9px] text-slate-400 font-mono">#<?= htmlspecialchars($p->codigo_barras) ?></span>
                                            <?php if (!empty($p->tiene_receta)): ?>
                                                <span class="text-[8px] font-black text-violet-500 uppercase bg-violet-50 px-1 rounded-sm border border-violet-100">Receta</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php 
                                $cat_icon  = $p->categoria_icono ?: 'fa-tag';
                                $cat_color = $p->categoria_color ?: 'blue';
                                $badges = [
                                    'emerald' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                    'violet'  => 'bg-violet-50 text-violet-600 border-violet-100',
                                    'amber'   => 'bg-amber-50 text-amber-600 border-amber-100',
                                    'rose'    => 'bg-rose-50 text-rose-600 border-rose-100',
                                    'blue'    => 'bg-blue-50 text-blue-600 border-blue-100',
                                ];
                                $c_cls = $badges[$cat_color] ?? $badges['blue'];
                                ?>
                                <span class="px-2.5 py-1 rounded-lg border <?= $c_cls ?> text-[9px] font-black uppercase inline-flex items-center gap-1.5">
                                    <i class="fas <?= $cat_icon ?> opacity-70"></i>
                                    <?= htmlspecialchars($p->categoria_nombre ?: 'Otros') ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-xs font-black text-slate-900">S/ <?= number_format($p->precio_venta, 2) ?></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($p->behavior === 'cocteles'): ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black text-amber-700 bg-amber-50 border border-amber-100">Barra</span>
                                <?php else: 
                                    $low = ($p->stock <= $p->stock_minimo);
                                    $s_cls = $low ? 'text-red-600 bg-red-50 border-red-100' : 'text-slate-600 bg-slate-50 border-slate-100';
                                ?>
                                    <span class="px-3 py-1 rounded-lg border <?= $s_cls ?> text-xs font-black">
                                        <?= number_format($p->stock, 0) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="<?= base_url('productos/editar/'.$p->id) ?>" class="w-8 h-8 flex items-center justify-center text-blue-500 hover:bg-blue-50 rounded-lg transition-colors">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>
                                    <button onclick="confirmarEliminar('<?= base_url('productos/eliminar/'.$p->id) ?>')" class="w-8 h-8 flex items-center justify-center text-red-400 hover:bg-red-50 rounded-lg transition-colors">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="5" class="py-20 text-center text-slate-300 font-bold text-xs uppercase tracking-widest">No hay registros</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL DE GESTIÓN: Ajuste Compacto -->
    <template x-teleport="body">
        <div x-show="modalGestion" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
            <div x-show="modalGestion" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
            <div @click.away="modalGestion = false" class="relative bg-white w-full max-w-4xl max-h-[85vh] rounded-2xl shadow-2xl overflow-hidden flex flex-col border border-slate-200">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-black text-slate-800">Gestionar Categorías</h2>
                        <p class="text-[9px] text-slate-400 font-black uppercase mt-0.5">Pestañas del Catálogo</p>
                    </div>
                    <button @click="modalGestion = false" class="text-slate-400 hover:text-red-500"><i class="fas fa-times text-xl"></i></button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 flex-1 overflow-hidden">
                    <div class="lg:col-span-5 p-6 bg-white overflow-y-auto border-r border-slate-100">
                        <div class="space-y-5">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-[9px] font-black text-slate-400 uppercase">Nombre</label>
                                <input type="text" x-model="form.nombre" class="w-full px-4 py-2 bg-slate-50 border border-slate-100 rounded-xl outline-none font-bold text-sm">
                            </div>
                            <!-- ... resto del form similar pero compacto ... -->
                            <div class="flex flex-col gap-1.5">
                                <label class="text-[9px] font-black text-slate-400 uppercase">Icono</label>
                                <div class="grid grid-cols-6 gap-2 p-2 bg-slate-50 rounded-xl">
                                    <template x-for="icon in ['fa-utensils', 'fa-wine-bottle', 'fa-glass-martini-alt', 'fa-hamburger', 'fa-beer', 'fa-tag']">
                                        <button type="button" @click="form.icono = icon" 
                                                class="h-8 flex items-center justify-center rounded-lg border-2 text-xs"
                                                :class="form.icono === icon ? 'bg-slate-800 border-slate-800 text-white' : 'bg-white border-transparent text-slate-400'">
                                            <i class="fas" :class="icon"></i>
                                        </button>
                                    </template>
                                </div>
                            </div>
                            
                            <div class="flex flex-col gap-1.5">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Lógica</label>
                                <select x-model="form.comportamiento" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-[11px] outline-none">
                                    <option value="produccion">⚙️ PRODUCCIÓN (Recetas)</option>
                                    <option value="licores">🥃 LICORES (Base bar)</option>
                                    <option value="cocteles">🍸 CÓCTELES (Consume bar)</option>
                                </select>
                            </div>

                            <button @click="guardarCategoria()" 
                                    class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black uppercase text-[10px] tracking-widest shadow-lg active:scale-95 transition-all">
                                Guardar Pestaña
                            </button>
                        </div>
                    </div>
                    <div class="lg:col-span-7 bg-slate-50 overflow-y-auto p-4 space-y-2">
                        <template x-for="cat in listado" :key="cat.id">
                            <div class="bg-white p-3 rounded-xl border border-slate-100 flex items-center justify-between shadow-sm">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs" :class="'bg-' + cat.color + '-500'">
                                        <i class="fas" :class="cat.icono || 'fa-tag'"></i>
                                    </div>
                                    <div class="font-bold text-slate-800 text-xs uppercase" x-text="cat.nombre"></div>
                                </div>
                                <div class="flex gap-1">
                                    <button @click="editarCategoria(cat)" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg"><i class="fas fa-edit text-xs"></i></button>
                                    <button @click="eliminarCategoria_ajax(cat.id)" class="p-2 text-red-400 hover:bg-red-50 rounded-lg"><i class="fas fa-trash-alt text-xs"></i></button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function res_categorias() {
    return {
        modalGestion: false, loading: false, editando: false, listado: [],
        form: { id: null, nombre: '', icono: 'fa-tag', color: 'blue', comportamiento: 'produccion', orden: 0 },
        abrirModalGestion() { this.modalGestion = true; this.cargarCategorias(); },
        async cargarCategorias() { const res = await fetch('<?= base_url('productos/get_categorias_json') ?>'); this.listado = await res.json(); },
        editarCategoria(cat) { this.editando = true; this.form = { ...cat }; },
        resetForm() { this.editando = false; this.form = { id: null, nombre: '', icono: 'fa-tag', color: 'blue', comportamiento: 'produccion', orden: 0 }; },
        async guardarCategoria() {
            if (!this.form.nombre) return Swal.fire('Error', 'Nombre obligatorio', 'error');
            const fd = new FormData();
            if (this.form.id && this.form.id !== 'null') fd.append('id', this.form.id);
            fd.append('nombre', this.form.nombre);
            fd.append('icono', this.form.icono);
            fd.append('color', this.form.color);
            fd.append('comportamiento', this.form.comportamiento);
            fd.append('orden', this.form.orden);
            const res = await fetch('<?= base_url('productos/guardar_categoria_ajax') ?>', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) { this.resetForm(); this.cargarCategorias(); }
        },
        async eliminarCategoria_ajax(id) {
            if(confirm('¿Eliminar?')) {
                const fd = new FormData(); fd.append('id', id);
                const res = await fetch('<?= base_url('productos/eliminar_categoria_ajax') ?>', { method: 'POST', body: fd });
                if ((await res.json()).success) this.cargarCategorias();
            }
        }
    }
}
// Buscador Simple
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('busqueda_productos');
    const tabla = document.getElementById('tabla_productos');
    if(input && tabla) {
        input.addEventListener('keyup', () => {
            const val = input.value.toLowerCase();
            Array.from(tabla.getElementsByTagName('tr')).forEach(tr => {
                const txt = tr.textContent.toLowerCase();
                tr.style.display = txt.includes(val) ? '' : 'none';
            });
        });
    }
});
function confirmarEliminar(url) {
    Swal.fire({ title: '¿Eliminar?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, eliminar' })
    .then((result) => { if (result.isConfirmed) window.location.href = url; });
}
</script>