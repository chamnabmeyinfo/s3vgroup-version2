<?php
/**
 * Advanced Filters Component
 * Reusable advanced filtering and column visibility system
 */
$filterId = $filterId ?? 'main-filter';
$defaultColumns = $defaultColumns ?? [];
$availableColumns = $availableColumns ?? [];
$filters = $filters ?? [];
$sortOptions = $sortOptions ?? [];
?>

<div id="<?= $filterId ?>" class="advanced-filters mb-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold flex items-center">
                <i class="fas fa-filter mr-2 text-blue-600"></i>
                Advanced Filters & Columns
            </h2>
            <div class="flex gap-2">
                <button onclick="resetFilters('<?= $filterId ?>')" class="btn-secondary-sm">
                    <i class="fas fa-redo mr-1"></i> Reset
                </button>
                <button onclick="toggleFilterPanel('<?= $filterId ?>')" class="btn-secondary-sm" title="Toggle Filters">
                    <i class="fas fa-chevron-down" id="toggle-icon-<?= $filterId ?>"></i>
                </button>
            </div>
        </div>
        
        <div id="filter-content-<?= $filterId ?>" class="filter-content hidden">
            <form method="GET" id="filter-form-<?= $filterId ?>" class="space-y-6">
                
                <!-- Text Search -->
                <?php if (isset($filters['search'])): ?>
                <div>
                    <label class="block text-sm font-medium mb-2">Search</label>
                    <input type="text" 
                           name="search" 
                           value="<?= escape($_GET['search'] ?? '') ?>"
                           placeholder="Search..."
                           class="w-full px-4 py-2 border rounded-lg"
                           onkeyup="debounceFilter('<?= $filterId ?>')">
                </div>
                <?php endif; ?>
                
                <!-- Date Range -->
                <?php if (isset($filters['date_range'])): ?>
                <div>
                    <label class="block text-sm font-medium mb-2">Date Range</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" 
                               name="date_from" 
                               value="<?= escape($_GET['date_from'] ?? '') ?>"
                               class="px-4 py-2 border rounded-lg">
                        <input type="date" 
                               name="date_to" 
                               value="<?= escape($_GET['date_to'] ?? '') ?>"
                               class="px-4 py-2 border rounded-lg">
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Status Filter -->
                <?php if (isset($filters['status'])): ?>
                <div>
                    <label class="block text-sm font-medium mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border rounded-lg" onchange="applyFilters('<?= $filterId ?>')">
                        <?php foreach ($filters['status']['options'] as $value => $label): ?>
                            <option value="<?= escape($value) ?>" <?= ($_GET['status'] ?? '') === $value || ($_GET['status'] === '' && $value === 'all') ? 'selected' : '' ?>>
                                <?= escape($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <!-- Featured Filter -->
                <?php if (isset($filters['featured'])): ?>
                <div>
                    <label class="block text-sm font-medium mb-2">Featured</label>
                    <select name="featured" class="w-full px-4 py-2 border rounded-lg" onchange="applyFilters('<?= $filterId ?>')">
                        <?php foreach ($filters['featured']['options'] as $value => $label): ?>
                            <option value="<?= escape($value) ?>" <?= ($_GET['featured'] ?? '') === $value || ($_GET['featured'] === '' && $value === 'all') ? 'selected' : '' ?>>
                                <?= escape($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <!-- Type Filter -->
                <?php if (isset($filters['type'])): ?>
                <div>
                    <label class="block text-sm font-medium mb-2">File Type</label>
                    <select name="type" class="w-full px-4 py-2 border rounded-lg" onchange="applyFilters('<?= $filterId ?>')">
                        <?php foreach ($filters['type']['options'] as $value => $label): ?>
                            <option value="<?= escape($value) ?>" <?= ($_GET['type'] ?? '') === $value || ($_GET['type'] === '' && $value === 'all') ? 'selected' : '' ?>>
                                <?= escape($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <!-- Size Filter -->
                <?php if (isset($filters['size'])): ?>
                <div>
                    <label class="block text-sm font-medium mb-2">File Size</label>
                    <select name="size" class="w-full px-4 py-2 border rounded-lg" onchange="applyFilters('<?= $filterId ?>')">
                        <?php foreach ($filters['size']['options'] as $value => $label): ?>
                            <option value="<?= escape($value) ?>" <?= ($_GET['size'] ?? '') === $value || ($_GET['size'] === '' && $value === 'all') ? 'selected' : '' ?>>
                                <?= escape($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <!-- Category Filter -->
                <?php if (isset($filters['category'])): ?>
                <div>
                    <label class="block text-sm font-medium mb-2">Category</label>
                    <select name="category" class="w-full px-4 py-2 border rounded-lg" onchange="applyFilters('<?= $filterId ?>')">
                        <option value="">All Categories</option>
                        <?php foreach ($filters['category']['options'] as $value => $label): ?>
                            <option value="<?= escape($value) ?>" <?= ($_GET['category'] ?? '') === $value ? 'selected' : '' ?>>
                                <?= escape($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <!-- Sort Options -->
                <?php if (!empty($sortOptions)): ?>
                <div>
                    <label class="block text-sm font-medium mb-2">Sort By</label>
                    <select name="sort" class="w-full px-4 py-2 border rounded-lg" onchange="applyFilters('<?= $filterId ?>')">
                        <?php foreach ($sortOptions as $value => $label): ?>
                            <option value="<?= escape($value) ?>" <?= ($_GET['sort'] ?? '') === $value ? 'selected' : '' ?>>
                                <?= escape($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <!-- Column Visibility -->
                <?php if (!empty($availableColumns)): ?>
                <div class="border-t pt-4">
                    <label class="block text-sm font-medium mb-3">Visible Columns</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <?php foreach ($availableColumns as $column => $label): ?>
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       name="columns[]" 
                                       value="<?= escape($column) ?>"
                                       class="column-toggle mr-2"
                                       data-column="<?= escape($column) ?>"
                                       <?= in_array($column, $defaultColumns) || empty($_GET['columns']) ? 'checked' : '' ?>
                                       onchange="toggleColumn('<?= escape($column) ?>', this.checked)">
                                <span class="text-sm"><?= escape($label) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <button type="button" onclick="selectAllColumns('<?= $filterId ?>')" class="text-sm text-blue-600 hover:underline">
                            Select All
                        </button>
                        <button type="button" onclick="deselectAllColumns('<?= $filterId ?>')" class="text-sm text-blue-600 hover:underline">
                            Deselect All
                        </button>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Preserve other GET parameters -->
                <?php foreach ($_GET as $key => $value): ?>
                    <?php if (!in_array($key, ['search', 'status', 'category', 'featured', 'type', 'size', 'sort', 'date_from', 'date_to', 'price_min', 'price_max', 'columns', 'page'])): ?>
                        <?php if (is_array($value)): ?>
                            <?php foreach ($value as $v): ?>
                                <input type="hidden" name="<?= escape($key) ?>[]" value="<?= escape($v) ?>">
                            <?php endforeach; ?>
                        <?php else: ?>
                            <input type="hidden" name="<?= escape($key) ?>" value="<?= escape($value) ?>">
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
                
                <div class="flex gap-2 pt-4 border-t">
                    <button type="button" onclick="applyFilters('<?= $filterId ?>')" class="btn-primary flex-1">
                        <i class="fas fa-filter mr-2"></i> Apply Filters
                    </button>
                    <button type="button" onclick="saveFilterPreset('<?= $filterId ?>')" class="btn-secondary">
                        <i class="fas fa-save mr-2"></i> Save Preset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleFilterPanel(filterId) {
    const content = document.getElementById('filter-content-' + filterId);
    const icon = document.getElementById('toggle-icon-' + filterId);
    
    content.classList.toggle('hidden');
    // Update icon based on visibility
    if (content.classList.contains('hidden')) {
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    } else {
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    }
}

function applyFilters(filterId) {
    document.getElementById('filter-form-' + filterId).submit();
}

function resetFilters(filterId) {
    const form = document.getElementById('filter-form-' + filterId);
    const url = new URL(window.location.href);
    url.search = '';
    window.location.href = url.toString();
}

function toggleColumn(column, visible) {
    const cells = document.querySelectorAll(`[data-column="${column}"]`);
    const headers = document.querySelectorAll(`th[data-column="${column}"]`);
    
    cells.forEach(cell => {
        cell.style.display = visible ? '' : 'none';
    });
    headers.forEach(header => {
        header.style.display = visible ? '' : 'none';
    });
    
    // Save to localStorage
    const visibleColumns = Array.from(document.querySelectorAll('.column-toggle:checked'))
        .map(cb => cb.value);
    localStorage.setItem('visible_columns_' + filterId, JSON.stringify(visibleColumns));
}

function selectAllColumns(filterId) {
    document.querySelectorAll('#filter-form-' + filterId + ' .column-toggle').forEach(cb => {
        cb.checked = true;
        toggleColumn(cb.dataset.column, true);
    });
}

function deselectAllColumns(filterId) {
    document.querySelectorAll('#filter-form-' + filterId + ' .column-toggle').forEach(cb => {
        cb.checked = false;
        toggleColumn(cb.dataset.column, false);
    });
}

// Restore column visibility from localStorage
document.addEventListener('DOMContentLoaded', function() {
    const saved = localStorage.getItem('visible_columns_<?= $filterId ?>');
    if (saved) {
        const visibleColumns = JSON.parse(saved);
        document.querySelectorAll('.column-toggle').forEach(cb => {
            const shouldBeVisible = visibleColumns.includes(cb.value);
            cb.checked = shouldBeVisible;
            toggleColumn(cb.dataset.column, shouldBeVisible);
        });
    }
});

let filterTimeout;
function debounceFilter(filterId) {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        applyFilters(filterId);
    }, 500);
}

function saveFilterPreset(filterId) {
    const form = document.getElementById('filter-form-' + filterId);
    const formData = new FormData(form);
    const preset = {};
    
    for (const [key, value] of formData.entries()) {
        if (key === 'columns[]') {
            if (!preset.columns) preset.columns = [];
            preset.columns.push(value);
        } else {
            preset[key] = value;
        }
    }
    
    const presetName = prompt('Enter preset name:');
    if (presetName) {
        const presets = JSON.parse(localStorage.getItem('filter_presets_' + filterId) || '[]');
        presets.push({ name: presetName, filters: preset });
        localStorage.setItem('filter_presets_' + filterId, JSON.stringify(presets));
        alert('Filter preset saved!');
    }
}
</script>

<style>
.column-toggle {
    cursor: pointer;
}
.filter-content {
    max-height: 600px;
    overflow-y: auto;
}
</style>

