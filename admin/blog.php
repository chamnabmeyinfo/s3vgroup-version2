<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Manage Blog Posts';
include __DIR__ . '/includes/header.php';

// Get all blog posts
$posts = db()->fetchAll("SELECT * FROM blog_posts ORDER BY created_at DESC");
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Blog Posts</h1>
        <a href="blog-edit.php" class="btn-primary">
            <i class="fas fa-plus mr-2"></i> Add New Post
        </a>
    </div>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Views</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Published</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($posts)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No blog posts found. <a href="blog-edit.php" class="text-blue-600 hover:underline">Add your first post</a></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium"><?= escape($post['title']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?= escape($post['category'] ?? '-') ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?= escape($post['view_count']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?= $post['published_at'] ? date('M d, Y', strtotime($post['published_at'])) : '-' ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded <?= $post['is_published'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                <?= $post['is_published'] ? 'Published' : 'Draft' ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            <a href="blog-edit.php?id=<?= $post['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
                            <?php if ($post['is_published']): ?>
                                <a href="<?= url('blog-post.php?slug=' . escape($post['slug'])) ?>" target="_blank" class="text-green-600 hover:underline">View</a>
                            <?php endif; ?>
                            <a href="blog-delete.php?id=<?= $post['id'] ?>" onclick="return confirm('Delete this post?')" class="text-red-600 hover:underline">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

