<?php
/**
 * Developer Settings (Developer Only)
 * Configure developer tools and preferences
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/header.php';

$pageTitle = 'Developer Settings';

$message = '';
$messageType = '';

// Load configs
$devConfigFile = __DIR__ . '/../config/developer.php';
$deployConfigFile = __DIR__ . '/../deploy-config.json';
$smartImporterConfigFile = __DIR__ . '/../config/smart-importer.php';

$devConfig = file_exists($devConfigFile) ? require $devConfigFile : [];
$deployConfig = file_exists($deployConfigFile) ? json_decode(file_get_contents($deployConfigFile), true) : [];
$smartImporterConfig = file_exists($smartImporterConfigFile) ? require $smartImporterConfigFile : [
    'ai_provider' => 'openai',
    'ai_api_key' => '',
    'ai_enabled' => true
];

// Handle AI config update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_ai_config'])) {
    $aiProvider = $_POST['ai_provider'] ?? 'openai';
    $aiApiKey = trim($_POST['ai_api_key'] ?? '');
    $aiEnabled = isset($_POST['ai_enabled']);
    
    $configFile = __DIR__ . '/../config/smart-importer.php';
    $configContent = "<?php
/**
 * Smart Product Importer Configuration
 * Configure AI API keys and settings
 */

return [
    // AI Provider: 'openai' or 'anthropic'
    'ai_provider' => " . var_export($aiProvider, true) . ",
    
    // AI API Key (get from https://platform.openai.com/api-keys or https://console.anthropic.com/)
    // Leave empty to disable AI features (pattern recognition will still work)
    // You can also set this via environment variable AI_API_KEY
    'ai_api_key' => " . var_export($aiApiKey, true) . ",
    
    // Default extraction method: 'auto', 'pattern', or 'ai'
    // 'auto' = try pattern first, use AI if confidence is low
    // 'pattern' = only use pattern recognition
    // 'ai' = force AI extraction
    'default_method' => 'auto',
    
    // Minimum confidence threshold to use AI (0-100)
    // If pattern recognition confidence is below this, AI will be used
    'ai_threshold' => 70,
    
    // Enable/disable AI features
    'ai_enabled' => " . var_export($aiEnabled, true) . ",
];
";
    
    if (file_put_contents($configFile, $configContent)) {
        $message = 'AI configuration updated successfully.';
        $messageType = 'success';
    } else {
        $message = 'Failed to update AI configuration. Check file permissions.';
        $messageType = 'error';
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $message = 'All password fields are required.';
        $messageType = 'error';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'New passwords do not match.';
        $messageType = 'error';
    } elseif (strlen($newPassword) < 8) {
        $message = 'New password must be at least 8 characters long.';
        $messageType = 'error';
    } else {
        if (password_verify($currentPassword, $devConfig['password'])) {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $configContent = file_get_contents($devConfigFile);
            $configContent = preg_replace(
                "/'password' => '[^']*',/",
                "'password' => '{$newHash}',",
                $configContent
            );
            
            if (file_put_contents($devConfigFile, $configContent)) {
                $message = 'Password changed successfully.';
                $messageType = 'success';
                $devConfig = require $devConfigFile;
            } else {
                $message = 'Failed to update password. Check file permissions.';
                $messageType = 'error';
            }
        } else {
            $message = 'Current password is incorrect.';
            $messageType = 'error';
        }
    }
}

?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-xl p-8 mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">
                    <i class="fas fa-cog mr-3"></i>
                    Developer Settings
                </h1>
                <p class="text-indigo-100 text-lg">Configure developer tools and preferences</p>
            </div>
            <div class="bg-white/20 rounded-full px-6 py-3 backdrop-blur-sm">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-user-shield"></i>
                    <span class="font-semibold">Developer Access</span>
                </div>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
    <div class="bg-<?= $messageType === 'success' ? 'green' : 'red' ?>-100 border-l-4 border-<?= $messageType === 'success' ? 'green' : 'red' ?>-500 text-<?= $messageType === 'success' ? 'green' : 'red' ?>-700 p-4 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?> mr-2 text-xl"></i>
            <span class="font-semibold"><?= escape($message) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Change Password -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border-2 border-indigo-200">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-6 text-white">
                <h2 class="text-2xl font-bold">
                    <i class="fas fa-key mr-2"></i>
                    Change Developer Password
                </h2>
            </div>
            <div class="p-6">
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div>
                        <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-lock text-indigo-600 mr-1"></i> Current Password
                        </label>
                        <input type="password" id="current_password" name="current_password" required
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    </div>
                    
                    <div>
                        <label for="new_password" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-key text-indigo-600 mr-1"></i> New Password
                        </label>
                        <input type="password" id="new_password" name="new_password" required minlength="8"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                    </div>
                    
                    <div>
                        <label for="confirm_password" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-check-circle text-indigo-600 mr-1"></i> Confirm New Password
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white py-4 rounded-lg font-bold text-lg hover:from-indigo-600 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i class="fas fa-save mr-2"></i>
                        Change Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Developer Info -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border-2 border-gray-200">
            <div class="bg-gradient-to-r from-gray-700 to-gray-900 p-6 text-white">
                <h2 class="text-2xl font-bold">
                    <i class="fas fa-user mr-2"></i>
                    Developer Information
                </h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-user text-gray-400 mr-3"></i>
                            <span class="text-sm font-medium text-gray-700">Username</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-900"><?= escape($devConfig['username'] ?? 'developer') ?></span>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-id-card text-gray-400 mr-3"></i>
                            <span class="text-sm font-medium text-gray-700">Name</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-900"><?= escape($devConfig['name'] ?? 'Project Developer') ?></span>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-envelope text-gray-400 mr-3"></i>
                            <span class="text-sm font-medium text-gray-700">Email</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-900"><?= escape($devConfig['email'] ?? 'developer@s3vgroup.com') ?></span>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-clock text-gray-400 mr-3"></i>
                            <span class="text-sm font-medium text-gray-700">Session Lifetime</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-900"><?= ($devConfig['session_lifetime'] ?? 86400) / 3600 ?> hours</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-shield-alt text-gray-400 mr-3"></i>
                            <span class="text-sm font-medium text-gray-700">Max Login Attempts</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-900"><?= $devConfig['max_login_attempts'] ?? 5 ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Configuration for Smart Import -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border-2 border-purple-200 mb-6">
        <div class="bg-gradient-to-r from-purple-500 via-pink-500 to-purple-600 p-6 text-white">
            <h2 class="text-2xl font-bold">
                <i class="fas fa-magic mr-2"></i>
                AI Configuration (Smart Import)
            </h2>
            <p class="text-purple-100 text-sm mt-2">Configure AI API keys for smart product extraction from any website</p>
        </div>
        <div class="p-6">
            <form method="POST" class="space-y-4">
                <input type="hidden" name="update_ai_config" value="1">
                
                <div>
                    <label for="ai_provider" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-robot text-purple-600 mr-1"></i> AI Provider
                    </label>
                    <select name="ai_provider" id="ai_provider" 
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="openai" <?= ($smartImporterConfig['ai_provider'] ?? 'openai') === 'openai' ? 'selected' : '' ?>>OpenAI (GPT-4o-mini)</option>
                        <option value="anthropic" <?= ($smartImporterConfig['ai_provider'] ?? '') === 'anthropic' ? 'selected' : '' ?>>Anthropic (Claude)</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        Choose your AI provider. OpenAI is recommended for better accuracy.
                    </p>
                </div>
                
                <div>
                    <label for="ai_api_key" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-key text-purple-600 mr-1"></i> AI API Key
                    </label>
                    <input type="password" 
                           id="ai_api_key" 
                           name="ai_api_key" 
                           value="<?= escape($smartImporterConfig['ai_api_key'] ?? '') ?>"
                           placeholder="sk-..." 
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <p class="text-xs text-gray-500 mt-1">
                        Get your API key from: 
                        <a href="https://platform.openai.com/api-keys" target="_blank" class="text-blue-600 hover:underline">OpenAI</a> or 
                        <a href="https://console.anthropic.com/" target="_blank" class="text-blue-600 hover:underline">Anthropic</a>.
                        Leave empty to use pattern recognition only (free).
                    </p>
                </div>
                
                <div>
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500" 
                               id="ai_enabled" 
                               name="ai_enabled" 
                               <?= ($smartImporterConfig['ai_enabled'] ?? true) ? 'checked' : '' ?>>
                        <span class="ml-3 text-sm font-medium text-gray-700">Enable AI features</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1 ml-8">
                        When disabled, only pattern recognition will be used (free, but less accurate).
                    </p>
                </div>
                
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-600 mr-2 mt-1"></i>
                        <div class="text-sm text-blue-800">
                            <strong>How it works:</strong>
                            <ul class="list-disc list-inside mt-2 space-y-1">
                                <li><strong>Pattern Recognition</strong> (Free): Extracts data using Schema.org, Open Graph, and HTML patterns</li>
                                <li><strong>AI Extraction</strong> (Requires API key): Uses AI to intelligently analyze page structure</li>
                                <li><strong>Hybrid Mode</strong>: Tries pattern first, uses AI if confidence is low</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-purple-500 to-pink-600 text-white py-4 rounded-lg font-bold text-lg hover:from-purple-600 hover:to-pink-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                    <i class="fas fa-save mr-2"></i>
                    Save AI Configuration
                </button>
            </form>
        </div>
    </div>

    <!-- Deployment Configuration -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 p-6 text-white">
            <h2 class="text-2xl font-bold">
                <i class="fas fa-rocket mr-2"></i>
                Deployment Configuration
            </h2>
        </div>
        <div class="p-6">
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
                <p class="text-sm text-gray-700">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    Deployment configuration is stored in <code class="bg-gray-100 px-2 py-1 rounded">deploy-config.json</code>. 
                    Edit this file directly to change deployment settings.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Current Settings</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">
                                <i class="fas fa-code-branch text-gray-400 mr-2"></i>Git Enabled
                            </span>
                            <span class="text-sm font-semibold <?= ($config['git']['enabled'] ?? false) ? 'text-green-600' : 'text-gray-600' ?>">
                                <?= ($config['git']['enabled'] ?? false) ? 'Yes' : 'No' ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">
                                <i class="fas fa-cloud text-gray-400 mr-2"></i>FTP Enabled
                            </span>
                            <span class="text-sm font-semibold <?= ($config['ftp']['enabled'] ?? false) ? 'text-green-600' : 'text-gray-600' ?>">
                                <?= ($config['ftp']['enabled'] ?? false) ? 'Yes' : 'No' ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">
                                <i class="fas fa-download text-gray-400 mr-2"></i>Auto Pull
                            </span>
                            <span class="text-sm font-semibold <?= ($config['database_sync']['auto_pull_before_deploy'] ?? false) ? 'text-green-600' : 'text-gray-600' ?>">
                                <?= ($config['database_sync']['auto_pull_before_deploy'] ?? false) ? 'Yes' : 'No' ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">
                                <i class="fas fa-upload text-gray-400 mr-2"></i>Auto Import
                            </span>
                            <span class="text-sm font-semibold <?= ($config['database_import']['auto_import'] ?? false) ? 'text-green-600' : 'text-gray-600' ?>">
                                <?= ($config['database_import']['auto_import'] ?? false) ? 'Yes' : 'No' ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Remote Server</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">
                                <i class="fas fa-globe text-gray-400 mr-2"></i>FTP Host
                            </span>
                            <span class="text-sm font-semibold text-gray-900"><?= escape($config['ftp']['host'] ?? 'Not set') ?></span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">
                                <i class="fas fa-folder text-gray-400 mr-2"></i>Remote Path
                            </span>
                            <span class="text-sm font-semibold text-gray-900"><?= escape($config['ftp']['remote_path'] ?? 'Not set') ?></span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">
                                <i class="fas fa-database text-gray-400 mr-2"></i>Database
                            </span>
                            <span class="text-sm font-semibold text-gray-900"><?= escape($config['database_remote']['dbname'] ?? 'Not set') ?></span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">
                                <i class="fas fa-link text-gray-400 mr-2"></i>Website URL
                            </span>
                            <span class="text-sm font-semibold text-gray-900"><?= escape($config['website_url'] ?? 'Not set') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
