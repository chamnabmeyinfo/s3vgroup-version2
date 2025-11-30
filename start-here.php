<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Forklift & Equipment Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-2xl w-full bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-4xl font-bold mb-6 text-center text-blue-600">🚀 Welcome!</h1>
            <p class="text-xl text-center mb-8">Your Forklift & Equipment website is ready to set up.</p>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                <h2 class="font-bold mb-4 text-lg">Quick Start Steps:</h2>
                <ol class="list-decimal list-inside space-y-2">
                    <li>First, test if PHP is working: <a href="hello.php" class="text-blue-600 underline">Test PHP</a></li>
                    <li>Run the setup to create database: <a href="setup.php" class="text-blue-600 underline font-bold">Run Setup Now</a></li>
                    <li>After setup, visit homepage: <a href="index.php" class="text-blue-600 underline">Homepage</a></li>
                </ol>
            </div>
            
            <div class="grid md:grid-cols-2 gap-4 mb-6">
                <div class="border rounded-lg p-4">
                    <h3 class="font-bold mb-2">🔧 Setup</h3>
                    <p class="text-sm text-gray-600 mb-3">Create database and tables</p>
                    <a href="setup.php" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Run Setup →
                    </a>
                </div>
                
                <div class="border rounded-lg p-4">
                    <h3 class="font-bold mb-2">✅ Test PHP</h3>
                    <p class="text-sm text-gray-600 mb-3">Verify PHP is working</p>
                    <a href="hello.php" class="inline-block bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        Test PHP →
                    </a>
                </div>
            </div>
            
            <?php
            // Try to check if database exists
            try {
                require_once __DIR__ . '/bootstrap/app.php';
                $db = db();
                $result = $db->fetchOne("SELECT 1");
                echo '<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">';
                echo '<p class="text-green-800">✓ Database connection successful! <a href="index.php" class="underline font-bold">Go to Homepage</a></p>';
                echo '</div>';
            } catch (Exception $e) {
                echo '<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">';
                echo '<p class="text-yellow-800">⚠ Database not set up yet. Please <a href="setup.php" class="underline font-bold">run setup</a> first.</p>';
                echo '</div>';
            }
            ?>
            
            <div class="text-center text-sm text-gray-600">
                <p>Having issues? Check <a href="TROUBLESHOOTING.md" class="text-blue-600 underline">TROUBLESHOOTING.md</a></p>
            </div>
        </div>
    </div>
</body>
</html>

