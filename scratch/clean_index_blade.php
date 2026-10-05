<?php
$filePath = __DIR__ . '/../resources/views/dtsen/index.blade.php';
$c = file_get_contents($filePath);

// 1. Export section
$c = str_replace(
    'style="background-color: #0f172a !important; color: #ffffff !important; border: 1px solid #065f46 !important;"',
    'class="p-5 rounded-2xl space-y-3 shadow-md card-dark-slate-emerald"',
    $c
);
$c = str_replace('class="p-5 rounded-2xl space-y-3 shadow-md card-dark-slate" class="p-5 rounded-2xl space-y-3 shadow-md card-dark-slate-emerald"', 'class="p-5 rounded-2xl space-y-3 shadow-md card-dark-slate-emerald"', $c);

$c = str_replace('style="background-color: rgba(16, 185, 129, 0.2) !important;"', 'class="p-1.5 rounded-xl text-emerald-400 font-bold text-sm icon-badge-emerald"', $c);
$c = str_replace('class="p-1.5 rounded-xl text-emerald-400 font-bold text-sm" class="p-1.5 rounded-xl text-emerald-400 font-bold text-sm icon-badge-emerald"', 'class="p-1.5 rounded-xl text-emerald-400 font-bold text-sm icon-badge-emerald"', $c);

$c = str_replace('style="color: #6ee7b7 !important;"', 'class="text-xs font-black uppercase tracking-wider text-emerald-glow"', $c);
$c = str_replace('class="text-xs font-black uppercase tracking-wider" class="text-xs font-black uppercase tracking-wider text-emerald-glow"', 'class="text-xs font-black uppercase tracking-wider text-emerald-glow"', $c);

$c = str_replace('style="background-color: #064e3b !important; color: #a7f3d0 !important; border: 1px solid #047857 !important;"', 'class="px-2 py-0.5 rounded font-mono text-xs card-dark-badge-emerald"', $c);
$c = str_replace('class="px-2 py-0.5 rounded font-mono text-xs" class="px-2 py-0.5 rounded font-mono text-xs card-dark-badge-emerald"', 'class="px-2 py-0.5 rounded font-mono text-xs card-dark-badge-emerald"', $c);

$c = str_replace('style="color: #34d399 !important;"', 'class="text-[11px] font-bold font-mono text-emerald-status"', $c);
$c = str_replace('class="text-[11px] font-bold font-mono" class="text-[11px] font-bold font-mono text-emerald-status"', 'class="text-[11px] font-bold font-mono text-emerald-status"', $c);

$c = str_replace('style="background-color: #1e293b !important; border: 1px solid #334155 !important;"', 'class="p-3.5 rounded-xl space-y-1.5 card-dark-item"', $c);
$c = str_replace('class="p-3.5 rounded-xl space-y-1.5" class="p-3.5 rounded-xl space-y-1.5 card-dark-item"', 'class="p-3.5 rounded-xl space-y-1.5 card-dark-item"', $c);

$c = str_replace('style="color: #a7f3d0 !important;"', 'class="text-xs font-bold truncate font-mono text-emerald-mint"', $c);
$c = str_replace('class="text-xs font-bold truncate font-mono" class="text-xs font-bold truncate font-mono text-emerald-mint"', 'class="text-xs font-bold truncate font-mono text-emerald-mint"', $c);

$c = str_replace('style="border-color: #334155 !important; color: #94a3b8 !important;"', 'class="flex items-center justify-between text-[11px] font-mono pt-1 border-t card-dark-item-footer"', $c);
$c = str_replace('class="flex items-center justify-between text-[11px] font-mono pt-1 border-t" class="flex items-center justify-between text-[11px] font-mono pt-1 border-t card-dark-item-footer"', 'class="flex items-center justify-between text-[11px] font-mono pt-1 border-t card-dark-item-footer"', $c);

$c = str_replace('style="background-color: #020617 !important; color: #f59e0b !important; border: 1px solid #334155 !important;"', 'class="px-2 py-0.5 rounded font-bold card-dark-badge-warning"', $c);
$c = str_replace('class="px-2 py-0.5 rounded font-bold" class="px-2 py-0.5 rounded font-bold card-dark-badge-warning"', 'class="px-2 py-0.5 rounded font-bold card-dark-badge-warning"', $c);

$c = str_replace('style="color: #cbd5e1 !important;"', 'class="text-slate-muted-light"', $c);

// 2. Import section
$c = str_replace('style="background-color: #0f172a !important; color: #ffffff !important; border: 1px solid #1e293b !important;"', '', $c);
$c = str_replace('style="border-color: #334155 !important;"', 'class="border-slate-dark"', $c);
$c = str_replace('style="background-color: rgba(59, 130, 246, 0.2);"', 'class="p-2 rounded-xl text-blue-400 text-lg icon-badge-blue"', $c);
$c = str_replace('class="p-2 rounded-xl text-blue-400 text-lg" class="p-2 rounded-xl text-blue-400 text-lg icon-badge-blue"', 'class="p-2 rounded-xl text-blue-400 text-lg icon-badge-blue"', $c);
$c = str_replace('style="color: #ffffff !important;"', '', $c);
$c = str_replace('style="background-color: #1e3a8a !important; color: #93c5fd !important; border: 1px solid #1d4ed8 !important;"', 'class="card-dark-badge-blue"', $c);
$c = str_replace('style="color: #fde047 !important;"', 'class="text-yellow-code"', $c);
$c = str_replace('style="background-color: #0284c7 !important; color: #ffffff !important; border: 1px solid #0369a1 !important;"', '', $c);
$c = str_replace('style="background-color: #1e293b !important; color: #ffffff !important; border: 1px solid #334155 !important;"', '', $c);
$c = str_replace('style="color: #94a3b8 !important;"', '', $c);
$c = str_replace('style="background-color: #020617 !important; color: #34d399 !important; border: 1px solid #334155 !important;"', 'class="card-dark-code-tag"', $c);
$c = str_replace('style="background-color: #059669 !important; color: #ffffff !important;"', '', $c);

// 3. Notices & modals
$c = str_replace('style="background-color: #fffbeb; border-color: #fde68a; color: #78350f;"', 'class="alert-notice-amber"', $c);
$c = str_replace('style="max-height: 88vh;"', 'class="max-h-[88vh]"', $c);
$c = str_replace('style="max-height: 60vh;"', 'class="max-h-[60vh]"', $c);
$c = str_replace('style="color: #2563eb !important;"', '', $c);
$c = str_replace('style="background-color: #0f172a; color: #ffffff !important;"', 'class="bg-slate-900 text-white"', $c);
$c = str_replace('style="max-height: 220px; overflow-y: auto;"', '', $c);

// 4. Progress bar widths
$c = preg_replace('/style="width:\s*\{\{\s*max\(20,\s*\$percentage\)\s*\}\}%"/', ':style="{ width: \'{{ max(20, $percentage) }}%\' }"', $c);
$c = preg_replace('/style="width:\s*\{\{\s*min\(100,\s*\$percentage\)\s*\}\}%"/', ':style="{ width: \'{{ min(100, $percentage) }}%\' }"', $c);

// 5. display: none -> x-cloak
$c = str_replace('style="display: none;"', 'x-cloak', $c);
$c = str_replace('style="display:none;"', 'x-cloak', $c);

file_put_contents($filePath, $c);
echo "SUCCESS - dtsen/index.blade.php updated\n";
