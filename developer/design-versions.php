<?php
/**
 * Design Version Management
 * Create, view, rollback, and delete design versions
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/header.php';

use App\Services\DesignVersionService;

$pageTitle = 'Design Version Management';
$versionService = new DesignVersionService();

$message = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $description = trim($_POST['description'] ?? 'Manual snapshot');
        $createdBy = $_SESSION['developer_username'] ?? 'developer';
        
        $result = $versionService->createVersion($description, $createdBy);
        
        if ($result['success']) {
            $message = "Version created successfully! Backed up {$result['files_backed_up']} files.";
        } else {
            $error = 'Failed to create version.';
        }
    } elseif ($action === 'rollback') {
        $versionId = $_POST['version_id'] ?? '';
        
        if ($versionId) {
            $result = $versionService->rollbackToVersion($versionId);
            
            if ($result['success']) {
                $message = "Successfully rolled back to version. {$result['files_restored']} files restored.";
            } else {
                $error = $result['error'] ?? 'Failed to rollback.';
            }
        }
    } elseif ($action === 'delete') {
        $versionId = $_POST['version_id'] ?? '';
        
        if ($versionId) {
            $result = $versionService->deleteVersion($versionId);
            
            if ($result['success']) {
                $message = 'Version deleted successfully.';
            } else {
                $error = $result['error'] ?? 'Failed to delete version.';
            }
        }
    }
}

// Get all versions
$versions = $versionService->getAllVersions();
$currentVersion = $versionService->getCurrentVersion();

?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl shadow-xl p-8 mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">
                    <i class="fas fa-history mr-3"></i>
                    Design Version Management
                </h1>
                <p class="text-purple-100 text-lg">Create snapshots, rollback, and manage front-end design versions</p>
            </div>
            <div class="bg-white/20 rounded-full p-6">
                <i class="fas fa-code-branch text-4xl"></i>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2 text-xl"></i>
            <span class="font-semibold"><?= escape($message) ?></span>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2 text-xl"></i>
            <span class="font-semibold"><?= escape($error) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Create New Version -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4 text-gray-800">
            <i class="fas fa-camera mr-2 text-purple-600"></i>
            Create New Snapshot
        </h2>
        <p class="text-gray-600 mb-4">Create a snapshot of all front-end files before making design changes.</p>
        
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="create">
            
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Description (optional)
                </label>
                <input type="text" 
                       id="description" 
                       name="description" 
                       placeholder="e.g., Before modern redesign, After color scheme update"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
            
            <button type="submit" class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:shadow-lg transform hover:scale-105 transition-all">
                <i class="fas fa-camera mr-2"></i>
                Create Snapshot
            </button>
        </form>
    </div>

    <!-- Current Version Info -->
    <?php if ($currentVersion): ?>
    <div class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl shadow-lg p-6 mb-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">
                    <i class="fas fa-check-circle text-blue-600 mr-2"></i>
                    Current Active Version
                </h3>
                <p class="text-gray-700">
                    <strong>Version:</strong> <?= escape($currentVersion['version_id']) ?><br>
                    <strong>Created:</strong> <?= date('F j, Y g:i A', strtotime($currentVersion['created_at'])) ?><br>
                    <strong>Files:</strong> <?= $currentVersion['files_count'] ?? 0 ?> files backed up
                </p>
                <?php if (!empty($currentVersion['description'])): ?>
                    <p class="text-gray-600 mt-2 italic"><?= escape($currentVersion['description']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Versions List -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-4 text-gray-800">
            <i class="fas fa-list mr-2 text-purple-600"></i>
            Version History
        </h2>
        
        <?php if (empty($versions)): ?>
            <div class="text-center py-12">
                <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg">No versions created yet.</p>
                <p class="text-gray-400 text-sm mt-2">Create your first snapshot to get started!</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Version ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Files</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($versions as $version): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <code class="text-sm text-gray-900 bg-gray-100 px-2 py-1 rounded">
                                    <?= escape(substr($version['version_id'], 0, 20)) ?>...
                                </code>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    <?= escape($version['description'] ?? 'No description') ?>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    by <?= escape($version['created_by'] ?? 'system') ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('M j, Y', strtotime($version['created_at'])) ?><br>
                                <span class="text-xs"><?= date('g:i A', strtotime($version['created_at'])) ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-semibold">
                                    <?= $version['files_count'] ?? 0 ?> files
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (($version['status'] ?? 'active') === 'rolled_back'): ?>
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-semibold">
                                        <i class="fas fa-undo mr-1"></i> Rolled Back
                                    </span>
                                <?php else: ?>
                                    <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs font-semibold">
                                        Active
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to rollback to this version? This will restore all files from this snapshot.');">
                                    <input type="hidden" name="action" value="rollback">
                                    <input type="hidden" name="version_id" value="<?= escape($version['version_id']) ?>">
                                    <button type="submit" 
                                            class="text-blue-600 hover:text-blue-900 hover:underline"
                                            title="Rollback to this version">
                                        <i class="fas fa-undo mr-1"></i> Rollback
                                    </button>
                                </form>
                                
                                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this version? This action cannot be undone.');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="version_id" value="<?= escape($version['version_id']) ?>">
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900 hover:underline"
                                            title="Delete this version">
                                        <i class="fas fa-trash mr-1"></i> Delete
                                    </button>
                                </form>
                                
                                <button onclick="showVersionDetails('<?= escape($version['version_id']) ?>')" 
                                        class="text-purple-600 hover:text-purple-900 hover:underline"
                                        title="View details">
                                    <i class="fas fa-info-circle mr-1"></i> Details
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Version Details Modal -->
<div id="versionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-6 rounded-t-xl flex items-center justify-between">
            <h3 class="text-2xl font-bold">Version Details</h3>
            <button onclick="closeVersionModal()" class="text-white hover:text-gray-200">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <div class="p-6" id="versionDetailsContent">
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-4xl text-gray-400"></i>
                <p class="text-gray-500 mt-4">Loading...</p>
            </div>
        </div>
    </div>
</div>

<script>
function showVersionDetails(versionId) {
    const modal = document.getElementById('versionModal');
    const content = document.getElementById('versionDetailsContent');
    
    modal.classList.remove('hidden');
    content.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-4xl text-gray-400"></i><p class="text-gray-500 mt-4">Loading...</p></div>';
    
    // Fetch version details via AJAX
    fetch('<?= url('developer/api/design-version-details.php') ?>?version_id=' + encodeURIComponent(versionId))
        .then(response => response.json())
        .then(data => {
            if (data.success && data.version) {
                const v = data.version;
                content.innerHTML = `
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-bold text-gray-800 mb-2">Version Information</h4>
                            <div class="space-y-2 text-sm">
                                <div><strong>Version ID:</strong> <code class="bg-gray-200 px-2 py-1 rounded">${v.version_id}</code></div>
                                <div><strong>Description:</strong> ${v.description || 'No description'}</div>
                                <div><strong>Created:</strong> ${new Date(v.created_at).toLocaleString()}</div>
                                <div><strong>Created By:</strong> ${v.created_by || 'system'}</div>
                                <div><strong>Files Count:</strong> ${v.files_count || 0} files</div>
                                <div><strong>Status:</strong> <span class="px-2 py-1 rounded ${v.status === 'rolled_back' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">${v.status || 'active'}</span></div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-bold text-gray-800 mb-2">Backed Up Files</h4>
                            <div class="max-h-64 overflow-y-auto">
                                <ul class="space-y-1 text-sm">
                                    ${(v.files || []).map(file => `<li class="flex items-center text-gray-700"><i class="fas fa-file mr-2 text-purple-600"></i>${file}</li>`).join('')}
                                </ul>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                content.innerHTML = '<div class="text-center py-8 text-red-500">Failed to load version details.</div>';
            }
        })
        .catch(error => {
            content.innerHTML = '<div class="text-center py-8 text-red-500">Error loading version details.</div>';
        });
}

function closeVersionModal() {
    document.getElementById('versionModal').classList.add('hidden');
}

// Close modal on outside click
document.getElementById('versionModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeVersionModal();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

