<?php
$behavior_map = [];
if (!empty($categorias)) {
    foreach ($categorias as $cat) { $behavior_map[$cat->id] = $cat->comportamiento; }
}

$insumos_json = [];
if (!empty($insumos_disponibles)) {
    foreach ($insumos_disponibles as $ins) {
        $insumos_json[] = [ 'id' => (int)$ins->id, 'nombre' => $ins->nombre, 'stock' => (float)$ins->stock ];
    }
}
?>

<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0"
     x-data="{
        id_categoria: '',
        behaviors: <?= htmlspecialchars(json_encode($behavior_map), ENT_QUOTES, 'UTF-8') ?>,
        get tipo() { return this.behaviors[this.id_categoria] || 'produccion'; },
        tieneReceta: false,
        ingredientes: [],
        insumosMaster: <?= htmlspecialchars(json_encode($insumos_json), ENT_QUOTES, 'UTF-8') ?>,
        addIngrediente() {
            this.ingredientes.push({ id_insumo: 0, nombre: '', cantidad: 1, unidad: 'und', showSearch: false });
        },
        removeIngrediente(index) { this.ingredientes.splice(index, 1); },
        selectInsumo(index, insumo) {
            this.ingredientes[index].id_insumo = insumo.id;
            this.ingredientes[index].nombre = insumo.nombre;
            this.ingredientes[index].showSearch = false;
        }
     }">

    <div class="p-4 sm:p-5 lg:p-8 w-full max-w-[1300px] mx-auto space-y-6">
        
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-[9px] font-black text-blue-600 uppercase tracking-widest mb-1">
                    <a href="<?= base_url('productos') ?>">Catálogo</a>
                    <i class="fas fa-chevron-right text-[7px] opacity-30"></i>
                    <span>Nuevo Registro</span>
                </nav>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">Agregar <span class="text-blue-600">Nuevo Producto</span></h1>
            </div>
            <a href="<?= base_url('productos') ?>" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition-colors uppercase tracking-widest">
                <i class="fas fa-times-circle mr-1"></i> Descartar
            </a>
        </header>

        <form action="<?= base_url('productos/guardar') ?>" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-6 pb-20">
            
            <div class="lg:col-span-8 space-y-6">
                <!-- Información General -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Código o SKU</label>
                            <input type="text" name="codigo_barras" required autofocus placeholder="Escriba código..."
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl outline-none font-bold text-slate-700 focus:bg-white focus:border-blue-400 transition-all text-sm">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Categoría / Sección</label>
                            <select name="id_categoria" x-model="id_categoria" required 
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl outline-none font-bold text-slate-700 appearance-none focus:bg-white focus:border-blue-400 transition-all text-sm">
                                <option value="">— SELECCIONE —</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->nombre) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nombre Comercial</label>
                        <input type="text" name="nombre" required placeholder="Ej: Pizza Americana Doble Queso"
                               class="w-full px-4 py-3 bg-slate-900 border-none rounded-xl outline-none font-black text-white text-base shadow-inner focus:ring-4 focus:ring-blue-500/10 transition-all">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Descripción</label>
                        <textarea name="descripcion" rows="2" placeholder="Información opcional para la venta..."
                                  class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl outline-none font-medium text-slate-600 focus:bg-white focus:border-blue-400 transition-all text-sm"></textarea>
                    </div>

                    <!-- Configuración Licores -->
                    <div x-show="tipo === 'cocteles'" x-cloak x-transition class="p-4 rounded-xl border border-amber-100 bg-amber-50/50 space-y-4">
                        <div class="flex items-center gap-2 mb-1">
                            <i class="fas fa-cocktail text-amber-500"></i>
                            <h3 class="font-black text-amber-900 uppercase text-[10px]">Ajustes de Barra</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-amber-800/40 uppercase tracking-widest ml-1">Licor de Origen</label>
                                <select name="id_licor_base" class="w-full px-4 py-2 bg-white border border-amber-200 rounded-xl outline-none font-bold text-xs">
                                    <option value="">— NO ASIGNADO —</option>
                                    <?php if (!empty($licores)): foreach ($licores as $l): ?>
                                        <option value="<?= $l->id ?>"><?= htmlspecialchars($l->nombre) ?></option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-amber-800/40 uppercase tracking-widest ml-1">Rendimiento</label>
                                <input type="number" name="ventas_por_botella" value="1" min="1" class="w-full px-4 py-2 bg-white border border-amber-200 rounded-xl outline-none font-black text-xs text-center">
                            </div>
                        </div>
                    </div>

                    <!-- Checkbox Receta -->
                    <div x-show="tipo === 'produccion'" x-cloak x-transition
                         class="rounded-xl border border-slate-100 bg-slate-50/50 p-4 transition-all"
                         :class="tieneReceta ? 'border-violet-300 bg-violet-50/30' : ''">
                        <label class="flex items-center gap-4 cursor-pointer select-none">
                            <div class="relative flex items-center justify-center">
                                <input type="checkbox" name="tiene_receta" value="1" x-model="tieneReceta" class="sr-only">
                                <div class="w-8 h-8 rounded-lg border-2 transition-all duration-300 flex items-center justify-center shadow-sm"
                                     :class="tieneReceta ? 'bg-violet-600 border-violet-600' : 'bg-white border-slate-200'">
                                    <svg x-show="tieneReceta" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <span class="text-sm font-black text-violet-900 block leading-tight">Activar Receta Automática</span>
                                <p class="text-[9px] text-violet-700/60 font-black uppercase tracking-widest mt-0.5">Descuenta insumos dinámicamente</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- TABLA DE RECETA: CORRECCIÓN DE VISIBILIDAD -->
                <div x-show="tieneReceta" x-cloak x-transition:enter="transition duration-300" 
                     class="bg-white border border-slate-200 rounded-2xl shadow-sm z-20 relative overflow-visible">
                    <div class="px-6 py-4 bg-violet-700 text-white flex items-center justify-between rounded-t-2xl">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-layer-group opacity-60"></i>
                            <h3 class="text-xs font-black uppercase tracking-widest">Composición</h3>
                        </div>
                        <button type="button" @click="addIngrediente()" 
                                class="px-4 py-2 bg-white text-violet-700 hover:bg-violet-50 rounded-xl font-black text-[10px] uppercase shadow-sm transition-all">
                            + Insumo
                        </button>
                    </div>
                    
                    <div class="p-3">
                        <div class="overflow-x-visible">
                            <table class="w-full border-separate border-spacing-y-2 min-w-[600px]">
                                <thead>
                                    <tr class="text-[9px] font-black uppercase text-slate-400 tracking-widest leading-none">
                                        <th class="px-4 py-1 text-left">Insumo</th>
                                        <th class="px-2 py-1 text-center w-24">Cantidad</th>
                                        <th class="px-2 py-1 text-center w-24">Medida</th>
                                        <th class="px-4 py-1 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(ing, index) in ingredientes" :key="index">
                                        <tr class="group animate-in fade-in duration-300">
                                            <td class="relative">
                                                <input type="hidden" name="insumos_id[]" x-model="ing.id_insumo">
                                                <div class="relative flex items-center">
                                                    <i class="fas fa-search absolute left-4 text-slate-300 text-[10px]"></i>
                                                    <input type="text" x-model="ing.nombre" @input="ing.showSearch = true; ing.id_insumo = 0" @click="ing.showSearch = true"
                                                           placeholder="Buscar insumo..."
                                                           class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl outline-none font-bold text-slate-700 focus:bg-white focus:border-violet-400 transition-all text-xs">
                                                </div>
                                                
                                                <!-- Dropdown PREMIUM (FIXED VISIBILITY) -->
                                                <div x-show="ing.showSearch" @click.away="ing.showSearch = false"
                                                     class="absolute z-[999] left-0 md:left-4 w-full md:w-[150%] mt-1 bg-white border border-slate-200 rounded-xl shadow-[0_15px_40px_rgba(0,0,0,0.15)] overflow-hidden max-h-56 overflow-y-auto animate-in fade-in zoom-in-95 duration-200">
                                                    <div class="p-2 bg-slate-50 border-b border-slate-100 text-[8px] font-black text-slate-400 uppercase flex justify-between">
                                                        <span>Resultados del Almacén</span>
                                                        <i class="fas fa-boxes opacity-20"></i>
                                                    </div>
                                                    <template x-for="master in insumosMaster.filter(i => i.nombre.toLowerCase().includes((ing.nombre || '').toLowerCase()))">
                                                        <div @click="selectInsumo(index, master)" 
                                                             class="px-5 py-3 hover:bg-violet-600 hover:text-white cursor-pointer transition-all border-b border-slate-50 last:border-0 group/item">
                                                            <div class="flex items-center justify-between">
                                                                <div class="text-[11px] font-bold uppercase" x-text="master.nombre"></div>
                                                                <span class="text-[9px] font-black bg-slate-50 px-2 py-0.5 rounded text-slate-500 group-hover/item:bg-white/20 group-hover/item:text-white shadow-sm" x-text="master.stock"></span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <div x-show="insumosMaster.filter(i => i.nombre.toLowerCase().includes((ing.nombre || '').toLowerCase())).length === 0" 
                                                         class="p-10 text-center text-slate-300 italic text-[10px]">No hay coincidencias</div>
                                                </div>
                                            </td>
                                            <td class="px-1 text-center">
                                                <input type="number" name="insumos_cantidad[]" x-model="ing.cantidad" step="0.001" 
                                                       class="w-full py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-center font-black text-sm text-violet-700 focus:bg-white focus:border-violet-300">
                                            </td>
                                            <td class="px-1 text-center">
                                                <input type="text" name="insumos_unidad[]" x-model="ing.unidad" placeholder="KM"
                                                       class="w-full py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-center font-black text-[10px] uppercase text-slate-400 focus:bg-white focus:border-violet-300">
                                            </td>
                                            <td class="text-right px-2">
                                                <button type="button" @click="removeIngrediente(index)" class="text-red-300 hover:text-red-500 transition-colors">
                                                    <i class="fas fa-trash-alt text-xs"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bloque Derecho -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Multimedia Compacto -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4" x-data="imagePreview()">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Presentación</h3>
                    <div class="relative aspect-video rounded-xl bg-slate-50 border-2 border-dashed border-slate-100 overflow-hidden group transition-all hover:border-blue-300">
                        <template x-if="preview">
                            <img :src="preview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!preview">
                            <div class="w-full h-full flex flex-col items-center justify-center p-6 text-center group-hover:bg-blue-50/20 transition-colors">
                                <i class="fas fa-camera text-2xl text-slate-200 mb-2"></i>
                                <span class="text-[9px] font-black text-slate-300 uppercase">Subir Foto</span>
                            </div>
                        </template>
                        <input type="file" name="imagen" class="absolute inset-0 opacity-0 cursor-pointer" @change="onFileChange">
                    </div>
                </div>

                <!-- Control Contable -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-5">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Precios y Stock</h3>
                    
                    <div class="space-y-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Costo de Compra (S/)</label>
                            <input type="number" name="precio_compra" step="0.01" value="0.00" 
                                   class="w-full px-4 py-2 bg-slate-50 border border-slate-100 rounded-xl font-bold text-slate-600 text-center focus:bg-white transition-all text-sm">
                        </div>

                        <div class="flex flex-col gap-1.5 p-3 rounded-xl bg-blue-50/50 border border-blue-100 shadow-sm shadow-blue-500/5">
                            <label class="text-[9px] font-black text-blue-600 uppercase tracking-widest ml-2">P. Venta Público (S/)</label>
                            <input type="number" name="precio_venta" step="0.01" value="0.00" 
                                   class="w-full px-4 py-1.5 bg-transparent border-none font-black text-blue-700 text-xl text-center focus:ring-0">
                        </div>
                    </div>

                    <div x-show="tipo !== 'cocteles'" x-transition class="grid grid-cols-2 gap-3 pt-1">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Base Inicial</label>
                            <input type="number" name="stock" value="0" step="0.01" 
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl font-bold text-slate-800 text-center text-sm">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Mínimo</label>
                            <input type="number" name="stock_minimo" value="5" 
                                   class="w-full px-4 py-2.5 bg-red-50 border border-red-100 rounded-xl font-bold text-red-500 text-center text-sm">
                        </div>
                    </div>
                </div>

                <!-- Acción Final -->
                <button type="submit" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black uppercase text-xs tracking-[0.2em] shadow-xl active:scale-95 transition-all">
                    Registrar Ítem
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function imagePreview() {
    return {
        preview: null,
        onFileChange(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (f) => { this.preview = f.target.result; };
                reader.readAsDataURL(file);
            }
        }
    }
}
</script>