<?php
$tab = isset($tab_activa) ? $tab_activa : 'todos';
$c = isset($conteos) && is_array($conteos) ? $conteos : ['todos' => 0, 'produccion' => 0, 'licores' => 0, 'cocteles' => 0];
$tabs = [
    'todos'       => ['label' => 'Todas',       'icon' => 'fa-layer-group',   'active' => 'border-slate-800 text-slate-900 bg-white', 'inactive' => 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100'],
    'produccion'  => ['label' => 'Producción',  'icon' => 'fa-utensils',       'active' => 'border-emerald-600 text-emerald-900 bg-emerald-50/80', 'inactive' => 'border-transparent text-slate-500 hover:text-emerald-800 hover:bg-emerald-50/50'],
    'licores'     => ['label' => 'Licores',     'icon' => 'fa-wine-bottle',    'active' => 'border-violet-600 text-violet-900 bg-violet-50/80', 'inactive' => 'border-transparent text-slate-500 hover:text-violet-800 hover:bg-violet-50/50'],
    'cocteles'    => ['label' => 'Cócteles',    'icon' => 'fa-glass-martini-alt', 'active' => 'border-amber-500 text-amber-950 bg-amber-50/90', 'inactive' => 'border-transparent text-slate-500 hover:text-amber-900 hover:bg-amber-50/50'],
];
?>
<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0">

    <div class="p-4 sm:p-6 lg:p-10 w-full max-w-[1600px] mx-auto">
        
        <header class="flex flex-col md:flex-row md:items-end justify-between mb-6 gap-6">
            <div>
                <nav class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Gestión de Inventario</nav>
                <h1 class="text-3xl font-black text-slate-800">Productos en Sucursal</h1>
                <p class="text-sm text-slate-500 mt-1">Filtra por línea de negocio para ver cada catálogo por separado.</p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <a href="<?= base_url('productos/nuevo') ?>" class="flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition-all shadow-lg shadow-blue-100">
                    <i class="fas fa-plus mr-2"></i> Nuevo Producto
                </a>
            </div>
        </header>

        <!-- Pestañas -->
        <div class="mb-4 flex flex-col gap-2">
            <div class="flex overflow-x-auto pb-1 gap-2 -mx-1 px-1 scrollbar-thin scrollbar-thumb-slate-200" style="scrollbar-width: thin;">
                <?php foreach ($tabs as $key => $meta):
                    $is = ($tab === $key);
                    $cls = $is ? $meta['active'] : $meta['inactive'];
                    $n = isset($c[$key]) ? (int) $c[$key] : 0;
                ?>
                    <a href="<?= base_url('productos?tab=' . $key) ?>"
                       class="flex items-center gap-2 whitespace-nowrap px-4 py-2.5 rounded-xl border-b-2 font-bold text-sm transition-all shrink-0 <?= $cls ?> <?= $is ? 'shadow-sm' : '' ?>">
                        <i class="fas <?= $meta['icon'] ?> text-xs opacity-80"></i>
                        <?= $meta['label'] ?>
                        <span class="min-w-[1.5rem] text-center text-[10px] px-1.5 py-0.5 rounded-md font-black <?= $is ? 'bg-white/70 text-slate-700' : 'bg-slate-200/80 text-slate-600' ?>"><?= $n ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[640px]">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-widest border-b border-slate-100">
                            <th class="px-4 py-3 font-bold">Código / Producto</th>
                            <th class="px-4 py-3 font-bold text-center">Línea</th>
                            <th class="px-4 py-3 font-bold text-right">Precio Venta</th>
                            <th class="px-4 py-3 font-bold text-center">Stock</th>
                            <th class="px-4 py-3 font-bold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (!empty($productos)): foreach ($productos as $p): ?>
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($p->nombre) ?></div>
                                <div class="text-[10px] text-slate-400 font-mono tracking-wider"><?= htmlspecialchars($p->codigo_barras) ?></div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php
                                $tl = isset($p->tipo_linea) ? $p->tipo_linea : 'produccion';
                                $tlab = ['produccion' => 'Producción', 'licores' => 'Licores', 'cocteles' => 'Cócteles'];
                                $tcolor = ['produccion' => 'bg-emerald-100 text-emerald-800', 'licores' => 'bg-violet-100 text-violet-800', 'cocteles' => 'bg-amber-100 text-amber-900'];
                                $tc = isset($tcolor[$tl]) ? $tcolor[$tl] : 'bg-slate-100 text-slate-600';
                                ?>
                                <span class="px-2 py-0.5 <?= $tc ?> rounded-full text-[10px] font-bold uppercase">
                                    <?= isset($tlab[$tl]) ? $tlab[$tl] : htmlspecialchars($tl) ?>
                                </span>
                                <?php if (!empty($p->categoria)): ?>
                                    <div class="text-[9px] text-slate-400 mt-0.5"><?= htmlspecialchars($p->categoria) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-sm font-black text-slate-900">S/ <?= number_format($p->precio_venta, 2) ?></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php
                                $tl = isset($p->tipo_linea) ? $p->tipo_linea : 'produccion';
                                if ($tl === 'cocteles'):
                                    $rep = isset($p->repositorio_botellas) ? (float) $p->repositorio_botellas : 0;
                                    $mx = isset($p->max_repositorio_botellas) ? (int) $p->max_repositorio_botellas : 5;
                                ?>
                                    <span class="px-3 py-1 rounded-lg text-xs font-black text-amber-800 bg-amber-50" title="Repositorio bar / máx.">
                                        Bar <?= number_format($rep, 0) ?>/<?= $mx ?>
                                    </span>
                                <?php else:
                                    $color_stock = ($p->stock <= $p->stock_minimo) ? 'text-red-600 bg-red-50' : 'text-emerald-600 bg-emerald-50';
                                ?>
                                    <span class="px-3 py-1 rounded-lg text-xs font-black <?= $color_stock ?>">
                                        <?= number_format($p->stock, 0) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="<?= base_url('productos/editar/'.$p->id) ?>" class="p-1.5 text-slate-400 hover:text-blue-600 transition-colors" title="Editar">
                                        <i class="fas fa-edit text-sm"></i>
                                    </a>
                                    <a href="#" 
                                    onclick="confirmarEliminar('<?= base_url('productos/eliminar/'.$p->id) ?>')" 
                                    class="p-1.5 text-slate-400 hover:text-red-600 transition-colors" 
                                    title="Eliminar">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="5" class="px-4 py-14 text-center">
                                <div class="inline-flex flex-col items-center gap-2 text-slate-400">
                                    <i class="fas fa-inbox text-3xl opacity-40"></i>
                                    <p class="text-sm font-medium">No hay productos en esta pestaña.</p>
                                    <a href="<?= base_url('productos/nuevo') ?>" class="text-sm font-bold text-blue-600 hover:underline">Registrar un producto</a>
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
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmarEliminar(url) {
    Swal.fire({
        title: '¿Eliminar producto?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        borderRadius: '1rem',
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}

<?php if ($this->session->flashdata('error')): ?>
    Swal.fire({
        icon: 'error',
        title: 'No se pudo completar',
        text: '<?= $this->session->flashdata('error') ?>',
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Entendido',
        customClass: {
            popup: 'rounded-2xl',
        }
    });
<?php endif; ?>

<?php if ($this->session->flashdata('success')): ?>
    Swal.fire({
        icon: 'success',
        title: 'Listo',
        text: '<?= $this->session->flashdata('success') ?>',
        confirmButtonColor: '#2563eb',
        timer: 2000,
        showConfirmButton: false,
        customClass: {
            popup: 'rounded-2xl',
        }
    });
<?php endif; ?>
</script>